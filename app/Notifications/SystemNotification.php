<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

// Async: penulisan notifikasi in-app + broadcast lewat queue agar tak menahan request.
class SystemNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public const TYPE_PROMO = 'promo';
    public const TYPE_INFORMATION = 'informasi';
    public const TYPE_CONFIRMATION = 'konfirmasi';

    public function __construct(
        public string $title,
        public string $message,
        public string $type = 'info',
        public ?string $url = null,
        public array $meta = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->payload($notifiable);
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload($notifiable));
    }

    public function broadcastType(): string
    {
        return 'system.notification';
    }

    private function payload(object $notifiable): array
    {
        $contentHtml = is_string($this->meta['content_html'] ?? null)
            ? trim((string) $this->meta['content_html'])
            : null;

        $meta = $this->meta;
        unset($meta['content_html']);

        return [
            'title' => $this->title,
            'message' => $this->message,
            'content_html' => $contentHtml !== '' ? $contentHtml : null,
            'type' => $this->type,
            'url' => $this->url,
            'meta' => $meta,
            'notifiable_name' => $notifiable->name ?? null,
            'app_name' => config('settings.value.app_name', config('app.name')),
            'created_at' => now()->toISOString(),
        ];
    }
}
