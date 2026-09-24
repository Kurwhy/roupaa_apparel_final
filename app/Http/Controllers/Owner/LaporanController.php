<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\RestockBatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index()
    {
        $orderYears = Order::whereIn('payment_status', ['dp_paid', 'paid'])
            ->selectRaw('YEAR(updated_at) as year')
            ->distinct()
            ->pluck('year');

        $restockYears = RestockBatch::where('status', 'selesai')
            ->selectRaw('YEAR(updated_at) as year')
            ->distinct()
            ->pluck('year');

        $availableYears = $orderYears->merge($restockYears)
            ->unique()
            ->filter()
            ->sortDesc()
            ->values()
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [now()->year];
        }

        return view('owner.laporan.index', compact('availableYears'));
    }

    public function getData(Request $request): JsonResponse
    {
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun', now()->year);

        $ordersQuery = Order::whereIn('payment_status', ['dp_paid', 'paid'])
            ->with('pelanggan')
            ->orderByDesc('updated_at');

        if ($bulan) {
            $ordersQuery->whereMonth('updated_at', $bulan)
                ->whereYear('updated_at', $tahun);
        } else {
            $ordersQuery->whereYear('updated_at', $tahun);
        }

        $orders = $ordersQuery->get();

        $totalPendapatan = $orders->sum(function ($order) {
            return $order->payment_status === 'paid'
                ? (float) ($order->final_price ?? 0)
                : (float) ($order->dp_amount ?? 0);
        });

        // Query restocks
        $restocksQuery = RestockBatch::where('status', 'selesai')
            ->with('verifiedByUser')
            ->orderByDesc('updated_at');

        if ($bulan) {
            $restocksQuery->whereMonth('updated_at', $bulan)
                ->whereYear('updated_at', $tahun);
        } else {
            $restocksQuery->whereYear('updated_at', $tahun);
        }

        $restocks = $restocksQuery->get();

        $totalPengeluaran = $restocks->sum(fn($r) => (float) ($r->total_pengeluaran ?? 0));

        $bulanLabels = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $periode = $bulan
            ? ($bulanLabels[(int) $bulan] . ' ' . $tahun)
            : ('Tahun ' . $tahun);

        return response()->json([
            'periode'           => $periode,
            'total_pendapatan'  => $totalPendapatan,
            'total_pengeluaran' => $totalPengeluaran,
            'saldo_bersih'      => $totalPendapatan - $totalPengeluaran,
            'total_transaksi'   => $orders->count(),
            'orders'  => $orders->map(fn($o) => [
                'id'              => $o->id,
                'order_number'    => $o->order_number,
                'pelanggan'       => $o->pelanggan?->nama_lengkap ?? '-',
                'project_name'    => $o->project_name,
                'payment_status'  => $o->payment_status,
                'payment_type'    => $o->payment_type,
                'dp_amount'       => (float) $o->dp_amount,
                'final_price'     => (float) ($o->final_price ?? 0),
                'jumlah_diterima' => $o->payment_status === 'paid'
                    ? (float) ($o->final_price ?? 0)
                    : (float) $o->dp_amount,
                'tanggal'         => $o->updated_at?->format('d M Y') ?? '-',
            ]),
            'restocks' => $restocks->map(fn($r) => [
                'id'                => $r->id,
                'invoice_number'    => $r->invoice_number ?? '-',
                'total_pengeluaran' => (float) ($r->total_pengeluaran ?? 0),
                'struk_url'         => $r->struk_path,
                'diverifikasi_oleh' => $r->verifiedByUser?->email ?? '-',
                'tanggal'           => $r->updated_at?->format('d M Y') ?? '-',
            ]),
        ]);
    }
}
