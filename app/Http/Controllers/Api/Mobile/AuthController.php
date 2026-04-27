<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Requests\Api\Mobile\UpdateMobileProfileRequest;
use App\Http\Requests\Api\Mobile\LoginMobileRequest;
use App\Http\Requests\Api\Mobile\RegisterMobileRequest;
use App\Http\Requests\Api\Mobile\SendMobileOtpRequest;
use App\Http\Requests\Api\Mobile\VerifyMobileOtpRequest;
use App\Services\MobileAuthService;
use App\Services\MobileProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends ApiController
{
    public function __construct(
        protected MobileAuthService $mobileAuthService,
        protected MobileProfileService $mobileProfileService
    ) {}

    public function register(RegisterMobileRequest $request)
    {
        try {
            $result = $this->mobileAuthService->register($request->validated());

            return $this->success([
                'user' => $this->userPayload($result['user']),
                'verification' => [
                    'purpose' => 'register',
                    'channel' => $result['otp']->channel,
                    'recipient' => $result['otp']->recipient,
                    'expires_at' => $result['otp']->expires_at->toISOString(),
                ],
            ], 'Registrasi berhasil. OTP telah dikirim.');
        } catch (\Throwable $th) {
            Log::error('Registration error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function login(LoginMobileRequest $request)
    {
        try {

            Log::info(config("mail"));
            $result = $this->mobileAuthService->login($request->validated());

            return $this->success([
                'user' => $this->userPayload($result['user']),
                'token' => $result['token'],
                'token_type' => $result['token_type'],
                'expires_at' => $result['expires_at'],
            ], 'Login berhasil.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function sendOtp(SendMobileOtpRequest $request)
    {
        try {
            $result = $this->mobileAuthService->sendOtp($request->validated());

            return $this->success([
                'user' => $this->userPayload($result['user']),
                'verification' => [
                    'purpose' => $result['otp']->purpose,
                    'channel' => $result['otp']->channel,
                    'recipient' => $result['otp']->recipient,
                    'expires_at' => $result['otp']->expires_at->toISOString(),
                ],
            ], 'OTP berhasil dikirim ulang.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function verifyOtp(VerifyMobileOtpRequest $request)
    {
        try {
            $result = $this->mobileAuthService->verifyOtp($request->validated());

            return $this->success([
                'user' => $this->userPayload($result['user']),
                'token' => $result['token'],
                'token_type' => $result['token_type'],
                'expires_at' => $result['expires_at'],
            ], 'OTP berhasil diverifikasi.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function me(Request $request)
    {
        return $this->success([
            'user' => $this->userPayload($request->user()),
        ], 'Profil user mobile berhasil dimuat.');
    }

    public function updateProfile(UpdateMobileProfileRequest $request)
    {
        try {
            $user = $this->mobileProfileService->update(
                $request->user(),
                $request->validated(),
                $request->file('avatar')
            );

            return $this->success([
                'user' => $this->userPayload($user),
            ], 'Profil berhasil diperbarui.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $this->mobileAuthService->logout($request->user());

            return $this->success([], 'Logout berhasil.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}
