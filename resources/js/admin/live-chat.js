import Pusher from 'pusher-js';

const config = window.__MANINJAU_ADMIN_LIVE_CHAT__ ?? {};

window.Pusher = Pusher;

document.addEventListener('DOMContentLoaded', () => {
  const shell = document.getElementById('chat-room-shell');
  const selectedConversationId = Number(config.selectedConversationId ?? 0) || 0;
  const listRoot = document.getElementById('chat-conversation-list');
  const conversationEmpty = document.getElementById('chat-conversation-empty');
  const searchForm = document.querySelector('[data-chat-search-form]');
  const searchInput = document.querySelector('[data-chat-search-input]');
  const searchEmpty = document.getElementById('chat-conversation-empty-search');
  const messageRoot = document.getElementById('chat-message-list');
  const emptyState = document.getElementById('chat-empty-state');
  const messageForm = document.getElementById('chat-message-form');
  const messageInput = document.getElementById('chat-message-input');
  const messageError = document.getElementById('chat-message-error');
  const messageSubmit = document.getElementById('chat-message-submit');
  const sidebarToggle = document.getElementById('chat-sidebar-toggle');
  const sidebarToggleLabel = sidebarToggle?.querySelector('[data-role="sidebar-toggle-label"]');
  const attachInput = document.getElementById('chat-attach-input');
  const attachBtn = document.getElementById('chat-attach-btn');
  const attachPreview = document.getElementById('chat-attach-preview');
  const avatarTones = ['tone-1', 'tone-2', 'tone-3', 'tone-4', 'tone-5', 'tone-6'];
  const sidebarStorageKey = 'maninjau-admin-live-chat-sidebar-collapsed';

  const reverbKey = String(config.reverbKey ?? '').trim();
  const reverbHost = String(config.reverbHost ?? window.location.hostname).trim();
  const reverbScheme = String(config.reverbScheme ?? 'http').toLowerCase();
  const reverbPort = Number(config.reverbPort ?? 8080) || 8080;
  const reverbTlsPort = Number(config.reverbTlsPort ?? 443) || 443;
  const pusherCluster = String(config.pusherCluster ?? 'mt1').trim() || 'mt1';
  const forceTLS = reverbScheme === 'https' || reverbScheme === 'wss' || window.location.protocol === 'https:';
  const authEndpoint = String(config.authEndpoint ?? `${window.location.origin}/broadcasting/auth`);
  const csrfToken = String(config.csrfToken ?? '').trim();
  const liveChatBaseUrl = String(config.liveChatBaseUrl ?? '').trim();

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function truncate(value, limit = 96) {
    const text = String(value ?? '').trim();
    if (text.length <= limit) {
      return text || '-';
    }

    return `${text.slice(0, limit).trimEnd()}…`;
  }

  function getInitial(value, fallback = 'U') {
    const text = String(value ?? '').trim();

    if (!text) {
      return fallback;
    }

    return text.charAt(0).toUpperCase();
  }

  function getAvatarTone(seed) {
    const numericSeed = Math.abs(Number(seed ?? 0)) || 0;
    return avatarTones[numericSeed % avatarTones.length];
  }

  function timeAgo(isoValue) {
    if (!isoValue) {
      return '-';
    }

    const timestamp = new Date(isoValue).getTime();
    if (Number.isNaN(timestamp)) {
      return '-';
    }

    const diffSeconds = Math.max(0, Math.floor((Date.now() - timestamp) / 1000));

    if (diffSeconds < 60) {
      return 'Baru saja';
    }

    if (diffSeconds < 3600) {
      return `${Math.floor(diffSeconds / 60)} menit lalu`;
    }

    if (diffSeconds < 86400) {
      return `${Math.floor(diffSeconds / 3600)} jam lalu`;
    }

    return new Intl.DateTimeFormat('id-ID', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
    }).format(new Date(isoValue));
  }

  function conversationUrl(conversationId) {
    return `${liveChatBaseUrl}/${conversationId}`;
  }

  function renderConversationCard(conversation) {
    const isSelected = selectedConversationId > 0 && Number(conversation.id) === selectedConversationId;
    const unreadCount = isSelected ? 0 : Number(conversation.unread_for_admin ?? 0);
    const preview = truncate(conversation.last_message ?? conversation.subject ?? '-');
    const title = conversation.mobile_user?.name || 'Percakapan';
    const subtitle = conversation.service_request_code || conversation.subject || '-';
    const timestamp = timeAgo(conversation.last_message_at);
    const unreadBadge = unreadCount > 0 ? `<span class="badge rounded-pill bg-danger" data-role="conversation-unread">${unreadCount}</span>` : '';
    const toneClass = getAvatarTone(conversation.id);

    return `
      <a
        href="${escapeHtml(conversationUrl(conversation.id))}"
        class="chat-thread-item ${isSelected ? 'is-active' : ''}"
        data-conversation-item
        data-conversation-id="${escapeHtml(conversation.id)}"
        data-conversation-url="${escapeHtml(conversationUrl(conversation.id))}"
      >
        <div class="chat-avatar chat-avatar-sm ${toneClass}">
          ${escapeHtml(getInitial(title))}
          <span class="chat-avatar-dot"></span>
        </div>
        <div class="chat-thread-body">
          <div class="chat-thread-head">
            <div class="flex-grow-1">
              <h6 class="chat-thread-name" data-role="conversation-name">${escapeHtml(title)}</h6>
              <div class="chat-thread-subject" data-role="conversation-subject">${escapeHtml(subtitle)}</div>
            </div>
            <div class="chat-thread-meta">
              ${unreadBadge}
              <div class="chat-thread-time" data-role="conversation-timestamp">${escapeHtml(timestamp)}</div>
            </div>
          </div>
          <div class="chat-thread-preview" data-role="conversation-preview">${escapeHtml(preview)}</div>
        </div>
      </a>
    `;
  }

  function normalizeSearchValue(value) {
    return String(value ?? '')
      .toLowerCase()
      .trim();
  }

  function updateConversationSearch() {
    if (!listRoot || !searchInput) {
      return;
    }

    const query = normalizeSearchValue(searchInput.value);
    const items = Array.from(listRoot.querySelectorAll('[data-conversation-item]'));
    let visibleCount = 0;

    items.forEach((item) => {
      const text = normalizeSearchValue(item.textContent);
      const visible = query === '' || text.includes(query);

      item.classList.toggle('d-none', !visible);

      if (visible) {
        visibleCount += 1;
      }
    });

    if (searchEmpty) {
      const showEmpty = query !== '' && visibleCount === 0;
      searchEmpty.classList.toggle('d-none', !showEmpty);
    }
  }

  function renderAttachment(attachment) {
    const attachmentUrl = attachment?.url || attachment?.uri || attachment?.path || '';
    if (!attachmentUrl) {
      return '';
    }

    return `
      <a href="${escapeHtml(attachmentUrl)}" target="_blank" class="chat-message-attachment">
        <img src="${escapeHtml(attachmentUrl)}" alt="Lampiran chat">
      </a>
    `;
  }

  function renderMessageBubble(message, conversation) {
    const isAdmin = (message.sender_type || '') === 'admin';
    const senderName = isAdmin ? 'Admin' : (conversation.mobile_user?.name || 'User');
    const body = String(message.body ?? '').trim();
    const attachments = Array.isArray(message.attachments) ? message.attachments : [];
    const attachmentMarkup = attachments.map(renderAttachment).join('');
    const bodyMarkup = body ? `<div class="chat-message-body">${escapeHtml(body)}</div>` : '';
    const attachmentNote = !body && attachmentMarkup ? `<div class="chat-message-note ${isAdmin ? 'text-white-50' : 'text-muted'}">Foto terkirim</div>` : '';
    const createdAt = message.created_at
      ? new Date(message.created_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })
      : '-';
    const avatarTone = isAdmin ? 'tone-1' : getAvatarTone(conversation.id);

    return `
      <div class="chat-message-row ${isAdmin ? 'is-admin' : 'is-user'}" data-message-item data-message-id="${escapeHtml(message.id)}">
        <div class="chat-avatar chat-avatar-sm ${avatarTone}">
          ${escapeHtml(getInitial(senderName, isAdmin ? 'A' : 'U'))}
        </div>
        <div class="chat-message-card ${isAdmin ? 'is-admin' : 'is-user'}">
          <div class="chat-message-head">
            <div class="chat-message-name">${escapeHtml(senderName)}</div>
            <div class="chat-message-time">${escapeHtml(createdAt)}</div>
          </div>
          ${bodyMarkup}
          ${attachmentMarkup ? `<div class="chat-message-attachments">${attachmentMarkup}</div>` : ''}
          ${attachmentNote}
        </div>
      </div>
    `;
  }

  function upsertConversation(conversation) {
    if (!listRoot) {
      return;
    }

    const existing = listRoot.querySelector(`[data-conversation-item][data-conversation-id="${conversation.id}"]`);
    const cardHtml = renderConversationCard(conversation);

    if (existing) {
      existing.outerHTML = cardHtml;
      const refreshed = listRoot.querySelector(`[data-conversation-item][data-conversation-id="${conversation.id}"]`);
      if (refreshed) {
        listRoot.prepend(refreshed);
      }
      return;
    }

    if (conversationEmpty) {
      conversationEmpty.remove();
    }

    listRoot.insertAdjacentHTML('afterbegin', cardHtml);
    updateConversationSearch();
  }

  function appendMessage(payload) {
    if (!messageRoot) {
      return;
    }

    const messageExists = messageRoot.querySelector(`[data-message-item][data-message-id="${payload.message.id}"]`);
    if (messageExists) {
      return;
    }

    if (emptyState) {
      emptyState.remove();
    }

    messageRoot.insertAdjacentHTML('beforeend', renderMessageBubble(payload.message, payload.conversation));
    messageRoot.scrollTo({ top: messageRoot.scrollHeight, behavior: 'smooth' });
  }

  function setMessageError(text) {
    if (!messageError) {
      return;
    }

    const value = String(text ?? '').trim();
    if (!value) {
      messageError.textContent = '';
      messageError.classList.add('d-none');
      return;
    }

    messageError.textContent = value;
    messageError.classList.remove('d-none');
  }

  function setSendingState(isSending) {
    if (messageSubmit) {
      messageSubmit.disabled = isSending;
      messageSubmit.setAttribute('aria-busy', isSending ? 'true' : 'false');
    }

    if (messageInput) {
      messageInput.disabled = isSending;
    }

    if (attachBtn) {
      attachBtn.disabled = isSending;
    }
  }

  // ---- Lampiran media ----
  // Simpan pilihan di DataTransfer agar bisa hapus per-item dan tetap terkirim
  // lewat FormData (input.files disinkronkan dari sini).
  const attachStore = typeof DataTransfer !== 'undefined' ? new DataTransfer() : null;

  function syncAttachInput() {
    if (attachStore && attachInput) {
      attachInput.files = attachStore.files;
    }
  }

  function formatFileSize(bytes) {
    if (!bytes) return '';
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
  }

  function renderAttachPreview() {
    if (!attachPreview) {
      return;
    }

    const files = attachStore ? Array.from(attachStore.files) : [];
    attachPreview.classList.toggle('d-none', files.length === 0);
    attachPreview.innerHTML = files
      .map((file, index) => {
        const remove = `<button type="button" class="chat-attach-remove" data-attach-index="${index}" aria-label="Hapus">&times;</button>`;

        if (file.type.startsWith('image/')) {
          return `<div class="chat-attach-thumb"><img src="${URL.createObjectURL(file)}" alt="">${remove}</div>`;
        }

        const isPdf = file.type === 'application/pdf' || /\.pdf$/i.test(file.name);
        const icon = isPdf ? 'ri-file-pdf-2-line' : 'ri-file-text-line';
        return `<div class="chat-attach-file">
            <span class="chat-attach-file__icon"><i class="${icon}"></i></span>
            <span class="chat-attach-file__meta">
              <span class="chat-attach-file__name">${escapeHtml(file.name)}</span>
              <span class="chat-attach-file__size">${formatFileSize(file.size)}</span>
            </span>
            ${remove}
          </div>`;
      })
      .join('');
  }

  function addAttachments(fileList) {
    if (!attachStore) {
      return;
    }

    const MAX = 6;
    for (const file of Array.from(fileList)) {
      if (attachStore.items.length >= MAX) {
        break;
      }
      if (file.size > 10 * 1024 * 1024) {
        setMessageError(`${file.name} melebihi 10MB.`);
        continue;
      }
      attachStore.items.add(file);
    }

    syncAttachInput();
    renderAttachPreview();
  }

  function removeAttachment(index) {
    if (!attachStore) {
      return;
    }
    attachStore.items.remove(index);
    syncAttachInput();
    renderAttachPreview();
  }

  function clearAttachments() {
    if (attachStore) {
      attachStore.items.clear();
      syncAttachInput();
    }
    if (attachInput) {
      attachInput.value = '';
    }
    renderAttachPreview();
  }

  if (attachBtn && attachInput) {
    attachBtn.addEventListener('click', () => attachInput.click());
    attachInput.addEventListener('change', (event) => {
      if (event.target.files?.length) {
        addAttachments(event.target.files);
      }
    });
  }

  if (attachPreview) {
    attachPreview.addEventListener('click', (event) => {
      const removeBtn = event.target.closest('[data-attach-index]');
      if (removeBtn) {
        removeAttachment(Number(removeBtn.dataset.attachIndex));
      }
    });
  }

  function setSidebarCollapsed(collapsed) {
    if (!shell || !sidebarToggle) {
      return;
    }

    shell.classList.toggle('is-sidebar-collapsed', collapsed);
    sidebarToggle.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
    sidebarToggle.setAttribute(
      'aria-label',
      collapsed ? 'Tampilkan sidebar percakapan' : 'Sembunyikan sidebar percakapan'
    );

    if (sidebarToggleLabel) {
      sidebarToggleLabel.textContent = collapsed ? 'Tampilkan sidebar' : 'Sembunyikan sidebar';
    }

    const icon = sidebarToggle.querySelector('i');
    if (icon) {
      icon.className = collapsed ? 'ri-layout-right-line' : 'ri-layout-left-line';
    }

    try {
      window.localStorage.setItem(sidebarStorageKey, collapsed ? '1' : '0');
    } catch (error) {
      console.warn('Tidak bisa menyimpan state sidebar:', error);
    }
  }

  if (searchForm) {
    searchForm.addEventListener('submit', (event) => {
      event.preventDefault();
      updateConversationSearch();
    });
  }

  if (searchInput) {
    let searchFrame = null;

    searchInput.addEventListener('input', () => {
      if (searchFrame) {
        cancelAnimationFrame(searchFrame);
      }

      searchFrame = requestAnimationFrame(updateConversationSearch);
    });

    updateConversationSearch();
  }

  function loadSidebarCollapsed() {
    try {
      return window.localStorage.getItem(sidebarStorageKey) === '1';
    } catch (error) {
      return false;
    }
  }

  async function sendAdminMessage(form) {
    const body = new FormData(form);
    const rawMessage = String(body.get('message') ?? '').trim();
    const hasAttachments = attachStore ? attachStore.files.length > 0 : (attachInput?.files?.length ?? 0) > 0;

    if (!rawMessage && !hasAttachments) {
      setMessageError('Tulis pesan atau lampirkan media.');
      messageInput?.focus();
      return;
    }

    setMessageError('');
    setSendingState(true);

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'X-Requested-With': 'XMLHttpRequest',
        },
        body,
      });

      const data = await response.json().catch(() => ({}));

      if (!response.ok) {
        const validationMessage = data?.errors?.message?.[0] || data?.message || 'Pesan gagal dikirim.';
        setMessageError(validationMessage);
        return;
      }

      if (data?.conversation) {
        upsertConversation(data.conversation);
      }

      if (data?.message && data?.conversation) {
        appendMessage({ conversation: data.conversation, message: data.message });
      }

      form.reset();
      clearAttachments();
      setMessageError('');
      messageInput?.focus();
    } catch (error) {
      console.warn('Gagal mengirim pesan admin:', error);
      setMessageError('Pesan gagal dikirim.');
    } finally {
      setSendingState(false);
    }
  }

  function initRealtime() {
    if (!reverbKey) {
      console.warn('Realtime chat admin tidak aktif: Reverb key kosong.');
      return;
    }

    Pusher.logToConsole = true;

    console.log('Inisialisasi realtime chat admin dengan config:', {
      reverbKey,
      reverbHost,
      reverbScheme,
      reverbPort,
      reverbTlsPort,
      pusherCluster,
      forceTLS,
      authEndpoint,
    });

    const pusher = new Pusher(reverbKey, {
      authEndpoint,
      authorizer: (channel) => ({
        authorize: async (socketId, callback) => {
          try {
            const response = await fetch(authEndpoint, {
              method: 'POST',
              credentials: 'include',
              headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
              },
              body: JSON.stringify({
                socket_id: socketId,
                channel_name: channel.name,
              }),
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
              console.warn('Auth realtime admin gagal:', response.status, data);
              callback(true, data);
              return;
            }

            callback(false, data);
          } catch (error) {
            console.warn('Auth realtime admin error:', error);
            callback(true, error);
          }
        },
      }),
      disableStats: true,
      enabledTransports: ['ws', 'wss'],
      cluster: pusherCluster,
      forceTLS,
      wsHost: reverbHost,
      wsPort: forceTLS ? reverbTlsPort : reverbPort,
      wssPort: forceTLS ? reverbTlsPort : reverbPort,
    });

    pusher.connection.bind('connected', () => {
      console.log('Realtime admin connected');
    });

    pusher.connection.bind('disconnected', () => {
      console.warn('Realtime admin disconnected');
    });

    pusher.connection.bind('error', (error) => {
      console.warn('Realtime admin connection error:', error);
    });

    const channel = pusher.subscribe('private-admin.mobile.chat');

    channel.bind('pusher:subscription_succeeded', () => {
      console.log('Realtime admin subscribed to private-admin.mobile.chat');
    });

    channel.bind('pusher:subscription_error', (status) => {
      console.warn('Realtime admin subscription error:', status);
    });

    channel.bind('chat.message.created', (payload) => {
      if (!payload || !payload.conversation || !payload.message) {
        return;
      }

      upsertConversation(payload.conversation);

      if (selectedConversationId > 0 && Number(payload.conversation.id) === selectedConversationId) {
        appendMessage(payload);
      }
    });

    window.addEventListener('beforeunload', () => {
      channel.unbind('chat.message.created');
      pusher.disconnect();
    });
  }

  if (messageForm) {
    messageForm.addEventListener('submit', (event) => {
      event.preventDefault();
      void sendAdminMessage(messageForm);
    });
  }

  if (messageInput) {
    messageInput.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' && (event.ctrlKey || event.metaKey)) {
        event.preventDefault();
        if (messageForm) {
          void sendAdminMessage(messageForm);
        }
      }
    });
  }

  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
      setSidebarCollapsed(!shell?.classList.contains('is-sidebar-collapsed'));
    });
  }

  setSidebarCollapsed(loadSidebarCollapsed());

  initRealtime();
});
