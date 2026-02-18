@extends('layouts.toyshop')

@section('title', 'Chat - ToyHaven Trading')

@push('styles')
<style>
    .chat-page { max-width: 900px; margin: 0 auto; }
    .chat-header { background: white; border-radius: 14px; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; }
    .chat-header-left { display: flex; align-items: center; gap: 0.75rem; }
    .chat-avatar { width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #0891b2, #0e7490); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; }
    .chat-status { font-size: 0.8rem; transition: color 0.3s ease; }
    .chat-status.online { color: #22c55e; }
    .chat-status.online .status-dot { animation: onlinePulse 2s ease-in-out infinite; }
    @keyframes onlinePulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }
    .chat-status.offline { color: #94a3b8; }
    .chat-body { background: white; border-radius: 14px; border: 1px solid #e2e8f0; min-height: 380px; max-height: 55vh; overflow-y: auto; padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem; scroll-behavior: smooth; }
    .msg-bubble { max-width: 75%; padding: 0.6rem 1rem; border-radius: 14px; position: relative; transition: transform 0.2s ease, opacity 0.2s ease; }
    .msg-bubble.mine { background: #0891b2; color: white; margin-left: auto; border-bottom-right-radius: 4px; }
    .msg-bubble.theirs { background: #f1f5f9; color: #0f172a; margin-right: auto; border-bottom-left-radius: 4px; }
    /* Message entrance animations */
    .msg-bubble.mine.msg-enter { animation: msgEnterMine 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
    .msg-bubble.theirs.msg-enter { animation: msgEnterTheirs 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
    @keyframes msgEnterMine {
        from { opacity: 0; transform: translateX(30px) scale(0.92); }
        100% { opacity: 1; transform: translateX(0) scale(1); }
    }
    @keyframes msgEnterTheirs {
        from { opacity: 0; transform: translateX(-30px) scale(0.92); }
        100% { opacity: 1; transform: translateX(0) scale(1); }
    }
    .msg-sender { font-size: 0.75rem; font-weight: 600; margin-bottom: 0.2rem; opacity: 0.9; }
    .msg-text { word-break: break-word; white-space: pre-wrap; }
    .msg-time { font-size: 0.7rem; margin-top: 0.35rem; opacity: 0.85; }
    .msg-status { font-size: 0.7rem; margin-left: 0.35rem; transition: opacity 0.25s ease, transform 0.3s ease, color 0.3s ease; display: inline-flex; align-items: center; }
    .msg-status.status-updated { animation: statusPulse 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
    .msg-status.msg-status-seen { color: #22c55e; }
    .msg-status.msg-status-seen .seen-check { animation: seenPop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
    .msg-status.msg-status-delivered { color: rgba(255, 255, 255, 0.85); animation: deliveredPulse 0.4s ease forwards; }
    .msg-status.msg-status-sent { color: rgba(255, 255, 255, 0.7); }
    @keyframes statusPulse {
        0% { opacity: 0.6; transform: scale(0.9); }
        50% { opacity: 1; transform: scale(1.12); }
        100% { opacity: 1; transform: scale(1); }
    }
    @keyframes deliveredPulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.06); }
        100% { transform: scale(1); }
    }
    @keyframes seenPop {
        0% { opacity: 0; transform: scale(0.6); }
        40% { opacity: 1; transform: scale(1.2); }
        100% { opacity: 1; transform: scale(1); }
    }
    .msg-attachments { margin-top: 0.5rem; display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .msg-attachments img { max-width: 200px; max-height: 180px; border-radius: 8px; object-fit: cover; }
    .msg-attachments video { max-width: 280px; max-height: 200px; border-radius: 8px; }
    .msg-attachments a { font-size: 0.8rem; color: inherit; text-decoration: underline; }
    /* Typing indicator with bouncing dots */
    .typing-indicator { font-size: 0.85rem; color: #64748b; padding: 0.35rem 0; display: flex; align-items: center; gap: 0.35rem; animation: typingFadeIn 0.25s ease; }
    @keyframes typingFadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
    .typing-dots { display: inline-flex; gap: 4px; align-items: center; }
    .typing-dots span { width: 6px; height: 6px; border-radius: 50%; background: #94a3b8; animation: typingBounce 1.4s ease-in-out infinite both; }
    .typing-dots span:nth-child(1) { animation-delay: 0s; }
    .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
    .typing-dots span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes typingBounce { 0%, 80%, 100% { transform: translateY(0); } 40% { transform: translateY(-6px); } }
    .chat-footer { background: white; border-radius: 14px; border: 1px solid #e2e8f0; padding: 0.75rem 1rem; margin-top: 0.5rem; }
    .chat-footer .form-control:focus { box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.2); transition: box-shadow 0.2s ease; }
    .chat-footer #sendBtn { transition: transform 0.15s ease, background-color 0.2s ease; }
    .chat-footer #sendBtn:hover { transform: scale(1.05); }
    .chat-footer #sendBtn:active { transform: scale(0.98); }
    .chat-footer #sendBtn.sending { pointer-events: none; opacity: 0.85; animation: sendPulse 0.6s ease infinite; }
    @keyframes sendPulse { 0%, 100% { opacity: 0.85; } 50% { opacity: 1; } }
    .listing-context { background: #f8fafc; border-radius: 12px; padding: 1rem; margin-bottom: 1rem; border: 1px solid #e2e8f0; transition: background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease; }
    .listing-context-link:hover { background: #f1f5f9; border-color: #0891b2; box-shadow: 0 2px 8px rgba(8, 145, 178, 0.15); }
    .listing-context img { width: 64px; height: 64px; object-fit: cover; border-radius: 8px; }
    .chat-body-product { position: sticky; top: 0; z-index: 2; background: white; padding: 0.6rem 0.8rem; margin: -1rem -1rem 0.75rem -1rem; border-bottom: 1px solid #e2e8f0; flex-shrink: 0; }
    .chat-body-product a { text-decoration: none; color: inherit; display: flex; align-items: center; gap: 0.6rem; padding: 0.4rem 0; border-radius: 10px; transition: background 0.2s, box-shadow 0.2s; }
    .chat-body-product a:hover { background: #f0f9ff; box-shadow: inset 0 0 0 1px rgba(8, 145, 178, 0.3); }
    .chat-body-product img { width: 48px; height: 48px; object-fit: cover; border-radius: 8px; flex-shrink: 0; }
    .chat-body-product .product-title { font-weight: 600; font-size: 0.9rem; color: #0f172a; }
    .chat-body-product .product-view { font-size: 0.75rem; color: #0891b2; margin-top: 0.15rem; }
    .chat-actions { display: flex; gap: 0.5rem; align-items: center; margin-top: 0.5rem; }
    .chat-actions .btn-report { font-size: 0.85rem; color: #64748b; }
</style>
@endpush

@section('content')
<div class="container my-4 chat-page">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trading.index') }}">Trading</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trading.conversations.index') }}">Messages</a></li>
            <li class="breadcrumb-item active">Chat</li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Listing context (product description + images) – clickable to view listing --}}
    @if($conversation->tradeListing)
        <a href="{{ route('trading.listings.show', $conversation->tradeListing->id) }}" class="listing-context listing-context-link text-decoration-none text-dark d-block">
            <div class="d-flex align-items-center gap-3">
                @if($conversation->tradeListing->image_path)
                    <img src="{{ asset('storage/' . $conversation->tradeListing->image_path) }}" alt="">
                @elseif($conversation->tradeListing->images->isNotEmpty())
                    <img src="{{ asset('storage/' . $conversation->tradeListing->images->first()->image_path) }}" alt="">
                @else
                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:64px;height:64px;"><i class="bi bi-image text-muted"></i></div>
                @endif
                <div class="flex-grow-1">
                    <strong>{{ $conversation->tradeListing->title }}</strong>
                    <p class="text-muted small mb-0 mt-1">{{ Str::limit($conversation->tradeListing->description, 120) }}</p>
                    @if($conversation->tradeListing->condition)
                        <span class="badge bg-secondary mt-1">{{ $conversation->tradeListing->condition }}</span>
                    @endif
                    <span class="d-block mt-1 small text-primary"><i class="bi bi-box-arrow-up-right me-1"></i>View listing</span>
                </div>
            </div>
        </a>
    @endif

    <div class="chat-header">
        <div class="chat-header-left">
            <a href="{{ route('trading.conversations.index') }}" class="text-dark"><i class="bi bi-arrow-left fs-5"></i></a>
            <div class="chat-avatar">{{ $other ? strtoupper(substr($other->name, 0, 1)) : '?' }}</div>
            <div>
                <div class="fw-bold">{{ $other?->name ?? 'User' }}</div>
                <div class="chat-status {{ $other && $other->isOnline() ? 'online' : 'offline' }}" id="otherUserStatus">
                    <i class="bi bi-circle-fill me-1 status-dot" style="font-size: 0.5rem;"></i>
                    <span id="otherUserStatusText">{{ $other && $other->isOnline() ? 'Online' : ($other && $other->last_seen_at ? 'Last seen ' . $other->last_seen_at->timezone(config('app.timezone'))->diffForHumans() : 'Offline') }}</span>
                </div>
            </div>
        </div>
        <div>
            @if($conversation->trade_id)
                <a href="{{ route('trading.trades.show', $conversation->trade->id) }}" class="btn btn-sm btn-outline-primary">Trade #{{ $conversation->trade_id }}</a>
            @endif
            <a href="{{ route('trading.conversations.report-form', $conversation) }}" class="btn btn-sm btn-outline-secondary ms-1" title="Report conversation">Report</a>
        </div>
    </div>

    <div class="chat-body" id="chatBody">
        @if($conversation->tradeListing)
            <div class="chat-body-product">
                <a href="{{ route('trading.listings.show', $conversation->tradeListing->id) }}" title="View listing">
                    @if($conversation->tradeListing->image_path)
                        <img src="{{ asset('storage/' . $conversation->tradeListing->image_path) }}" alt="">
                    @elseif($conversation->tradeListing->images->isNotEmpty())
                        <img src="{{ asset('storage/' . $conversation->tradeListing->images->first()->image_path) }}" alt="">
                    @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:48px;height:48px;"><i class="bi bi-image text-muted"></i></div>
                    @endif
                    <div>
                        <div class="product-title">{{ Str::limit($conversation->tradeListing->title, 40) }}</div>
                        <div class="product-view"><i class="bi bi-box-arrow-up-right me-1"></i>View product</div>
                    </div>
                </a>
            </div>
        @endif
        @foreach($messages as $msg)
            @include('trading.conversations.partials.message', ['msg' => $msg])
        @endforeach
        <div id="typingIndicator" class="typing-indicator" style="display:none;">
            <span class="typing-dots"><span></span><span></span><span></span></span>
            <span id="typingUserName"></span> is typing...
        </div>
    </div>

    <div class="chat-footer">
        <form id="messageForm" class="d-flex flex-column gap-2">
            @csrf
            <div class="d-flex gap-2 align-items-end">
                <input type="text" name="message" id="messageInput" class="form-control rounded-3" placeholder="Type a message..." maxlength="5000" autocomplete="off">
                <label class="btn btn-outline-secondary btn-sm mb-0 rounded-3" title="Image or video">
                    <i class="bi bi-image"></i>
                    <input type="file" id="attachmentInput" accept="image/*,video/*" multiple class="d-none">
                </label>
                <button type="submit" class="btn btn-primary rounded-3" id="sendBtn">
                    <i class="bi bi-send"></i>
                </button>
            </div>
            <div id="attachmentPreview" class="d-flex flex-wrap gap-2"></div>
        </form>
    </div>
</div>

<script>
window.CONVERSATION_ID = {{ $conversation->id }};
window.AUTH_ID = {{ Auth::id() }};
window.OTHER_USER = @json($other ? ['id' => $other->id, 'name' => $other->name] : null);
@if(config('broadcasting.default') !== 'null')
@php
    $driver = config('broadcasting.default');
    $conn = config('broadcasting.connections.' . $driver);
    $echoConfig = [
        'key' => $conn['key'] ?? $conn['options']['key'] ?? null,
        'cluster' => $conn['options']['cluster'] ?? 'mt1',
        'wsHost' => $conn['options']['host'] ?? null,
        'wsPort' => $conn['options']['port'] ?? null,
        'wssPort' => $conn['options']['port'] ?? null,
        'scheme' => $conn['options']['scheme'] ?? 'https',
        'authEndpoint' => url('/broadcasting/auth'),
        'typingUrl' => route('trading.conversations.typing', $conversation),
        'presenceUrl' => route('trading.conversations.presence', $conversation),
    ];
@endphp
window.ECHO_CONFIG = @json($echoConfig);
@endif
</script>
@vite(['resources/js/app.js'])
@if(config('broadcasting.default') !== 'null')
    @vite(['resources/js/echo-conversation.js'])
@endif
<script>
(function() {
    var chatBody = document.getElementById('chatBody');
    var form = document.getElementById('messageForm');
    var input = document.getElementById('messageInput');
    var attachmentInput = document.getElementById('attachmentInput');
    var attachmentPreview = document.getElementById('attachmentPreview');
    var sendBtn = document.getElementById('sendBtn');

    function scrollToBottom() {
        chatBody.scrollTop = chatBody.scrollHeight;
    }
    function animateNewBubble(bubble) {
        bubble.classList.add('msg-enter');
        bubble.addEventListener('animationend', function onEnd() {
            bubble.classList.remove('msg-enter');
            bubble.removeEventListener('animationend', onEnd);
        }, { once: true });
    }

    var typingTimeout;
    var isTyping = false;
    input.addEventListener('input', function() {
        if (!isTyping) {
            window.dispatchEvent(new CustomEvent('conversation-typing', { detail: { typing: true } }));
            isTyping = true;
        }
        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(function() {
            window.dispatchEvent(new CustomEvent('conversation-typing', { detail: { typing: false } }));
            isTyping = false;
        }, 1500);
    });

    attachmentInput.addEventListener('change', function() {
        selectedFiles = Array.from(this.files);
        attachmentPreview.innerHTML = '';
        selectedFiles.forEach(function(file) {
            var div = document.createElement('div');
            div.className = 'd-flex align-items-center gap-1 bg-light rounded px-2 py-1 small';
            div.innerHTML = '<span class="text-truncate" style="max-width:120px">' + escapeHtml(file.name) + '</span> <button type="button" class="btn btn-link btn-sm p-0 text-danger remove-attach" data-name="' + escapeHtml(file.name) + '">&times;</button>';
            attachmentPreview.appendChild(div);
        });
    });

    var selectedFiles = [];
    attachmentPreview.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-attach')) {
            var fileName = e.target.dataset.name;
            selectedFiles = selectedFiles.filter(function(f) { return f.name !== fileName; });
            e.target.closest('.d-flex').remove();
        }
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var msg = input.value.trim();
        var files = selectedFiles.length > 0 ? selectedFiles : Array.from(attachmentInput.files || []);
        if (!msg && files.length === 0) return;

        sendBtn.disabled = true;
        sendBtn.classList.add('sending');
        var fd = new FormData();
        fd.append('_token', document.querySelector('meta[name="csrf-token"]').content || document.querySelector('input[name="_token"]').value);
        fd.append('message', msg);
        files.forEach(function(file) { fd.append('attachments[]', file); });

        fetch('{{ route("trading.conversations.messages.store", $conversation) }}', {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.message) {
                var bubble = document.createElement('div');
                bubble.className = 'msg-bubble mine';
                bubble.dataset.messageId = data.message.id;
                var html = '';
                if (data.message.message) {
                    html += '<div class="msg-text">' + escapeHtml(data.message.message) + '</div>';
                }
                if (data.message.attachments && data.message.attachments.length) {
                    html += '<div class="msg-attachments">';
                    data.message.attachments.forEach(function(a) {
                        if (a.is_image) html += '<img src="' + escapeHtml(a.url) + '" alt="">';
                        else if (a.is_video) html += '<video src="' + escapeHtml(a.url) + '" controls></video>';
                        else html += '<a href="' + escapeHtml(a.url) + '" target="_blank">' + escapeHtml(a.file_name || 'File') + '</a>';
                    });
                    html += '</div>';
                }
                var timeStr = formatMessageTime(data.message.created_at) || data.message.formatted_created_at || 'Just now';
                html += '<div class="msg-time">' + escapeHtml(timeStr) + ' <span class="msg-status msg-status-sent">Sent</span></div>';
                bubble.innerHTML = html;
                chatBody.appendChild(bubble);
                animateNewBubble(bubble);
                requestAnimationFrame(function() { requestAnimationFrame(scrollToBottom); });
            }
            input.value = '';
            attachmentInput.value = '';
            attachmentPreview.innerHTML = '';
            selectedFiles = [];
        })
        .catch(function(err) { 
            console.error('Failed to send message:', err);
            alert('Failed to send message. Please try again.'); 
        })
        .finally(function() { sendBtn.disabled = false; sendBtn.classList.remove('sending'); });
    });

    function escapeHtml(s) {
        if (!s) return '';
        var div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }
    function formatMessageTime(isoOrFormatted) {
        if (!isoOrFormatted) return 'Just now';
        var d = new Date(isoOrFormatted);
        if (isNaN(d.getTime())) return isoOrFormatted;
        var now = new Date();
        var todayStart = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        var yesterdayStart = new Date(todayStart);
        yesterdayStart.setDate(yesterdayStart.getDate() - 1);
        var msgDateStart = new Date(d.getFullYear(), d.getMonth(), d.getDate());
        var timeStr = d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
        if (msgDateStart.getTime() === todayStart.getTime()) return 'Today, ' + timeStr;
        if (msgDateStart.getTime() === yesterdayStart.getTime()) return 'Yesterday, ' + timeStr;
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ', ' + timeStr;
    }

    window.conversationScrollToBottom = scrollToBottom;
    window.conversationAppendMessage = function(payload) {
        if (payload.sender_id === window.AUTH_ID) return;
        var bubble = document.createElement('div');
        bubble.className = 'msg-bubble theirs';
        bubble.dataset.messageId = payload.id;
        var html = '<div class="msg-sender">' + escapeHtml(payload.sender_name || '') + '</div>';
        if (payload.message) {
            html += '<div class="msg-text">' + escapeHtml(payload.message) + '</div>';
        }
        if (payload.attachments && payload.attachments.length) {
            html += '<div class="msg-attachments">';
            payload.attachments.forEach(function(a) {
                if (a.is_image) html += '<img src="' + escapeHtml(a.url) + '" alt="">';
                else if (a.is_video) html += '<video src="' + escapeHtml(a.url) + '" controls></video>';
                else html += '<a href="' + escapeHtml(a.url) + '" target="_blank">' + escapeHtml(a.file_name || 'File') + '</a>';
            });
            html += '</div>';
        }
        var timeStr = formatMessageTime(payload.created_at) || payload.formatted_created_at || payload.created_at_formatted || 'Just now';
        html += '<div class="msg-time">' + escapeHtml(timeStr) + '</div>';
        bubble.innerHTML = html;
        
        // Hide typing indicator when message arrives
        var typingInd = document.getElementById('typingIndicator');
        if (typingInd) typingInd.style.display = 'none';
        
        chatBody.appendChild(bubble);
        animateNewBubble(bubble);
        requestAnimationFrame(function() { requestAnimationFrame(scrollToBottom); });
        
        // Mark as delivered and seen
        var token = document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').getAttribute('content') || (document.querySelector('input[name="_token"]') && document.querySelector('input[name="_token"]').value);
        fetch('{{ route("trading.conversations.mark-delivered", $conversation) }}', { 
            method: 'POST', 
            headers: { 
                'Content-Type': 'application/json', 
                'Accept': 'application/json', 
                'X-CSRF-TOKEN': token || '', 
                'X-Requested-With': 'XMLHttpRequest' 
            }, 
            body: JSON.stringify({ message_ids: [payload.id] }) 
        }).catch(function(err){ console.error('Mark delivered error:', err); });
        
        fetch('{{ route("trading.conversations.mark-seen", $conversation) }}', { 
            method: 'POST', 
            headers: { 
                'Accept': 'application/json', 
                'X-CSRF-TOKEN': token || '', 
                'X-Requested-With': 'XMLHttpRequest' 
            } 
        }).catch(function(err){ console.error('Mark seen error:', err); });
    };
    window.conversationUpdateStatus = function(messageId, status) {
        var bubble = chatBody.querySelector('[data-message-id="' + messageId + '"]');
        if (!bubble) return;
        var el = bubble.querySelector('.msg-status');
        if (!el) {
            // Create status element if it doesn't exist
            var msgTime = bubble.querySelector('.msg-time');
            if (msgTime) {
                var newStatus = document.createElement('span');
                newStatus.className = 'msg-status';
                msgTime.appendChild(document.createTextNode(' '));
                msgTime.appendChild(newStatus);
                el = newStatus;
            }
        }
        if (!el) return;
        
        var label = status === 'seen' ? 'Seen' : (status === 'delivered' ? 'Delivered' : 'Sent');
        var icon = status === 'seen' ? '<i class="bi bi-check2-all me-1 seen-check" aria-hidden="true"></i>' : (status === 'delivered' ? '<i class="bi bi-check2 me-1" aria-hidden="true"></i>' : '');
        el.innerHTML = icon + escapeHtml(label);
        el.className = 'msg-status msg-status-' + status;
        
        // Trigger animation
        el.offsetHeight; // Force reflow
        el.classList.add('status-updated');
        setTimeout(function() { el.classList.remove('status-updated'); }, 600);
    };
    var typingTimeout;
    window.conversationShowTyping = function(name, typing) {
        var ind = document.getElementById('typingIndicator');
        var nameEl = document.getElementById('typingUserName');
        
        if (typing) {
            nameEl.textContent = name || '';
            ind.style.display = 'flex';
            requestAnimationFrame(scrollToBottom);
            
            // Auto-hide typing indicator after 3 seconds if no update
            clearTimeout(typingTimeout);
            typingTimeout = setTimeout(function() {
                ind.style.display = 'none';
            }, 3000);
        } else {
            ind.style.display = 'none';
            clearTimeout(typingTimeout);
        }
    };
    window.conversationUpdatePresence = function(data) {
        var st = document.getElementById('otherUserStatus');
        var stText = document.getElementById('otherUserStatusText');
        if (!st) return;
        var relative = (data.last_seen_relative != null) ? data.last_seen_relative : (data.last_seen_at ? data.last_seen_at : '');
        if (data.is_online) {
            st.className = 'chat-status online';
            st.innerHTML = '<i class="bi bi-circle-fill me-1 status-dot" style="font-size:0.5rem;"></i><span id="otherUserStatusText">Online</span>';
        } else {
            st.className = 'chat-status offline';
            var lastSeenText = relative ? (relative.includes('Last seen') ? relative : 'Last seen ' + relative) : 'Offline';
            st.innerHTML = '<i class="bi bi-circle me-1" style="font-size:0.5rem;"></i><span id="otherUserStatusText">' + escapeHtml(lastSeenText) + '</span>';
        }
    };
    var otherStatusUrl = '{{ route("trading.conversations.other-status", $conversation) }}';
    var presenceUrl = '{{ route("trading.conversations.presence", $conversation) }}';
    function refreshPresence() {
        if (document.hidden) return;
        fetch(otherStatusUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { 
                if (!r.ok) throw new Error('Failed to fetch presence');
                return r.json(); 
            })
            .then(function(data) {
                window.conversationUpdatePresence({ 
                    is_online: data.is_online, 
                    last_seen_relative: data.last_seen_relative, 
                    last_seen_at: data.last_seen_at 
                });
            })
            .catch(function(err) { 
                console.error('Presence fetch error:', err);
            });
    }
    function updateMyPresence() {
        if (document.hidden) return;
        var csrf = document.querySelector('meta[name="csrf-token"]');
        fetch(presenceUrl, { 
            method: 'POST', 
            headers: { 
                'Accept': 'application/json', 
                'X-CSRF-TOKEN': (csrf && csrf.getAttribute('content')) || '', 
                'X-Requested-With': 'XMLHttpRequest' 
            } 
        }).catch(function(err){ 
            console.error('Presence update error:', err);
        });
    }
    // Initial presence check
    refreshPresence();
    updateMyPresence();
    
    // More frequent updates for better real-time feel (every 5 seconds for other user status)
    var presenceRefreshInterval = setInterval(refreshPresence, 5000);
    var myPresenceInterval = setInterval(updateMyPresence, 10000);
    
    // Update presence when user interacts
    chatBody.addEventListener('scroll', function() { updateMyPresence(); }, { passive: true });
    input.addEventListener('focus', function() { updateMyPresence(); });
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        clearInterval(presenceRefreshInterval);
        clearInterval(myPresenceInterval);
    });
    
    // Initial scroll to bottom after page load
    setTimeout(scrollToBottom, 100);
})();
</script>
@endsection
