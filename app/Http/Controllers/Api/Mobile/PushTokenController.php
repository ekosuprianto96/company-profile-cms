<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Services\MobilePushTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PushTokenController extends ApiController
{
    public function __construct(
        protected MobilePushTokenService $mobilePushTokenService
    ) {}

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'expo_push_token' => ['required', 'string', 'max:255'],
                'platform' => ['nullable', 'string', 'in:android,ios,web'],
                'device_name' => ['nullable', 'string', 'max:150'],
            ]);

            $pushToken = $this->mobilePushTokenService->sync($request->user(), $validated);

            return $this->success([
                'push_token' => [
                    'id' => $pushToken->id,
                    'expo_push_token' => $pushToken->expo_push_token,
                    'platform' => $pushToken->platform,
                    'device_name' => $pushToken->device_name,
                    'is_active' => $pushToken->is_active,
                    'last_seen_at' => optional($pushToken->last_seen_at)?->toISOString(),
                ],
            ], 'Token push berhasil disinkronkan.');
        } catch (\Throwable $th) {
            Log::error('Push token sync error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function destroyCurrent(Request $request)
    {
        try {
            $validated = $request->validate([
                'expo_push_token' => ['required', 'string', 'max:255'],
            ]);

            $request->user()
                ->pushTokens()
                ->where('expo_push_token', $validated['expo_push_token'])
                ->update([
                    'is_active' => false,
                    'last_seen_at' => now(),
                ]);

            return $this->success([], 'Token push berhasil dinonaktifkan.');
        } catch (\Throwable $th) {
            Log::error('Push token deactivate error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}
