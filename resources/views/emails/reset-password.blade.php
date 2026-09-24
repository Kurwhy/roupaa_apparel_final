<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — ROUPAA Apparel</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f4f4f4;
            font-family: Arial, sans-serif;
        }

        .wrapper {
            max-width: 560px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
        }

        .header {
            background: #1a1a1a;
            padding: 28px 32px;
            text-align: center;
        }

        .header h1 {
            color: #F2CA50;
            margin: 0;
            font-size: 20px;
            letter-spacing: 4px;
        }

        .header p {
            color: #aaaaaa;
            margin: 6px 0 0;
            font-size: 13px;
        }

        .body {
            padding: 32px;
        }

        .body p {
            color: #444444;
            font-size: 15px;
            line-height: 1.7;
            margin: 0 0 16px;
        }

        .btn-wrap {
            text-align: center;
            margin: 28px 0;
        }

        .btn {
            display: inline-block;
            background: #1a1a1a;
            color: #F2CA50 !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .note {
            background: #f8f8f8;
            border-left: 3px solid #F2CA50;
            padding: 12px 16px;
            border-radius: 4px;
            margin: 20px 0;
        }

        .note p {
            color: #666666;
            font-size: 13px;
            margin: 0;
        }

        .url-box {
            word-break: break-all;
            font-size: 12px;
            color: #888888;
            background: #f0f0f0;
            padding: 10px 14px;
            border-radius: 6px;
            margin-top: 8px;
        }

        .footer {
            background: #f8f8f8;
            padding: 20px 32px;
            text-align: center;
        }

        .footer p {
            color: #aaaaaa;
            font-size: 12px;
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="wrapper">

        <div class="header">
            <h1>ROUPAA APPAREL</h1>
            <p>Custom Apparel & Merchandise</p>
        </div>

        <div class="body">
            <p>Halo, <strong>{{ $userName }}</strong>!</p>
            <p>
                Kami menerima permintaan reset password untuk akun kamu di ROUPAA Apparel.
                Klik tombol di bawah ini untuk membuat password baru.
            </p>

            <div class="btn-wrap">
                <a href="{{ $resetUrl }}" class="btn">Reset Password Saya</a>
            </div>

            <div class="note">
                <p><strong>⏱ Link berlaku selama 60 menit</strong> sejak email ini dikirim.</p>
                <p style="margin-top:8px">Jika kamu tidak merasa meminta reset password, abaikan email ini.</p>
            </div>

            <p>Jika tombol tidak berfungsi, salin URL berikut ke browser:</p>
            <div class="url-box">{{ $resetUrl }}</div>
        </div>

        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem ROUPAA Apparel. Jangan balas email ini.</p>
            <p style="margin-top:6px">© {{ date('Y') }} ROUPAA Apparel. All rights reserved.</p>
        </div>

    </div>
</body>

</html>
