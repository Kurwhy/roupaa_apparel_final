<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemSpec;
use App\Models\ProductInventory;
use App\Models\AdminNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\OrderStateUpdated;

class OrderSpecificationController extends Controller
{
    public function store(Request $request, $orderId)
    {
        $user  = auth('api')->user();

        // Early ownership check (tanpa lock, untuk fast reject)
        $order = Order::findOrFail($orderId);
        if ($order->pelanggan_id !== $user->pelanggan->id) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'items'                        => 'required|array|min:1',
            'items.*.qty'                  => 'required|integer|min:1',
            'items.*.product_inventory_id' => 'required|exists:product_inventories,id',
            'items.*.specs'                => 'nullable|array',
        ], [
            'items.required'                        => 'Spesifikasi pesanan wajib diisi.',
            'items.min'                             => 'Minimal pilih satu varian produk.',
            'items.*.qty.required'                  => 'Jumlah pesanan wajib diisi.',
            'items.*.qty.integer'                   => 'Jumlah pesanan harus berupa angka.',
            'items.*.qty.min'                       => 'Jumlah pesanan minimal 1.',
            'items.*.product_inventory_id.required' => 'Varian produk wajib dipilih.',
            'items.*.product_inventory_id.exists'   => 'Varian produk tidak valid.',
        ]);
        try {
            DB::beginTransaction();

            // Kunci dan re-cek status di dalam transaction untuk mencegah race condition
            $order = Order::with('product')->lockForUpdate()->findOrFail($orderId);

            if ($order->pelanggan_id !== $user->pelanggan->id) {
                DB::rollBack();
                return response()->json(['error' => 'Akses ditolak.'], 403);
            }

            if (!in_array($order->status, ['menunggu_spesifikasi', 'menunggu_estimasi'])) {
                DB::rollBack();
                return response()->json(['error' => 'Status order tidak memungkinkan pengisian spesifikasi.'], 422);
            }

            $oldItemIds = $order->items()->pluck('id');
            if ($oldItemIds->isNotEmpty()) {
                OrderItemSpec::whereIn('order_item_id', $oldItemIds)->delete();
                OrderItem::whereIn('id', $oldItemIds)->delete();
            }

            $totalQty       = 0;
            $estimatedPrice = 0;

            foreach ($request->items as $itemData) {
                $qty         = (int) $itemData['qty'];
                $inventoryId = $itemData['product_inventory_id'];

                $inventory = ProductInventory::findOrFail($inventoryId);

                if ($inventory->product_id !== $order->product_id) {
                    DB::rollBack();
                    return response()->json([
                        'error' => "Varian '{$inventory->variant_name}' tidak sesuai dengan produk pesanan ini.",
                    ], 422);
                }

                if ($inventory->harga_jual <= 0) {
                    DB::rollback();
                    return response()->json([
                        'error' => "Harga untuk varian '{$inventory->variant_name}' belum ditentukan. Hubungi admin."
                    ], 422);
                }

                $biayaSablon = (float) $order->biaya_sablon;
                $unitPrice   = (float) $inventory->harga_jual + $biayaSablon;
                $subtotal    = $unitPrice * $qty;

                $orderItem = OrderItem::create([
                    'order_id'             => $order->id,
                    'product_inventory_id' => $inventoryId,
                    'qty'                  => $qty,
                    'unit_price'           => $unitPrice,
                    'subtotal'             => $subtotal,
                ]);

                $specValueIds = array_filter($itemData['specs'] ?? []);
                foreach ($specValueIds as $attributeId => $valueId) {
                    OrderItemSpec::create([
                        'order_item_id'             => $orderItem->id,
                        'product_attribute_id'       => $attributeId,
                        'product_attribute_value_id' => $valueId,
                    ]);
                }

                $totalQty       += $qty;
                $estimatedPrice += $subtotal;
            }

            $order->update([
                'status'          => 'menunggu_estimasi',
                'total_quantity'  => $totalQty,
                'final_price' => $estimatedPrice,
            ]);

            $order->chats()->create([
                'user_id'           => $user->id,
                'message'           => sprintf(
                    'SISTEM: Pelanggan telah melengkapi spesifikasi. Total: %d pcs | Estimasi harga: Rp %s. Menunggu konfirmasi harga dari Admin.',
                    $totalQty,
                    number_format($estimatedPrice, 0, ',', '.')
                ),
                'is_system_message' => true,
            ]);

            DB::commit();

            event(new OrderStateUpdated($order->id, 'spec_submitted'));

            $order->load('pelanggan');
            AdminNotification::send(
                'spec_submitted',
                'Spesifikasi Dikirim',
                "{$order->pelanggan->nama_lengkap} mengisi spesifikasi pesanan \"{$order->project_name}\".",
                ['order_id' => $order->id]
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Spesifikasi berhasil disimpan! Tim kami akan segera mengkonfirmasi harga.',
                'data'    => [
                    'total_qty'       => $totalQty,
                    'final_price' => $estimatedPrice,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('[Order Spec] Gagal simpan spesifikasi', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Gagal menyimpan spesifikasi: ' . $e->getMessage()], 500);
        }
    }
}
