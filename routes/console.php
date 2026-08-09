<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// (Dihapus) Schedule::job(new SendBulkEmailJob)->everyMinute();
// Job ini di-dispatch per-email dari EmailManagementController (dengan model),
// bukan dijadwalkan tanpa argumen — versi terjadwal itu selalu error karena $data kosong.

// Tandai OTP mobile yang sudah lewat masa berlaku menjadi expired.
Schedule::command('mobile:expire-otps')->everyMinute()->withoutOverlapping();

// Lepas reservasi voucher yang menggantung (tidak dibayar) agar kuota kembali.
Schedule::command('vouchers:release-stale')->hourly()->withoutOverlapping();
