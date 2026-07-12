<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Invoice Templates
    |--------------------------------------------------------------------------
    | Template Blade yang dipakai untuk merender invoice PDF per jenis order.
    | Nilai default di sini bisa ditimpa dari pengaturan admin (mobile app
    | settings) melalui AppServiceProvider, sehingga pemanggilannya tetap
    | seragam lewat config('invoice.templates.*').
    |
    | Kop invoice (nama bisnis & logo) diambil dinamis dari settings dan
    | dipanggil via config('app.name') & config('app.logo').
    */
    'templates' => [
        'service' => env('INVOICE_TEMPLATE_SERVICE', 'classic'),
        'product' => env('INVOICE_TEMPLATE_PRODUCT', 'classic'),
    ],

    // Daftar template yang tersedia (dipakai di dropdown pemilihan admin).
    'available' => [
        'classic' => 'Classic',
        'modern' => 'Modern',
    ],
];
