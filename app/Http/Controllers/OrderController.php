<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Category;
use App\Models\Product;
use App\Models\AdminNotification;
use App\Events\OrderStateUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index()
    {
        return view('customer.orders.index');
    }

    public function create()
    {
        $categories = Category::with('products.attributes.values')->get();
        return view('customer.orders.create', compact('categories'));
    }

    public function show($id)
    {
        return view('customer.orders.show', compact('id'));
    }

    public function getOrdersData()
    {
        $user = auth('api')->user();

        if (!$user || !$user->pelanggan) {
            return response()->json(['error' => 'Akses ditolak atau data pelanggan tidak valid.'], 403);
        }

        $orders = Order::with(['product'])
            ->where('pelanggan_id', $user->pelanggan->id)
            ->latest()
            ->get();

        return response()->json(['status' => 'success', 'data' => $orders]);
    }

    public function getOrderDetails($id)
    {
        $user = auth('api')->user();
        $order = Order::with(['product.attributes.values', 'product.inventories', 'items.productInventory', 'chats.user'])->findOrFail($id);

        if ($order->pelanggan_id !== $user->pelanggan->id) {
            return response()->json(['error' => 'Akses Ditolak: Ini bukan project Anda.'], 403);
        }

        return response()->json(['status' => 'success', 'data' => $order]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id'  => 'required|exists:categories,id',
            'product_id'   => [
                'required',
                'exists:products,id',
                function ($attribute, $value, $fail) use ($request) {
                    $belongs = Product::where('id', $value)
                        ->where('category_id', $request->category_id)
                        ->exists();
                    if (!$belongs) {
                        $fail('Produk yang dipilih tidak sesuai dengan kategori.');
                    }
                },
            ],
            'project_name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (mb_strlen(trim($value)) < 3) {
                        $fail('Nama project minimal 3 karakter dan tidak boleh kosong.');
                    }
                },
            ],
            'notes'        => 'nullable|string|max:1000',
            'design_file'  => 'nullable|file|mimes:jpeg,png,jpg,pdf,ai,cdr|max:10240',
        ], [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists'   => 'Kategori tidak valid.',
            'product_id.required'  => 'Produk wajib dipilih.',
            'product_id.exists'    => 'Produk tidak valid.',
            'project_name.required' => 'Nama project wajib diisi.',
            'project_name.max'     => 'Nama project maksimal 255 karakter.',
            'notes.max'            => 'Catatan maksimal 1000 karakter.',
            'design_file.mimes'    => 'File referensi harus berformat JPG, PNG, PDF, AI, atau CDR.',
            'design_file.max'      => 'Ukuran file referensi maksimal 10MB.',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            $filePath = null;
            if ($request->hasFile('design_file')) {
                $file = $request->file('design_file');
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_.\-]/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_BASENAME));
                $filePath = $file->storeAs('referensi_desain', $filename, 'public');
            }

            $user = auth('api')->user();

            do {
                $orderNumber = 'ORD-' . strtoupper(Str::random(8));
            } while (Order::where('order_number', $orderNumber)->exists());

            $order = Order::create([
                'order_number'        => $orderNumber,
                'pelanggan_id'        => $user->pelanggan->id,
                'product_id'          => $request->product_id,
                'project_name'        => trim($request->project_name),
                'status'              => 'diskusi_desain',
                'reference_file_path' => $filePath,
                'design_notes'        => $request->notes,
            ]);

            DB::commit();
            $order->load('pelanggan');
            AdminNotification::send(
                'new_order',
                'Pesanan Baru Masuk!',
                "{$order->pelanggan->nama_lengkap} membuat pesanan \"{$order->project_name}\".",
                ['order_id' => $order->id, 'from_user_id' => $user->id]
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Project berhasil diinisiasi! Silakan masuk ke Ruang Project.'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }

    public function approveDesign($id)
    {
        $user  = auth('api')->user();
        $order = Order::findOrFail($id);

        if ($order->pelanggan_id !== $user->pelanggan->id) {
            return response()->json(['error' => 'Akses Ditolak: Ini bukan project Anda.'], 403);
        }

        $order->update(['is_design_approved' => true]);
        event(new OrderStateUpdated($order->id, 'design_approved'));

        $order->load('pelanggan');
        $user = auth('api')->user();
        AdminNotification::send(
            'design_approved',
            'Desain Disetujui!',
            "{$order->pelanggan->nama_lengkap} menyetujui desain \"{$order->project_name}\". Lanjutkan pesanan.",
            ['order_id' => $order->id, 'from_user_id' => $user->id]
        );

        return response()->json(['success' => true, 'message' => 'Desain disetujui! Menunggu Admin memproses.']);
    }
}
