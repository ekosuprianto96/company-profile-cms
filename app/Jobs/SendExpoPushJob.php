<?php

namespace App\Jobs;

use App\Services\ExpoPushNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Kirim Expo push notification secara async supaya HTTP ke exp.host (bisa lambat)
 * tidak menahan response dari jalur request (submit pengajuan, bayar, chat, campaign).
 */
class SendExpoPushJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 10;

    /** @param string[] $tokens */
    public function __construct(
        public array $tokens,
        public array $payload,
    ) {}

    public function handle(ExpoPushNotificationService $expo): void
    {
        if (empty($this->tokens)) {
            return;
        }

        $expo->sendToTokens($this->tokens, $this->payload);
    }
}
