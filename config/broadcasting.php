<?php

return [
    'default' => env('BROADCAST_CONNECTION', 'log'),

    'connections' => [
        'reverb' => [
            'driver' => 'reverb',
            'key' => env('REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'app_id' => env('REVERB_APP_ID'),
            'options' => [
                // Host/port yang dipakai SERVER untuk MENERBITKAN event ke Reverb.
                // WAJIB menunjuk server Reverb LOKAL (mis. 127.0.0.1:8080), BUKAN
                // domain publik (REVERB_HOST) — kalau pakai domain publik, request
                // publish nyasar ke nginx dan balas "404 Not Found" (bug ini).
                // Klien mobile tetap konek via REVERB_HOST publik + proxy nginx.
                'host' => env('REVERB_PUBLISH_HOST', '127.0.0.1'),
                'port' => env('REVERB_PUBLISH_PORT', env('REVERB_SERVER_PORT', 8080)),
                'scheme' => env('REVERB_PUBLISH_SCHEME', 'http'),
                'useTLS' => env('REVERB_PUBLISH_SCHEME', 'http') === 'https',
            ],
        ],

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'host' => env('PUSHER_HOST', '127.0.0.1'),
                'port' => env('PUSHER_PORT', 6001),
                'scheme' => env('PUSHER_SCHEME', 'http'),
                'encrypted' => env('PUSHER_SCHEME', 'http') === 'https',
                'useTLS' => env('PUSHER_SCHEME', 'http') === 'https',
            ],
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],
    ],
];
