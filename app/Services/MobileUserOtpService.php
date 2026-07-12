<?php

namespace App\Services;

use App\Models\MobileUser;
use App\Models\MobileUserOtp;
use App\Repositories\MobileUserOtpRepository;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class MobileUserOtpService
{
    public function __construct(
        protected MobileUserOtpRepository $otpRepository,
        protected TwilioVerifyService $twilioVerifyService
    ) {}

    public function createAndSend(MobileUser $user, string $channel, string $purpose): MobileUserOtp
    {
        $recipient = $this->resolveRecipient($user, $channel);

        // Cegah spam pengiriman: bila masih ada OTP pending yang belum kadaluarsa,
        // kembalikan yang itu (tanpa membuat/mengirim ulang). Klien menghitung mundur
        // berdasarkan expires_at tersimpan sehingga konsisten meski layar dibuka ulang.
        $existing = $this->otpRepository->latestPending($recipient, $channel, $purpose);
        if ($existing && $existing->expires_at && $existing->expires_at->isFuture()) {
            return $existing;
        }

        $this->otpRepository->expirePending($recipient, $channel, $purpose);

        $attributes = [
            'mobile_user_id' => $user->id,
            'purpose' => $purpose,
            'channel' => $channel,
            'recipient' => $recipient,
            'expires_at' => now()->addMinutes(app(MobileAppSettingService::class)->otpExpireMinutes()),
            'status' => 'pending',
        ];

        if ($channel === 'email') {
            $code = $this->generateCode();

            $attributes['code_hash'] = Hash::make($code);
            $attributes['code_encrypted'] = Crypt::encryptString($code);
        }

        return $this->otpRepository->store($attributes);
    }

    public function verify(string $recipient, string $channel, string $purpose, string $code): MobileUserOtp
    {
        $otp = $this->otpRepository->latestPending($recipient, $channel, $purpose);

        if (! $otp) {
            throw new \Exception('OTP tidak ditemukan atau sudah tidak aktif.', 404);
        }

        if ($otp->verified_at) {
            throw new \Exception('OTP sudah digunakan.', 422);
        }

        if ($otp->expires_at->isPast()) {
            $otp->update(['status' => 'expired']);
            throw new \Exception('OTP sudah kadaluarsa.', 422);
        }

        if ($otp->attempts >= config('mobile_auth.otp_max_attempts')) {
            $otp->update(['status' => 'expired']);
            throw new \Exception('Batas percobaan OTP sudah habis.', 422);
        }

        $otp->increment('attempts');
        $otp->refresh();

        if ($channel === 'email') {
            if (! Hash::check($code, (string) $otp->code_hash)) {
                throw new \Exception('Kode OTP email tidak valid.', 422);
            }
        }

        if ($channel === 'sms') {
            $result = $this->twilioVerifyService->checkVerification($recipient, $code);

            if (($result['valid'] ?? false) !== true || ($result['status'] ?? '') !== 'approved') {
                throw new \Exception('Kode OTP SMS tidak valid.', 422);
            }

            $otp->provider_response = $result;
        }

        $otp->verified_at = now();
        $otp->status = 'verified';
        $otp->save();

        return $otp;
    }

    private function resolveRecipient(MobileUser $user, string $channel): string
    {
        $recipient = $channel === 'sms' ? $user->phone : $user->email;

        if (empty($recipient)) {
            throw new \Exception("Kontak {$channel} untuk pengiriman OTP tidak tersedia.", 422);
        }

        return $recipient;
    }

    private function generateCode(): string
    {
        $length = max(4, (int) config('mobile_auth.otp_length', 6));
        $max = (10 ** $length) - 1;

        return str_pad((string) random_int(0, $max), $length, '0', STR_PAD_LEFT);
    }
}
