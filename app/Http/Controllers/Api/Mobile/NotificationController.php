<?php

namespace App\Http\Controllers\Api\Mobile;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends ApiController
{
    public function index(Request $request)
    {
        $type = $this->normalizeType($request->query('type'));
        $perPage = max(5, min((int) $request->query('per_page', 20), 50));

        // Keyset/cursor pagination pada (created_at, id) → cepat & stabil untuk infinite scroll.
        $paginator = $request->user()->notifications()
            ->when($type, fn ($query) => $query->where('category', $type))
            ->reorder()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->cursorPaginate($perPage);

        $notifications = collect($paginator->items())
            ->map(fn (DatabaseNotification $notification) => $this->notificationPayload($notification))
            ->values();

        return $this->success([
            'notifications' => $notifications,
            'next_cursor' => optional($paginator->nextCursor())->encode(),
            'has_more' => $paginator->hasMorePages(),
            'unread_count' => $request->user()->unreadNotifications()->count(),
            'unread_counts_by_type' => $this->unreadCountsByType($request->user()),
        ], 'Notifikasi berhasil dimuat.');
    }

    public function show(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();

        return $this->success([
            'notification' => $this->notificationPayload($notification),
        ], 'Detail notifikasi berhasil dimuat.');
    }

    public function unreadCount(Request $request)
    {
        return $this->success([
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ], 'Jumlah notifikasi belum dibaca berhasil dimuat.');
    }

    public function markRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        return $this->success([
            'notification' => $this->notificationPayload($notification->fresh()),
        ], 'Notifikasi ditandai sudah dibaca.');
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return $this->success([], 'Semua notifikasi ditandai sudah dibaca.');
    }

    private function notificationPayload(DatabaseNotification $notification): array
    {
        $data = $notification->data ?? [];

        return [
            'id' => $notification->id,
            'type' => $this->normalizeType($data['type'] ?? class_basename($notification->type)) ?? 'informasi',
            'title' => $data['title'] ?? 'Notifikasi',
            'message' => $data['message'] ?? '',
            'content_html' => $data['content_html'] ?? null,
            'url' => $data['url'] ?? null,
            'meta' => $this->publicMeta($data['meta'] ?? []),
            'read_at' => optional($notification->read_at)?->toISOString(),
            'created_at' => optional($notification->created_at)?->toISOString(),
        ];
    }

    private function publicMeta(mixed $meta): array
    {
        if (! is_array($meta)) {
            return [];
        }

        $blockedKeys = [
            'admin_id',
            'target',
            'user_ids',
            'user_id',
            'service_request_id',
            'conversation_id',
            'notifiable_id',
            'notifiable_type',
            'sender_type',
            'internal',
            'debug',
        ];

        $public = [];

        foreach ($meta as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            if (in_array($key, $blockedKeys, true) || str_starts_with($key, '_')) {
                continue;
            }

            if (is_array($value)) {
                $nested = $this->publicMeta($value);
                if ($nested !== []) {
                    $public[$key] = $nested;
                }
                continue;
            }

            $public[$key] = $value;
        }

        return $public;
    }

    private function normalizeType(mixed $type): ?string
    {
        $type = is_string($type) ? trim(strtolower($type)) : '';

        $legacyMap = [
            'info' => 'informasi',
            'success' => 'konfirmasi',
            'warning' => 'informasi',
            'danger' => 'konfirmasi',
            'secondary' => 'informasi',
            'system.notification' => 'informasi',
        ];

        if (isset($legacyMap[$type])) {
            return $legacyMap[$type];
        }

        return in_array($type, ['promo', 'informasi', 'konfirmasi'], true) ? $type : null;
    }

    /**
     * Hitung unread per kategori langsung di SQL (COUNT ... GROUP BY), memakai index.
     *
     * @return array<string, int>
     */
    private function unreadCountsByType(\App\Models\MobileUser $user): array
    {
        $counts = [
            'promo' => 0,
            'informasi' => 0,
            'konfirmasi' => 0,
        ];

        $rows = $user->notifications()
            ->reorder()
            ->whereNull('read_at')
            ->selectRaw('category, COUNT(*) as aggregate')
            ->groupBy('category')
            ->pluck('aggregate', 'category');

        foreach ($rows as $category => $total) {
            $key = isset($counts[$category]) ? $category : 'informasi';
            $counts[$key] += (int) $total;
        }

        return $counts;
    }
}
