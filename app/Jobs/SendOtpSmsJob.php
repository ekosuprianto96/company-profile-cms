<?php

namespace App\Jobs;

use App\Models\MobileUserOtp;
use App\Services\NotificationTemplateService;
use App\Services\ZenzivaSmsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

/**
 * Kirim OTP SMS via Zenziva secara async (di luar jalur request login/register)
 * supaya lambatnya provider tidak menahan response API. Lihat audit performa.
 */
class SendOtpSmsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public int $otpId) {}

    public function handle(ZenzivaSmsService $zenziva, NotificationTemplateService $templates): void
    {
        $otp = MobileUserOtp::with('user')->find($this->otpId);
        if (! $otp || $otp->status === 'verified') {
            return;
        }

        $code = Crypt::decryptString((string) $otp->code_encrypted);
        $minutes = max(1, (int) now()->diffInMinutes($otp->expires_at, false));

        $rendered = $templates->render('otp.mobile', 'sms', 'user', [
            'recipient_name' => $otp->user?->name ?? 'Pengguna',
            'otp_code' => $code,
            'otp_purpose' => $otp->purpose,
            'otp_expire_minutes' => (string) $minutes,
        ]);

        try {
            $response = $zenziva->send($otp->recipient, $rendered['body']);

            $otp->update([
                'provider' => 'zenziva',
                'provider_sid' => data_get($response, 'messages.messageId', data_get($response, 'messageId')),
                'provider_response' => $response,
                'sent_at' => now(),
                'status' => 'sent',
            ]);
        } catch (\Throwable $e) {
            Log::error('SendOtpSmsJob gagal (otp ' . $this->otpId . '): ' . $e->getMessage());
            throw $e; // biarkan retry (tries=3)
        }
    }
}
