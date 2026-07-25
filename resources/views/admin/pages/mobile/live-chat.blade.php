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
    /* Live chat mengikuti design system admin: kartu putih, border tipis,
       radius .5rem, shadow-sm, brand #275a56. Tanpa gradient/blur. */
    .chat-room-shell { --c-brand:#275a56; --c-line:#e9ecef; --c-muted:#6c757d; --c-soft:#f6f9f8; }
    .chat-room-grid {
        display:grid; grid-template-columns:320px minmax(0,1fr);
        height:calc(100vh - 230px); min-height:520px; transition:grid-template-columns .2s ease;
    }
    /* Sidebar bisa disembunyikan lewat tombol (JS toggle .is-sidebar-collapsed) */
    .chat-room-shell.is-sidebar-collapsed .chat-room-grid { grid-template-columns:0 minmax(0,1fr); }
    .chat-room-shell.is-sidebar-collapsed .chat-room-sidebar { opacity:0; visibility:hidden; }

    /* ---------- Sidebar ---------- */
    .chat-room-sidebar {
        display:flex; flex-direction:column; min-width:0; overflow:hidden;
        border-right:1px solid var(--c-line); background:#fff;
    }
    .chat-room-sidebar-header { padding:14px 16px; border-bottom:1px solid var(--c-line); flex:none; }
    .chat-room-sidebar-top { display:flex; align-items:center; justify-content:space-between; gap:8px; }
    .chat-room-section-title { margin:0; font-size:.95rem; font-weight:600; }
    .chat-room-kicker { font-size:.75rem; color:var(--c-muted); margin-top:2px; }
    .chat-room-stat {
        display:inline-flex; align-items:center; gap:5px; font-size:.7rem; font-weight:600;
        color:#28a745; background:#eaf7ee; border:1px solid #c9ebd4; border-radius:999px; padding:2px 9px;
    }
    .chat-room-stat .dot { width:6px; height:6px; border-radius:50%; background:#28a745; }

    .chat-search-form { display:flex; gap:6px; margin-top:12px; }
    .chat-search-field { position:relative; flex:1; min-width:0; }
    .chat-search-field > span { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#adb5bd; line-height:1; }
    .chat-search-field .form-control { padding-left:30px; font-size:.83rem; }
    .chat-search-button { flex:none; padding:.35rem .65rem; }
    .chat-search-empty { padding:14px 16px; font-size:.8rem; color:var(--c-muted); }

    .chat-thread-list { flex:1; min-height:0; overflow-y:auto; padding:8px; }
    .chat-thread-list::-webkit-scrollbar { width:6px; }
    .chat-thread-list::-webkit-scrollbar-thumb { background:#dfe5e4; border-radius:6px; }
    .chat-thread-item {
        display:flex; align-items:flex-start; gap:10px; padding:10px; margin-bottom:2px;
        border-radius:.375rem; border-left:3px solid transparent;
        text-decoration:none; color:inherit; transition:background .12s;
    }
    .chat-thread-item:hover { background:var(--c-soft); }
    .chat-thread-item.is-active { background:var(--c-soft); border-left-color:var(--c-brand); }
    .chat-thread-body { min-width:0; flex:1; }
    .chat-thread-head { display:flex; align-items:flex-start; gap:8px; }
    .chat-thread-name { margin:0; font-size:.85rem; font-weight:600; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .chat-thread-subject { font-size:.71rem; color:var(--c-brand); font-weight:500; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .chat-thread-meta { flex:none; text-align:right; }
    .chat-thread-time { font-size:.67rem; color:#adb5bd; white-space:nowrap; }
    .chat-thread-preview { font-size:.76rem; color:var(--c-muted); margin-top:3px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

    /* ---------- Avatar ---------- */
    .chat-avatar {
        width:38px; height:38px; border-radius:50%; flex:none; position:relative;
        display:inline-flex; align-items:center; justify-content:center;
        font-weight:600; font-size:.82rem; color:#fff; background:var(--c-brand);
    }
    .chat-avatar-sm { width:32px; height:32px; font-size:.74rem; }
    .chat-avatar-dot { position:absolute; right:-1px; bottom:-1px; width:9px; height:9px; border-radius:50%; background:#28a745; border:2px solid #fff; }
    .chat-avatar.tone-1{background:#275a56}.chat-avatar.tone-2{background:#c8915c}.chat-avatar.tone-3{background:#4b7bec}
    .chat-avatar.tone-4{background:#8e6bbf}.chat-avatar.tone-5{background:#2f9e8f}.chat-avatar.tone-6{background:#c1554f}

    /* ---------- Panel ---------- */
    .chat-room-panel { display:flex; flex-direction:column; min-width:0; overflow:hidden; background:#fff; }
    .chat-panel { display:flex; flex-direction:column; flex:1; min-height:0; }
    .chat-panel-header {
        display:flex; align-items:center; justify-content:space-between; gap:12px;
        padding:12px 16px; border-bottom:1px solid var(--c-line); flex:none; flex-wrap:wrap;
    }
    .chat-participant { display:flex; align-items:center; gap:10px; min-width:0; flex:1; }
    .chat-participant-copy { min-width:0; }
    .chat-participant-name { margin:0; font-size:.95rem; font-weight:600; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .chat-participant-subtitle { font-size:.74rem; color:var(--c-muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .chat-participant-contact { display:flex; flex-wrap:wrap; gap:5px; margin-top:4px; }
    .chat-contact-pill {
        display:inline-flex; align-items:center; gap:4px; font-size:.68rem; color:var(--c-muted);
        background:var(--c-soft); border:1px solid var(--c-line); border-radius:999px; padding:1px 8px;
    }
    .chat-panel-actions { display:flex; align-items:center; gap:6px; flex:none; }
    .chat-panel-action-btn { display:inline-flex; align-items:center; gap:5px; font-size:.76rem; padding:.3rem .6rem; white-space:nowrap; }
    .chat-status-badge {
        display:inline-flex; align-items:center; gap:5px; font-size:.68rem; font-weight:600;
        color:#28a745; background:#eaf7ee; border:1px solid #c9ebd4; border-radius:999px; padding:2px 9px;
    }
    .chat-status-badge::before { content:''; width:6px; height:6px; border-radius:50%; background:#28a745; }

    /* ---------- Pesan ---------- */
    .chat-message-wrap { flex:1; min-height:0; display:flex; flex-direction:column; }
    .chat-message-list { flex:1; min-height:0; overflow-y:auto; padding:18px 16px; background:#fbfcfc; }
    .chat-message-list::-webkit-scrollbar { width:6px; }
    .chat-message-list::-webkit-scrollbar-thumb { background:#dfe5e4; border-radius:6px; }

    .chat-message-row { display:flex; align-items:flex-end; gap:8px; margin-bottom:14px; }
    .chat-message-row.is-admin { justify-content:flex-end; }
    .chat-message-row.is-admin .chat-avatar { order:2; }
    .chat-message-card {
        max-width:min(70%, 520px); min-width:0; padding:8px 12px;
        border:1px solid var(--c-line); background:#fff; border-radius:.65rem;
    }
    .chat-message-row.is-user  .chat-message-card { border-bottom-left-radius:.2rem; }
    .chat-message-row.is-admin .chat-message-card { border-bottom-right-radius:.2rem;
        background:var(--c-brand); border-color:var(--c-brand); color:#fff; }
    .chat-message-head { display:flex; align-items:baseline; gap:8px; margin-bottom:2px; }
    .chat-message-name { font-size:.71rem; font-weight:600; opacity:.9; }
    .chat-message-time { font-size:.65rem; opacity:.7; }
    .chat-message-body { font-size:.85rem; line-height:1.5; word-break:break-word; white-space:pre-wrap; }
    .chat-message-note { font-size:.7rem; margin-top:3px; }
    .chat-message-attachments { display:flex; flex-wrap:wrap; gap:6px; margin-top:6px; }
    .chat-message-attachment { display:block; border-radius:.375rem; overflow:hidden; border:1px solid rgba(0,0,0,.08); line-height:0; }
    .chat-message-attachment img { display:block; width:140px; height:140px; object-fit:cover; }

    /* ---------- Composer ---------- */
    .chat-composer { flex:none; padding:12px 16px; border-top:1px solid var(--c-line); background:#fff; }
    .chat-composer-body { display:flex; align-items:center; gap:8px; }
    .chat-composer-input { flex:1; min-width:0; font-size:.85rem; }
    .chat-composer-send { flex:none; padding:.4rem .75rem; }
    .chat-composer-attach {
        flex:none; width:38px; height:38px; border-radius:.375rem; border:1px solid var(--c-line);
        background:#fff; color:var(--c-muted); font-size:1.05rem; line-height:1;
        display:inline-flex; align-items:center; justify-content:center;
    }
    .chat-composer-attach:hover { color:var(--c-brand); border-color:var(--c-brand); }
    /* Pratinjau media sebelum kirim */
    .chat-attach-preview { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:10px; }
    /* Thumbnail gambar (kotak) */
    .chat-attach-thumb { position:relative; width:60px; height:60px; border-radius:.5rem;
        overflow:hidden; border:1px solid var(--c-line); background:var(--c-soft); }
    .chat-attach-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
    /* Chip dokumen (memanjang) */
    .chat-attach-file { position:relative; display:flex; align-items:center; gap:8px;
        max-width:220px; padding:7px 26px 7px 8px; border:1px solid var(--c-line);
        border-radius:.5rem; background:var(--c-soft); }
    .chat-attach-file__icon { flex:none; width:34px; height:34px; border-radius:.4rem;
        background:#fff; border:1px solid var(--c-line); display:flex; align-items:center;
        justify-content:center; color:var(--c-brand); font-size:1.1rem; }
    .chat-attach-file__meta { min-width:0; display:flex; flex-direction:column; }
    .chat-attach-file__name { font-size:.75rem; font-weight:600; color:#212529;
        overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .chat-attach-file__size { font-size:.68rem; color:var(--c-muted); }
    /* Tombol hapus (di kedua varian) */
    .chat-attach-remove { position:absolute; top:3px; right:3px; width:18px; height:18px;
        border:none; border-radius:50%; background:rgba(15,23,42,.65); color:#fff;
        font-size:.8rem; line-height:1; display:flex; align-items:center; justify-content:center; cursor:pointer; padding:0; }
    .chat-attach-remove:hover { background:#dc3545; }

    /* ---------- State kosong ---------- */
    .chat-empty-panel { display:flex; align-items:center; justify-content:center; flex:1; padding:24px; }
    .chat-empty-panel-inner { text-align:center; max-width:340px; }
    .chat-empty-panel-icon { font-size:34px; color:#ced4da; line-height:1; }
    .chat-empty-panel-title { margin:10px 0 4px; font-size:.98rem; font-weight:600; }
    .chat-empty-panel-text { font-size:.82rem; color:var(--c-muted); margin:0; }

    @media (max-width: 991.98px) {
        .chat-room-grid { grid-template-columns:1fr; height:auto; }
        .chat-room-sidebar { border-right:0; border-bottom:1px solid var(--c-line); max-height:42vh; }
        .chat-room-panel { min-height:60vh; }
        .chat-message-card { max-width:88%; }
    }
</style>

<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap:16px;">
                <div>
                    <h4 class="card-title mb-1">Live Chat</h4>
                    <p class="text-muted mb-0">Kelola percakapan user dengan admin. Pilih thread di kiri untuk membaca dan membalas &mdash; pesan masuk tersinkron otomatis.</p>
                </div>
                <a href="{{ route('admin.mobile.index') }}" class="btn btn-light btn-sm"><i class="ri-arrow-left-line me-1"></i> Overview</a>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow-sm border-0 overflow-hidden">
            <div class="chat-room-shell" id="chat-room-shell">
                <div class="chat-room-grid">

                    {{-- ============ Daftar percakapan ============ --}}
                    <aside class="chat-room-sidebar">
                        <div class="chat-room-sidebar-header">
                            <div class="chat-room-sidebar-top">
                                <div>
                                    <h5 class="chat-room-section-title">Percakapan</h5>
                                    <div class="chat-room-kicker">{{ $conversationCount }} thread &middot; {{ $unreadCount }} belum dibaca</div>
                                </div>
                                <span class="chat-room-stat"><span class="dot"></span> Realtime</span>
                            </div>

                            <form method="GET" action="{{ route('admin.mobile.live_chat') }}" class="chat-search-form" data-chat-search-form>
                                <div class="chat-search-field">
                                    <span><i class="ri-search-line"></i></span>
                                    <input type="text" class="form-control form-control-sm" name="q"
                                           value="{{ request('q') }}" placeholder="Cari nama atau pesan…"
                                           aria-label="Cari percakapan" data-chat-search-input>
                                </div>
                                <button class="btn btn-primary btn-sm chat-search-button" type="submit" aria-label="Cari percakapan">
                                    <i class="ri-arrow-right-line"></i>
                                </button>
                            </form>
                        </div>

                        <div id="chat-conversation-empty-search" class="chat-search-empty d-none">
                            Tidak ada percakapan yang cocok.
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
                                <a href="{{ $conversationUrl($conversation) }}"
                                   class="chat-thread-item {{ $selectedConversation && $selectedConversation->id === $conversation['id'] ? 'is-active' : '' }}"
                                   data-conversation-item
                                   data-conversation-id="{{ $conversation['id'] }}"
                                   data-conversation-url="{{ $conversationUrl($conversation) }}">
                                    <div class="chat-avatar chat-avatar-sm {{ $conversationTone }}">
                                        {{ $chatInitial($conversationName) }}
                                        <span class="chat-avatar-dot"></span>
                                    </div>
                                    <div class="chat-thread-body">
                                        <div class="chat-thread-head">
                                            <div class="flex-grow-1" style="min-width:0">
                                                <h6 class="chat-thread-name" data-role="conversation-name">{{ $conversationName }}</h6>
                                                <div class="chat-thread-subject" data-role="conversation-subject">{{ $conversationSubject }}</div>
                                            </div>
                                            <div class="chat-thread-meta">
                                                @if (($conversation['unread_for_admin'] ?? 0) > 0)
                                                    <span class="badge rounded-pill bg-danger" data-role="conversation-unread">{{ $conversation['unread_for_admin'] }}</span>
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
                                        <div class="chat-empty-panel-icon"><i class="ri-chat-voice-line"></i></div>
                                        <h5 class="chat-empty-panel-title">Belum ada percakapan</h5>
                                        <p class="chat-empty-panel-text">Thread akan muncul di sini saat user mulai chat.</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </aside>

                    {{-- ============ Ruang percakapan ============ --}}
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
                                        <div class="chat-avatar {{ $selectedTone }}">{{ $chatInitial($selectedName) }}</div>
                                        <div class="chat-participant-copy">
                                            <h4 class="chat-participant-name">{{ $selectedName }}</h4>
                                            <div class="chat-participant-subtitle">
                                                {{ $selectedSubject }}@if ($selectedService) &middot; {{ $selectedService }} @endif
                                            </div>
                                            <div class="chat-participant-contact">
                                                <span class="chat-contact-pill"><i class="ri-mail-line"></i> {{ $selectedContact }}</span>
                                                <span class="chat-status-badge">{{ ucfirst($selectedConversation->status) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="chat-panel-actions">
                                        <button id="chat-sidebar-toggle" type="button"
                                                class="btn btn-outline-secondary btn-sm chat-panel-action-btn"
                                                aria-pressed="false" aria-label="Sembunyikan sidebar percakapan">
                                            <i class="ri-layout-left-line"></i>
                                            <span data-role="sidebar-toggle-label">Sembunyikan sidebar</span>
                                        </button>
                                        @if ($selectedConversation->service_request_id)
                                            <a href="{{ route('admin.mobile.service_requests.show', $selectedConversation->service_request_id) }}"
                                               class="btn btn-outline-primary btn-sm chat-panel-action-btn">
                                                <i class="ri-file-text-line"></i> Detail Order
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <div class="chat-message-wrap">
                                    <div class="chat-message-list" id="chat-message-list">
                                        @forelse ($selectedMessages as $message)
                                            @php
                                                $isAdmin = ($message['sender_type'] ?? '') === 'admin';
                                                $attachments = collect($message['attachments'] ?? [])->filter(fn ($a) => is_array($a))->values();
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
                                                                @php $attachmentUrl = $attachment['url'] ?? $attachment['uri'] ?? $attachment['path'] ?? null; @endphp
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
                                            <div id="chat-empty-state" class="chat-empty-panel">
                                                <div class="chat-empty-panel-inner">
                                                    <div class="chat-empty-panel-icon"><i class="ri-message-3-line"></i></div>
                                                    <h5 class="chat-empty-panel-title">Belum ada pesan</h5>
                                                    <p class="chat-empty-panel-text">Mulai percakapan dengan user di kolom bawah.</p>
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                <form id="chat-message-form" method="POST"
                                      action="{{ route('admin.mobile.live_chat.messages', $selectedConversation->id) }}"
                                      class="chat-composer" autocomplete="off" enctype="multipart/form-data">
                                    @csrf

                                    {{-- Pratinjau media sebelum dikirim (diisi oleh JS) --}}
                                    <div id="chat-attach-preview" class="chat-attach-preview d-none"></div>

                                    <div class="chat-composer-body">
                                        <input id="chat-attach-input" type="file" name="attachments[]" multiple
                                               accept="image/*,application/pdf" class="d-none">
                                        <button id="chat-attach-btn" type="button" class="chat-composer-attach"
                                                aria-label="Lampirkan media">
                                            <i class="ri-attachment-2"></i>
                                        </button>
                                        <input id="chat-message-input" type="text" name="message"
                                               class="form-control chat-composer-input" placeholder="Tulis balasan…"
                                               value="{{ old('message') }}">
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
                                    <div class="chat-empty-panel-icon"><i class="ri-message-3-line"></i></div>
                                    <h5 class="chat-empty-panel-title">Pilih percakapan</h5>
                                    <p class="chat-empty-panel-text">Pilih thread di sisi kiri, atau buka dari detail order untuk membalas user.</p>
                                </div>
                            </div>
                        @endif
                    </section>

                </div>
            </div>
        </div>
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
