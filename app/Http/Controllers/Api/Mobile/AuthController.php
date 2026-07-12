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
use Illuminate\Validation\ValidationException;

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

    public function sendPasswordOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'channel' => ['nullable', 'in:email,sms'],
            ]);

            $channel = $validated['channel'] ?? 'email';
            $otp = $this->mobileAuthService->sendPasswordChangeOtp($request->user(), $channel);

            return $this->success([
                'verification' => [
                    'channel' => $otp->channel,
                    'recipient' => $otp->recipient,
                    'expires_at' => $otp->expires_at->toISOString(),
                ],
            ], 'Kode verifikasi telah dikirim.');
        } catch (ValidationException $th) {
            return $this->error($th->getMessage() ?: 'Permintaan tidak valid.', 422, $th->errors());
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function verifyPasswordOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'channel' => ['nullable', 'in:email,sms'],
                'code' => ['required', 'string'],
            ]);

            $this->mobileAuthService->verifyPasswordChangeOtp($request->user(), $validated['channel'] ?? 'email', $validated['code']);

            return $this->success([], 'Kode verifikasi valid.');
        } catch (ValidationException $th) {
            return $this->error($th->getMessage() ?: 'Permintaan tidak valid.', 422, $th->errors());
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $this->mobileAuthService->changePassword($request->user(), $validated['password']);

            return $this->success([], 'Password berhasil diperbarui.');
        } catch (ValidationException $th) {
            return $this->error($th->getMessage() ?: 'Permintaan tidak valid.', 422, $th->errors());
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}
