<?php

namespace App\Services;

use App\Mail\MobileOtpMail;
use App\Models\MobileUserOtp;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class OtpDispatchService
{
    public function __construct(
        protected ZenzivaSmsService $zenzivaSmsService,
        protected NotificationTemplateService $templates
    ) {}

    public function dispatch(MobileUserOtp $otp): MobileUserOtp
    {
        if ($otp->channel === 'email') {
            $this->dispatchEmail($otp);
        }

        if ($otp->channel === 'sms') {
            $this->dispatchSms($otp);
        }

        return $otp->refresh();
    }

    private function dispatchEmail(MobileUserOtp $otp): void
    {
        $code = Crypt::decryptString((string) $otp->code_encrypted);

        Mail::to($otp->recipient)->queue(new MobileOtpMail(
            userName: $otp->user?->name ?? 'Pengguna',
            code: $code,
            purpose: $otp->purpose,
            expiresAt: $otp->expires_at,
        ));

        $otp->update([
            'provider' => 'laravel_mail',
            'sent_at' => now(),
            'status' => 'sent',
        ]);
    }

    /** SMS via Zenziva dengan teks dari template notifikasi (channel sms). */
    private function dispatchSms(MobileUserOtp $otp): void
    {
        // Async: kirim SMS lewat queue agar lambatnya Zenziva tak menahan response.
        \App\Jobs\SendOtpSmsJob::dispatch($otp->id);
    }
}
