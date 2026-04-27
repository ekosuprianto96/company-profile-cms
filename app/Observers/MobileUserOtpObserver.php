<?php

namespace App\Observers;

use App\Models\MobileUserOtp;
use App\Services\OtpDispatchService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class MobileUserOtpObserver implements ShouldHandleEventsAfterCommit
{
    public function created(MobileUserOtp $mobileUserOtp): void
    {
        app(OtpDispatchService::class)->dispatch($mobileUserOtp);
    }
}
