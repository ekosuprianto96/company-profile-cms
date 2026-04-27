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

        if (! $user->is_active) {
            throw new \Exception('Akun sedang nonaktif.', 403);
        }

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

        if ($payload['purpose'] === 'login' && ! $user->isVerified()) {
            throw new \Exception('Akun belum aktif untuk login OTP.', 403);
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

    public function issueToken(MobileUser $user, string $deviceName): array
    {
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
