<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; color: #222; font-size: 12px; }
        .header-table { width: 100%; margin-bottom: 24px; }
        .header-table td { vertical-align: top; }
        .brand { font-size: 22px; font-weight: bold; color: #111; letter-spacing: 1px; }
        .brand span { color: #c79a2a; }
        .invoice-title { font-size: 16px; font-weight: bold; text-transform: uppercase; color: #555; text-align: right; }
        .info-table { width: 100%; margin-bottom: 24px; }
        .info-table td { padding: 4px 0; font-size: 12px; }
        .label { color: #777; width: 140px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.items th { background-color: #111; color: #fff; padding: 8px 10px; font-size: 11px; text-transform: uppercase; text-align: left; }
        table.items td { padding: 8px 10px; border-bottom: 1px solid #eee; font-size: 12px; }
        .text-right { text-align: right; }
        .total-table { width: 100%; margin-top: 8px; }
        .total-row td { font-weight: bold; font-size: 14px; border-top: 2px solid #111; padding: 8px 10px; }
        .footer { margin-top: 36px; font-size: 10px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td width="50%">
                <div class="brand">ROUPAA<span>.</span></div>
                <p style="margin:4px 0 0;color:#777;">Custom Apparel & Sablon</p>
            </td>
            <td width="50%">
                <div class="invoice-title">Invoice</div>
                <p style="margin:4px 0 0;text-align:right;color:#777;">No. {{ $order->order_number }}</p>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="label">Nama Pelanggan</td>
            <td>: {{ $order->pelanggan->nama_lengkap ?? '-' }}</td>
            <td class="label" style="text-align:right;">Tanggal Selesai</td>
            <td style="text-align:right;">: {{ $order->completed_at ? \Carbon\Carbon::parse($order->completed_at)->translatedFormat('d F Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Nama Proyek</td>
            <td>: {{ $order->project_name }}</td>
            <td class="label" style="text-align:right;">Status Pembayaran</td>
            <td style="text-align:right;">: {{ $order->payment_status === 'paid' ? 'Lunas' : ucfirst($order->payment_status) }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Varian Produk</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Harga Satuan</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->productInventory->variant_name ?? 'Produk Custom' }}</td>
                    <td class="text-right">{{ $item->qty }}</td>
                    <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="total-table">
        <tr class="total-row">
            <td class="text-right" style="width:80%;">Total Pembayaran</td>
            <td class="text-right" style="width:20%;">Rp {{ number_format($order->final_price ?? 0, 0, ',', '.') }}</td>
        </tr>
    </table>

    <p class="footer">Terima kasih telah mempercayakan pesanan Anda kepada ROUPAA Apparel.<br>Invoice ini dibuat secara otomatis oleh sistem.</p>
</body>
</html>