<?php

namespace App\Http\Controllers\Api\Admin;

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
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $this->normalizeStatus($status, 200));
    }

    protected function error(string $message, int $status = 422, array $errors = []): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message, 'errors' => $errors], $this->normalizeStatus($status, 500));
    }

    protected function adminPayload($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => optional($user->role)->name ?? null,
            'credential_key_masked' => $user->credential_key ? substr($user->credential_key, 0, 7) . '••••' : null,
        ];
    }
}
