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
            return $this->success([
                'conversations' => $this->chatService->listForUser($request->user()),
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

            return $this->success([
                'conversation' => $this->conversationPayload($conversation->fresh(['mobileUser', 'serviceRequest.service', 'assignedAdmin']), $request->user()),
                'messages' => $conversation->messages
                    ->sortBy('created_at')
                    ->values()
                    ->map(fn ($message) => $this->chatService->messagePayload($message)),
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

    public function storeMessage(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'message' => ['nullable', 'string', 'max:5000'],
                'attachments' => ['nullable', 'array', 'max:4'],
                'attachments.*' => ['file', 'image', 'max:5120'],
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
                $attachments
            );

            return $this->success([
                'conversation' => $this->conversationPayload($conversation->fresh(['mobileUser', 'serviceRequest.service', 'assignedAdmin']), $request->user()),
                'message' => $this->chatService->messagePayload($message->fresh(['senderMobileUser', 'senderAdmin'])),
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
