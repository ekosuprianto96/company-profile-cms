<?php

namespace App\Events\Mobile;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageCreated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public array $conversation,
        public array $message
    ) {}

    public function broadcastOn(): array
    {
        $mobileUserId = (int) ($this->conversation['mobile_user']['id'] ?? 0);

        $channels = [new PrivateChannel('admin.mobile.chat')];

        if ($mobileUserId > 0) {
            $channels[] = new PrivateChannel('mobile.chat.user.' . $mobileUserId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'chat.message.created';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation' => $this->conversation,
            'message' => $this->message,
        ];
    }
}
