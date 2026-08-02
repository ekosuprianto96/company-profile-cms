<?php

namespace App\Http\Controllers\Api\Admin;

use App\Services\AdminAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends ApiController
{
    public function __construct(protected AdminAuthService $adminAuthService) {}

    /** Tahap 1: email + password + credential key → kirim OTP email. */
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
                'credential_key' => ['required', 'string'],
            ]);

            $result = $this->adminAuthService->requestOtp($validated);

            return $this->success([
                'email_masked' => $result['email_masked'],
                'expires_at' => optional($result['expires_at'])?->toISOString(),
            ], 'OTP telah dikirim ke email admin.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    /** Tahap 2: verifikasi OTP → token. */
    public function verifyOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
                'code' => ['required', 'string'],
                'device_name' => ['nullable', 'string', 'max:100'],
            ]);

            $result = $this->adminAuthService->verifyOtp($validated);

            return $this->success([
                'admin' => $this->adminPayload($result['user']),
                'token' => $result['token'],
                'token_type' => $result['token_type'],
            ], 'Login admin berhasil.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function resendOtp(Request $request)
    {
        try {
            $validated = $request->validate(['email' => ['required', 'email']]);
            $result = $this->adminAuthService->resendOtp($validated);

            return $this->success([
                'email_masked' => $result['email_masked'],
                'expires_at' => optional($result['expires_at'])?->toISOString(),
            ], 'OTP dikirim ulang.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function me(Request $request)
    {
        return $this->success(['admin' => $this->adminPayload($request->user())], 'Profil admin.');
    }

    public function logout(Request $request)
    {
        try {
            $this->adminAuthService->logout($request->user());

            return $this->success([], 'Logout berhasil.');
        } catch (\Throwable $th) {
            Log::error('Admin logout error: ' . $th->getMessage());

            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}
