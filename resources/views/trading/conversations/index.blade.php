@extends('layouts.toyshop')

@section('title', 'Messages - ToyHaven Trading')

@push('styles')
<style>
    :root {
        --chat-bg: #0f172a;
        --chat-sidebar-bg: #1e293b;
        --chat-panel-bg: #f8fafc;
        --chat-mine: #0ea5e9;
        --chat-theirs: #e2e8f0;
        --chat-text: #0f172a;
        --chat-accent: #0ea5e9;
    }
    .messages-wrapper {
        display: flex;
        height: calc(100vh - 180px);
        min-height: 420px;
        max-height: 780px;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15);
        border: 1px solid #e2e8f0;
    }
    .messages-sidebar {
        width: 320px;
        min-width: 280px;
        background: var(--chat-sidebar-bg);
        display: flex;
        flex-direction: column;
        color: #e2e8f0;
    }
    .messages-sidebar-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        flex-shrink: 0;
    }
    .messages-sidebar-header h2 {
        font-size: 1.15rem;
        font-weight: 700;
        margin: 0;
        color: #fff;
    }
    .messages-sidebar-header p { font-size: 0.8rem; color: #94a3b8; margin: 0.25rem 0 0 0; }
    .conv-list {
        flex: 1;
        overflow-y: auto;
    }
    .conv-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.875rem 1.25rem;
        cursor: pointer;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        transition: background 0.15s;
        text-decoration: none;
        color: inherit;
    }
    .conv-item:hover { background: rgba(255,255,255,0.06); }
    .conv-item.active { background: rgba(14, 165, 233, 0.2); border-left: 3px solid var(--chat-accent); }
    .conv-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .conv-item-body { flex: 1; min-width: 0; }
    .conv-item-name { font-weight: 600; color: #f1f5f9; font-size: 0.9rem; }
    .conv-item-preview { font-size: 0.8rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 0.15rem; }
    .conv-item-meta { display: flex; align-items: center; justify-content: space-between; margin-top: 0.2rem; }
    .conv-item-time { font-size: 0.7rem; color: #64748b; }
    .conv-unread {
        background: var(--chat-accent);
        color: #fff;
        font-size: 0.7rem;
        min-width: 1.25rem;
        height: 1.25rem;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
    .chat-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: var(--chat-panel-bg);
        min-width: 0;
    }
    .chat-panel-empty {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #64748b;
        padding: 2rem;
    }
    .chat-panel-empty i { font-size: 4rem; margin-bottom: 1rem; opacity: 0.5; }
    .chat-panel-empty p { margin: 0; font-size: 1rem; }
    .chat-header {
        padding: 0.875rem 1.25rem;
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }
    .chat-header-left { display: flex; align-items: center; gap: 0.75rem; }
    .chat-header-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
    }
    .chat-header-name { font-weight: 600; color: #0f172a; font-size: 1rem; }
    .chat-header-status { font-size: 0.75rem; color: #22c55e; }
    .chat-header-status.offline { color: #94a3b8; }
    .chat-body {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        scroll-behavior: smooth;
    }
    .msg-bubble {
        max-width: 78%;
        padding: 0.6rem 1rem;
        border-radius: 16px;
        font-size: 0.9375rem;
        animation: msgIn 0.25s ease;
    }
    @keyframes msgIn {
        from { opacity: 0; transform: scale(0.96); }
        to { opacity: 1; transform: scale(1); }
    }
    .msg-bubble.mine {
        background: var(--chat-mine);
        color: #fff;
        margin-left: auto;
        border-bottom-right-radius: 4px;
    }
    .msg-bubble.theirs {
        background: #fff;
        color: var(--chat-text);
        border: 1px solid #e2e8f0;
        margin-right: auto;
        border-bottom-left-radius: 4px;
    }
    .msg-sender { font-size: 0.7rem; font-weight: 600; margin-bottom: 0.2rem; opacity: 0.9; }
    .msg-text { word-break: break-word; white-space: pre-wrap; }
    .msg-time { font-size: 0.7rem; margin-top: 0.35rem; opacity: 0.85; }
    .msg-status { font-size: 0.7rem; margin-left: 0.25rem; }
    .msg-attachments { margin-top: 0.4rem; display: flex; flex-wrap: wrap; gap: 0.4rem; }
    .msg-attachments img { max-width: 200px; max-height: 160px; border-radius: 10px; object-fit: cover; }
    .msg-attachments video { max-width: 260px; max-height: 180px; border-radius: 10px; }
    .msg-offered-product {
        display: block;
        background: rgba(0,0,0,0.06);
        border-radius: 10px;
        padding: 0.5rem;
        margin-bottom: 0.35rem;
        text-decoration: none;
        color: inherit;
    }
    .msg-offered-product:hover { background: rgba(0,0,0,0.08); }
    .msg-unsent-text { font-size: 0.85rem; font-style: italic; opacity: 0.9; }
    .msg-unsend-btn { opacity: 0.7; padding: 0 0.25rem; font-size: 0.75rem; }
    .msg-unsend-btn:hover { opacity: 1; }
    .typing-indicator {
        font-size: 0.85rem;
        color: #64748b;
        padding: 0.4rem 0;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .typing-dots span {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #94a3b8;
        animation: typingBounce 1.2s ease-in-out infinite both;
    }
    .typing-dots span:nth-child(1) { animation-delay: 0s; }
    .typing-dots span:nth-child(2) { animation-delay: 0.15s; }
    .typing-dots span:nth-child(3) { animation-delay: 0.3s; }
    @keyframes typingBounce {
        0%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(-5px); }
    }
    .chat-footer {
        padding: 0.75rem 1rem;
        background: #fff;
        border-top: 1px solid #e2e8f0;
        flex-shrink: 0;
    }
    .chat-footer .form-control:focus { border-color: var(--chat-accent); box-shadow: 0 0 0 3px rgba(14,165,233,0.2); }
    .chat-footer .btn-primary { background: var(--chat-accent); border-color: var(--chat-accent); }
    .chat-footer .btn-primary:hover { background: #0284c7; border-color: #0284c7; }
    .empty-state-full {
        text-align: center;
        padding: 3rem 2rem;
    }
    .empty-state-full i { font-size: 3.5rem; color: #cbd5e1; }
    .empty-state-full h5 { margin-top: 1rem; font-weight: 600; color: #334155; }
    .empty-state-full p { color: #64748b; margin-bottom: 1.5rem; }
</style>
@endpush

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trading.index') }}">Trading</a></li>
            <li class="breadcrumb-item active">Messages</li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($conversations->isEmpty())
        <div class="empty-state-full">
            <i class="bi bi-chat-dots"></i>
            <h5>No conversations yet</h5>
            <p class="mb-0">Message a seller from a trade listing to start chatting.</p>
            <a href="{{ route('trading.index') }}" class="btn btn-primary">Browse Listings</a>
        </div>
    @else
        <div class="messages-wrapper">
            <aside class="messages-sidebar">
                <div class="messages-sidebar-header">
                    <h2><i class="bi bi-chat-dots-fill me-2"></i>Messages</h2>
                    <p>Your conversations — click to open</p>
                </div>
                <div class="conv-list" id="convList">
                    @foreach($conversations as $conv)
                        @php $other = $conv->getOtherUser(Auth::id()); @endphp
                        <a href="{{ route('trading.conversations.show', $conv) }}" class="conv-item" data-conv-id="{{ $conv->id }}" data-other-name="{{ $other?->name ?? 'User' }}">
                            <div class="conv-avatar">{{ $other ? strtoupper(substr($other->name ?? '?', 0, 1)) : '?' }}</div>
                            <div class="conv-item-body">
                                <div class="conv-item-name">{{ $other?->name ?? 'User' }}</div>
                                <div class="conv-item-preview">
                                    @if($conv->trade_listing_id && $conv->tradeListing)
                                        {{ Str::limit($conv->tradeListing->title, 28) }}
                                    @elseif($conv->trade_id)
                                        Trade #{{ $conv->trade_id }}
                                    @else
                                        —
                                    @endif
                                </div>
                                <div class="conv-item-meta">
                                    <span class="conv-item-time">@if($conv->last_message_at){{ $conv->last_message_at->diffForHumans() }}@endif</span>
                                    @if(($conv->unread_count ?? 0) > 0)
                                        <span class="conv-unread">{{ $conv->unread_count > 99 ? '99+' : $conv->unread_count }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </aside>
            <main class="chat-panel" id="chatPanel">
                <div class="chat-panel-empty" id="chatPanelEmpty">
                    <i class="bi bi-chat-left-text"></i>
                    <p>Select a conversation to start messaging</p>
                </div>
                <div id="chatPanelContent" style="display:none; flex:1; flex-direction:column; min-height:0;">
                    <div class="chat-header" id="chatHeader">
                        <div class="chat-header-left">
                            <div class="chat-header-avatar" id="chatHeaderAvatar">?</div>
                            <div>
                                <div class="chat-header-name" id="chatHeaderName">—</div>
                                <div class="chat-header-status offline" id="chatHeaderStatus">Offline</div>
                            </div>
                        </div>
                        <div>
                            <a href="#" id="chatReportBtn" class="btn btn-sm btn-outline-secondary d-none" target="_blank">Report</a>
                        </div>
                    </div>
                    <div class="chat-body" id="chatBody"></div>
                    <div id="typingIndicator" class="typing-indicator px-3" style="display:none;">
                        <span class="typing-dots"><span></span><span></span><span></span></span>
                        <span id="typingUserName"></span> is typing...
                    </div>
                    <div class="chat-footer" id="chatFooter">
                        <form id="messageForm" class="d-flex flex-column gap-2">
                            @csrf
                            <div id="offerListingWrap" class="d-flex align-items-center gap-2 flex-wrap" style="display:none !important;">
                                <span class="small text-muted">Offer:</span>
                                <select id="offerProductSelect" class="form-select form-select-sm" style="max-width:200px;"></select>
                            </div>
                            <div class="d-flex gap-2 align-items-end">
                                <input type="text" name="message" id="messageInput" class="form-control rounded-pill" placeholder="Type a message..." maxlength="5000" autocomplete="off">
                                <label class="btn btn-outline-secondary btn-sm mb-0 rounded-pill" title="Attach"><i class="bi bi-image"></i>
                                    <input type="file" id="attachmentInput" accept="image/*,video/*" multiple class="d-none">
                                </label>
                                <button type="submit" class="btn btn-primary rounded-pill" id="sendBtn"><i class="bi bi-send-fill"></i></button>
                            </div>
                            <div id="attachmentPreview" class="d-flex flex-wrap gap-2"></div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
        <div class="mt-3">{{ $conversations->links() }}</div>
    @endif
</div>

<div class="modal fade" id="unsendModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Remove message?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to remove this message? This cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="unsendConfirmBtn"><i class="bi bi-trash3 me-1"></i> Remove</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.AUTH_ID = {{ Auth::id() }};
window.APP_TIMEZONE = @json(config('app.timezone', 'Asia/Manila'));
window.CONVERSATIONS_INDEX_URL = @json(route('trading.conversations.index'));
window.BASE_URL = @json(url('/'));
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
    ];
@endphp
window.ECHO_CONFIG = @json($echoConfig);
window.CONVERSATION_ID = 0;
@else
window.ECHO_CONFIG = null;
@endif
</script>
@if(config('broadcasting.default') !== 'null')
@vite(['resources/js/echo-conversation.js'])
@endif
@vite(['resources/js/chat-app.js'])
@endpush
@endsection
