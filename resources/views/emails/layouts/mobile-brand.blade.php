@php
    $settings = config('settings.value', []);
    $footerSettings = config('footer_settings.value', []);
    $appName = $settings['app_name'] ?? config('app.name', 'Aplikasi');
    $logoFile = data_get($settings, 'app_logo.file');
    $logoUrl = $logoFile ? image_url('informasi', $logoFile) : null;
    $headerBg = $settings['background_menu_header'] ?? '#2f5d57';
    $headerText = $settings['text_menu_header'] ?? '#ffffff';
    $tagline = data_get($footerSettings, 'tagline', 'Terima kasih telah mempercayakan kebutuhan proyek Anda kepada kami.');
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $appName }}</title>
</head>
<body style="margin:0;padding:0;background:#f3f5f7;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f5f7;border-collapse:collapse;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px;border-collapse:collapse;background:#ffffff;border-radius:18px;overflow:hidden;box-shadow:0 12px 30px rgba(15,23,42,.08);">
                    <tr>
                        <td style="background:{{ $headerBg }};padding:28px 30px;color:{{ $headerText }};">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                                <tr>
                                    <td style="vertical-align:middle;">
                                        @if ($logoUrl)
                                            <img src="{{ $logoUrl }}" alt="{{ $appName }}" style="display:block;height:44px;max-width:180px;object-fit:contain;margin-bottom:10px;">
                                        @endif
                                        <div style="font-size:12px;letter-spacing:1.4px;text-transform:uppercase;opacity:.9;">{{ $appName }}</div>
                                        <div style="font-size:24px;line-height:1.25;font-weight:700;margin-top:8px;">@yield('headline', 'Notifikasi Pengajuan Survey')</div>
                                        <div style="font-size:14px;line-height:1.6;margin-top:10px;opacity:.92;">@yield('subheadline', $tagline)</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 30px 28px;">
                            <div style="border-top:1px solid #e5e7eb;padding-top:18px;font-size:12px;line-height:1.7;color:#6b7280;">
                                Email ini dikirim otomatis oleh sistem {{ $appName }}. Mohon tidak membalas email ini.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
