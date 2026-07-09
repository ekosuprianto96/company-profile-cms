<?php

namespace App\Services;

use App\Events\Mobile\ChatMessageCreated;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\MobileServiceRequest;
use App\Models\MobileUser;
use App\Models\User;
use App\Services\SystemNotificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatService
{
    public function __construct(
        protected SystemNotificationService $systemNotificationService
    ) {}

    public function findOrCreateConversation(MobileUser $mobileUser, ?int $serviceRequestId = null, ?string $subject = null): ChatConversation
    {
        return DB::transaction(function () use ($mobileUser, $serviceRequestId, $subject) {
            if ($serviceRequestId) {
                $conversation = ChatConversation::query()
                    ->where('service_request_id', $serviceRequestId)
                    ->where('mobile_user_id', $mobileUser->id)
                    ->first();

                if ($conversation) {
                    return $conversation;
                }

                $serviceRequest = MobileServiceRequest::query()
                    ->with('service')
                    ->where('id', $serviceRequestId)
                    ->where('mobile_user_id', $mobileUser->id)
                    ->first();

                if (! $serviceRequest) {
                    throw new \Exception('Pengajuan untuk percakapan tidak ditemukan.', 404);
                }

                return ChatConversation::query()->create([
                    'mobile_user_id' => $mobileUser->id,
                    'service_request_id' => $serviceRequest->id,
                    'subject' => $subject ?: ($serviceRequest->transaction_code_label . ' - ' . ($serviceRequest->service?->title ?? 'Pengajuan')),
                    'status' => 'open',
                ]);
            }

            $conversation = ChatConversation::query()
                ->where('mobile_user_id', $mobileUser->id)
                ->whereNull('service_request_id')
                ->where('status', 'open')
                ->latest()
                ->first();

            if ($conversation) {
                return $conversation;
            }

            return ChatConversation::query()->create([
                'mobile_user_id' => $mobileUser->id,
                'subject' => $subject ?: 'Percakapan baru',
                'status' => 'open',
            ]);
        });
    }

    public function listForAdmin(?string $keyword = null): Collection
    {
        return ChatConversation::query()
            ->with(['mobileUser', 'serviceRequest.service', 'assignedAdmin', 'messages' => fn ($query) => $query->latest()->limit(1)])
            ->when($keyword, function (Builder $query, string $keyword) {
                $query->where(function (Builder $inner) use ($keyword) {
                    $inner->whereHas('mobileUser', function (Builder $userQuery) use ($keyword) {
                        $userQuery->where('name', 'like', '%' . $keyword . '%')
                            ->orWhere('email', 'like', '%' . $keyword . '%')
                            ->orWhere('phone', 'like', '%' . $keyword . '%');
                    })->orWhereHas('serviceRequest', function (Builder $serviceRequestQuery) use ($keyword) {
                        $serviceRequestQuery->where('transaction_code', 'like', '%' . $keyword . '%');
                    });
                });
            })
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get()
            ->map(function (ChatConversation $conversation) {
                return $this->conversationSummary($conversation);
            });
    }

    public function listForUser(MobileUser $mobileUser): Collection
    {
        return ChatConversation::query()
            ->with(['serviceRequest.service', 'messages' => fn ($query) => $query->latest()->limit(1)])
            ->where('mobile_user_id', $mobileUser->id)
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get()
            ->map(function (ChatConversation $conversation) {
                return $this->conversationSummary($conversation);
            });
    }

    public function getConversationForAdmin(int $id): ChatConversation
    {
        $conversation = ChatConversation::query()
            ->with([
                'mobileUser',
                'serviceRequest.service',
                'assignedAdmin',
                'messages.senderAdmin',
                'messages.senderMobileUser',
            ])
            ->findOrFail($id);

        return $conversation;
    }

    public function getConversationForUser(MobileUser $mobileUser, int $id): ChatConversation
    {
        $conversation = ChatConversation::query()
            ->with([
                'serviceRequest.service',
                'messages.senderAdmin',
                'messages.senderMobileUser',
            ])
            ->where('mobile_user_id', $mobileUser->id)
            ->findOrFail($id);

        return $conversation;
    }

    public function sendAdminMessage(ChatConversation $conversation, User $admin, string $body, array $attachments = []): ChatMessage
    {
        $message = DB::transaction(function () use ($conversation, $admin, $body, $attachments) {
            $storedAttachments = $this->storeAttachments($attachments);

            $message = ChatMessage::query()->create([
                'chat_conversation_id' => $conversation->id,
                'sender_type' => 'admin',
                'sender_user_id' => $admin->id,
                'sender_mobile_user_id' => null,
                'body' => trim($body),
                'attachments' => $storedAttachments ?: null,
            ]);

            $conversation->forceFill([
                'assigned_admin_user_id' => $conversation->assigned_admin_user_id ?: $admin->id,
                'admin_last_read_at' => now(),
                'last_message_at' => now(),
                'last_message_preview' => Str::limit($this->messagePreview($message), 120),
            ])->save();

            return $message;
        });

        $this->broadcastMessageCreated($message);
        $this->notifyChatParticipant($message, $conversation->fresh(['mobileUser', 'serviceRequest.service']), $admin);

        return $message;
    }

    public function sendMobileMessage(ChatConversation $conversation, MobileUser $mobileUser, string $body, array $attachments = []): ChatMessage
    {
        $message = DB::transaction(function () use ($conversation, $mobileUser, $body, $attachments) {
            $storedAttachments = $this->storeAttachments($attachments);

            $message = ChatMessage::query()->create([
                'chat_conversation_id' => $conversation->id,
                'sender_type' => 'mobile',
                'sender_user_id' => null,
                'sender_mobile_user_id' => $mobileUser->id,
                'body' => trim($body),
                'attachments' => $storedAttachments ?: null,
            ]);

            $conversation->forceFill([
                'mobile_last_read_at' => now(),
                'last_message_at' => now(),
                'last_message_preview' => Str::limit($this->messagePreview($message), 120),
            ])->save();

            return $message;
        });

        $this->broadcastMessageCreated($message);
        $this->notifyChatParticipant($message, $conversation->fresh(['mobileUser', 'serviceRequest.service']), $mobileUser);

        return $message;
    }

    public function markReadForAdmin(ChatConversation $conversation, ?User $admin = null): ChatConversation
    {
        $conversation->forceFill([
            'admin_last_read_at' => now(),
            'assigned_admin_user_id' => $conversation->assigned_admin_user_id ?: $admin?->id,
        ])->save();

        return $conversation->fresh();
    }

    public function markReadForMobile(ChatConversation $conversation): ChatConversation
    {
        $conversation->forceFill([
            'mobile_last_read_at' => now(),
        ])->save();

        return $conversation->fresh();
    }

    public function conversationSummary(ChatConversation $conversation): array
    {
        $lastMessage = $conversation->messages->first();

        return [
            'id' => $conversation->id,
            'subject' => $conversation->subject,
            'status' => $conversation->status,
            'service_request_id' => $conversation->service_request_id,
            'service_request_code' => $conversation->serviceRequest?->transaction_code_label,
            'service_title' => $conversation->serviceRequest?->service?->title,
            'mobile_user' => [
                'id' => $conversation->mobileUser?->id,
                'name' => $conversation->mobileUser?->name,
                'email' => $conversation->mobileUser?->email,
                'phone' => $conversation->mobileUser?->phone,
            ],
            'assigned_admin_user_id' => $conversation->assigned_admin_user_id,
            'assigned_admin_name' => $conversation->assignedAdmin?->name,
            'last_message' => $conversation->last_message_preview ?? ($lastMessage?->body ? Str::limit(strip_tags($lastMessage->body), 120) : null),
            'last_message_at' => optional($conversation->last_message_at)?->toISOString(),
            'mobile_last_read_at' => optional($conversation->mobile_last_read_at)?->toISOString(),
            'admin_last_read_at' => optional($conversation->admin_last_read_at)?->toISOString(),
            'unread_for_admin' => $this->unreadForAdmin($conversation),
            'unread_for_mobile' => $this->unreadForMobile($conversation),
            'created_at' => optional($conversation->created_at)?->toISOString(),
        ];
    }

    public function messagePayload(ChatMessage $message): array
    {
        return [
            'id' => $message->id,
            'chat_conversation_id' => $message->chat_conversation_id,
            'sender_type' => $message->sender_type,
            'sender_user' => $message->sender_type === 'admin' ? [
                'id' => $message->senderAdmin?->id,
                'name' => $message->senderAdmin?->name,
            ] : null,
            'sender_mobile_user' => $message->sender_type === 'mobile' ? [
                'id' => $message->senderMobileUser?->id,
                'name' => $message->senderMobileUser?->name,
            ] : null,
            'body' => $message->body,
            'attachments' => $this->normalizeAttachments($message->attachments ?? []),
            'created_at' => optional($message->created_at)?->toISOString(),
        ];
    }

    protected function broadcastMessageCreated(ChatMessage $message): void
    {
        $freshMessage = $message->fresh(['conversation.mobileUser', 'conversation.serviceRequest.service', 'senderAdmin', 'senderMobileUser']);

        if (! $freshMessage?->conversation?->mobileUser?->id) {
            return;
        }

        try {
            event(new ChatMessageCreated(
                $this->conversationSummary($freshMessage->conversation),
                $this->messagePayload($freshMessage)
            ));
        } catch (\Throwable $th) {
            report($th);

            Log::warning('Chat realtime broadcast failed; message was saved without live update.', [
                'chat_message_id' => $freshMessage->id,
                'chat_conversation_id' => $freshMessage->chat_conversation_id,
                'error' => $th->getMessage(),
            ]);
        }
    }

    protected function notifyChatParticipant(ChatMessage $message, ?ChatConversation $conversation, User|MobileUser $sender): void
    {
        if (! $conversation) {
            return;
        }

        try {
            if ($sender instanceof User) {
                $this->systemNotificationService->notifyChatMessageToMobileUser($conversation, $message, $sender);
                return;
            }

            if ($sender instanceof MobileUser) {
                $this->systemNotificationService->notifyChatMessageToAdmins($conversation, $message, $sender);
            }
        } catch (\Throwable $th) {
            report($th);
        }
    }

    /**
     * @param  array<int, mixed>  $attachments
     * @return array<int, array<string, mixed>>
     */
    protected function storeAttachments(array $attachments): array
    {
        if ($attachments === []) {
            return [];
        }

        $stored = [];

        foreach ($attachments as $attachment) {
            if ($attachment instanceof UploadedFile) {
                $extension = $attachment->getClientOriginalExtension() ?: 'jpg';
                $fileName = now()->format('Y-m-d') . '-' . Str::uuid() . '.' . $extension;
                $relativePath = $attachment->storeAs('mobile/chat-messages', $fileName, 'public');

                $stored[] = [
                    'file_name' => $fileName,
                    'mime_type' => $attachment->getMimeType(),
                    'path' => $relativePath,
                    'url' => url(Storage::disk('public')->url($relativePath)),
                    'size' => $attachment->getSize(),
                ];

                continue;
            }

            if (is_array($attachment)) {
                $path = (string) ($attachment['path'] ?? $attachment['uri'] ?? '');
                $url = (string) ($attachment['url'] ?? '');

                if ($path === '' && $url !== '') {
                    $path = $url;
                }

                if ($path === '') {
                    continue;
                }

                $stored[] = [
                    'file_name' => $attachment['file_name'] ?? basename($path),
                    'mime_type' => $attachment['mime_type'] ?? $attachment['mimeType'] ?? null,
                    'path' => $path,
                    'url' => $url ?: $path,
                    'size' => $attachment['size'] ?? null,
                ];
            }
        }

        return $stored;
    }

    /**
     * @param  array<int, mixed>  $attachments
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeAttachments(array $attachments): array
    {
        return collect($attachments)
            ->filter(fn ($attachment) => is_array($attachment))
            ->values()
            ->map(function (array $attachment) {
                $path = (string) ($attachment['path'] ?? $attachment['uri'] ?? '');
                $url = (string) ($attachment['url'] ?? '');

            return [
                'file_name' => $attachment['file_name'] ?? basename($path ?: $url),
                'mime_type' => $attachment['mime_type'] ?? $attachment['mimeType'] ?? null,
                'path' => $path ?: null,
                'url' => $url ?: ($path !== '' ? (filter_var($path, FILTER_VALIDATE_URL) ? $path : url(Storage::disk('public')->url($path))) : null),
                'size' => $attachment['size'] ?? null,
            ];
        })
            ->filter(fn (array $attachment) => ! empty($attachment['url']))
            ->all();
    }

    protected function messagePreview(ChatMessage $message): string
    {
        $body = trim(strip_tags((string) $message->body));

        if ($body !== '') {
            return $body;
        }

        $attachments = $this->normalizeAttachments($message->attachments ?? []);

        if ($attachments === []) {
            return '';
        }

        return count($attachments) > 1
            ? 'Mengirim ' . count($attachments) . ' foto'
            : 'Mengirim foto';
    }

    public function unreadForAdmin(ChatConversation $conversation): int
    {
        return $conversation->messages()
            ->where('sender_type', 'mobile')
            ->when($conversation->admin_last_read_at, function ($query) use ($conversation) {
                $query->where('created_at', '>', $conversation->admin_last_read_at);
            })
            ->count();
    }

    public function unreadForMobile(ChatConversation $conversation): int
    {
        return $conversation->messages()
            ->where('sender_type', 'admin')
            ->when($conversation->mobile_last_read_at, function ($query) use ($conversation) {
                $query->where('created_at', '>', $conversation->mobile_last_read_at);
            })
            ->count();
    }
}
