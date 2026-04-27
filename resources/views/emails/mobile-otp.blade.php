<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kode OTP Mobile App</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
    <h2 style="margin-bottom: 8px;">Halo, {{ $userName }}</h2>
    <p style="margin-top: 0;">
        Berikut kode OTP untuk proses {{ $purpose === 'login' ? 'login' : 'verifikasi akun' }} aplikasi mobile Anda:
    </p>

    <div style="display: inline-block; padding: 14px 22px; margin: 12px 0; font-size: 28px; font-weight: 700; letter-spacing: 8px; background: #f3f4f6; border-radius: 10px;">
        {{ $code }}
    </div>

    <p>Kode ini berlaku sampai {{ $expiresAt->format('d M Y H:i') }}.</p>
    <p>Jika Anda tidak merasa meminta OTP ini, abaikan email ini.</p>
</body>
</html>
