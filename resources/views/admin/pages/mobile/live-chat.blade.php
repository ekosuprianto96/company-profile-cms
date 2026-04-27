@extends('admin.layouts.main')

@section('content')
@php
    $conversations = $conversations ?? collect();
    $selectedConversation = $selectedConversation ?? null;
    $selectedMessages = $selectedMessages ?? collect();
    $conversationCount = $conversations->count();
    $unreadCount = $conversations->sum(fn ($conversation) => (int) ($conversation['unread_for_admin'] ?? 0));

    $previewText = function (?string $text) {
        return trim(preg_replace('/\s+/u', ' ', strip_tags((string) $text))) ?: '-';
    };

    $chatAvatarTone = function (int|string|null $seed): string {
        $tones = ['tone-1', 'tone-2', 'tone-3', 'tone-4', 'tone-5', 'tone-6'];

        return $tones[abs((int) $seed) % count($tones)];
    };

    $chatInitial = function (?string $text, string $fallback = 'U'): string {
        $value = trim((string) $text);

        if ($value === '') {
            return $fallback;
        }

        return mb_strtoupper(mb_substr($value, 0, 1));
    };

    $conversationUrl = function ($conversation) {
        return route('admin.mobile.live_chat', ['conversation' => $conversation['id']]);
    };
@endphp

<style>
    .chat-room-shell {
        --chat-bg: #f3f7fb;
        --chat-surface: rgba(255, 255, 255, 0.88);
        --chat-border: rgba(15, 23, 42, 0.08);
        --chat-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
        --chat-muted: #64748b;
        --chat-brand: #275a56;
        --chat-brand-soft: #eaf3f1;
        --chat-accent: #f5b946;
        --chat-bubble-user: #ffffff;
        --chat-bubble-admin: linear-gradient(135deg, #2f7a72 0%, #275a56 100%);
        --chat-bubble-admin-text: #ffffff;
        color: #0f172a;
    }

    .chat-room-hero,
    .chat-room-sidebar,
    .chat-room-panel,
    .chat-room-composer,
    .chat-search-box,
    .chat-thread-item,
    .chat-message-card,
    .chat-empty-panel {
        border: 1px solid var(--chat-border);
        box-shadow: var(--chat-shadow);
        backdrop-filter: blur(18px);
        background: var(--chat-surface);
    }

    .chat-room-hero {
        border-radius: 28px;
        margin-bottom: 18px;
        overflow: hidden;
        position: relative;
        background:
            radial-gradient(circle at top right, rgba(245, 185, 70, 0.18), transparent 35%),
            linear-gradient(135deg, rgba(39, 90, 86, 0.06), rgba(255, 255, 255, 0.92));
    }

    .chat-room-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            linear-gradient(120deg, rgba(39, 90, 86, 0.08), transparent 30%),
            linear-gradient(300deg, rgba(245, 185, 70, 0.08), transparent 35%);
        pointer-events: none;
    }

    .chat-room-hero-inner {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 24px 28px;
        flex-wrap: wrap;
    }

    .chat-eyebrow {
        font-size: 12px;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--chat-brand);
        font-weight: 800;
        margin-bottom: 8px;
    }

    .chat-title {
        margin: 0;
        font-size: 32px;
        font-weight: 800;
        line-height: 1.1;
        color: #0f172a;
    }

    .chat-subtitle {
        margin: 8px 0 0;
        color: var(--chat-muted);
        max-width: 720px;
    }

    .chat-room-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .chat-room-grid {
        display: grid;
        grid-template-columns: minmax(320px, 390px) minmax(0, 1fr);
        gap: 18px;
        height: clamp(760px, calc(100dvh - 132px), 1040px);
        align-items: stretch;
        overflow: hidden;
    }

    .chat-room-shell.is-sidebar-collapsed .chat-room-grid {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .chat-room-shell.is-sidebar-collapsed .chat-room-sidebar {
        display: none;
    }

    .chat-room-sidebar,
    .chat-room-panel {
        border-radius: 28px;
        overflow: hidden;
    }

    .chat-room-sidebar {
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 0;
        max-height: none;
        overflow: hidden;
    }

    .chat-room-panel {
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 0;
        max-height: none;
        overflow: hidden;
    }

    .chat-room-sidebar-header {
        padding: 22px 22px 14px;
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
    }

    .chat-room-sidebar-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .chat-room-kicker {
        font-size: 11px;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--chat-muted);
        font-weight: 800;
    }

    .chat-room-section-title {
        margin: 0;
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
    }

    .chat-room-stat {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 7px 11px;
        background: var(--chat-brand-soft);
        color: var(--chat-brand);
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .chat-room-stat .dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #20c997;
        box-shadow: 0 0 0 5px rgba(32, 201, 151, 0.12);
    }

    .chat-search-box {
        border-radius: 20px;
        margin: 0 22px 18px;
        padding: 10px;
    }

    .chat-search-form {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .chat-search-field {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        min-width: 0;
        height: 48px;
        padding: 0 14px;
        border-radius: 18px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        background: rgba(248, 251, 253, 0.9);
    }

    .chat-search-field .form-control {
        border: 0;
        box-shadow: none !important;
        background: transparent;
        padding: 0;
        height: 100%;
        min-height: 0;
    }

    .chat-search-field .form-control::placeholder {
        color: #94a3b8;
    }

    .chat-search-button {
        width: 48px;
        height: 48px;
        min-width: 48px;
        padding: 0;
        border-radius: 16px;
        background: linear-gradient(135deg, var(--chat-brand), #184742);
        border: 0;
        flex: 0 0 auto;
    }

    .chat-search-empty {
        margin: 0 14px 18px;
        padding: 18px;
        border-radius: 22px;
        border: 1px dashed rgba(39, 90, 86, 0.18);
        background: rgba(255, 255, 255, 0.8);
        color: var(--chat-muted);
        text-align: center;
    }

    .chat-thread-list {
        padding: 0 14px 18px;
        overflow-y: auto;
        flex: 1;
        min-height: 0;
    }

    .chat-thread-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 15px;
        border-radius: 22px;
        margin-bottom: 12px;
        text-decoration: none;
        color: inherit;
        transition: transform 150ms ease, box-shadow 150ms ease, border-color 150ms ease, background 150ms ease;
        background: rgba(255, 255, 255, 0.94);
    }

    .chat-thread-item:hover {
        transform: translateY(-1px);
        border-color: rgba(39, 90, 86, 0.2);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
    }

    .chat-thread-item.is-active {
        background: linear-gradient(135deg, rgba(39, 90, 86, 0.1), rgba(255, 255, 255, 0.98));
        border-color: rgba(39, 90, 86, 0.22);
    }

    .chat-thread-item.is-active .chat-thread-name {
        color: var(--chat-brand);
    }

    .chat-avatar {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        display: grid;
        place-items: center;
        font-weight: 800;
        color: white;
        flex: 0 0 auto;
        position: relative;
        overflow: hidden;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18), 0 12px 24px rgba(15, 23, 42, 0.12);
    }

    .chat-avatar::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.16), transparent 55%);
    }

    .chat-avatar.tone-1 { background: linear-gradient(135deg, #275a56, #3f8f80); }
    .chat-avatar.tone-2 { background: linear-gradient(135deg, #7c3aed, #8b5cf6); }
    .chat-avatar.tone-3 { background: linear-gradient(135deg, #0f766e, #14b8a6); }
    .chat-avatar.tone-4 { background: linear-gradient(135deg, #0f172a, #334155); }
    .chat-avatar.tone-5 { background: linear-gradient(135deg, #b45309, #f59e0b); }
    .chat-avatar.tone-6 { background: linear-gradient(135deg, #be185d, #ec4899); }

    .chat-avatar.chat-avatar-sm {
        width: 46px;
        height: 46px;
        border-radius: 16px;
        font-size: 14px;
    }

    .chat-avatar.chat-avatar-lg {
        width: 58px;
        height: 58px;
        border-radius: 20px;
        font-size: 18px;
    }

    .chat-avatar-dot {
        position: absolute;
        right: 6px;
        bottom: 6px;
        width: 11px;
        height: 11px;
        border-radius: 999px;
        border: 2px solid #fff;
        background: #22c55e;
        box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.14);
        z-index: 1;
    }

    .chat-thread-body {
        min-width: 0;
        flex: 1;
    }

    .chat-thread-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .chat-thread-name {
        margin: 0;
        font-size: 15px;
        font-weight: 800;
        color: #0f172a;
    }

    .chat-thread-subject {
        margin-top: 3px;
        font-size: 12px;
        color: var(--chat-muted);
    }

    .chat-thread-preview {
        margin-top: 10px;
        font-size: 13px;
        color: #475569;
        line-height: 1.45;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .chat-thread-meta {
        text-align: right;
        flex: 0 0 auto;
    }

    .chat-thread-time {
        font-size: 11px;
        color: var(--chat-muted);
        margin-top: 8px;
    }

    .chat-panel {
        display: flex;
        flex-direction: column;
        min-height: 0;
        height: 100%;
        overflow: hidden;
    }

    .chat-panel-header {
        padding: 22px 24px;
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        background:
            linear-gradient(135deg, rgba(39, 90, 86, 0.06), rgba(255, 255, 255, 0.96)),
            rgba(255, 255, 255, 0.92);
    }

    .chat-participant {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    .chat-participant-copy {
        min-width: 0;
    }

    .chat-participant-name {
        margin: 0;
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
    }

    .chat-participant-subtitle {
        font-size: 13px;
        color: var(--chat-muted);
        margin-top: 3px;
    }

    .chat-participant-contact {
        margin-top: 6px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .chat-contact-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 12px;
        background: var(--chat-brand-soft);
        color: var(--chat-brand);
        font-weight: 700;
    }

    .chat-panel-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .chat-panel-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 999px;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 700;
    }

    .chat-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 999px;
        padding: 8px 12px;
        background: #ecfeff;
        color: #0f766e;
        border: 1px solid rgba(15, 118, 110, 0.12);
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .chat-status-badge::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #22c55e;
        box-shadow: 0 0 0 5px rgba(34, 197, 94, 0.12);
    }

    .chat-message-wrap {
        flex: 1;
        min-height: 0;
        max-height: none;
        overflow-y: auto;
        padding: 22px;
        background:
            radial-gradient(circle at top left, rgba(39, 90, 86, 0.05), transparent 28%),
            radial-gradient(circle at bottom right, rgba(245, 185, 70, 0.06), transparent 26%),
            #f8fbfd;
    }

    .chat-message-list {
        height: 100%;
        overflow-y: auto;
        padding-right: 6px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .chat-message-row {
        display: flex;
        align-items: flex-end;
        gap: 10px;
    }

    .chat-message-row.is-admin {
        flex-direction: row-reverse;
    }

    .chat-message-card {
        max-width: min(78%, 760px);
        border-radius: 22px;
        padding: 14px 16px;
        position: relative;
        overflow: hidden;
    }

    .chat-message-card.is-admin {
        background: var(--chat-bubble-admin);
        color: var(--chat-bubble-admin-text);
        border-color: transparent;
    }

    .chat-message-card.is-user {
        background: var(--chat-bubble-user);
    }

    .chat-message-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        margin-bottom: 8px;
    }

    .chat-message-name {
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.02em;
    }

    .chat-message-time {
        font-size: 11px;
        opacity: 0.7;
        white-space: nowrap;
    }

    .chat-message-body {
        white-space: pre-wrap;
        word-break: break-word;
        font-size: 14px;
        line-height: 1.6;
    }

    .chat-message-attachments {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(72px, 96px));
        gap: 8px;
        margin-top: 12px;
    }

    .chat-message-attachment {
        width: 100%;
        max-width: 120px;
        aspect-ratio: 1 / 1;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid rgba(15, 23, 42, 0.08);
        background: #fff;
    }

    .chat-message-attachment img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .chat-message-note {
        margin-top: 8px;
        font-size: 12px;
        opacity: 0.8;
    }

    .chat-message-empty,
    .chat-empty-panel {
        border-radius: 28px;
    }

    .chat-message-empty {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100%;
        text-align: center;
        color: var(--chat-muted);
        padding: 48px 24px;
    }

    .chat-composer {
        border-top: 1px solid rgba(15, 23, 42, 0.06);
        padding: 16px 18px 18px;
        background: rgba(255, 255, 255, 0.96);
    }

    .chat-composer-body {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .chat-composer-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        background: #f2f4f7;
        color: #98a2b3;
        font-size: 18px;
        border: 1px solid rgba(15, 23, 42, 0.08);
    }

    .chat-composer-input {
        flex: 1;
        min-width: 0;
        height: 44px;
        min-height: 44px;
        resize: none;
        border-radius: 999px;
        border-color: rgba(15, 23, 42, 0.12);
        box-shadow: none !important;
        padding: 0 18px;
        background: #fff;
        line-height: 44px;
    }

    .chat-composer-input::placeholder {
        color: #98a2b3;
    }

    .chat-composer-input:focus {
        border-color: rgba(39, 90, 86, 0.3);
        box-shadow: 0 0 0 0.2rem rgba(39, 90, 86, 0.08) !important;
    }

    .chat-composer-send {
        width: 44px;
        height: 44px;
        min-width: 44px;
        padding: 0;
        border-radius: 999px;
        border: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #3c9e79, #2f7a72);
        box-shadow: 0 16px 30px rgba(39, 90, 86, 0.24);
    }

    .chat-composer-send:hover {
        filter: brightness(1.03);
    }

    .chat-empty-panel {
        min-height: 100%;
        background:
            radial-gradient(circle at top, rgba(39, 90, 86, 0.09), transparent 35%),
            linear-gradient(180deg, rgba(255, 255, 255, 0.95), rgba(248, 251, 253, 0.95));
    }

    .chat-empty-panel-inner {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 100%;
        padding: 52px 24px;
        text-align: center;
    }

    .chat-empty-panel-icon {
        width: 84px;
        height: 84px;
        border-radius: 28px;
        display: grid;
        place-items: center;
        margin-bottom: 18px;
        background: linear-gradient(135deg, rgba(39, 90, 86, 0.12), rgba(245, 185, 70, 0.12));
        color: var(--chat-brand);
        font-size: 38px;
    }

    .chat-empty-panel-title {
        font-size: 22px;
        font-weight: 800;
        margin: 0;
        color: #0f172a;
    }

    .chat-empty-panel-text {
        margin: 10px auto 0;
        max-width: 520px;
        color: var(--chat-muted);
    }

    @media (max-width: 1199px) {
        .chat-room-grid {
            grid-template-columns: 1fr;
            height: auto;
            min-height: auto;
            overflow: visible;
        }

        .chat-room-sidebar,
        .chat-room-panel {
            height: auto;
            min-height: auto;
            overflow: visible;
        }
    }

    @media (max-width: 767px) {
        .chat-room-hero-inner,
        .chat-panel-header,
        .chat-composer {
            padding-left: 18px;
            padding-right: 18px;
        }

        .chat-title {
            font-size: 26px;
        }

        .chat-participant-name {
            font-size: 18px;
        }

        .chat-message-wrap {
            padding: 16px;
        }

        .chat-message-card {
            max-width: 92%;
        }

        .chat-composer-body {
            flex-direction: column;
            align-items: stretch;
        }

        .chat-composer-send {
            width: 100%;
        }
    }
</style>

<div class="chat-room-shell" id="chat-room-shell">
    <div class="chat-room-grid">
        <aside class="chat-room-sidebar">
            <div class="chat-room-sidebar-header">
                <div class="chat-room-sidebar-top">
                    <div>
                        <div class="chat-room-kicker">All Inbox</div>
                        <h5 class="chat-room-section-title">Threads</h5>
                    </div>
                    <div class="chat-room-stat">
                        <span class="dot"></span>
                        Realtime
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <span class="chat-room-stat"><i class="ri-message-2-line"></i> {{ $conversationCount }} thread</span>
                    <span class="chat-room-stat"><i class="ri-mail-unread-line"></i> {{ $unreadCount }} unread</span>
                </div>
            </div>

            <div class="chat-search-box mt-3">
                <form method="GET" action="{{ route('admin.mobile.live_chat') }}" class="chat-search-form" data-chat-search-form>
                    <div class="chat-search-field">
                        <span class="text-muted">
                            <i class="ri-search-line"></i>
                        </span>
                        <input
                            type="text"
                            class="form-control"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Search message or people"
                            aria-label="Search message or people"
                            data-chat-search-input
                        >
                    </div>
                    <button class="btn btn-primary chat-search-button" type="submit" aria-label="Cari percakapan">
                        <i class="ri-arrow-right-line"></i>
                    </button>
                </form>
            </div>

            <div id="chat-conversation-empty-search" class="chat-search-empty d-none">
                Tidak ada percakapan yang cocok dengan kata kunci ini.
            </div>

            <div id="chat-conversation-list" class="chat-thread-list">
                @forelse ($conversations as $conversation)
                    @php
                        $conversationName = $conversation['mobile_user']['name'] ?? 'Percakapan';
                        $conversationSubject = $conversation['service_request_code'] ?? $conversation['subject'] ?? '-';
                        $conversationPreview = $previewText($conversation['last_message'] ?? null);
                        $conversationTime = $conversation['last_message_at'] ? \Illuminate\Support\Carbon::parse($conversation['last_message_at'])->diffForHumans() : '-';
                        $conversationTone = $chatAvatarTone($conversation['id'] ?? 0);
                    @endphp
                    <a
                        href="{{ $conversationUrl($conversation) }}"
                        class="chat-thread-item {{ $selectedConversation && $selectedConversation->id === $conversation['id'] ? 'is-active' : '' }}"
                        data-conversation-item
                        data-conversation-id="{{ $conversation['id'] }}"
                        data-conversation-url="{{ $conversationUrl($conversation) }}"
                    >
                        <div class="chat-avatar chat-avatar-sm {{ $conversationTone }}">
                            {{ $chatInitial($conversationName) }}
                            <span class="chat-avatar-dot"></span>
                        </div>
                        <div class="chat-thread-body">
                            <div class="chat-thread-head">
                                <div class="flex-grow-1">
                                    <h6 class="chat-thread-name" data-role="conversation-name">{{ $conversationName }}</h6>
                                    <div class="chat-thread-subject" data-role="conversation-subject">{{ $conversationSubject }}</div>
                                </div>
                                <div class="chat-thread-meta">
                                    @if (($conversation['unread_for_admin'] ?? 0) > 0)
                                        <span class="badge rounded-pill bg-danger mb-1" data-role="conversation-unread">{{ $conversation['unread_for_admin'] }}</span>
                                    @endif
                                    <div class="chat-thread-time" data-role="conversation-timestamp">{{ $conversationTime }}</div>
                                </div>
                            </div>
                            <div class="chat-thread-preview" data-role="conversation-preview">{{ $conversationPreview }}</div>
                        </div>
                    </a>
                @empty
                    <div id="chat-conversation-empty" class="chat-empty-panel">
                        <div class="chat-empty-panel-inner">
                            <div class="chat-empty-panel-icon">
                                <i class="ri-chat-voice-line"></i>
                            </div>
                            <h5 class="chat-empty-panel-title">Belum ada percakapan</h5>
                            <p class="chat-empty-panel-text mb-0">Saat user mulai chat atau admin membalas, thread akan muncul di sini secara realtime.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </aside>

        <section class="chat-room-panel">
            @if ($selectedConversation)
                @php
                    $selectedName = $selectedConversation->mobileUser?->name ?? '-';
                    $selectedContact = $selectedConversation->mobileUser?->email ?? $selectedConversation->mobileUser?->phone ?? '-';
                    $selectedSubject = $selectedConversation->serviceRequest?->transaction_code_label ?? 'Percakapan umum';
                    $selectedService = $selectedConversation->serviceRequest?->service?->title;
                    $selectedTone = $chatAvatarTone($selectedConversation->id);
                @endphp
                <div class="chat-panel">
                    <div class="chat-panel-header">
                        <div class="chat-participant">
                            <div class="chat-participant-copy">
                                <h4 class="chat-participant-name">{{ $selectedName }}</h4>
                                <div class="chat-participant-subtitle">
                                    {{ $selectedSubject }}
                                    @if ($selectedService)
                                        • {{ $selectedService }}
                                    @endif
                                </div>
                                <div class="chat-participant-contact">
                                    <span class="chat-contact-pill"><i class="ri-mail-line"></i> {{ $selectedContact }}</span>
                                    <span class="chat-contact-pill"><i class="ri-time-line"></i> {{ $selectedConversation->status }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="chat-panel-actions">
                            <button
                                id="chat-sidebar-toggle"
                                type="button"
                                class="btn btn-outline-secondary chat-panel-action-btn"
                                aria-pressed="false"
                                aria-label="Sembunyikan sidebar percakapan"
                            >
                                <i class="ri-layout-left-line"></i>
                                <span data-role="sidebar-toggle-label">Sembunyikan sidebar</span>
                            </button>
                            @if ($selectedConversation->service_request_id)
                                <a href="{{ route('admin.mobile.service_requests.show', $selectedConversation->service_request_id) }}" class="btn btn-outline-primary chat-panel-action-btn">
                                    <i class="ri-file-text-line"></i> <span>Detail</span>
                                </a>
                            @endif
                            <span class="chat-status-badge">Open</span>
                        </div>
                    </div>

                    <div class="chat-message-wrap">
                        <div class="chat-message-list" id="chat-message-list">
                            @forelse ($selectedMessages as $message)
                                @php
                                    $isAdmin = ($message['sender_type'] ?? '') === 'admin';
                                    $attachments = collect($message['attachments'] ?? [])
                                        ->filter(fn ($attachment) => is_array($attachment))
                                        ->values();
                                    $messageName = $isAdmin ? 'Admin' : $selectedName;
                                    $messageTime = \Illuminate\Support\Carbon::parse($message['created_at'])->format('d M Y H:i');
                                @endphp
                                <div class="chat-message-row {{ $isAdmin ? 'is-admin' : 'is-user' }}" data-message-item data-message-id="{{ $message['id'] }}">
                                    <div class="chat-avatar chat-avatar-sm {{ $isAdmin ? 'tone-1' : $selectedTone }}">
                                        {{ $chatInitial($messageName, $isAdmin ? 'A' : 'U') }}
                                    </div>
                                    <div class="chat-message-card {{ $isAdmin ? 'is-admin' : 'is-user' }}">
                                        <div class="chat-message-head">
                                            <div class="chat-message-name">{{ $messageName }}</div>
                                            <div class="chat-message-time">{{ $messageTime }}</div>
                                        </div>

                                        @if (trim((string) ($message['body'] ?? '')) !== '')
                                            <div class="chat-message-body">{{ $message['body'] ?? '' }}</div>
                                        @endif

                                        @if ($attachments->isNotEmpty())
                                            <div class="chat-message-attachments">
                                                @foreach ($attachments as $attachment)
                                                    @php
                                                        $attachmentUrl = $attachment['url'] ?? $attachment['uri'] ?? $attachment['path'] ?? null;
                                                    @endphp
                                                    @if ($attachmentUrl)
                                                        <a href="{{ $attachmentUrl }}" target="_blank" class="chat-message-attachment">
                                                            <img src="{{ $attachmentUrl }}" alt="Lampiran chat">
                                                        </a>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif

                                        @if (trim((string) ($message['body'] ?? '')) === '' && $attachments->isNotEmpty())
                                            <div class="chat-message-note {{ $isAdmin ? 'text-white-50' : 'text-muted' }}">Foto terkirim</div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="chat-message-empty">
                                    <div class="chat-empty-panel-inner">
                                        <div class="chat-empty-panel-icon">
                                            <i class="ri-message-3-line"></i>
                                        </div>
                                        <h5 class="chat-empty-panel-title">Belum ada pesan</h5>
                                        <p class="chat-empty-panel-text mb-0">Mulai percakapan dengan user agar thread ini hidup dan langsung tersinkron realtime.</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <form
                        id="chat-message-form"
                        method="POST"
                        action="{{ route('admin.mobile.live_chat.messages', $selectedConversation->id) }}"
                        class="chat-composer"
                        autocomplete="off"
                    >
                        @csrf

                        <div class="chat-composer-body">
                            <span class="chat-composer-icon" aria-hidden="true">
                                <i class="ri-camera-3-line"></i>
                            </span>
                            <input id="chat-message-input" type="text" name="message" class="form-control chat-composer-input" placeholder="Type your message here" required value="{{ old('message') }}">
                            <button id="chat-message-submit" class="btn btn-primary chat-composer-send" type="submit" aria-label="Kirim pesan">
                                <i class="ri-send-plane-fill"></i>
                            </button>
                        </div>

                        <div id="chat-message-error" class="text-danger mt-2 small d-none" role="alert"></div>
                        @error('message')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </form>
                </div>
            @else
                <div class="chat-empty-panel">
                    <div class="chat-empty-panel-inner">
                        <div class="chat-empty-panel-icon">
                            <i class="ri-message-3-line"></i>
                        </div>
                        <h5 class="chat-empty-panel-title">Pilih percakapan</h5>
                        <p class="chat-empty-panel-text mb-0">Pilih thread di sisi kiri atau buka dari detail pengajuan untuk mulai membalas user.</p>
                    </div>
                </div>
            @endif
        </section>
    </div>
</div>
@endsection

@push('admin-scripts')
    <script>
      window.__MANINJAU_ADMIN_LIVE_CHAT__ = {
        selectedConversationId: Number(@json($selectedConversation?->id ?? null)) || 0,
        liveChatBaseUrl: @json(route('admin.mobile.live_chat')),
        reverbKey: @json(config('broadcasting.connections.reverb.key')),
        reverbHost: @json(config('broadcasting.connections.reverb.options.host') ?: request()->getHost()),
        reverbScheme: @json(config('broadcasting.connections.reverb.options.scheme') ?: 'http'),
        reverbPort: Number(@json(config('broadcasting.connections.reverb.options.port') ?: 8080)),
        reverbTlsPort: Number(@json(config('broadcasting.connections.reverb.options.tls_port') ?: 443)),
        pusherCluster: @json(config('broadcasting.connections.pusher.options.cluster') ?: 'mt1'),
        authEndpoint: `${window.location.origin}/broadcasting/auth`,
        csrfToken: @json(csrf_token()),
      };
    </script>
    @vite(['resources/js/admin/live-chat.js'])
@endpush
