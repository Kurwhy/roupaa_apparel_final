<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\CustomerNotification;
use App\Events\OrderStateUpdated;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderNotificationMail;
use App\Mail\OrderCompletedMail;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('pelanggan')->latest();

        if ($request->has('search') && $request->search != '') {
            $search = '%' . addcslashes($request->search, '%_\\') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', $search)
                    ->orWhere('project_name', 'like', $search)
                    ->orWhereHas('pelanggan', function ($sq) use ($search) {
                        $sq->where('nama_lengkap', 'like', $search)
                            ->orWhere('nama_instansi_brand', 'like', $search);
                    });
            });
        }

        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(10)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function getData(Request $request)
    {
        $orders = Order::with(['pelanggan', 'product'])->latest()->get();

        return response()->json([
            'orders' => $orders->map(fn($o) => [
                'id'           => $o->id,
                'order_number' => $o->order_number,
                'tanggal'      => $o->created_at->translatedFormat('d M Y'),
                'pelanggan'    => $o->pelanggan->nama_lengkap ?? '-',
                'instansi'     => $o->pelanggan->nama_instansi_brand ?? 'Personal Order',
                'project_name' => $o->project_name,
                'product_name' => $o->product->name ?? 'Produk',
                'status'       => $o->status,
                'show_url'     => route('ops.orders.show', $o->id),
            ]),
        ]);
    }

    public function show($id)
    {
        $order = Order::with(['pelanggan', 'items.productInventory', 'chats.user.pelanggan'])
            ->findOrFail($id);

        $initialChats = $order->chats->map(function ($c) {
            return [
                'id'                => $c->id,
                'user_id'           => $c->user_id,
                'is_system_message' => $c->is_system_message,
                'message'           => $c->message,
                'attachment_path'   => $c->attachment_path,
                'read_at'           => $c->read_at ? \Carbon\Carbon::parse($c->read_at)->toISOString() : null,
                'created_at'        => $c->created_at ? \Carbon\Carbon::parse($c->created_at)->toISOString() : null,
                'user'              => $c->user ? ['id' => $c->user->id, 'role' => $c->user->role] : null,
            ];
        })->values();

        return view('admin.orders.show', compact('order', 'initialChats'));
    }

    public function uploadMockup(Request $request, $id)
    {
        $request->validate([
            'mockup_files'   => 'required|array',
            'mockup_files.*' => 'image|mimes:jpeg,png,jpg,webp|max:20480',
            'biaya_sablon'   => 'nullable|numeric|min:0',
        ]);

        $order = Order::findOrFail($id);
        $order->load('pelanggan');
        $pelangganUserId = $order->pelanggan->user_id ?? null;

        $paths = [];
        foreach ($request->file('mockup_files') as $file) {
            $filename = 'mockup_' . $order->order_number . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $paths[]  = $file->storeAs('mockups', $filename, 'public');
        }

        $order->update([
            'final_mockup_path' => $paths,
            'biaya_sablon'      => (float) $request->input('biaya_sablon', 0),
        ]);

        event(new OrderStateUpdated($order->id, 'mockup_updated'));

        if ($pelangganUserId) {
            CustomerNotification::send(
                userId: $pelangganUserId,
                type: 'mockup_uploaded',
                title: 'Mockup Desain Tersedia',
                message: 'Admin telah mengunggah mockup untuk pesanan #' . $order->order_number . '. Silakan tinjau.',
                extra: ['order_id' => $order->id],
            );

            if ($order->pelanggan->user?->email) {
                Mail::to($order->pelanggan->user->email)->send(new OrderNotificationMail(
                    $order,
                    'Mockup Desain Tersedia',
                    'Admin telah mengunggah mockup desain untuk pesanan "' . $order->project_name . '". Silakan tinjau dan berikan persetujuan melalui halaman pesanan.'
                ));
            }
        }

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Mockup Final berhasil diunggah!');
    }

    public function revisiMockup($id)
    {
        $order = Order::findOrFail($id);

        $mockups = $order->final_mockup_path ?? [];

        foreach ($mockups as $mockup) {
            if ($mockup && Storage::disk('public')->exists($mockup)) {
                Storage::disk('public')->delete($mockup);
            }
        }

        $order->update([
            'final_mockup_path'  => null,
            'is_design_approved' => false,
            'biaya_sablon'       => 0,
            'status'             => 'diskusi_desain',
        ]);

        event(new OrderStateUpdated($order->id, 'mockup_revised'));

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Mockup dihapus dan status dikembalikan ke tahap desain.');
    }

    public function lanjutkanPesanan($id)
    {
        $order = Order::findOrFail($id);
        $order->load('pelanggan');
        $pelangganUserId = $order->pelanggan->user_id ?? null;

        $order->update(['status' => 'menunggu_spesifikasi']);

        event(new OrderStateUpdated($order->id, 'order_continued'));

        if ($pelangganUserId) {
            CustomerNotification::send(
                userId: $pelangganUserId,
                type: 'status_update',
                title: 'Pesanan Dilanjutkan',
                message: 'Silakan isi spesifikasi untuk pesanan #' . $order->order_number,
                extra: ['order_id' => $order->id],
            );
        }

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Pesanan dilanjutkan! Customer sekarang bisa mengisi spesifikasi.');
    }

    public function setFinalPrice(Request $request, $id)
    {
        $order = Order::with(['items.productInventory', 'pelanggan'])->findOrFail($id);
        $pelangganUserId = $order->pelanggan->user_id ?? null;

        if ($order->status !== 'menunggu_estimasi') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Pesanan hanya bisa dikonfirmasi saat status menunggu estimasi.',
                ], 422);
            }
            return redirect()->back()->with('error', 'Pesanan hanya bisa dikonfirmasi saat status menunggu estimasi.');
        }

        $order->update(['status' => 'menunggu_pembayaran']);

        $order->chats()->create([
            'user_id'           => auth('api')->id(),
            'message'           => sprintf(
                'SISTEM: Admin telah mengonfirmasi pesanan senilai Rp %s. Silakan lakukan pembayaran.',
                number_format((float) $order->final_price, 0, ',', '.')
            ),
            'is_system_message' => true,
        ]);

        event(new OrderStateUpdated($order->id, 'price_set'));

        if ($pelangganUserId) {
            CustomerNotification::send(
                userId: $pelangganUserId,
                type: 'payment_required',
                title: 'Pesanan Dikonfirmasi',
                message: 'Silakan lakukan pembayaran untuk pesanan #' . $order->order_number,
                extra: ['order_id' => $order->id],
            );

            if ($order->pelanggan->user?->email) {
                Mail::to($order->pelanggan->user->email)->send(new OrderNotificationMail(
                    $order,
                    'Pesanan Dikonfirmasi',
                    'Pesanan "' . $order->project_name . '" telah dikonfirmasi admin. Silakan lakukan pembayaran melalui halaman pesanan.'
                ));
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Pesanan berhasil dikonfirmasi.',
            ]);
        }

        return redirect()->back()->with('success', 'Pesanan berhasil dikonfirmasi. Pelanggan akan diarahkan untuk membayar.');
    }

    public function markComplete(Request $request, $id)
    {
        $request->validate([
            'production_photos'   => 'required|array|min:1|max:10',
            'production_photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'production_notes'    => 'nullable|string|max:1000',
        ]);

        $order = Order::with('pelanggan')->findOrFail($id);
        $pelangganUserId = $order->pelanggan->user_id ?? null;

        if ($order->status !== 'diproses') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Hanya pesanan yang sedang diproses yang bisa ditandai selesai.',
            ], 422);
        }

        // Hapus foto lama dari storage sebelum menyimpan yang baru
        $oldPhotos = is_array($order->production_photo_path) ? $order->production_photo_path : [];
        foreach ($oldPhotos as $oldPath) {
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $paths = [];
        foreach ($request->file('production_photos') as $file) {
            $filename = 'production_' . $order->order_number . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $paths[] = $file->storeAs('productions', $filename, 'public');
        }

        $order->update([
            'production_photo_path' => $paths,
            'production_notes'      => $request->production_notes,
            'status'                => 'siap_diambil',
        ]);

        $deliveryMsg = $order->payment_status === 'paid'
            ? 'Pesanan siap untuk diambil di tempat atau dikirimkan ke alamat Anda.'
            : 'Pesanan sudah selesai diproduksi. Silakan lunasi sisa pembayaran agar bisa diambil/dikirim.';

        $order->chats()->create([
            'user_id'           => auth('api')->id(),
            'message'           => "SISTEM: Produksi pesanan Anda telah selesai!\n{$deliveryMsg}",
            'is_system_message' => true,
        ]);

        event(new OrderStateUpdated($order->id, 'production_complete'));

        if ($pelangganUserId) {
            CustomerNotification::send(
                userId: $pelangganUserId,
                type: 'production_complete',
                title: 'Produksi Selesai',
                message: 'Pesanan #' . $order->order_number . ' siap. Silakan cek detail pengambilan.',
                extra: ['order_id' => $order->id],
            );

            if ($order->pelanggan->user?->email) {
                Mail::to($order->pelanggan->user->email)->send(new OrderNotificationMail(
                    $order,
                    'Pesanan Siap Diambil',
                    $order->payment_status === 'paid'
                        ? 'Pesanan "' . $order->project_name . '" telah selesai diproduksi dan siap untuk diambil atau dikirimkan.'
                        : 'Pesanan "' . $order->project_name . '" telah selesai diproduksi. Silakan lunasi sisa pembayaran agar pesanan dapat diambil/dikirim.'
                ));
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Pesanan ditandai selesai produksi.']);
        }

        return redirect()->back()->with('success', 'Pesanan selesai produksi!');
    }

    public function markDelivered($id)
    {
        $order = Order::findOrFail($id);
        $order->load('pelanggan');
        $pelangganUserId = $order->pelanggan->user_id ?? null;

        if ($order->status !== 'siap_diambil') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pesanan belum siap diambil.',
            ], 422);
        }

        if ($order->payment_status !== 'paid') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pelunasan belum dilakukan. Pesanan tidak bisa ditandai selesai.',
            ], 422);
        }

        $order->update([
            'status'       => 'selesai',
            'completed_at' => now(),
        ]);

        $order->chats()->create([
            'user_id'           => auth('api')->id(),
            'message'           => 'SISTEM: Pesanan telah selesai dan diserahkan. Terima kasih telah mempercayakan pesanan Anda kepada ROUPAA Apparel!',
            'is_system_message' => true,
        ]);

        event(new OrderStateUpdated($order->id, 'order_delivered'));

        if ($pelangganUserId) {
            CustomerNotification::send(
                userId: $pelangganUserId,
                type: 'order_complete',
                title: 'Pesanan Selesai',
                message: 'Pesanan #' . $order->order_number . ' telah diserahkan. Terima kasih.',
                extra: ['order_id' => $order->id],
            );

            if ($order->pelanggan->user?->email) {
                Mail::to($order->pelanggan->user->email)->send(new OrderCompletedMail($order));
            }
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['status' => 'success']);
        }

        return redirect()->back()->with('success', 'Pesanan ditandai selesai!');
    }
}
