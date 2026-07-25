<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

abstract class ApiController extends Controller
{
    protected function normalizeStatus(int $status, int $fallback = 500): int
    {
        return ($status >= 100 && $status <= 599) ? $status : $fallback;
    }

    protected function success(array $data = [], string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $this->normalizeStatus($status, 200));
    }

    protected function error(string $message, int $status = 422, array $errors = [], ?string $code = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ];

        // Kode mesin opsional agar klien bisa menangani kasus khusus (mis.
        // 'account_blocked' → alihkan ke layar informasi blokir).
        if ($code !== null) {
            $payload['code'] = $code;
        }

        return response()->json($payload, $this->normalizeStatus($status, 500));
    }

    /**
     * Respons standar untuk akun diblokir (code 'account_blocked') bila exception
     * yang ditangkap adalah MobileAccountBlockedException; selain itu null.
     */
    protected function accountBlockedResponse(\Throwable $th): ?JsonResponse
    {
        if ($th instanceof \App\Exceptions\MobileAccountBlockedException) {
            return $this->error($th->getMessage(), 403, [], 'account_blocked');
        }

        return null;
    }

    protected function userPayload($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar_url' => $user->avatar_url,
            'email_verified_at' => optional($user->email_verified_at)?->toISOString(),
            'phone_verified_at' => optional($user->phone_verified_at)?->toISOString(),
            'is_active' => (bool) $user->is_active,
            'last_login_at' => optional($user->last_login_at)?->toISOString(),
            'created_at' => optional($user->created_at)?->toISOString(),
        ];
    }
}
