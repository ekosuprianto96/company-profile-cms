<?php

use App\Services\InformasiService;

if (!function_exists('mStyles')) {
    function mStyles(string $name = '')
    {
        return new App\Services\StylesConfig(name: $name);
    }
}

if (!function_exists('assetAdmin')) {
    function assetAdmin(string $type, string $fileName = '', ?string $subFolder = null): string
    {
        if ($subFolder) {
            return asset('assets/admin/' . $type . '/' . $subFolder . '/' . $fileName);
        }

        return asset('assets/admin/' . $type . '/' . $fileName);
    }
}

if (!function_exists('assetFrontend')) {
    function assetFrontend(string $type, string $fileName = '', ?string $subFolder = null): string
    {
        if ($subFolder) {
            return asset('assets/frontend/' . $type . '/' . $subFolder . '/' . $fileName);
        }

        return asset('assets/frontend/' . $type . '/' . $fileName);
    }
}

if (!function_exists('storageUrl')) {
    /**
     * URL publik untuk file di disk `public`.
     *
     * WAJIB dipakai untuk apa pun yang dikirim ke aplikasi mobile. Sengaja
     * memakai `asset()` dan BUKAN `Storage::disk('public')->url()`:
     *
     *   - `asset()`      -> mengikuti host request yang masuk
     *   - `Storage::url()` -> selalu memakai APP_URL dari .env
     *
     * Mobile terhubung lewat tunnel/ngrok, sedangkan APP_URL menunjuk domain
     * `.test` yang hanya ada di /etc/hosts mesin dev. URL dari `Storage::url()`
     * jadi HTTP 200 kalau dites dari laptop, tapi mati total di HP — bug yang
     * sudah tiga kali muncul di proyek ini (banner, bukti transfer, avatar).
     *
     * Path yang sudah berupa URL penuh (mis. avatar dari login pihak ketiga)
     * dikembalikan apa adanya.
     */
    function storageUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return str_starts_with($path, 'http')
            ? $path
            : asset('storage/' . ltrim($path, '/'));
    }
}

if (!function_exists('greetingUser')) {
    function greetingUser()
    {
        $date = now()->format('H');
        return match (true) {
            $date < 12 => 'Selamat Pagi',
            $date < 18 => 'Selamat Siang',
            default => 'Selamat Malam'
        };
    }
}

if (!function_exists('image_url')) {
    function image_url($type, $fileName = '')
    {
        return asset('assets/images/' . $type . '/' . $fileName);
    }
}

if (!function_exists('cutTextByWords')) {
    function cutTextByWords($text, $wordLimit, $wordSuffix = '...')
    {
        $words = explode(' ', $text);

        if (count($words) > $wordLimit) {
            return implode(' ', array_slice($words, 0, $wordLimit)) . $wordSuffix;
        }

        return $text;
    }
}

if (!function_exists('isActiveMenu')) {
    function isActiveMenu($routeName, $activeClass = 'text-blue-500')
    {
        return request()->routeIs($routeName) ? $activeClass : '';
    }
}

if (!function_exists(('parseCounterSection'))) {
    function parseCounterSection($input)
    {
        preg_match_all('/\[\+(\d+),\s*([^\]]+)\]/', $input, $matches, PREG_SET_ORDER);

        $result = [];
        foreach ($matches as $match) {
            $result[] = [
                'value' => (int) $match[1], // Ambil angka
                'label' => trim($match[2])  // Ambil teks dan hilangkan spasi berlebih
            ];
        }

        return $result;
    }
}

if (! function_exists('merge_tags')) {
    function merge_tags(string $template, array $data = []): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{{ ' . $key . ' }}', $value, $template);
            $template = str_replace('{{' . $key . '}}', $value, $template); // jaga-jaga tanpa spasi
        }

        return $template;
    }
}

if (!function_exists('app_themes')) {
    function app_themes()
    {
        $services = app(InformasiService::class);
        $themes = $services->findByKey('theme_settings')
            ->decode(true)
            ->get();
        return $themes->value ?? [];
    }
}

if (!function_exists('generateGradientColor')) {
    function generateGradientColor(array $gradientSettings)
    {
        $colorsFormat = [];
        $mergeColors = '';
        if (count($gradientSettings['colors']) > 0) {
            foreach ($gradientSettings['colors'] as $value) {
                $colorsFormat[] = $value['color'] . ' ' . $value['color_stop'] . '%';
            }
        }

        $mergeColors = implode(', ', $colorsFormat);
        return "linear-gradient({$gradientSettings['degre']}deg, {$mergeColors})";
    }
}

if (!function_exists('replacePhoneNumber')) {
    function replacePhoneNumber(string $phone)
    {
        // Cek apakah nomor diawali dengan 0, ganti jadi 62
        if (preg_match('/^0/', $phone)) {
            return preg_replace('/^0/', '62', $phone);
        }
        // Cek apakah nomor diawali dengan 8, ganti jadi 62
        elseif (preg_match('/^8/', $phone)) {
            return '62' . substr($phone, 1);
        }

        // Jika tidak dimulai dengan 0 atau 8, kembalikan nomor tanpa perubahan
        return str_replace(['+', ' ', '(', ')', '-'], '', $phone);
    }
}

if (!function_exists('setConfigMail')) {
    function setConfigMail(
        string $transport = 'smtp',
        string $host = '',
        int $port = 0,
        string $username = '',
        string $password = '',
        string $encryption = '',
        string $fromAddress = '',
        string $fromName = ''
    ) {
        $payloadMailers = [
            'transport' => $transport ?: 'smtp',
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'password' => $password,
            'encryption' => $encryption,
        ];

        $currentMailerConfig = config('mail.mailers.smtp') ?? [];

        config([
            'mail.default' => $transport,
            'mail.mailers.smtp' => [
                ...$currentMailerConfig,
                ...$payloadMailers,
                'timeout' => $currentMailerConfig['timeout'] ?? null,
                'auth_mode' => $currentMailerConfig['auth_mode'] ?? null,
            ],
            'mail.from' => [
                'address' => $fromAddress,
                'name' => $fromName,
            ],
        ]);

        return config('mail');
    }
}
