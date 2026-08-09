<?php

namespace App\Events\Mobile;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Indikator "sedang mengetik". Disiarkan ke channel chat yang sama dengan pesan,
 * lalu tiap sisi hanya menampilkan indikator dari pihak LAWAN (filter sender_type).
 * Ringan & tidak menyentuh DB.
 */
class ChatTyping implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $conversationId,
        public int $mobileUserId,
        public string $senderType, // 'admin' | 'mobile'
        public string $senderName,
        public bool $isTyping
    ) {}

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('admin.mobile.chat')];

        if ($this->mobileUserId > 0) {
            $channels[] = new PrivateChannel('mobile.chat.user.' . $this->mobileUserId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'chat.typing';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'sender_type' => $this->senderType,
            'sender_name' => $this->senderName,
            'is_typing' => $this->isTyping,
        ];
    }
}
