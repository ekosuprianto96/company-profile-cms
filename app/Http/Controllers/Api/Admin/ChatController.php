<?php

namespace App\Http\Controllers\Api\Admin;

use App\Services\ChatService;
use Illuminate\Http\Request;

class ChatController extends ApiController
{
    public function __construct(protected ChatService $chatService) {}

    public function index(Request $request)
    {
        try {
            $conversations = $this->chatService->listForAdmin($request->query('q'));

            return $this->success(['conversations' => $conversations->values()], 'Daftar chat.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function show(Request $request, int $id)
    {
        try {
            $conversation = $this->chatService->getConversationForAdmin($id);
            $before = $request->query('before');
            $page = $this->chatService->paginateMessages($conversation, $before ? (int) $before : null);

            // Buka pesan = tandai dibaca oleh admin.
            $this->chatService->markReadForAdmin($conversation, $request->user());

            return $this->success([
                'conversation' => $this->chatService->conversationSummary($conversation),
                'messages' => $page['messages'],
                'has_more_older' => $page['has_more_older'],
                'oldest_message_id' => $page['oldest_message_id'],
            ], 'Detail chat.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    /** Get-or-create conversation dari pengajuan/order (untuk tombol Chat di admin app). */
    public function start(Request $request)
    {
        try {
            $validated = $request->validate([
                'service_request_id' => ['nullable', 'integer', 'exists:mobile_service_requests,id'],
                'mobile_user_id' => ['nullable', 'integer', 'exists:mobile_users,id'],
            ]);

            $conversation = $this->chatService->startForAdmin(
                $validated['service_request_id'] ?? null,
                $validated['mobile_user_id'] ?? null,
            );

            return $this->success(
                ['conversation' => $this->chatService->conversationSummary($conversation->fresh(['mobileUser', 'serviceRequest.service']))],
                'Percakapan siap.',
            );
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function send(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'message' => ['nullable', 'string', 'max:5000'],
                'attachments' => ['nullable', 'array', 'max:5'],
                'attachments.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
                'reply_to_message_id' => ['nullable', 'integer', 'exists:chat_messages,id'],
            ]);

            $body = trim((string) ($validated['message'] ?? ''));
            $attachments = $request->file('attachments', []);

            if ($body === '' && empty($attachments)) {
                return $this->error('Pesan atau media wajib diisi.', 422);
            }

            $conversation = $this->chatService->getConversationForAdmin($id);
            $message = $this->chatService->sendAdminMessage(
                $conversation,
                $request->user(),
                $body,
                $attachments,
                isset($validated['reply_to_message_id']) ? (int) $validated['reply_to_message_id'] : null
            );

            return $this->success(['message' => $this->chatService->messagePayload($message->fresh(['senderAdmin', 'senderMobileUser', 'replyTo.senderAdmin', 'replyTo.senderMobileUser']))], 'Pesan terkirim.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function markRead(Request $request, int $id)
    {
        try {
            $conversation = $this->chatService->getConversationForAdmin($id);
            $this->chatService->markReadForAdmin($conversation, $request->user());

            return $this->success([], 'Ditandai dibaca.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}
