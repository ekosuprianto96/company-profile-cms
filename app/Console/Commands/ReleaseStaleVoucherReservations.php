<?php

namespace App\Console\Commands;

use App\Models\VoucherRedemption;
use Illuminate\Console\Command;

class ReleaseStaleVoucherReservations extends Command
{
    protected $signature = 'vouchers:release-stale {--hours=24}';

    protected $description = 'Lepas reservasi voucher yang tidak pernah diselesaikan (bayar) agar kuota kembali.';

    public function handle(): int
    {
        $threshold = now()->subHours((int) $this->option('hours'));

        $count = VoucherRedemption::query()
            ->where('status', 'reserved')
            ->where('reserved_at', '<', $threshold)
            ->update(['status' => 'released', 'released_at' => now()]);

        $this->info("Released {$count} stale voucher reservation(s).");

        return self::SUCCESS;
    }
}
