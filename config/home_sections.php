<?php

// Katalog opsi untuk section dinamis home. Dipakai bersama oleh admin (form +
// validasi) dan resolver, supaya selalu sinkron.

return [
    'sources' => [
        'product' => 'Produk',
        'service' => 'Layanan',
        'voucher' => 'Voucher',
        'inspire' => 'Inspirasi',
        'blog' => 'Blog',
        'package' => 'Paket (belum aktif)',
    ],

    'layouts' => [
        'slider' => 'Slider (geser horizontal)',
        'list' => 'List (vertikal)',
        'grid' => 'Grid',
    ],

    'selection_modes' => [
        'auto' => 'Otomatis (berdasar aturan)',
        'manual' => 'Pilih manual',
    ],

    // Opsi filter otomatis per source.
    'auto_filters' => [
        'product' => [
            'newest' => 'Terbaru',
            'discount' => 'Sedang Diskon',
            'popular' => 'Terlaris',
            'featured' => 'Unggulan',
            'top_rated' => 'Rating Tertinggi',
        ],
        'service' => [
            'newest' => 'Terbaru',
            'popular' => 'Populer',
            'featured' => 'Unggulan',
            'coming_soon' => 'Segera Hadir',
        ],
        'voucher' => [
            'active' => 'Aktif',
            'newest' => 'Terbaru',
        ],
        'inspire' => [
            'newest' => 'Terbaru',
            'featured' => 'Unggulan',
        ],
        'blog' => [
            'newest' => 'Terbaru',
        ],
        'package' => [
            'newest' => 'Terbaru',
        ],
    ],
];
