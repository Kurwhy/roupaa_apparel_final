<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $headline }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5;padding:40px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background-color:#ffffff;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="background-color:#111111;padding:28px 32px;text-align:center;">
                            <span style="color:#f2ca50;font-size:22px;font-weight:900;letter-spacing:2px;text-transform:uppercase;">ROUPAA<span style="color:#ffffff;">.</span></span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:36px 32px 12px;">
                            <p style="margin:0 0 6px;color:#9a9a9a;font-size:11px;text-transform:uppercase;letter-spacing:1.5px;font-weight:bold;">Pesanan #{{ $order->order_number }}</p>
                            <h1 style="margin:0 0 16px;color:#111111;font-size:20px;font-weight:800;">{{ $headline }}</h1>
                            <p style="margin:0 0 24px;color:#444444;font-size:14px;line-height:1.6;">{{ $bodyMessage }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 32px;">
                            <a href="{{ $orderUrl }}" style="display:inline-block;background-color:#f2ca50;color:#111111;text-decoration:none;font-weight:800;font-size:13px;text-transform:uppercase;letter-spacing:1px;padding:14px 28px;border-radius:10px;">
                                Lihat Detail Pesanan
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px;background-color:#fafafa;border-top:1px solid #eeeeee;">
                            <p style="margin:0;color:#999999;font-size:11px;line-height:1.6;">Email ini dikirim otomatis oleh sistem ROUPAA Apparel. Jika ada pertanyaan, silakan balas melalui halaman pesanan di website kami.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>