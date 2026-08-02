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
        $code = Crypt::decryptString((string) $otp->code_encrypted);
        $minutes = max(1, (int) now()->diffInMinutes($otp->expires_at, false));

        $rendered = $this->templates->render('otp.mobile', 'sms', 'user', [
            'recipient_name' => $otp->user?->name ?? 'Pengguna',
            'otp_code' => $code,
            'otp_purpose' => $otp->purpose,
            'otp_expire_minutes' => (string) $minutes,
        ]);

        $response = $this->zenzivaSmsService->send($otp->recipient, $rendered['body']);

        $otp->update([
            'provider' => 'zenziva',
            'provider_sid' => data_get($response, 'messages.messageId', data_get($response, 'messageId')),
            'provider_response' => $response,
            'sent_at' => now(),
            'status' => 'sent',
        ]);
    }
}
