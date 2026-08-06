<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\MobileUser;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class SystemNotificationService
{
    public function __construct(
        protected ExpoPushNotificationService $expoPushNotificationService,
        protected NotificationTemplateService $templates
    ) {}

    /**
     * Kirim ke satu pengguna memakai TEMPLATE: in-app (database) pakai template
     * `in_app`, push pakai template `push`. Teks tak lagi hardcoded.
     */
    private function dispatchToUser(MobileUser $user, string $eventKey, array $context, string $type = SystemNotification::TYPE_INFORMATION, ?string $url = null): void
    {
        $context = array_merge([
            'recipient_name' => $user->name,
            'customer_name' => $user->name,
        ], $context);

        $inApp = $this->templates->render($eventKey, 'in_app', 'user', $context);
        if (trim($inApp['subject'] . $inApp['body']) !== '') {
            $user->notify(new SystemNotification($inApp['subject'], $inApp['body'], $type, $url, $context));
        }

        $push = $this->templates->render($eventKey, 'push', 'user', $context);
        if (trim($push['subject'] . $push['body']) !== '') {
            $this->sendExpoPushToUsers(collect([$user]), $push['subject'], $push['body'], $type, $url, $context);
        }
    }

    /** Kirim ke semua admin memakai template in-app admin. */
    private function dispatchToAdmins(string $eventKey, array $context, string $type = SystemNotification::TYPE_INFORMATION, ?string $url = null): void
    {
        $inApp = $this->templates->render($eventKey, 'in_app', 'admin', $context);
        if (trim($inApp['subject'] . $inApp['body']) !== '') {
            $this->notifyAdmins($inApp['subject'], $inApp['body'], $type, $url, $context);
        }
    }

    /** Context standar dari sebuah pengajuan (service request). */
    private function serviceRequestContext($sr, array $extra = []): array
    {
        return array_merge([
            'transaction_code' => $sr->transaction_code_label,
            'service_title' => $sr->service?->title ?? '-',
            'customer_name' => $sr->user?->name,
            'survey_date' => optional($sr->survey_date)?->format('d M Y') ?? '-',
            'survey_address' => $sr->survey_address ?? '-',
            'total_amount' => 'Rp' . number_format((int) $sr->total_amount, 0, ',', '.'),
            'admin_note' => $sr->admin_note ?? '',
            'rejection_reason' => $sr->rejection_reason ?? '',
            'payment_status' => $sr->payment_status ?? '',
            'service_request_id' => $sr->id,
            'service_request_code' => $sr->transaction_code_label,
        ], $extra);
    }

    public function notifyAllMobileUsers(string $title, string $message, string $type = SystemNotification::TYPE_INFORMATION, ?string $url = null, array $meta = []): void
    {
        $users = MobileUser::query()
            ->where('is_active', true)
            ->get();

        if ($users->isNotEmpty()) {
            Notification::send($users, new SystemNotification($title, $message, $type, $url, $meta));
            $this->sendExpoPushToUsers($users, $title, $message, $type, $url, $meta);
        }
    }

    public function notifyMobileUsersByIds(array $userIds, string $title, string $message, string $type = SystemNotification::TYPE_INFORMATION, ?string $url = null, array $meta = []): void
    {
        $users = MobileUser::query()
            ->whereIn('id', $userIds)
            ->where('is_active', true)
            ->get();

        if ($users->isNotEmpty()) {
            Notification::send($users, new SystemNotification($title, $message, $type, $url, $meta));
            $this->sendExpoPushToUsers($users, $title, $message, $type, $url, $meta);
        }
    }

    public function notifyMobileUser(MobileUser $user, string $title, string $message, string $type = 'info', ?string $url = null, array $meta = []): void
    {
        $user->notify(new SystemNotification($title, $message, $type, $url, $meta));
        $this->sendExpoPushToUsers(collect([$user]), $title, $message, $type, $url, $meta);
    }

    public function notifyAdmins(string $title, string $message, string $type = 'info', ?string $url = null, array $meta = []): void
    {
        $admins = User::query()
            ->with('role')
            ->whereHas('role', function ($query) {
                $query->whereIn('nama', ['admin', 'superadmin']);
            })
            ->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new SystemNotification($title, $message, $type, $url, $meta));
        }
    }

    public function notifyServiceRequestCreated($serviceRequest): void
    {
        $url = '/admin/mobile/service-requests/' . $serviceRequest->id;
        $ctx = $this->serviceRequestContext($serviceRequest);

        if ($serviceRequest->user) {
            $this->dispatchToUser($serviceRequest->user, 'service_request.submitted', $ctx, SystemNotification::TYPE_INFORMATION);
        }
        $this->dispatchToAdmins('service_request.submitted', $ctx, SystemNotification::TYPE_INFORMATION, $url);
    }

    /** Notifikasi pengajuan (proposal) baru — untuk user & admin, dari template. */
    public function notifyProposalSubmitted(\App\Models\Proposal $proposal, $serviceRequest): void
    {
        $ctx = $this->serviceRequestContext($serviceRequest, [
            'proposal_number' => $proposal->proposal_number,
            'total_amount' => 'Rp' . number_format((int) $proposal->total_amount, 0, ',', '.'),
        ]);
        $url = '/admin/mobile/service-requests/' . $serviceRequest->id;

        if ($serviceRequest->user) {
            $this->dispatchToUser($serviceRequest->user, 'proposal.submitted', $ctx, SystemNotification::TYPE_CONFIRMATION);
        }
        $this->dispatchToAdmins('proposal.submitted', $ctx, SystemNotification::TYPE_INFORMATION, $url);
    }

    public function notifyServiceRequestPaymentUpdated($serviceRequest): void
    {
        $ctx = $this->serviceRequestContext($serviceRequest);
        $url = '/admin/mobile/service-requests/' . $serviceRequest->id;

        if ($serviceRequest->user) {
            $this->dispatchToUser($serviceRequest->user, 'service_request.payment_updated', $ctx, SystemNotification::TYPE_CONFIRMATION);
        }
        $this->dispatchToAdmins('service_request.payment_updated', $ctx, SystemNotification::TYPE_CONFIRMATION, $url);
    }

    public function notifyServiceRequestDecision($serviceRequest, string $decision): void
    {
        $eventKey = match ($decision) {
            'approved' => 'service_request.approved',
            'completed' => 'service_request.completed',
            default => 'service_request.rejected',
        };
        $ctx = $this->serviceRequestContext($serviceRequest, ['decision' => $decision]);
        $url = '/admin/mobile/service-requests/' . $serviceRequest->id;

        if ($serviceRequest->user) {
            $this->dispatchToUser($serviceRequest->user, $eventKey, $ctx, SystemNotification::TYPE_CONFIRMATION);
        }
        // Admin: sebagian event keputusan tak punya template admin → fallback aman (skip bila kosong).
        $this->dispatchToAdmins($eventKey, $ctx, SystemNotification::TYPE_CONFIRMATION, $url);
    }

    public function notifyCampaign(string $title, string $message, string $type, bool $sendToAll, array $userIds = [], ?string $url = null, array $meta = [], ?string $contentHtml = null): void
    {
        $type = in_array($type, [
            SystemNotification::TYPE_PROMO,
            SystemNotification::TYPE_INFORMATION,
            SystemNotification::TYPE_CONFIRMATION,
        ], true) ? $type : SystemNotification::TYPE_INFORMATION;

        if ($contentHtml !== null) {
            $meta['content_html'] = $contentHtml;
            $message = Str::of(strip_tags($contentHtml))
                ->replaceMatches('/\s+/u', ' ')
                ->trim()
                ->toString();
        }

        if ($sendToAll) {
            $this->notifyAllMobileUsers($title, $message, $type, $url, $meta);
            $this->notifyCampaignAdmins($title, $type, null, $url, $meta, true);
            return;
        }

        if ($userIds !== []) {
            $this->notifyMobileUsersByIds($userIds, $title, $message, $type, $url, $meta);
            $this->notifyCampaignAdmins($title, $type, count($userIds), $url, $meta, false);
        }
    }

    public function notifyChatMessageToMobileUser(ChatConversation $conversation, ChatMessage $message, User $admin): void
    {
        if (! $conversation->mobileUser) {
            return;
        }

        $preview = Str::limit(strip_tags($message->body), 120);
        $tokens = $conversation->mobileUser->pushTokens()->where('is_active', true)->get();

        if ($tokens->isEmpty()) {
            return;
        }

        $rendered = $this->templates->render('chat.message_to_user', 'push', 'user', [
            'recipient_name' => $conversation->mobileUser->name,
            'sender_name' => $admin->name,
            'message_preview' => $preview,
        ]);
        $title = $rendered['subject'] !== '' ? $rendered['subject'] : $admin->name;
        $preview = $rendered['body'] !== '' ? $rendered['body'] : $preview;

        \App\Jobs\SendExpoPushJob::dispatch($tokens->pluck('expo_push_token')->filter()->values()->all(), [
            'title' => $title,
            'body' => $preview,
            'message' => $preview,
            'data' => [
                'type' => SystemNotification::TYPE_INFORMATION,
                'url' => '/messages',
                'meta' => [
                    'conversation_id' => $conversation->id,
                    'service_request_id' => $conversation->service_request_id,
                    'service_request_code' => $conversation->serviceRequest?->transaction_code_label,
                    'sender_type' => 'admin',
                    'sender_name' => $admin->name,
                ],
                'title' => $title,
                'message' => $preview,
            ],
        ]);
    }

    public function notifyChatMessageToAdmins(ChatConversation $conversation, ChatMessage $message, MobileUser $mobileUser): void
    {
        $preview = Str::limit(strip_tags($message->body), 120);
        $rendered = $this->templates->render('chat.message_to_admins', 'in_app', 'admin', [
            'sender_name' => $mobileUser->name,
            'message_preview' => $preview,
        ]);
        $title = $rendered['subject'] !== '' ? $rendered['subject'] : 'Pesan baru dari user';
        $preview = $rendered['body'] !== '' ? $rendered['body'] : $preview;

        $this->notifyAdmins($title, $preview, SystemNotification::TYPE_INFORMATION, '/admin/mobile/live-chat/' . $conversation->id, [
            'conversation_id' => $conversation->id,
            'service_request_id' => $conversation->service_request_id,
            'service_request_code' => $conversation->serviceRequest?->transaction_code_label,
            'sender_type' => 'mobile',
            'sender_name' => $mobileUser->name,
            'user_name' => $mobileUser->name,
        ]);
    }

    private function sendExpoPushToUsers(Collection $users, string $title, string $message, string $type, ?string $url, array $meta): void
    {
        $userIds = $users->pluck('id')->filter()->values()->all();

        if ($userIds === []) {
            return;
        }

        $tokens = \App\Models\MobilePushToken::query()
            ->whereIn('mobile_user_id', $userIds)
            ->where('is_active', true)
            ->get();

        if ($tokens->isEmpty()) {
            return;
        }

        \App\Jobs\SendExpoPushJob::dispatch($tokens->pluck('expo_push_token')->filter()->values()->all(), [
            'title' => $title,
            'body' => $message,
            'message' => $message,
            'data' => [
                'type' => $type,
                'url' => $url,
                'meta' => $meta,
                'title' => $title,
                'message' => $message,
            ],
        ]);
    }

    private function notifyCampaignAdmins(string $title, string $type, ?int $recipientCount, ?string $url, array $meta, bool $sendToAll): void
    {
        $campaignLabel = match ($type) {
            SystemNotification::TYPE_PROMO => 'Promo',
            SystemNotification::TYPE_CONFIRMATION => 'Konfirmasi',
            default => 'Informasi',
        };

        $scopeLabel = $sendToAll ? 'semua user aktif' : (($recipientCount ?? 0) . ' user terpilih');
        $summaryTitle = $campaignLabel . ' berhasil dikirim';
        $summaryMessage = $title . ' telah dikirim ke ' . $scopeLabel . '.';

        $this->notifyAdmins($summaryTitle, $summaryMessage, $type, $url ?? '/admin/mobile/notifications?type=' . $type, [
            ...$meta,
            'campaign_title' => $title,
            'campaign_type' => $type,
            'recipient_count' => $recipientCount,
            'send_to_all' => $sendToAll,
        ]);
    }
}
