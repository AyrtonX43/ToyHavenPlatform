/**
 * Unified Messages app: list + chat in one page, no refresh when switching.
 * Real-time via polling (and Echo when Reverb is available).
 */
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

(function () {
  const AUTH_ID = window.AUTH_ID;
  const APP_TIMEZONE = window.APP_TIMEZONE || 'Asia/Manila';
  const BROADCAST_CONFIG = window.BROADCAST_CONFIG;
  const CONVERSATIONS_INDEX_URL = window.CONVERSATIONS_INDEX_URL || '';

  let state = {
    conversationId: null,
    routes: null,
    other: null,
    myListings: [],
    lastMessageId: null,
    pollInterval: null,
    echoChannel: null,
  };

  const chatPanel = document.getElementById('chatPanel');
  const chatBody = document.getElementById('chatBody');
  const messagesEmpty = document.getElementById('messagesEmpty');
  const messageForm = document.getElementById('messageForm');
  const messageInput = document.getElementById('messageInput');
  const sendBtn = document.getElementById('sendBtn');
  const typingIndicator = document.getElementById('typingIndicator');
  const typingUserName = document.getElementById('typingUserName');
  const chatHeaderAvatar = document.getElementById('chatHeaderAvatar');
  const chatHeaderName = document.getElementById('chatHeaderName');
  const chatHeaderStatus = document.getElementById('chatHeaderStatus');
  const liveBadge = document.getElementById('liveBadge');
  const reportLink = document.getElementById('reportLink');

  function escapeHtml(s) {
    if (s == null) return '';
    const div = document.createElement('div');
    div.textContent = s;
    return div.innerHTML;
  }

  function formatTime(iso) {
    if (!iso) return 'Just now';
    const d = new Date(iso);
    if (isNaN(d.getTime())) return iso;
    const opts = { timeZone: APP_TIMEZONE, hour: 'numeric', minute: '2-digit', hour12: true };
    return d.toLocaleTimeString('en-PH', opts);
  }

  function renderOfferedListing(listing) {
    if (!listing || !listing.url) return '';
    const img = listing.image_url
      ? `<img src="${escapeHtml(listing.image_url)}" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:8px;">`
      : '<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:48px;height:48px;"><i class="bi bi-image text-muted"></i></div>';
    return `<a href="${escapeHtml(listing.url)}" class="msg-offered-product" target="_blank"><div class="d-flex align-items-center gap-2">${img}<div class="flex-grow-1 min-w-0"><div class="fw-semibold small text-truncate">${escapeHtml(listing.title || '')}</div>${listing.condition ? `<span class="badge bg-secondary" style="font-size:0.65rem;">${escapeHtml(listing.condition)}</span>` : ''}<div class="text-primary small mt-0"><i class="bi bi-box-arrow-up-right me-1"></i>View listing</div></div></div></a>`;
  }

  function buildMessageBubble(msg) {
    const isMine = msg.sender_id === AUTH_ID;
    const isUnsent = msg.is_unsent;
    const status = msg.seen_at ? 'Seen' : msg.delivered_at ? 'Delivered' : 'Sent';
    let html = '<div class="msg-bubble ' + (isMine ? 'mine' : 'theirs') + (isUnsent ? ' msg-unsent' : '') + '" data-message-id="' + msg.id + '">';
    if (!isMine) html += '<div class="msg-sender">' + escapeHtml(msg.sender_name || '') + '</div>';
    if (isUnsent) {
      html += '<div class="msg-unsent-text"><i class="bi bi-x-circle me-1"></i> ' + (isMine ? 'You removed this message' : 'This message was removed') + '</div>';
    } else {
      if (msg.offered_listing) html += renderOfferedListing(msg.offered_listing);
      if (msg.message) html += '<div class="msg-text">' + escapeHtml(msg.message) + '</div>';
      if (msg.attachments && msg.attachments.length) {
        html += '<div class="msg-attachments">';
        msg.attachments.forEach((a) => {
          if (a.is_image) html += '<img src="' + escapeHtml(a.url) + '" alt="">';
          else if (a.is_video) html += '<video src="' + escapeHtml(a.url) + '" controls></video>';
          else html += '<a href="' + escapeHtml(a.url) + '" target="_blank">' + escapeHtml(a.file_name || 'File') + '</a>';
        });
        html += '</div>';
      }
      html += '<div class="msg-time">' + escapeHtml(msg.formatted_created_at || formatTime(msg.created_at)) + '</div>';
    }
    html += '</div>';
    return html;
  }

  function scrollChatToBottom() {
    if (chatBody) chatBody.scrollTop = chatBody.scrollHeight;
  }

  function appendMessage(msg, animate = true) {
    if (!chatBody || !state.conversationId) return;
    const existing = chatBody.querySelector('[data-message-id="' + msg.id + '"]');
    if (existing) return;
    const div = document.createElement('div');
    div.innerHTML = buildMessageBubble(msg).trim();
    const bubble = div.firstElementChild;
    if (animate) bubble.classList.add('msg-enter');
    chatBody.appendChild(bubble);
    state.lastMessageId = Math.max(state.lastMessageId || 0, msg.id);
    requestAnimationFrame(() => requestAnimationFrame(scrollChatToBottom));
  }

  function setChatUI(data) {
    state.conversationId = data.conversation.id;
    state.routes = data.routes;
    state.other = data.other;
    state.myListings = data.my_listings || [];
    state.lastMessageId = data.messages.length ? Math.max(...data.messages.map((m) => m.id)) : null;

    if (chatPanel) chatPanel.classList.add('active');
    if (messagesEmpty) messagesEmpty.style.display = 'none';

    if (chatHeaderAvatar) chatHeaderAvatar.textContent = state.other && state.other.name ? state.other.name.charAt(0).toUpperCase() : '?';
    if (chatHeaderName) chatHeaderName.textContent = (state.other && state.other.name) || 'User';
    if (chatHeaderStatus) {
      chatHeaderStatus.textContent = state.other && state.other.is_online ? 'Online' : 'Offline';
      chatHeaderStatus.classList.remove('live', 'typing');
      if (state.other && state.other.is_online) chatHeaderStatus.classList.add('live');
    }
    if (reportLink && state.routes.report_form) {
      reportLink.href = state.routes.report_form;
      reportLink.classList.remove('d-none');
    }

    chatBody.innerHTML = '';
    (data.messages || []).forEach((m) => appendMessage(m, false));
    scrollChatToBottom();

    messageForm.onsubmit = (e) => {
      e.preventDefault();
      sendMessage();
    };
    messageInput.oninput = () => debounceTyping();

    startPolling();
    subscribeEcho();
    updateHistory();
  }

  function clearChat() {
    state.conversationId = null;
    state.routes = null;
    state.other = null;
    state.lastMessageId = null;
    stopPolling();
    unsubscribeEcho();
    if (chatPanel) chatPanel.classList.remove('active');
    if (messagesEmpty) messagesEmpty.style.display = 'flex';
    if (chatBody) chatBody.innerHTML = '';
    if (typingIndicator) typingIndicator.style.display = 'none';
  }

  function updateHistory() {
    const url = CONVERSATIONS_INDEX_URL + '#c' + state.conversationId;
    if (window.history && window.history.replaceState) window.history.replaceState(null, '', url);
  }

  function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value || '';
  }

  function fetchConversation(convId) {
    const url = (CONVERSATIONS_INDEX_URL.replace(/\/?$/, '') + '/' + convId);
    return fetch(url, {
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    }).then((r) => {
      if (!r.ok) throw new Error('Failed to load conversation');
      return r.json();
    });
  }

  function openConversation(convId) {
    const row = document.querySelector('.conv-row[data-conv-id="' + convId + '"]');
    if (row) {
      document.querySelectorAll('.conv-row').forEach((r) => r.classList.remove('active'));
      row.classList.add('active');
      row.querySelector('.unread-dot')?.remove();
    }
    fetchConversation(convId)
      .then((data) => setChatUI(data))
      .catch((err) => {
        console.error(err);
        alert('Could not load conversation. Please try again.');
      });
  }

  function sendMessage() {
    const text = (messageInput && messageInput.value || '').trim();
    if (!state.routes || !state.conversationId) return;
    if (!text) return;

    const fd = new FormData();
    fd.append('_token', getCsrf());
    fd.append('message', text);

    sendBtn.disabled = true;
    fetch(state.routes.messages_store, {
      method: 'POST',
      body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.message) {
          appendMessage(data.message);
          messageInput.value = '';
        }
        if (data.error) alert(data.error);
      })
      .catch(() => alert('Failed to send message.'))
      .finally(() => { sendBtn.disabled = false; });
  }

  let typingTimer = null;
  function debounceTyping() {
    if (!state.routes || !state.conversationId) return;
    if (typingTimer) clearTimeout(typingTimer);
    fetch(state.routes.typing, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': getCsrf(),
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({ typing: true }),
    }).catch(() => {});
    typingTimer = setTimeout(() => {
      fetch(state.routes.typing, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': getCsrf(),
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ typing: false }),
      }).catch(() => {});
      typingTimer = null;
    }, 1500);
  }

  function startPolling() {
    stopPolling();
    state.pollInterval = setInterval(() => {
      if (!state.conversationId || !state.routes || !state.lastMessageId) return;
      fetch(state.routes.messages_index + '?after_id=' + state.lastMessageId, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      })
        .then((r) => r.json())
        .then((data) => {
          (data.messages || []).forEach((m) => appendMessage(m));
          if ((data.messages || []).length > 0 && state.routes.mark_seen) {
            fetch(state.routes.mark_seen, {
              method: 'POST',
              headers: { Accept: 'application/json', 'X-CSRF-TOKEN': getCsrf(), 'X-Requested-With': 'XMLHttpRequest' },
            }).catch(() => {});
          }
        })
        .catch(() => {});
    }, 2500);
  }

  function stopPolling() {
    if (state.pollInterval) {
      clearInterval(state.pollInterval);
      state.pollInterval = null;
    }
  }

  let echoInstance = null;
  function getEcho() {
    if (!BROADCAST_CONFIG || !BROADCAST_CONFIG.key) return null;
    if (echoInstance) return echoInstance;
    try {
      window.Pusher = Pusher;
      const scheme = (BROADCAST_CONFIG.scheme || 'http').toLowerCase();
      echoInstance = new Echo({
        broadcaster: 'pusher',
        key: BROADCAST_CONFIG.key,
        wsHost: BROADCAST_CONFIG.host || 'localhost',
        wsPort: BROADCAST_CONFIG.port || 8080,
        wssPort: BROADCAST_CONFIG.port || 8080,
        forceTLS: scheme === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: (typeof window !== 'undefined' && window.location ? window.location.origin : '') + '/broadcasting/auth',
        auth: {
          headers: {
            'X-CSRF-TOKEN': getCsrf(),
            Accept: 'application/json',
          },
        },
      });
      window.Echo = echoInstance;
      return echoInstance;
    } catch (e) {
      return null;
    }
  }

  function subscribeEcho() {
    const Echo = getEcho();
    if (!Echo) {
      if (liveBadge) liveBadge.classList.add('d-none');
      return;
    }
    unsubscribeEcho();
    try {
      const channelName = 'conversation.' + state.conversationId;
      state.echoChannel = Echo.private(channelName);

      state.echoChannel.listen('.MessageSent', (e) => {
        if (e.sender_id === AUTH_ID) return;
        appendMessage({
          id: e.id,
          sender_id: e.sender_id,
          sender_name: e.sender_name,
          message: e.message,
          created_at: e.created_at,
          formatted_created_at: e.formatted_created_at,
          attachments: e.attachments || [],
          offered_listing: e.offered_listing,
          is_unsent: false,
        });
        if (typingIndicator) typingIndicator.style.display = 'none';
        if (state.routes && state.routes.mark_seen) {
          fetch(state.routes.mark_seen, {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': getCsrf(), 'X-Requested-With': 'XMLHttpRequest' },
          }).catch(() => {});
        }
      });

      state.echoChannel.listen('.UserTyping', (e) => {
        if (e.user_id === AUTH_ID) return;
        if (typingIndicator) {
          typingUserName.textContent = e.user_name || '';
          typingIndicator.style.display = 'inline-flex';
          typingIndicator.style.margin = '0 1rem 0.5rem';
        }
        setTimeout(() => {
          if (typingIndicator) typingIndicator.style.display = 'none';
        }, 3000);
      });

      if (liveBadge) liveBadge.classList.remove('d-none');
    } catch (err) {
      console.warn('Echo subscribe failed', err);
      if (liveBadge) liveBadge.classList.add('d-none');
    }
  }

  function unsubscribeEcho() {
    if (state.echoChannel && state.conversationId) {
      try {
        const E = getEcho();
        if (E) E.leave('conversation.' + state.conversationId);
      } catch (_) {}
      state.echoChannel = null;
    }
  }

  document.querySelectorAll('.conv-row').forEach((row) => {
    row.addEventListener('click', () => {
      const id = row.getAttribute('data-conv-id');
      if (id) openConversation(id);
    });
  });

  window.addEventListener('hashchange', () => {
    const m = (window.location.hash || '').match(/^#c(\d+)$/);
    if (m) openConversation(m[1]);
  });

  const hashMatch = (window.location.hash || '').match(/^#c(\d+)$/);
  if (hashMatch) openConversation(hashMatch[1]);
})();
