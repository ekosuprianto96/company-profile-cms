<?php

namespace App\Services;

use App\Mail\AdminLoginOtpMail;
use App\Models\AdminLoginOtp;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdminAuthService
{
    /**
     * Tahap 1: validasi email + password + credential key + akses admin.
     * Bila valid, kirim OTP ke email admin dan kembalikan info verifikasi.
     */
    public function requestOtp(array $payload): array
    {
        $user = User::where('email', $payload['email'])->first();

        if (! $user || ! Hash::check($payload['password'], (string) $user->password)) {
            throw new \Exception('Email atau password salah.', 422);
        }

        if (empty($user->credential_key) || ! hash_equals((string) $user->credential_key, (string) $payload['credential_key'])) {
            throw new \Exception('Credential key tidak valid.', 422);
        }

        if (! $user->canAccessMobileAdmin()) {
            throw new \Exception('Akun ini tidak memiliki akses ke aplikasi admin.', 403);
        }

        if (empty($user->email)) {
            throw new \Exception('Akun admin tidak memiliki email untuk pengiriman OTP.', 422);
        }

        $otp = $this->createAndSendOtp($user);

        return [
            'user_id' => $user->id,
            'email' => $user->email,
            'email_masked' => $this->maskEmail($user->email),
            'expires_at' => $otp->expires_at,
        ];
    }

    /** Tahap 2: verifikasi OTP lalu terbitkan token akses admin. */
    public function verifyOtp(array $payload): array
    {
        $user = User::where('email', $payload['email'])->first();

        if (! $user) {
            throw new \Exception('Akun admin tidak ditemukan.', 404);
        }

        $otp = AdminLoginOtp::where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (! $otp || ! $otp->expires_at || $otp->expires_at->isPast()) {
            throw new \Exception('OTP tidak ditemukan atau sudah kadaluarsa.', 422);
        }

        if (! Hash::check((string) $payload['code'], $otp->code_hash)) {
            throw new \Exception('Kode OTP salah.', 422);
        }

        if (! $user->canAccessMobileAdmin()) {
            throw new \Exception('Akun ini tidak memiliki akses ke aplikasi admin.', 403);
        }

        $otp->update(['status' => 'verified', 'verified_at' => now()]);

        return $this->issueToken($user, $payload['device_name'] ?? 'admin-device');
    }

    /** Kirim ulang OTP (memakai/menyegarkan kode). */
    public function resendOtp(array $payload): array
    {
        $user = User::where('email', $payload['email'])->first();

        if (! $user || ! $user->canAccessMobileAdmin()) {
            throw new \Exception('Akun admin tidak ditemukan.', 404);
        }

        $otp = $this->createAndSendOtp($user);

        return [
            'email_masked' => $this->maskEmail($user->email),
            'expires_at' => $otp->expires_at,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    protected function createAndSendOtp(User $user): AdminLoginOtp
    {
        // Kadaluarskan OTP pending lama.
        AdminLoginOtp::where('user_id', $user->id)->where('status', 'pending')->update(['status' => 'expired']);

        $code = (string) random_int(100000, 999999);
        $minutes = (int) app(MobileAppSettingService::class)->otpExpireMinutes();
        $expiresAt = now()->addMinutes($minutes > 0 ? $minutes : 10);

        $otp = AdminLoginOtp::create([
            'user_id' => $user->id,
            'code_hash' => Hash::make($code),
            'code_encrypted' => Crypt::encryptString($code),
            'expires_at' => $expiresAt,
            'status' => 'pending',
        ]);

        Mail::to($user->email)->queue(new AdminLoginOtpMail($user->name ?? 'Admin', $code, $expiresAt));

        return $otp;
    }

    protected function issueToken(User $user, string $deviceName): array
    {
        $token = $user->createToken($deviceName, ['admin']);

        return [
            'user' => $user,
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
        ];
    }

    protected function maskEmail(string $email): string
    {
        [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');
        $visible = mb_substr($name, 0, min(2, mb_strlen($name)));

        return $visible . str_repeat('*', max(1, mb_strlen($name) - mb_strlen($visible))) . '@' . $domain;
    }
}
