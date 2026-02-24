/**
 * Single-page chat: no refresh when switching conversations.
 * Real-time via polling (and Echo if configured).
 */
(function () {
    const AUTH_ID = window.AUTH_ID;
    const APP_TIMEZONE = window.APP_TIMEZONE || 'Asia/Manila';
    const BASE_URL = window.BASE_URL || '';

    function csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
            document.querySelector('input[name="_token"]')?.value || '';
    }

    function escapeHtml(s) {
        if (!s) return '';
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
        if (!listing?.url) return '';
        const img = listing.image_url
            ? `<img src="${escapeHtml(listing.image_url)}" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:8px;">`
            : '<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:48px;height:48px;"><i class="bi bi-image text-muted"></i></div>';
        return `<a href="${escapeHtml(listing.url)}" class="msg-offered-product d-block text-decoration-none text-dark">
            <div class="d-flex align-items-center gap-2">${img}
            <div class="flex-grow-1 min-w-0"><div class="fw-semibold small text-truncate">${escapeHtml(listing.title || '')}</div>
            ${listing.condition ? `<span class="badge bg-secondary" style="font-size:0.65rem;">${escapeHtml(listing.condition)}</span>` : ''}
            <div class="text-primary small mt-0"><i class="bi bi-box-arrow-up-right me-1"></i>View listing</div></div></div></a>`;
    }

    let state = {
        conversationId: null,
        lastMessageId: null,
        pollTimer: null,
        typingTimeout: null,
    };

    const convList = document.getElementById('convList');
    const chatPanel = document.getElementById('chatPanel');
    const chatPanelEmpty = document.getElementById('chatPanelEmpty');
    const chatPanelContent = document.getElementById('chatPanelContent');
    const chatBody = document.getElementById('chatBody');
    const chatHeaderAvatar = document.getElementById('chatHeaderAvatar');
    const chatHeaderName = document.getElementById('chatHeaderName');
    const chatHeaderStatus = document.getElementById('chatHeaderStatus');
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const attachmentInput = document.getElementById('attachmentInput');
    const attachmentPreview = document.getElementById('attachmentPreview');
    const sendBtn = document.getElementById('sendBtn');
    const offerProductSelect = document.getElementById('offerProductSelect');
    const offerListingWrap = document.getElementById('offerListingWrap');
    const typingIndicator = document.getElementById('typingIndicator');
    const typingUserName = document.getElementById('typingUserName');

    if (!convList || !chatPanel) return;

    function scrollToBottom() {
        if (chatBody) chatBody.scrollTop = chatBody.scrollHeight;
    }

    function animateBubble(el) {
        el.style.animation = 'none';
        el.offsetHeight;
        el.style.animation = 'msgIn 0.25s ease';
    }

    function renderMessage(msg) {
        const isMine = msg.sender_id === AUTH_ID;
        const isUnsent = !!msg.is_unsent;
        const status = msg.status || (msg.seen_at ? 'Seen' : (msg.delivered_at ? 'Delivered' : 'Sent'));
        let html = '';
        if (!isMine) html += `<div class="msg-sender">${escapeHtml(msg.sender_name || '')}</div>`;
        if (isUnsent) {
            html += `<div class="msg-unsent-text"><i class="bi bi-x-circle me-1"></i> ${isMine ? 'You removed this message' : 'This message was removed'}</div>`;
        } else {
            if (msg.offered_listing) html += renderOfferedListing(msg.offered_listing);
            if (msg.message) html += `<div class="msg-text">${escapeHtml(msg.message)}</div>`;
            if (msg.attachments?.length) {
                html += '<div class="msg-attachments">';
                msg.attachments.forEach(a => {
                    if (a.is_image) html += `<img src="${escapeHtml(a.url)}" alt="">`;
                    else if (a.is_video) html += `<video src="${escapeHtml(a.url)}" controls></video>`;
                    else html += `<a href="${escapeHtml(a.url)}" target="_blank">${escapeHtml(a.file_name || 'File')}</a>`;
                });
                html += '</div>';
            }
        }
        const timeStr = msg.formatted_created_at || formatTime(msg.created_at) || 'Just now';
        html += '<div class="msg-time">' + escapeHtml(timeStr);
        if (isMine && !isUnsent) {
            html += ` <button type="button" class="msg-unsend-btn btn btn-link p-0 border-0 text-danger" data-msg-id="${msg.id}" title="Remove"><i class="bi bi-trash3" style="font-size:0.7rem;"></i></button>`;
            html += ` <span class="msg-status">${escapeHtml(status)}</span>`;
        }
        html += '</div>';

        const bubble = document.createElement('div');
        bubble.className = `msg-bubble ${isMine ? 'mine' : 'theirs'} ${isUnsent ? 'msg-unsent' : ''}`;
        bubble.dataset.messageId = msg.id;
        bubble.innerHTML = html;
        return bubble;
    }

    function stopPolling() {
        if (state.pollTimer) {
            clearInterval(state.pollTimer);
            state.pollTimer = null;
        }
    }

    function startPolling() {
        stopPolling();
        if (!state.conversationId) return;
        state.pollTimer = setInterval(() => {
            if (document.hidden || !state.lastMessageId) return;
            fetch(`${BASE_URL}/trading/conversations/${state.conversationId}/messages?after_id=${state.lastMessageId}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(r => r.json())
                .then(data => {
                    const messages = data.messages || [];
                    messages.forEach(msg => {
                        if (msg.sender_id !== AUTH_ID) {
                            const el = renderMessage(msg);
                            chatBody.appendChild(el);
                            animateBubble(el);
                            state.lastMessageId = Math.max(state.lastMessageId || 0, msg.id);
                            markSeen();
                        }
                    });
                    if (messages.length) scrollToBottom();
                })
                .catch(() => {});
        }, 2500);
    }

    function markSeen() {
        if (!state.conversationId) return;
        fetch(`${BASE_URL}/trading/conversations/${state.conversationId}/seen`, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
        }).catch(() => {});
    }

    function loadConversation(convId) {
        const item = convList.querySelector(`.conv-item[data-conv-id="${convId}"]`);
        if (item) {
            convList.querySelectorAll('.conv-item').forEach(el => el.classList.remove('active'));
            item.classList.add('active');
        }

        state.conversationId = convId;
        state.lastMessageId = null;
        chatPanelEmpty.style.display = 'none';
        chatPanelContent.style.display = 'flex';
        chatBody.innerHTML = '';
        if (typingIndicator) typingIndicator.style.display = 'none';

        fetch(`${BASE_URL}/trading/conversations/${convId}/payload`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(r => r.json())
            .then(data => {
                const other = data.other || {};
                chatHeaderAvatar.textContent = (other.name || '?').charAt(0).toUpperCase();
                chatHeaderName.textContent = other.name || 'User';
                chatHeaderStatus.textContent = other.is_online ? 'Online' : (other.last_seen_relative ? `Last seen ${other.last_seen_relative}` : 'Offline');
                chatHeaderStatus.classList.toggle('offline', !other.is_online);

                const reportBtn = document.getElementById('chatReportBtn');
                if (reportBtn) {
                    reportBtn.href = `${BASE_URL}/trading/conversations/${state.conversationId}/report`;
                    reportBtn.classList.remove('d-none');
                }

                offerProductSelect.innerHTML = '<option value="">— Select listing to offer —</option>';
                (data.my_listings || []).forEach(l => {
                    offerProductSelect.innerHTML += `<option value="${l.id}">${escapeHtml(l.title)}</option>`;
                });
                offerListingWrap.style.display = (data.my_listings?.length > 0) ? 'flex' : 'none';

                (data.messages || []).forEach(msg => {
                    const el = renderMessage(msg);
                    chatBody.appendChild(el);
                    state.lastMessageId = Math.max(state.lastMessageId || 0, msg.id);
                });
                scrollToBottom();
                startPolling();

                if (window.chatEchoSubscribe) window.chatEchoSubscribe(convId);
            })
            .catch(err => {
                console.error(err);
                chatPanelEmpty.style.display = 'flex';
                chatPanelContent.style.display = 'none';
            });
    }

    convList.addEventListener('click', (e) => {
        const item = e.target.closest('.conv-item[data-conv-id]');
        if (!item) return;
        e.preventDefault();
        const id = parseInt(item.dataset.convId, 10);
        if (id) {
            stopPolling();
            if (window.chatEchoUnsubscribe) window.chatEchoUnsubscribe();
            history.replaceState(null, '', `${window.CONVERSATIONS_INDEX_URL}?open=${id}`);
            loadConversation(id);
        }
    });

    let selectedFiles = [];
    attachmentInput?.addEventListener('change', () => {
        selectedFiles = Array.from(attachmentInput.files || []);
        attachmentPreview.innerHTML = '';
        selectedFiles.forEach(f => {
            const div = document.createElement('div');
            div.className = 'd-flex align-items-center gap-1 bg-light rounded px-2 py-1 small';
            div.innerHTML = `<span class="text-truncate" style="max-width:100px">${escapeHtml(f.name)}</span> <button type="button" class="btn btn-link btn-sm p-0 text-danger remove-attach" data-name="${escapeHtml(f.name)}">&times;</button>`;
            attachmentPreview.appendChild(div);
        });
    });
    attachmentPreview?.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-attach')) {
            const name = e.target.dataset.name;
            selectedFiles = selectedFiles.filter(f => f.name !== name);
            e.target.closest('.d-flex').remove();
        }
    });

    messageForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        if (!state.conversationId) return;
        const msg = messageInput.value.trim();
        const files = selectedFiles.length ? selectedFiles : Array.from(attachmentInput?.files || []);
        const offeredId = offerProductSelect?.value ? parseInt(offerProductSelect.value, 10) : 0;
        if (!msg && files.length === 0 && !offeredId) return;

        sendBtn.disabled = true;
        const fd = new FormData();
        fd.append('_token', csrf());
        fd.append('message', msg);
        if (offeredId) fd.append('offered_listing_id', offeredId);
        files.forEach(f => fd.append('attachments[]', f));

        fetch(`${BASE_URL}/trading/conversations/${state.conversationId}/messages`, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        })
            .then(r => r.json())
            .then(data => {
                if (data.message) {
                    const bubble = renderMessage({ ...data.message, sender_id: AUTH_ID });
                    chatBody.appendChild(bubble);
                    animateBubble(bubble);
                    state.lastMessageId = data.message.id;
                    scrollToBottom();
                }
                messageInput.value = '';
                attachmentInput.value = '';
                attachmentPreview.innerHTML = '';
                selectedFiles = [];
                if (offerProductSelect) offerProductSelect.value = '';
            })
            .catch(() => alert('Failed to send. Try again.'))
            .finally(() => { sendBtn.disabled = false; });
    });

    messageInput?.addEventListener('input', () => {
        if (!state.conversationId) return;
        clearTimeout(state.typingTimeout);
        fetch(`${BASE_URL}/trading/conversations/${state.conversationId}/typing`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ typing: true }),
        }).catch(() => {});
        state.typingTimeout = setTimeout(() => {
            fetch(`${BASE_URL}/trading/conversations/${state.conversationId}/typing`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ typing: false }),
            }).catch(() => {});
        }, 1500);
    });

    chatBody?.addEventListener('click', (e) => {
        const btn = e.target.closest('.msg-unsend-btn');
        if (!btn) return;
        e.preventDefault();
        const msgId = btn.dataset.msgId;
        const bubble = chatBody.querySelector(`[data-message-id="${msgId}"]`);
        if (!bubble || bubble.dataset.unsent === '1') return;
        window.pendingUnsendId = msgId;
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('unsendModal'));
        modal.show();
    });

    document.getElementById('unsendConfirmBtn')?.addEventListener('click', () => {
        const msgId = window.pendingUnsendId;
        if (!msgId || !state.conversationId) return;
        window.pendingUnsendId = null;
        bootstrap.Modal.getInstance(document.getElementById('unsendModal'))?.hide();
        fetch(`${BASE_URL}/trading/conversations/${state.conversationId}/messages/${msgId}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(r => {
                if (r.ok) {
                    const bubble = chatBody.querySelector(`[data-message-id="${msgId}"]`);
                    if (bubble) {
                        bubble.classList.add('msg-unsent');
                        bubble.querySelector('.msg-text')?.remove();
                        bubble.querySelector('.msg-attachments')?.remove();
                        bubble.querySelector('.msg-offered-product')?.remove();
                        const timeDiv = bubble.querySelector('.msg-time');
                        if (timeDiv) {
                            timeDiv.querySelector('.msg-unsend-btn')?.remove();
                            timeDiv.querySelector('.msg-status')?.remove();
                            const unsent = document.createElement('div');
                            unsent.className = 'msg-unsent-text';
                            unsent.innerHTML = '<i class="bi bi-x-circle me-1"></i> You removed this message';
                            bubble.insertBefore(unsent, timeDiv);
                        }
                    }
                }
            })
            .catch(() => {});
    });

    window.conversationAppendMessage = function (payload) {
        if (payload.sender_id === AUTH_ID) return;
        if (parseInt(payload.conversation_id, 10) !== state.conversationId) return;
        const bubble = renderMessage(payload);
        chatBody.appendChild(bubble);
        animateBubble(bubble);
        state.lastMessageId = Math.max(state.lastMessageId || 0, payload.id);
        scrollToBottom();
        if (typingIndicator) typingIndicator.style.display = 'none';
        markSeen();
    };
    window.conversationShowTyping = function (name, typing) {
        if (!typingIndicator) return;
        if (typing) {
            typingUserName.textContent = name || '';
            typingIndicator.style.display = 'flex';
            scrollToBottom();
        } else {
            typingIndicator.style.display = 'none';
        }
    };
    window.conversationHandleUnsent = function (messageId) {
        const bubble = chatBody?.querySelector(`[data-message-id="${messageId}"]`);
        if (!bubble) return;
        bubble.classList.add('msg-unsent');
        bubble.querySelector('.msg-text')?.remove();
        bubble.querySelector('.msg-attachments')?.remove();
        bubble.querySelector('.msg-offered-product')?.remove();
        const timeDiv = bubble.querySelector('.msg-time');
        if (timeDiv) {
            timeDiv.querySelector('.msg-unsend-btn')?.remove();
            timeDiv.querySelector('.msg-status')?.remove();
            const unsent = document.createElement('div');
            unsent.className = 'msg-unsent-text';
            unsent.innerHTML = '<i class="bi bi-x-circle me-1"></i> This message was removed';
            bubble.insertBefore(unsent, timeDiv);
        }
    };

    const openId = new URLSearchParams(window.location.search).get('open');
    if (openId) {
        const id = parseInt(openId, 10);
        if (id) loadConversation(id);
    }

    window.addEventListener('beforeunload', () => stopPolling());
})();
