<?php

namespace App\Console\Commands;

use App\Models\MobileUserOtp;
use Illuminate\Console\Command;

class ExpireMobileOtps extends Command
{
    protected $signature = 'mobile:expire-otps';

    protected $description = 'Tandai OTP mobile yang pending & sudah melewati waktu kadaluarsa (expires_at) menjadi expired.';

    public function handle(): int
    {
        $count = MobileUserOtp::query()
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);

        $this->info("OTP kedaluwarsa ditandai expired: {$count}");

        return self::SUCCESS;
    }
}
