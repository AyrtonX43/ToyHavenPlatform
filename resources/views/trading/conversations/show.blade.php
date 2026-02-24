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
    .chat-body { background: white; border-radius: 14px; border: 1px solid #e2e8f0; min-height: 400px; max-height: 60vh; overflow-y: auto; padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem; scroll-behavior: smooth; }
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
    /* Typing indicator with bouncing dots and reply animation */
    .typing-indicator { font-size: 0.85rem; color: #64748b; padding: 0.35rem 0; display: flex; align-items: center; gap: 0.35rem; animation: typingFadeIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
    @keyframes typingFadeIn { from { opacity: 0; transform: translateY(8px) scale(0.96); } to { opacity: 1; transform: translateY(0) scale(1); } }
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
    .msg-offered-product { transition: background 0.2s, box-shadow 0.2s; }
    .msg-offered-product:hover { background: rgba(0,0,0,0.08) !important; }
    .offer-product-btn { font-size: 0.85rem; }
    .msg-unsent { background: rgba(0,0,0,0.06) !important; color: #64748b; }
    .msg-unsent.mine { background: rgba(8, 145, 178, 0.15) !important; color: #64748b; }
    .msg-unsent-text { font-size: 0.85rem; font-style: italic; }
    .msg-unsend-btn { opacity: 0.6; text-decoration: none !important; }
    .msg-unsend-btn:hover { opacity: 1; }
    .msg-bubble:hover .msg-unsend-btn { opacity: 0.8; }
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
            @if($myListings->isNotEmpty())
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="small text-muted">Offer your product:</span>
                    <select id="offerProductSelect" class="form-select form-select-sm" style="max-width:220px;">
                        <option value="">-- Select listing to offer --</option>
                        @foreach($myListings as $listing)
                            <option value="{{ $listing->id }}">{{ Str::limit($listing->title, 35) }}</option>
                        @endforeach
                    </select>
                    <span id="selectedOfferPreview" class="small text-success" style="display:none;"></span>
                </div>
            @endif
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

    <!-- Unsend confirmation modal -->
    <div class="modal fade" id="unsendModal" tabindex="-1" aria-labelledby="unsendModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="unsendModalLabel"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Remove message?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to remove this message? This action cannot be undone. The message will be replaced with &quot;You removed this message&quot; for you and the other person.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="unsendConfirmBtn">
                        <i class="bi bi-trash3 me-1"></i> Remove message
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.CONVERSATION_ID = {{ $conversation->id }};
window.AUTH_ID = {{ Auth::id() }};
window.OTHER_USER = @json($other ? ['id' => $other->id, 'name' => $other->name] : null);
window.APP_TIMEZONE = @json(config('app.timezone', 'Asia/Manila'));
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
        var offerSelect = document.getElementById('offerProductSelect');
        var offeredId = (offerSelect && offerSelect.value) ? parseInt(offerSelect.value, 10) : 0;
        if (!msg && files.length === 0 && !offeredId) return;

        sendBtn.disabled = true;
        sendBtn.classList.add('sending');
        var fd = new FormData();
        fd.append('_token', document.querySelector('meta[name="csrf-token"]').content || document.querySelector('input[name="_token"]').value);
        fd.append('message', msg);
        if (offeredId) fd.append('offered_listing_id', offeredId);
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
                if (data.message.offered_listing) {
                    html += renderOfferedListing(data.message.offered_listing);
                }
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
                html += '<div class="msg-time d-flex align-items-center gap-1 flex-wrap">' + escapeHtml(timeStr);
                html += ' <button type="button" class="msg-unsend-btn btn btn-link btn-sm p-0 ms-1" data-message-id="' + data.message.id + '" title="Remove message" aria-label="Remove message"><i class="bi bi-trash3 text-danger" style="font-size: 0.75rem;"></i></button>';
                html += ' <span class="msg-status msg-status-sent">Sent</span></div>';
                bubble.innerHTML = html;
                chatBody.appendChild(bubble);
                animateNewBubble(bubble);
                requestAnimationFrame(function() { requestAnimationFrame(scrollToBottom); });
            }
            input.value = '';
            attachmentInput.value = '';
            attachmentPreview.innerHTML = '';
            selectedFiles = [];
            if (offerSelect) { offerSelect.value = ''; }
        })
        .catch(function(err) { 
            console.error('Failed to send message:', err);
            alert('Failed to send message. Please try again.'); 
        })
        .finally(function() { sendBtn.disabled = false; sendBtn.classList.remove('sending'); });
    });

    function renderOfferedListing(listing) {
        if (!listing || !listing.url) return '';
        var img = listing.image_url ? '<img src="' + escapeHtml(listing.image_url) + '" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:8px;">' : '<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:48px;height:48px;"><i class="bi bi-image text-muted"></i></div>';
        return '<a href="' + escapeHtml(listing.url) + '" class="msg-offered-product d-block text-decoration-none text-dark rounded p-2 mb-2" style="background:rgba(0,0,0,0.06);"><div class="d-flex align-items-center gap-2">' + img + '<div class="flex-grow-1 min-w-0"><div class="fw-semibold small text-truncate">' + escapeHtml(listing.title || '') + '</div>' + (listing.condition ? '<span class="badge bg-secondary" style="font-size:0.65rem;">' + escapeHtml(listing.condition) + '</span>' : '') + '<div class="text-primary small mt-0"><i class="bi bi-box-arrow-up-right me-1"></i>View listing</div></div></div></a>';
    }
    function escapeHtml(s) {
        if (!s) return '';
        var div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }
    function formatMessageTime(isoOrFormatted) {
        if (!isoOrFormatted) return 'Just now';
        var tz = window.APP_TIMEZONE || 'Asia/Manila';
        var d = new Date(isoOrFormatted);
        if (isNaN(d.getTime())) return isoOrFormatted;
        var opts = { timeZone: tz, hour: 'numeric', minute: '2-digit', hour12: true };
        var dateOpts = { timeZone: tz, month: 'short', day: 'numeric' };
        var now = new Date();
        var todayStart = new Date(now.toLocaleString('en-US', { timeZone: tz }));
        todayStart.setHours(0, 0, 0, 0);
        var yesterdayStart = new Date(todayStart);
        yesterdayStart.setDate(yesterdayStart.getDate() - 1);
        var msgDateStart = new Date(d.toLocaleString('en-US', { timeZone: tz }));
        msgDateStart.setHours(0, 0, 0, 0);
        var timeStr = d.toLocaleTimeString('en-PH', opts);
        if (msgDateStart.getTime() === todayStart.getTime()) return 'Today, ' + timeStr;
        if (msgDateStart.getTime() === yesterdayStart.getTime()) return 'Yesterday, ' + timeStr;
        return d.toLocaleDateString('en-PH', dateOpts) + ', ' + timeStr;
    }

    window.conversationScrollToBottom = scrollToBottom;
    window.conversationAppendMessage = function(payload) {
        if (payload.sender_id === window.AUTH_ID) return;
        var bubble = document.createElement('div');
        bubble.className = 'msg-bubble theirs';
        bubble.dataset.messageId = payload.id;
        var html = '<div class="msg-sender">' + escapeHtml(payload.sender_name || '') + '</div>';
        if (payload.offered_listing) {
            html += renderOfferedListing(payload.offered_listing);
        }
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
    
    // Real-time presence: poll every 3s when WebSockets may be unavailable; Echo updates instantly when available
    var presenceRefreshInterval = setInterval(refreshPresence, 3000);
    var myPresenceInterval = setInterval(updateMyPresence, 8000);
    
    // Poll for new messages every 4s when Echo may not be connected (fallback for real-time)
    var lastMessageId = 0;
    chatBody.querySelectorAll('[data-message-id]').forEach(function(el) {
        var id = parseInt(el.getAttribute('data-message-id'), 10);
        if (id > lastMessageId) lastMessageId = id;
    });
    function normalizePolledMessage(m) {
        var atts = (m.attachments || []).map(function(a) {
            return { url: a.url || (a.file_path ? ('/storage/' + a.file_path) : ''), is_image: a.is_image || (a.file_type && a.file_type.indexOf('image/') === 0), is_video: a.is_video || (a.file_type && a.file_type.indexOf('video/') === 0), file_name: a.file_name };
        });
        var listing = m.trade_listing || m.tradeListing;
        var offered = null;
        if (listing) {
            var img = listing.image_path || (listing.images && listing.images[0] ? listing.images[0].image_path : null);
            offered = { id: listing.id, title: listing.title, condition: listing.condition, image_url: img ? ('/storage/' + img) : null, url: '/trading/listings/' + listing.id };
        }
        return { id: m.id, sender_id: m.sender_id, sender_name: (m.sender && m.sender.name) || m.sender_name || '', message: m.message, created_at: m.created_at, formatted_created_at: m.formatted_created_at || m.created_at_formatted || m.created_at, attachments: atts, offered_listing: offered };
    }
    var pollMessages = function() {
        if (document.hidden) return;
        fetch('{{ route("trading.conversations.messages.index", $conversation) }}?after_id=' + lastMessageId, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.ok ? r.json() : null; })
            .then(function(data) {
                if (data && data.messages && data.messages.length > 0) {
                    data.messages.forEach(function(m) {
                        if (m.id > lastMessageId) lastMessageId = m.id;
                        if (m.sender_id !== window.AUTH_ID && !chatBody.querySelector('[data-message-id="' + m.id + '"]')) {
                            var payload = normalizePolledMessage(m);
                            if (typeof window.conversationAppendMessage === 'function') window.conversationAppendMessage(payload);
                        }
                    });
                }
            })
            .catch(function() {});
    };
    var pollInterval = setInterval(pollMessages, 4000);
    
    // Update presence when user interacts
    chatBody.addEventListener('scroll', function() { updateMyPresence(); }, { passive: true });
    input.addEventListener('focus', function() { updateMyPresence(); });
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        clearInterval(presenceRefreshInterval);
        clearInterval(myPresenceInterval);
        clearInterval(pollInterval);
    });
    
    // Initial scroll to bottom after page load
    setTimeout(scrollToBottom, 100);

    // Unsend: event delegation for msg-unsend-btn
    var unsendModal = document.getElementById('unsendModal');
    var unsendConfirmBtn = document.getElementById('unsendConfirmBtn');
    var pendingUnsendId = null;
    chatBody.addEventListener('click', function(e) {
        var btn = e.target.closest('.msg-unsend-btn');
        if (!btn) return;
        e.preventDefault();
        var msgId = btn.getAttribute('data-message-id');
        var bubble = chatBody.querySelector('[data-message-id="' + msgId + '"]');
        if (!bubble || bubble.getAttribute('data-unsent') === '1') return;
        pendingUnsendId = msgId;
        var modalInstance = bootstrap.Modal.getOrCreateInstance(unsendModal);
        modalInstance.show();
    });
    if (unsendConfirmBtn && unsendModal) {
        unsendConfirmBtn.addEventListener('click', function() {
            if (!pendingUnsendId) return;
            var msgId = pendingUnsendId;
            pendingUnsendId = null;
            var modalInstance = bootstrap.Modal.getInstance(unsendModal);
            if (modalInstance) modalInstance.hide();
            var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value;
            fetch('/trading/conversations/{{ $conversation->id }}/messages/' + msgId, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token || '', 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function(r) {
                if (r.ok) applyUnsentToBubble(msgId);
                else r.json().then(function(d) { alert(d.error || 'Failed to remove message.'); });
            }).catch(function() { alert('Failed to remove message. Please try again.'); });
        });
    }

    function applyUnsentToBubble(msgId) {
        var bubble = chatBody.querySelector('[data-message-id="' + msgId + '"]');
        if (!bubble) return;
        bubble.setAttribute('data-unsent', '1');
        bubble.classList.add('msg-unsent');
        var isMine = bubble.classList.contains('mine');
        [].forEach.call(bubble.querySelectorAll('.msg-offered-product, .msg-text, .msg-attachments'), function(el) { el.remove(); });
        var unsentDiv = document.createElement('div');
        unsentDiv.className = 'msg-unsent-text';
        unsentDiv.innerHTML = '<i class="bi bi-x-circle me-1"></i> ' + (isMine ? 'You removed this message' : 'This message was removed');
        var msgTime = bubble.querySelector('.msg-time');
        if (msgTime) bubble.insertBefore(unsentDiv, msgTime);
        else bubble.appendChild(unsentDiv);
        var timeEl = bubble.querySelector('.msg-time');
        if (timeEl) {
            var unsendBtn = timeEl.querySelector('.msg-unsend-btn');
            if (unsendBtn) unsendBtn.remove();
            var statusEl = timeEl.querySelector('.msg-status');
            if (statusEl) statusEl.remove();
        }
    }
    window.conversationHandleUnsent = function(messageId) { applyUnsentToBubble(messageId); };
})();
</script>
@endsection
