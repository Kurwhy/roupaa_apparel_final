<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderChat;
use App\Models\Pelanggan;
use App\Models\ProductInventory;
use App\Models\RawMaterial;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function getDashboardData()
    {
        $stats = [
            'diskusi_desain'       => Order::where('status', 'diskusi_desain')->count(),
            'menunggu_spesifikasi' => Order::where('status', 'menunggu_spesifikasi')->count(),
            'menunggu_estimasi'    => Order::where('status', 'menunggu_estimasi')->count(),
            'menunggu_pembayaran'  => Order::where('status', 'menunggu_pembayaran')->count(),
            'diproses'             => Order::where('status', 'diproses')->count(),
            'siap_diambil'         => Order::where('status', 'siap_diambil')->count(),
            'selesai'              => Order::where('status', 'selesai')->count(),
            'total'                => Order::count(),
        ];

        $totalCustomers = Pelanggan::count();

        $unreadChats = OrderChat::whereNull('read_at')
            ->where('is_system_message', false)
            ->whereHas('user', function ($q) {
                $q->where('role', 'pelanggan');
            })
            ->count();

        $newOrderIds = Order::where('status', 'diskusi_desain')
            ->whereDoesntHave('chats', function ($q) {
                $q->where('is_system_message', false)
                    ->whereHas('user', function ($u) {
                        $u->whereIn('role', ['admin', 'owner']);
                    });
            })
            ->pluck('id');

        $newOrders = Order::with('pelanggan')
            ->whereIn('id', $newOrderIds)
            ->latest()
            ->get()
            ->map(function ($order) {
                return [
                    'id'           => $order->id,
                    'order_number' => $order->order_number,
                    'project_name' => $order->project_name,
                    'created_at'   => $order->created_at->diffForHumans(),
                    'pelanggan'    => [
                        'nama_lengkap'        => $order->pelanggan->nama_lengkap ?? '-',
                        'nama_instansi_brand' => $order->pelanggan->nama_instansi_brand ?? null,
                    ],
                ];
            });

        $latestOrders = Order::with('pelanggan')
            ->withCount(['chats as unread_chats' => function ($q) {
                $q->whereNull('read_at')
                    ->where('is_system_message', false)
                    ->whereHas('user', fn($u) => $u->where('role', 'pelanggan'));
            }])
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($order) => [
                'id'              => $order->id,
                'order_number'    => $order->order_number,
                'project_name'    => $order->project_name,
                'status'          => $order->status,
                'status_label'    => $order->status_label,
                'payment_status'  => $order->payment_status,
                'final_price'     => $order->final_price,
                'created_at'      => $order->created_at->format('d M Y, H:i'),
                'unread_chats'    => $order->unread_chats,
                'pelanggan'       => [
                    'nama_lengkap'        => $order->pelanggan->nama_lengkap ?? '-',
                    'nama_instansi_brand' => $order->pelanggan->nama_instansi_brand ?? null,
                ],
            ]);

        $actionNeeded = [];

        $waitingApproval = Order::where('status', 'diskusi_desain')
            ->whereNotNull('final_mockup_path')
            ->where('is_design_approved', false)
            ->count();
        if ($waitingApproval > 0) {
            $actionNeeded[] = [
                'type' => 'warning',
                'icon' => 'hourglass_top',
                'color' => 'yellow',
                'title' => "{$waitingApproval} pesanan menunggu ACC customer",
                'message' => 'Mockup sudah dikirim, menunggu persetujuan pelanggan.',
            ];
        }

        $approvedNotContinued = Order::where('status', 'diskusi_desain')
            ->where('is_design_approved', true)->count();
        if ($approvedNotContinued > 0) {
            $actionNeeded[] = [
                'type' => 'urgent',
                'icon' => 'priority_high',
                'color' => 'red',
                'title' => "{$approvedNotContinued} pesanan siap dilanjutkan",
                'message' => 'Customer sudah ACC desain. Segera klik "Lanjutkan Pesanan".',
            ];
        }

        $dpPending = Order::where('payment_status', 'dp_paid')
            ->whereIn('status', ['diproses', 'siap_diambil'])->count();
        if ($dpPending > 0) {
            $actionNeeded[] = [
                'type' => 'info',
                'icon' => 'payments',
                'color' => 'yellow',
                'title' => "{$dpPending} pesanan menunggu pelunasan",
                'message' => 'Customer sudah bayar DP, sisa pembayaran belum dilunasi.',
            ];
        }

        $readyToDeliver = Order::where('status', 'siap_diambil')
            ->where('payment_status', 'paid')->count();
        if ($readyToDeliver > 0) {
            $actionNeeded[] = [
                'type' => 'success',
                'icon' => 'local_shipping',
                'color' => 'green',
                'title' => "{$readyToDeliver} pesanan siap diserahkan",
                'message' => 'Produksi selesai & lunas. Tandai selesai setelah diserahkan.',
            ];
        }

        $lowStockApparel = ProductInventory::where('stock', '<', 20)
            ->with('product:id,name')
            ->get()
            ->map(fn($inv) => [
                'name'  => ($inv->product->name ?? '') . ' - ' . $inv->variant_name,
                'stock' => $inv->stock,
                'unit'  => 'Pcs',
            ]);

        $lowStockRaw = collect();
        try {
            $lowStockRaw = RawMaterial::whereColumn('stock', '<=', 'min_stock_alert')
                ->get()
                ->map(fn($raw) => [
                    'name' => $raw->name,
                    'stock' => $raw->stock,
                    'unit' => $raw->unit,
                ]);
        } catch (\Exception $e) {
        }

        $lowStockItems = $lowStockApparel->merge($lowStockRaw);
        if ($lowStockItems->count() > 0) {
            $actionNeeded[] = [
                'type' => 'warning',
                'icon' => 'inventory',
                'color' => 'red',
                'title' => "{$lowStockItems->count()} barang stok menipis",
                'message' => 'Segera lakukan restock.',
                'items' => $lowStockItems->values(),
            ];
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'stats'           => $stats,
                'total_customers' => $totalCustomers,
                'unread_chats'    => $unreadChats,
                'new_orders'      => $newOrders,
                'latest_orders'   => $latestOrders,
                'action_needed'   => $actionNeeded,
            ],
        ]);
    }
}
