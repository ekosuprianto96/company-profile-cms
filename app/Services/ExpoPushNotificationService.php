<?php

namespace App\Services;

use App\Models\MobilePushToken;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoPushNotificationService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function sendToTokens(Collection|array $tokens, array $payload): void
    {
        $expoTokens = collect($tokens)
            ->map(function ($token) {
                if ($token instanceof MobilePushToken) {
                    return $token->expo_push_token;
                }

                if (is_string($token)) {
                    return $token;
                }

                return null;
            })
            ->filter(fn ($token) => is_string($token) && trim($token) !== '')
            ->unique()
            ->values();

        if ($expoTokens->isEmpty()) {
            return;
        }

        $messages = $expoTokens->map(function (string $expoPushToken) use ($payload) {
            return array_filter([
                'to' => $expoPushToken,
                'title' => $payload['title'] ?? 'Notifikasi',
                'body' => $payload['body'] ?? $payload['message'] ?? '',
                'sound' => 'default',
                'channelId' => 'default',
                'priority' => 'high',
                'data' => $payload['data'] ?? [],
                'badge' => $payload['badge'] ?? null,
            ], static fn ($value) => $value !== null);
        })->values();

        try {
            $messages->chunk(100)->each(function ($chunk) {
                $response = Http::connectTimeout(5)->timeout(10)
                    ->acceptJson()
                    ->asJson()
                    ->post('https://exp.host/--/api/v2/push/send', $chunk->values()->all());

                if (! $response->successful()) {
                    Log::warning('Expo push notification request failed.', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'payload_size' => $chunk->count(),
                    ]);
                }
            });
        } catch (\Throwable $th) {
            Log::warning('Expo push notification exception.', [
                'message' => $th->getMessage(),
                'payload_size' => $messages->count(),
            ]);
        }
    }
}
