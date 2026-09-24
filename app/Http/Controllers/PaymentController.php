<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\ProductInventory;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey    = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized  = config('services.midtrans.is_sanitized');
        Config::$is3ds        = config('services.midtrans.is_3ds');
    }

    public function getSnapToken(Request $request, int $orderId)
    {
        $user  = auth('api')->user();
        $order = Order::with(['items.productInventory', 'pelanggan.user'])->findOrFail($orderId);

        if ($order->pelanggan_id !== $user->pelanggan->id) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'payment_type' => 'required|in:dp,lunas,pelunasan',
            'final_price'  => 'required|numeric|min:1',
        ]);

        if (!$order->final_price || $order->final_price <= 0) {
            return response()->json(['error' => 'Harga final pesanan belum ditentukan. Hubungi admin.'], 422);
        }

        $dbFinalPrice = (float) $order->final_price;
        $reqFinalPrice = (float) $request->final_price;

        if (abs($dbFinalPrice - $reqFinalPrice) > 1) {
            return response()->json(['error' => 'Harga tidak sesuai dengan data pesanan.'], 422);
        }

        $paymentType  = $request->payment_type;
        $totalHarga   = $dbFinalPrice;
        $dpAmount     = round($totalHarga * 0.5);
        $chargeAmount = $paymentType === 'dp' ? $dpAmount : $totalHarga;

        $midtransOrderId = 'ROUPAA-' . $order->order_number . '-' . time();

        $itemDetails = [];
        foreach ($order->items as $item) {
            $itemDetails[] = [
                'id'       => 'INV-' . $item->id,
                'price'    => (int) $item->unit_price,
                'quantity' => $item->qty,
                'name'     => substr($item->productInventory->variant_name ?? 'Produk Custom', 0, 50),
            ];
        }

        if ($paymentType === 'dp') {
            $itemDetails = [[
                'id'       => 'DP-' . $order->order_number,
                'price'    => (int) $chargeAmount,
                'quantity' => 1,
                'name'     => 'Down Payment 50% - Order #' . $order->order_number,
            ]];
        }

        $params = [
            'transaction_details' => [
                'order_id'     => $midtransOrderId,
                'gross_amount' => (int) $chargeAmount,
            ],
            'item_details'    => $itemDetails,
            'customer_details' => [
                'first_name' => $order->pelanggan->nama_lengkap,
                'email'      => $order->pelanggan->user->email,
                'phone'      => $order->pelanggan->no_whatsapp ?? '',
            ],
            'callbacks' => [
                'finish' => url('/customer/progress-pesanan/' . $order->id),
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            $order->update([
                'payment_type'         => $paymentType,
                'dp_amount'            => $dpAmount,
                'midtrans_snap_token'  => $snapToken,
                'midtrans_order_id'    => $midtransOrderId,
            ]);

            return response()->json([
                'status'       => 'success',
                'snap_token'   => $snapToken,
                'client_key'   => config('services.midtrans.client_key'),
                'amount'       => $chargeAmount,
                'payment_type' => $paymentType,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal membuat token pembayaran: ' . $e->getMessage()], 500);
        }
    }

    public function webhook(Request $request)
    {
        $payload        = $request->all();
        $signatureKey   = hash(
            'sha512',
            ($payload['order_id'] ?? '') .
                ($payload['status_code'] ?? '') .
                ($payload['gross_amount'] ?? '') .
                config('services.midtrans.server_key')
        );

        if ($signatureKey !== ($payload['signature_key'] ?? '')) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $order = Order::where('midtrans_order_id', $payload['order_id'])->first();
        if (!$order) {
            return response()->json(['error' => 'Order tidak ditemukan'], 404);
        }

        $transactionStatus = $payload['transaction_status'];
        $fraudStatus       = $payload['fraud_status'] ?? 'accept';

        if ($transactionStatus === 'capture' && $fraudStatus === 'accept') {
            $this->handlePaymentSuccess($order);
        } elseif ($transactionStatus === 'settlement') {
            $this->handlePaymentSuccess($order);
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $order->update(['payment_status' => 'unpaid']);
        }

        return response()->json(['status' => 'ok']);
    }

    private function handlePaymentSuccess(Order $order)
    {
        $stockShortages = [];
        $updatedOrder   = null;

        DB::transaction(function () use ($order, &$stockShortages, &$updatedOrder) {
            // Kunci baris order: webhook & confirm-payment bisa datang bersamaan,
            // hanya satu yang boleh memproses transisi status + stok
            $lockedOrder = Order::with('items.productInventory')
                ->lockForUpdate()
                ->find($order->id);

            // Idempotency: jika sudah paid penuh, abaikan webhook ulang
            if (!$lockedOrder || $lockedOrder->payment_status === 'paid') {
                return;
            }

            if ($lockedOrder->payment_type === 'pelunasan' || $lockedOrder->payment_status === 'dp_paid') {
                $newPaymentStatus = 'paid';
            } elseif ($lockedOrder->payment_type === 'lunas') {
                $newPaymentStatus = 'paid';
            } else {
                $newPaymentStatus = 'dp_paid';
            }

            // Idempotency: jika status pembayaran tidak berubah, abaikan
            if ($lockedOrder->payment_status === $newPaymentStatus) {
                return;
            }

            // Stok dikurangi hanya pada pembayaran sukses PERTAMA (DP 50% atau langsung lunas).
            // Pelunasan (dp_paid -> paid) tidak mengurangi stok lagi.
            if ($lockedOrder->payment_status !== 'dp_paid') {
                foreach ($lockedOrder->items as $item) {
                    if (!$item->product_inventory_id) {
                        continue;
                    }

                    $inventory = ProductInventory::lockForUpdate()->find($item->product_inventory_id);
                    if (!$inventory) {
                        continue;
                    }

                    // Pembayaran sudah diterima, jadi tidak bisa ditolak:
                    // kurangi sebanyak yang tersedia, sisanya dilaporkan ke admin
                    $reduce = min($inventory->stock, $item->qty);
                    if ($reduce > 0) {
                        $inventory->decrement('stock', $reduce);
                    }

                    if ($reduce < $item->qty) {
                        $stockShortages[] = sprintf(
                            '%s (kurang %d pcs)',
                            $inventory->variant_name,
                            $item->qty - $reduce
                        );
                    }

                    Log::info('[Payment Success] Stok dikurangi', [
                        'order_id'     => $lockedOrder->id,
                        'inventory_id' => $inventory->id,
                        'variant_name' => $inventory->variant_name,
                        'dikurangi'    => $reduce,
                        'sisa'         => $inventory->stock,
                    ]);
                }
            }

            $updateData = [
                'payment_status'       => $newPaymentStatus,
                'payment_confirmed_at' => now(),
            ];

            if (!in_array($lockedOrder->status, ['diproses', 'siap_diambil', 'selesai'])) {
                $updateData['status'] = 'diproses';
            }

            $lockedOrder->update($updateData);
            $updatedOrder = $lockedOrder;
        });

        // Transaction memutuskan tidak ada yang perlu diproses (duplikat) -> selesai
        if (!$updatedOrder) {
            return;
        }

        $order            = $updatedOrder;
        $newPaymentStatus = $order->payment_status;

        $order->chats()->create([
            'user_id'           => $order->pelanggan->user_id,
            'message'           => sprintf(
                'SISTEM: Pembayaran %s berhasil diterima sebesar Rp %s.%s',
                $newPaymentStatus === 'paid' ? 'Lunas' : 'DP 50%',
                number_format(
                    $newPaymentStatus === 'paid' && $order->payment_type === 'pelunasan'
                        ? ($order->final_price - $order->dp_amount)
                        : ($newPaymentStatus === 'paid' ? $order->final_price : $order->dp_amount),
                    0,
                    ',',
                    '.'
                ),
                $newPaymentStatus === 'paid' && $order->status === 'siap_diambil'
                    ? ' Pesanan siap untuk diambil atau dikirimkan.'
                    : ($newPaymentStatus === 'dp_paid' ? ' Pesanan Anda sedang diproses.' : '')
            ),
            'is_system_message' => true,
        ]);
        $order->load('pelanggan');
        AdminNotification::send(
            $newPaymentStatus === 'paid' ? 'payment_lunas' : 'payment_dp',
            $newPaymentStatus === 'paid' ? 'Pembayaran Lunas!' : 'DP 50% Diterima',
            sprintf(
                "%s melakukan %s untuk \"%s\" sebesar Rp %s.",
                $order->pelanggan->nama_lengkap ?? 'Customer',
                $newPaymentStatus === 'paid' ? 'pelunasan' : 'pembayaran DP',
                $order->project_name,
                number_format(
                    $newPaymentStatus === 'paid' && $order->payment_type === 'pelunasan'
                        ? ($order->final_price - $order->dp_amount)
                        : ($newPaymentStatus === 'paid' ? $order->final_price : $order->dp_amount),
                    0,
                    ',',
                    '.'
                )
            ),
            ['order_id' => $order->id]
        );

        if (!empty($stockShortages)) {
            AdminNotification::send(
                'stock_shortage',
                'Stok Tidak Cukup!',
                sprintf(
                    'Pesanan "%s" sudah dibayar tetapi stok kurang: %s. Segera lakukan restock.',
                    $order->project_name,
                    implode(', ', $stockShortages)
                ),
                ['order_id' => $order->id]
            );
        }

        event(new \App\Events\OrderStateUpdated($order->id, 'payment_confirmed'));
    }

    public function confirmFromCallback(Request $request, $orderId)
    {
        $user  = auth('api')->user();
        $order = Order::with('pelanggan')->findOrFail($orderId);

        if ($order->pelanggan_id !== $user->pelanggan->id) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        // Idempotency: cukup cek payment_status saja, tidak perlu AND dengan order status
        // karena order status bisa sudah maju lebih jauh (siap_diambil, selesai)
        if ($order->payment_status === 'paid') {
            return response()->json([
                'status'  => 'already_confirmed',
                'message' => 'Pembayaran sudah dikonfirmasi sebelumnya.',
            ]);
        }

        if ($order->midtrans_order_id) {
            try {
                $serverKey = config('services.midtrans.server_key');
                $baseUrl   = config('services.midtrans.is_production')
                    ? 'https://api.midtrans.com'
                    : 'https://api.sandbox.midtrans.com';

                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
                    'Accept'        => 'application/json',
                ])->get("{$baseUrl}/v2/{$order->midtrans_order_id}/status");

                if ($response->successful()) {
                    $midtransData      = $response->json();
                    $transactionStatus = $midtransData['transaction_status'] ?? null;
                    $fraudStatus       = $midtransData['fraud_status'] ?? 'accept';

                    if (
                        ($transactionStatus === 'capture' && $fraudStatus === 'accept') ||
                        $transactionStatus === 'settlement'
                    ) {
                        $this->handlePaymentSuccess($order);
                        return response()->json([
                            'status'  => 'confirmed',
                            'message' => 'Pembayaran berhasil dikonfirmasi.',
                        ]);
                    }

                    if ($transactionStatus === 'pending') {
                        return response()->json([
                            'status'  => 'pending',
                            'message' => 'Pembayaran masih pending.',
                        ]);
                    }

                    return response()->json([
                        'status'  => 'unconfirmed',
                        'message' => "Status Midtrans: {$transactionStatus}",
                    ], 422);
                }
            } catch (\Exception $e) {
                Log::warning('Midtrans status check failed: ' . $e->getMessage());

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tidak dapat menghubungi server Midtrans. Silakan coba beberapa saat lagi.',
                ], 503);
            }
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Transaksi Midtrans tidak ditemukan untuk pesanan ini.',
        ], 422);
    }
}
