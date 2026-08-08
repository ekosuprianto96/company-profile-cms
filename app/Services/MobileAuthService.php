<?php

namespace App\Services;

use App\Models\MobileUser;
use App\Repositories\MobileUserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MobileAuthService
{
    public function __construct(
        protected MobileUserRepository $mobileUserRepository,
        protected MobileUserOtpService $mobileUserOtpService
    ) {}

    public function register(array $payload): array
    {
        return DB::transaction(function () use ($payload) {
            $user = $this->mobileUserRepository->store([
                'name' => $payload['name'],
                'email' => $payload['email'] ?? null,
                'phone' => $payload['phone'] ?? null,
                'password' => $payload['password'],
                'is_active' => true,
            ]);

            $otp = $this->mobileUserOtpService->createAndSend(
                $user,
                $payload['otp_channel'],
                'register'
            );

            return [
                'user' => $user->fresh(),
                'otp' => $otp->fresh(),
            ];
        });
    }

    public function login(array $payload): array
    {
        $user = $this->mobileUserRepository->findByLogin($payload['login']);

        if (! $user || ! Hash::check($payload['password'], (string) $user->password)) {
            throw new \Exception('Email/nomor telepon atau password tidak valid.', 422);
        }

        $this->assertCanAccess($user);

        if (! $user->isVerified()) {
            throw new \Exception('Akun belum diverifikasi. Silakan verifikasi OTP terlebih dahulu.', 403);
        }

        return $this->issueToken($user, $payload['device_name']);
    }

    public function sendOtp(array $payload): array
    {
        if ($payload['purpose'] === 'login') {
            $user = $this->resolveLoginOtpUser($payload);
        } else {
            $recipient = $payload['channel'] === 'sms' ? $payload['phone'] : $payload['email'];
            $user = $this->mobileUserRepository->findByRecipient($recipient, $payload['channel']);
        }

        if (! $user) {
            throw new \Exception('Akun mobile tidak ditemukan untuk pengiriman OTP.', 404);
        }

        // Jangan kirim OTP login ke akun yang diblokir/nonaktif.
        if ($payload['purpose'] === 'login') {
            $this->assertCanAccess($user);

            // Akun belum diverifikasi? JANGAN blokir. Alihkan ke verifikasi:
            // kirim ulang OTP 'register' lalu kembalikan payload verifikasi
            // (purpose=register) supaya aplikasi mengarahkan user ke layar OTP,
            // bukan buntu di pesan error.
            if (! $user->isVerified()) {
                $otp = $this->mobileUserOtpService->createAndSend($user, $payload['channel'], 'register');

                return [
                    'user' => $user,
                    'otp' => $otp->fresh(),
                ];
            }
        }

        if ($payload['purpose'] === 'register' && $user->isVerified()) {
            throw new \Exception('Akun ini sudah terverifikasi.', 422);
        }

        $otp = $this->mobileUserOtpService->createAndSend($user, $payload['channel'], $payload['purpose']);
        
        return [
            'user' => $user,
            'otp' => $otp->fresh(),
        ];
    }

    public function verifyOtp(array $payload): array
    {
        $recipient = $payload['channel'] === 'sms' ? $payload['phone'] : $payload['email'];
        $otp = $this->mobileUserOtpService->verify(
            recipient: $recipient,
            channel: $payload['channel'],
            purpose: $payload['purpose'],
            code: $payload['code']
        );

        $user = $otp->user;

        if (! $user) {
            throw new \Exception('Akun mobile untuk OTP ini tidak ditemukan.', 404);
        }

        if ($payload['channel'] === 'email' && is_null($user->email_verified_at)) {
            $user->email_verified_at = now();
        }

        if ($payload['channel'] === 'sms' && is_null($user->phone_verified_at)) {
            $user->phone_verified_at = now();
        }

        $user->save();

        return $this->issueToken($user->fresh(), $payload['device_name']);
    }

    public function logout(MobileUser $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    public function sendPasswordChangeOtp(MobileUser $user, string $channel = 'email'): \App\Models\MobileUserOtp
    {
        $channel = $channel === 'sms' ? 'sms' : 'email';

        if ($channel === 'email' && empty($user->email)) {
            throw new \Exception('Email belum tersedia untuk verifikasi.', 422);
        }

        if ($channel === 'sms' && empty($user->phone)) {
            throw new \Exception('Nomor telepon belum tersedia untuk verifikasi.', 422);
        }

        return $this->mobileUserOtpService->createAndSend($user, $channel, 'password_change');
    }

    public function verifyPasswordChangeOtp(MobileUser $user, string $channel, string $code): void
    {
        $channel = $channel === 'sms' ? 'sms' : 'email';
        $recipient = $channel === 'sms' ? $user->phone : $user->email;

        if (empty($recipient)) {
            throw new \Exception('Kontak untuk verifikasi tidak tersedia.', 422);
        }

        // Melempar exception bila kode tidak valid/kadaluarsa. Menandai OTP sebagai verified.
        $this->mobileUserOtpService->verify($recipient, $channel, 'password_change', $code);
    }

    public function changePassword(MobileUser $user, string $password): MobileUser
    {
        // Wajib ada OTP password_change yang baru saja terverifikasi (langkah "Lanjutkan").
        $otp = \App\Models\MobileUserOtp::query()
            ->where('mobile_user_id', $user->id)
            ->where('purpose', 'password_change')
            ->where('status', 'verified')
            ->whereNotNull('verified_at')
            ->where('verified_at', '>=', now()->subMinutes(15))
            ->latest('verified_at')
            ->first();

        if (! $otp) {
            throw new \Exception('Verifikasi OTP diperlukan atau sudah kadaluarsa. Silakan verifikasi ulang.', 422);
        }

        $user->password = Hash::make($password);
        $user->save();

        // Konsumsi OTP agar tidak bisa dipakai ulang.
        $otp->update(['status' => 'expired']);

        return $user->fresh();
    }

    /** Pastikan akun boleh mengakses (tidak diblokir & aktif). */
    private function assertCanAccess(MobileUser $user): void
    {
        if ($user->isBanned()) {
            throw new \App\Exceptions\MobileAccountBlockedException(
                $user->ban_reason
                    ? 'Akun Anda diblokir: ' . $user->ban_reason
                    : 'Akun Anda telah diblokir. Hubungi admin untuk informasi lebih lanjut.',
                $user->ban_reason,
            );
        }

        if (! $user->is_active) {
            throw new \Exception('Akun sedang nonaktif.', 403);
        }
    }

    public function issueToken(MobileUser $user, string $deviceName): array
    {
        // Choke point semua jalur login (password & OTP): tolak akun banned/nonaktif.
        $this->assertCanAccess($user);

        $token = $user->createToken(
            $deviceName,
            ['mobile:auth'],
            now()->addDays(config('mobile_auth.token_expiration_days'))
        );

        $user->update([
            'last_login_at' => now(),
        ]);

        return [
            'user' => $user->fresh(),
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => now()->addDays(config('mobile_auth.token_expiration_days'))->toISOString(),
        ];
    }

    private function resolveLoginOtpUser(array $payload): ?MobileUser
    {
        $channel = $payload['channel'];
        $email = $payload['email'] ?? null;
        $phone = $payload['phone'] ?? null;

        if ($channel === 'email') {
            $user = $email
                ? $this->mobileUserRepository->findByEmail($email)
                : ($phone ? $this->mobileUserRepository->findByPhone($phone) : null);

            if ($user && empty($user->email)) {
                throw new \Exception('Akun ini belum memiliki email terdaftar untuk OTP email.', 422);
            }

            return $user;
        }

        $user = $phone
            ? $this->mobileUserRepository->findByPhone($phone)
            : ($email ? $this->mobileUserRepository->findByEmail($email) : null);

        if ($user && empty($user->phone)) {
            throw new \Exception('Akun ini belum memiliki nomor telepon terdaftar untuk OTP SMS.', 422);
        }

        return $user;
    }
}
