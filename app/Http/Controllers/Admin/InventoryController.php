<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductInventory;
use App\Models\RawMaterial;
use App\Models\RestockBatch;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    public function index()
    {
        $inventories = ProductInventory::with('product')->orderBy('stock', 'asc')->get();
        $pendingBatches = RestockBatch::where('status', 'pending')->latest()->get();
        $masterProducts = Product::orderBy('name')->get();

        return view('admin.inventory.index', compact('inventories', 'pendingBatches', 'masterProducts'));
    }

    public function printRestock(Request $request)
    {
        $items = $request->items ?? [];

        $invoiceNumber = 'PO-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        RestockBatch::create([
            'invoice_number' => $invoiceNumber,
            'items_data' => $items,
            'status' => 'pending'
        ]);

        return view('admin.inventory.print', compact('items', 'invoiceNumber'));
    }

    public function applyRestock(Request $request)
    {
        $request->validate([
            'batch_id'          => 'required|exists:restock_batches,id',
            'total_pengeluaran' => 'required|numeric|min:0',
            'struk'             => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'total_pengeluaran.required' => 'Total pengeluaran wajib diisi.',
            'struk.required'             => 'Bukti struk/nota wajib diupload.',
            'struk.mimes'                => 'Struk harus berupa gambar (jpg/png) atau PDF.',
        ]);

        $batch = RestockBatch::findOrFail($request->batch_id);

        if ($batch->status === 'selesai') {
            return redirect()->back()->with('error', 'Batch ini sudah pernah diapply sebelumnya.');
        }

        // Ambil ID valid dari batch asli untuk mencegah item asing masuk
        $batchItemIds = collect($batch->items_data ?? [])->pluck('id')->filter()->map(fn($id) => (string) $id)->toArray();

        $actualItems = $request->items ?? [];

        foreach ($actualItems as $item) {
            if (empty($item['name']) || empty($item['id']))
                continue;

            // Validasi item harus ada di batch asli
            if (!in_array((string) $item['id'], $batchItemIds)) {
                continue;
            }

            $qtyAdded = (int) $item['qty'];
            if ($qtyAdded <= 0) continue;

            $inv = ProductInventory::find($item['id']);
            if ($inv)
                $inv->increment('stock', $qtyAdded);
        }

        $strukRelativePath = $request->file('struk')->store('struk-belanja', 'public');

        $batch->update([
            'status'            => 'selesai',
            'items_data'        => $actualItems,
            'total_pengeluaran' => $request->total_pengeluaran,
            'struk_path'        => $strukRelativePath,
            'verified_by'       => auth('api')->id(),
        ]);

        return redirect()->back()->with('success', 'Barang berhasil masuk gudang dan pengeluaran tercatat!');
    }

    public function storeApparel(Request $request)
    {
        $request->validate([
            'product_id'   => 'required|exists:products,id',
            'sku'          => 'required|unique:product_inventories,sku',
            'variant_name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('product_inventories')
                    ->where('product_id', $request->product_id),
            ],
            'stock'      => 'required|integer|min:0|max:999999',
            'harga_jual' => 'nullable|numeric|min:0|max:999999',
            'image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'product_id.required'   => 'Produk induk wajib dipilih.',
            'product_id.exists'     => 'Produk tidak valid.',
            'sku.required'          => 'Kode SKU wajib diisi.',
            'sku.unique'            => 'Kode SKU sudah dipakai, gunakan kode lain.',
            'variant_name.required' => 'Spesifikasi varian wajib diisi.',
            'variant_name.unique'   => 'Nama varian sudah ada untuk produk ini.',
            'stock.required'        => 'Stok awal wajib diisi.',
            'stock.min'             => 'Stok tidak boleh negatif.',
            'stock.max'             => 'Stok maksimal adalah 999.999.',
            'harga_jual.max'        => 'Harga maksimal di bawah 7 digit (Rp 999.999).',
            'image.image'           => 'File harus berupa gambar.',
            'image.max'             => 'Ukuran gambar maksimal 2MB.',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('inventory', 'public');
        }

        ProductInventory::create([
            'product_id' => $request->product_id,
            'sku' => strtoupper($request->sku),
            'variant_name' => $request->variant_name,
            'stock' => $request->stock,
            'harga_jual' => $request->harga_jual ?? 0,
            'image_path' => $imagePath ?: null,
        ]);

        return redirect()->back()->with('success', 'Barang baru berhasil ditambahkan ke gudang!');
    }

    public function updateStock(Request $request)
    {
        $request->validate([
            'item_id'    => 'required',
            'type'       => 'required|in:apparel,raw',
            'new_stock'  => 'required|numeric|min:0|max:999999',
            'harga_jual' => 'nullable|numeric|min:0|max:999999',
        ], [
            'item_id.required'   => 'ID barang tidak ditemukan.',
            'type.required'      => 'Tipe barang wajib diisi.',
            'new_stock.required' => 'Jumlah stok baru wajib diisi.',
            'new_stock.min'      => 'Stok tidak boleh negatif.',
            'new_stock.max'      => 'Stok maksimal adalah 999.999.',
            'harga_jual.max'     => 'Harga maksimal di bawah 7 digit (Rp 999.999).',
        ]);

        if ($request->type === 'apparel') {
            $item = ProductInventory::findOrFail($request->item_id);
            $item->update([
                'stock'      => $request->new_stock,
                'harga_jual' => $request->harga_jual ?? $item->harga_jual,
            ]);
        } elseif ($request->type === 'raw') {
            $item = RawMaterial::findOrFail($request->item_id);
            $item->update(['stock' => $request->new_stock]);
        }

        return response()->json(['message' => 'Stok berhasil diperbarui!']);
    }

    public function destroyApparel($id)
    {
        $item = ProductInventory::findOrFail($id);

        $hasOrderHistory = \App\Models\OrderItem::where('product_inventory_id', $item->id)->exists();

        if ($hasOrderHistory) {
            return response()->json([
                'message' => 'Varian ini tidak bisa dihapus karena sudah pernah dipakai pada pesanan. Hapus hanya bisa dilakukan untuk varian yang belum pernah dipesan.',
            ], 422);
        }

        $item->delete();

        return response()->json([
            'message' => 'Varian berhasil dihapus dari gudang.',
        ]);
    }
}
