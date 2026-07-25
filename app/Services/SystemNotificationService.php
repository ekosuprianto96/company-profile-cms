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
        protected ExpoPushNotificationService $expoPushNotificationService
    ) {}

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
        $title = 'Pengajuan baru masuk';
        $message = 'Pengajuan survey ' . $serviceRequest->transaction_code_label . ' untuk layanan ' . ($serviceRequest->service?->title ?? '-') . ' baru saja dibuat.';
        $url = '/admin/mobile/service-requests/' . $serviceRequest->id;

        if ($serviceRequest->user) {
            $this->notifyMobileUser($serviceRequest->user, $title, $message, SystemNotification::TYPE_INFORMATION, null, [
                'service_request_id' => $serviceRequest->id,
                'service_request_code' => $serviceRequest->transaction_code_label,
                'user_name' => $serviceRequest->user->name,
            ]);
        }

        $this->notifyAdmins($title, $message, SystemNotification::TYPE_INFORMATION, $url, [
            'service_request_id' => $serviceRequest->id,
            'service_request_code' => $serviceRequest->transaction_code_label,
            'user_name' => $serviceRequest->user?->name,
        ]);
    }

    /** Notifikasi pengajuan (proposal) baru — pesan lengkap dgn data proposal, untuk user & admin. */
    public function notifyProposalSubmitted(\App\Models\Proposal $proposal, $serviceRequest): void
    {
        $user = $serviceRequest->user;
        $name = $user?->name ?? 'Pelanggan';
        $serviceTitle = $serviceRequest->service?->title ?? $proposal->service?->title ?? 'layanan';
        $code = $serviceRequest->transaction_code_label;
        $total = 'Rp' . number_format((int) $proposal->total_amount, 0, ',', '.');
        $submittedAt = optional($proposal->submitted_at)?->format('d M Y, H:i') ?? now()->format('d M Y, H:i');

        $title = 'Pengajuan diterima';

        $meta = [
            'service_request_id' => $serviceRequest->id,
            'service_request_code' => $code,
            'proposal_number' => $proposal->proposal_number,
            'service_title' => $serviceTitle,
            'total_amount' => $total,
            'submitted_at' => $submittedAt,
            'user_name' => $name,
        ];

        if ($user) {
            $userMessage = "Halo {$name}, pengajuan Anda untuk layanan \"{$serviceTitle}\" sudah kami terima dan tercatat "
                . "dengan nomor pengajuan {$proposal->proposal_number} (order {$code}). Perkiraan biaya {$total}, "
                . "diajukan pada {$submittedAt}. Tim kami akan meninjau dan menghubungi Anda paling lambat 1×24 jam kerja. "
                . "Anda juga bisa langsung menyelesaikan pembayaran agar pengajuan lebih cepat diproses.";

            $this->notifyMobileUser($user, $title, $userMessage, SystemNotification::TYPE_CONFIRMATION, null, $meta);
        }

        $adminMessage = "Pengajuan baru dari {$name} untuk layanan \"{$serviceTitle}\". Nomor {$proposal->proposal_number} "
            . "(order {$code}), perkiraan biaya {$total}, diajukan {$submittedAt}. Mohon segera ditinjau.";

        $this->notifyAdmins($title, $adminMessage, SystemNotification::TYPE_INFORMATION, '/admin/mobile/service-requests/' . $serviceRequest->id, $meta);
    }

    public function notifyServiceRequestPaymentUpdated($serviceRequest): void
    {
        $title = 'Status pembayaran diperbarui';
        $message = 'Status pembayaran pengajuan survey ' . $serviceRequest->transaction_code_label . ' sekarang ' . $serviceRequest->payment_status . '.';

        if ($serviceRequest->user) {
            $this->notifyMobileUser($serviceRequest->user, $title, $message, SystemNotification::TYPE_CONFIRMATION, null, [
                'service_request_id' => $serviceRequest->id,
                'service_request_code' => $serviceRequest->transaction_code_label,
                'user_name' => $serviceRequest->user->name,
            ]);
        }

        $this->notifyAdmins($title, $message, SystemNotification::TYPE_CONFIRMATION, '/admin/mobile/service-requests/' . $serviceRequest->id, [
            'service_request_id' => $serviceRequest->id,
            'service_request_code' => $serviceRequest->transaction_code_label,
            'user_name' => $serviceRequest->user?->name,
        ]);
    }

    public function notifyServiceRequestDecision($serviceRequest, string $decision): void
    {
        $title = match ($decision) {
            'approved' => 'Pengajuan disetujui',
            'completed' => 'Pengajuan selesai',
            default => 'Pengajuan ditolak',
        };

        $message = match ($decision) {
            'approved' => 'Pengajuan survey ' . $serviceRequest->transaction_code_label . ' telah disetujui oleh admin.',
            'completed' => 'Pengajuan survey ' . $serviceRequest->transaction_code_label . ' telah selesai diproses.',
            default => 'Pengajuan survey ' . $serviceRequest->transaction_code_label . ' ditolak oleh admin.',
        };

        if ($serviceRequest->user) {
            $this->notifyMobileUser($serviceRequest->user, $title, $message, SystemNotification::TYPE_CONFIRMATION, null, [
                'service_request_id' => $serviceRequest->id,
                'service_request_code' => $serviceRequest->transaction_code_label,
                'decision' => $decision,
                'user_name' => $serviceRequest->user->name,
            ]);
        }

        $this->notifyAdmins($title, $message, SystemNotification::TYPE_CONFIRMATION, '/admin/mobile/service-requests/' . $serviceRequest->id, [
            'service_request_id' => $serviceRequest->id,
            'service_request_code' => $serviceRequest->transaction_code_label,
            'decision' => $decision,
            'user_name' => $serviceRequest->user?->name,
        ]);
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

        $title = 'Pesan baru dari admin';
        $preview = Str::limit(strip_tags($message->body), 120);
        $tokens = $conversation->mobileUser->pushTokens()->where('is_active', true)->get();

        if ($tokens->isEmpty()) {
            return;
        }

        $this->expoPushNotificationService->sendToTokens($tokens, [
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
        $title = 'Pesan baru dari user';
        $preview = Str::limit(strip_tags($message->body), 120);

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

        $this->expoPushNotificationService->sendToTokens($tokens, [
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
