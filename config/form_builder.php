<?php

// Katalog form builder (khusus layanan). Dipakai bersama oleh admin (form +
// validasi) dan resolver schema, supaya selalu sinkron.

return [
    // Tipe field yang tersedia.
    'field_types' => [
        'text' => 'Teks Singkat',
        'textarea' => 'Teks Panjang',
        'number' => 'Angka',
        'email' => 'Email',
        'phone' => 'No. Telepon',
        'date' => 'Tanggal',
        'time' => 'Jam',
        'datetime' => 'Tanggal & Jam',
        'select' => 'Dropdown (pilih satu)',
        'multiselect' => 'Dropdown (pilih banyak)',
        'radio' => 'Radio (pilih satu)',
        'checkbox' => 'Checkbox (ya/tidak)',
        'checkbox_group' => 'Checkbox (pilih banyak)',
        'image' => 'Upload Gambar',
        'file' => 'Upload Dokumen',
        'location' => 'Lokasi (peta + alamat)',
        'section' => 'Judul Bagian',
        'note' => 'Catatan (teks statis)',
    ],

    // Tipe yang membutuhkan daftar opsi (bisa static atau dari datasource).
    'option_types' => ['select', 'multiselect', 'radio', 'checkbox_group'],

    // Tipe upload berkas.
    'file_types' => ['image', 'file'],

    // Tipe tampilan saja (tidak menghasilkan jawaban).
    'display_types' => ['section', 'note'],

    'options_sources' => [
        'static' => 'Isi manual',
        'datasource' => 'Ambil dari master data',
    ],

    /**
     * Registry master data yang bisa jadi sumber opsi.
     * value = kolom nilai, text = kolom label. Semua difilter is_active bila ada
     * dan diurutkan sort_order lalu label.
     */
    'datasources' => [
        'categories' => [
            'label' => 'Kategori (master)',
            'model' => \App\Models\Category::class,
            'value' => 'id',
            'text' => 'name',
        ],
        'services' => [
            'label' => 'Layanan',
            'model' => \App\Models\MobileService::class,
            'value' => 'id',
            'text' => 'title',
        ],
        'product_categories' => [
            'label' => 'Kategori Produk',
            'model' => \App\Models\ProductCategory::class,
            'value' => 'id',
            'text' => 'name',
        ],
        'products' => [
            'label' => 'Produk',
            'model' => \App\Models\Product::class,
            'value' => 'id',
            'text' => 'name',
        ],
    ],

    // Status proposal.
    'proposal_statuses' => [
        'submitted' => 'Masuk',
        'in_review' => 'Ditinjau',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'cancelled' => 'Dibatalkan',
    ],

    // Peran data field → kolom Order Layanan (MobileServiceRequest).
    // Admin menandai field mana yang mengisi kolom order, agar form apa pun
    // (kunci bebas) tetap mengisi order dengan benar. Kosong = tidak dipetakan.
    'field_roles' => [
        'survey_location' => 'Lokasi Survei',
        'survey_date' => 'Tanggal Survei',
        'issue_photos' => 'Foto Kondisi',
        'need_type' => 'Jenis Kebutuhan',
        'budget_option' => 'Perkiraan Budget',
        'building_type' => 'Jenis Bangunan',
        'description' => 'Deskripsi',
    ],

    // Jenis komponen biaya layanan (skema harga dinamis).
    'price_types' => [
        'survey' => 'Biaya Survei',
        'consultation' => 'Biaya Konsultasi',
        'dp' => 'Uang Muka (DP)',
        'registration' => 'Biaya Pendaftaran',
        'fixed' => 'Biaya Tetap',
        'other' => 'Lainnya',
    ],
];
