<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Preset catatan "Stop Terima Pengajuan"
    |--------------------------------------------------------------------------
    | Daftar catatan cepat yang bisa dipilih admin saat menghentikan penerimaan
    | pengajuan sebuah layanan. Admin memilih salah satu → mengisi field →
    | boleh mengedit sebelum menyimpan (catatan bisa berbeda tiap layanan).
    */
    'pause_reason_presets' => [
        'Mohon maaf, pengajuan untuk layanan ini sedang kami hentikan sementara karena antrean sedang penuh. Silakan coba beberapa waktu lagi.',
        'Tim kami sedang libur. Penerimaan pengajuan dibuka kembali setelah masa libur. Terima kasih atas pengertiannya.',
        'Kuota pengajuan untuk periode ini telah penuh. Penerimaan akan dibuka kembali pada periode berikutnya.',
        'Layanan ini sedang dalam pemeliharaan. Penerimaan pengajuan akan dibuka kembali dalam waktu dekat.',
        'Kami sedang menyelesaikan pengajuan yang masuk agar pelayanan tetap maksimal. Pengajuan baru untuk sementara ditutup.',
    ],
];
