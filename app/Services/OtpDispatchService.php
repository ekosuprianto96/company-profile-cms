<?php

namespace App\Services;

use App\Mail\MobileOtpMail;
use App\Models\MobileUserOtp;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class OtpDispatchService
{
    public function __construct(
        protected TwilioVerifyService $twilioVerifyService
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

    private function dispatchSms(MobileUserOtp $otp): void
    {
        $response = $this->twilioVerifyService->sendVerification($otp->recipient);

        $otp->update([
            'provider' => 'twilio_verify',
            'provider_sid' => $response['sid'] ?? null,
            'provider_response' => $response,
            'sent_at' => now(),
            'status' => 'sent',
        ]);
    }
}
