<?php

use App\Jobs\SendBulkEmailJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new SendBulkEmailJob)->everyMinute();

// Tandai OTP mobile yang sudah lewat masa berlaku menjadi expired.
Schedule::command('mobile:expire-otps')->everyMinute()->withoutOverlapping();

// Lepas reservasi voucher yang menggantung (tidak dibayar) agar kuota kembali.
Schedule::command('vouchers:release-stale')->hourly()->withoutOverlapping();
