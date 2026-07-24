<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Models\MobileUser;
use App\Services\ChatService;
use App\Traits\AdminView;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    use AdminView;

    public function __construct(
        protected ChatService $chatService
    ) {
        $this->setView('admin.pages.mobile');
    }

    public function index(Request $request, ?int $conversation = null)
    {
        $conversations = $this->chatService->listForAdmin($request->query('q'));
        $selectedConversation = null;

        if ($conversation) {
            $selectedConversation = $this->chatService->getConversationForAdmin($conversation);
            $this->chatService->markReadForAdmin($selectedConversation, $request->user());
        } elseif ($request->filled('service_request_id') && $request->filled('user_id')) {
            $mobileUser = MobileUser::query()->findOrFail((int) $request->query('user_id'));
            $selectedConversation = $this->chatService->findOrCreateConversation(
                $mobileUser,
                (int) $request->query('service_request_id')
            );
            $selectedConversation = $this->chatService->getConversationForAdmin($selectedConversation->id);
            $this->chatService->markReadForAdmin($selectedConversation, $request->user());
        } elseif ($conversations->isNotEmpty()) {
            $selectedConversation = $this->chatService->getConversationForAdmin((int) $conversations->first()['id']);
            $this->chatService->markReadForAdmin($selectedConversation, $request->user());
        }

        if ($selectedConversation) {
            $selectedConversation->load([
                'mobileUser',
                'serviceRequest.service',
                'assignedAdmin',
                'messages.senderAdmin',
                'messages.senderMobileUser',
            ]);
        }

        return $this->view('live-chat', [
            'sections' => $this->sections(),
            'conversations' => $conversations,
            'selectedConversation' => $selectedConversation,
            'selectedMessages' => $selectedConversation
                ? $selectedConversation->messages->sortBy('created_at')->values()->map(fn ($message) => $this->chatService->messagePayload($message))
                : collect(),
        ]);
    }

    public function store(Request $request, int $conversation)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        try {
            $chatConversation = $this->chatService->getConversationForAdmin($conversation);
            $message = $this->chatService->sendAdminMessage($chatConversation, $request->user(), $validated['message']);
            $chatConversation = $this->chatService->getConversationForAdmin($chatConversation->id);
            $chatConversation->load([
                'mobileUser',
                'serviceRequest.service',
                'assignedAdmin',
                'messages.senderAdmin',
                'messages.senderMobileUser',
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'conversation' => $this->chatService->conversationSummary($chatConversation),
                    'message' => $this->chatService->messagePayload($message->fresh(['senderAdmin', 'senderMobileUser'])),
                ]);
            }

            return redirect()
                ->route('admin.mobile.live_chat', ['conversation' => $chatConversation->id])
                ->with('success', 'Pesan berhasil dikirim.');
        } catch (\Throwable $th) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => $th->getMessage(),
                ], 422);
            }

            return redirect()
                ->back()
                ->with('error', $th->getMessage());
        }
    }

    private function sections(): array
    {
        return [
            [
                'title' => 'Overview',
                'route' => route('admin.mobile.index'),
                'icon' => 'ri-dashboard-line',
                'description' => 'Ringkasan kesiapan backoffice mobile.',
            ],
            [
                'title' => 'Users',
                'route' => route('admin.mobile.users'),
                'icon' => 'ri-user-settings-line',
                'description' => 'Kelola customer aplikasi mobile.',
            ],
            [
                'title' => 'OTP Logs',
                'route' => route('admin.mobile.otp_logs'),
                'icon' => 'ri-shield-keyhole-line',
                'description' => 'Pantau OTP email dan SMS.',
            ],
            [
                'title' => 'Service Requests',
                'route' => route('admin.mobile.service_requests.index'),
                'icon' => 'ri-file-list-3-line',
                'description' => 'Review pengajuan dari aplikasi mobile.',
            ],
            [
                'title' => 'Services',
                'route' => route('admin.mobile.services'),
                'icon' => 'ri-service-line',
                'description' => 'Kelola layanan mobile.',
            ],
            [
                'title' => 'Home Layout',
                'route' => route('admin.mobile.home_layout'),
                'icon' => 'ri-layout-2-line',
                'description' => 'Atur layout home mobile.',
            ],
            [
                'title' => 'Notifications',
                'route' => route('admin.mobile.notifications'),
                'icon' => 'ri-notification-3-line',
                'description' => 'Kirim notifikasi ke user mobile.',
            ],
            [
                'title' => 'Live Chat',
                'route' => route('admin.mobile.live_chat'),
                'icon' => 'ri-message-3-line',
                'description' => 'Percakapan user dan admin.',
            ],
            [
                'title' => 'Settings',
                'route' => route('admin.mobile.settings'),
                'icon' => 'ri-settings-4-line',
                'description' => 'Pengaturan biaya, pajak, dan pembayaran.',
            ],
        ];
    }
}
