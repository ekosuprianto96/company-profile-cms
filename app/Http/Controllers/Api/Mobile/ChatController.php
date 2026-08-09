<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Models\ChatConversation;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ChatController extends ApiController
{
    public function __construct(
        protected ChatService $chatService
    ) {}

    public function index(Request $request)
    {
        try {
            $paginator = $this->chatService->paginateForUser($request->user());

            return $this->success([
                'conversations' => collect($paginator->items())
                    ->map(fn (ChatConversation $conversation) => $this->chatService->conversationSummary($conversation))
                    ->values(),
                'next_cursor' => optional($paginator->nextCursor())->encode(),
                'has_more' => $paginator->hasMorePages(),
            ], 'Daftar chat berhasil dimuat.');
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) {
                return $this->error(
                    $th->getMessage() ?: 'Permintaan tidak valid.',
                    422,
                    $th->errors()
                );
            }

            Log::error('Chat list error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function unreadCount(Request $request)
    {
        try {
            return $this->success([
                'unread_count' => $this->chatService->totalUnreadForMobile($request->user()),
            ], 'Jumlah chat belum dibaca berhasil dimuat.');
        } catch (\Throwable $th) {
            Log::error('Chat unread-count error: ' . $th->getMessage());

            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'service_request_id' => ['nullable', 'integer', 'exists:mobile_service_requests,id'],
                'subject' => ['nullable', 'string', 'max:150'],
            ]);

            $conversation = $this->chatService->findOrCreateConversation(
                $request->user(),
                $validated['service_request_id'] ?? null,
                $validated['subject'] ?? null
            );

            return $this->success([
                'conversation' => $this->conversationPayload(
                    $conversation->fresh(['mobileUser', 'serviceRequest.service', 'assignedAdmin']),
                    $request->user()
                ),
            ], 'Percakapan berhasil disiapkan.');
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) {
                return $this->error(
                    $th->getMessage() ?: 'Permintaan tidak valid.',
                    422,
                    $th->errors()
                );
            }

            Log::error('Chat create error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function show(Request $request, int $id)
    {
        try {
            $conversation = $this->chatService->getConversationForUser($request->user(), $id);
            $this->chatService->markReadForMobile($conversation);

            $page = $this->chatService->paginateMessages($conversation);

            return $this->success([
                'conversation' => $this->conversationPayload($conversation->fresh(['mobileUser', 'serviceRequest.service', 'assignedAdmin']), $request->user()),
                'messages' => $page['messages'],
                'has_more_older' => $page['has_more_older'],
                'oldest_message_id' => $page['oldest_message_id'],
            ], 'Detail percakapan berhasil dimuat.');
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) {
                return $this->error(
                    $th->getMessage() ?: 'Permintaan tidak valid.',
                    422,
                    $th->errors()
                );
            }

            Log::error('Chat show error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function messages(Request $request, int $id)
    {
        try {
            $conversation = $this->chatService->getConversationForUser($request->user(), $id);
            $beforeId = $request->integer('before') ?: null;

            $page = $this->chatService->paginateMessages($conversation, $beforeId);

            return $this->success([
                'messages' => $page['messages'],
                'has_more_older' => $page['has_more_older'],
                'oldest_message_id' => $page['oldest_message_id'],
            ], 'Pesan berhasil dimuat.');
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) {
                return $this->error($th->getMessage() ?: 'Permintaan tidak valid.', 422, $th->errors());
            }

            Log::error('Chat messages error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function storeMessage(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'message' => ['nullable', 'string', 'max:5000'],
                'attachments' => ['nullable', 'array', 'max:4'],
                'attachments.*' => ['file', 'image', 'max:5120'],
                'reply_to_message_id' => ['nullable', 'integer', 'exists:chat_messages,id'],
            ]);

            $conversation = $this->chatService->getConversationForUser($request->user(), $id);
            $messageBody = trim((string) ($validated['message'] ?? ''));
            $attachments = $request->file('attachments', []);

            if ($messageBody === '' && empty($attachments)) {
                throw ValidationException::withMessages([
                    'message' => 'Pesan atau gambar harus diisi.',
                ]);
            }

            $message = $this->chatService->sendMobileMessage(
                $conversation,
                $request->user(),
                $messageBody,
                $attachments,
                isset($validated['reply_to_message_id']) ? (int) $validated['reply_to_message_id'] : null
            );

            return $this->success([
                'conversation' => $this->conversationPayload($conversation->fresh(['mobileUser', 'serviceRequest.service', 'assignedAdmin']), $request->user()),
                'message' => $this->chatService->messagePayload($message->fresh(['senderMobileUser', 'senderAdmin', 'replyTo.senderAdmin', 'replyTo.senderMobileUser'])),
            ], 'Pesan berhasil dikirim.');
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) {
                return $this->error(
                    $th->getMessage() ?: 'Permintaan tidak valid.',
                    422,
                    $th->errors()
                );
            }

            Log::error('Chat send message error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function markRead(Request $request, int $id)
    {
        try {
            $conversation = $this->chatService->getConversationForUser($request->user(), $id);
            $conversation = $this->chatService->markReadForMobile($conversation);

            return $this->success([
                'conversation' => $this->conversationPayload($conversation->fresh(['mobileUser', 'serviceRequest.service', 'assignedAdmin']), $request->user()),
            ], 'Percakapan ditandai sudah dibaca.');
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) {
                return $this->error(
                    $th->getMessage() ?: 'Permintaan tidak valid.',
                    422,
                    $th->errors()
                );
            }

            Log::error('Chat mark read error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    private function conversationPayload(ChatConversation $conversation, $viewer): array
    {
        return [
            'id' => $conversation->id,
            'subject' => $conversation->subject,
            'status' => $conversation->status,
            'service_request_id' => $conversation->service_request_id,
            'service_request_code' => $conversation->serviceRequest?->transaction_code_label,
            'service_title' => $conversation->serviceRequest?->service?->title,
            'last_message_at' => optional($conversation->last_message_at)?->toISOString(),
            'unread_count' => $this->chatService->unreadForMobile($conversation),
            'created_at' => optional($conversation->created_at)?->toISOString(),
        ];
    }
}
