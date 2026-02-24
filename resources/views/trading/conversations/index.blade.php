@extends('layouts.toyshop')

@section('title', 'Messages - ToyHaven Trading')

@push('styles')
<style>
    .messages-app { display: flex; height: calc(100vh - 180px); min-height: 420px; max-width: 1200px; margin: 0 auto; background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); border: 1px solid #e2e8f0; overflow: hidden; }
    .messages-sidebar { width: 320px; min-width: 280px; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; background: #fafbfc; }
    .messages-sidebar-header { padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; background: #fff; }
    .messages-sidebar-header h1 { font-size: 1.25rem; font-weight: 700; margin: 0; color: #0f172a; }
    .messages-sidebar-list { flex: 1; overflow-y: auto; }
    .conv-row { display: flex; align-items: center; gap: 12px; padding: 12px 1rem; cursor: pointer; border: none; border-bottom: 1px solid #e2e8f0; background: #fff; width: 100%; text-align: left; transition: background 0.15s; }
    .conv-row:hover { background: #f1f5f9; }
    .conv-row.active { background: #ecfeff; border-left: 3px solid #0891b2; }
    .conv-row.unread { background: #f0f9ff; }
    .conv-avatar { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #0891b2, #0e7490); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem; flex-shrink: 0; }
    .conv-info { flex: 1; min-width: 0; }
    .conv-name { font-weight: 600; color: #0f172a; font-size: 0.95rem; }
    .conv-preview { font-size: 0.8rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }
    .conv-meta { flex-shrink: 0; text-align: right; }
    .conv-time { font-size: 0.75rem; color: #94a3b8; }
    .unread-dot { width: 10px; height: 10px; border-radius: 50%; background: #0891b2; display: inline-block; margin-left: 4px; }
    .messages-main { flex: 1; display: flex; flex-direction: column; min-width: 0; background: #fff; }
    .messages-empty { flex: 1; display: flex; align-items: center; justify-content: center; color: #94a3b8; flex-direction: column; gap: 1rem; padding: 2rem; }
    .messages-empty i { font-size: 4rem; opacity: 0.5; }
    .messages-empty p { margin: 0; font-size: 1rem; }
    .chat-panel { flex: 1; display: none; flex-direction: column; min-height: 0; }
    .chat-panel.active { display: flex; }
    .chat-header { padding: 12px 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; background: #fff; }
    .chat-header-left { display: flex; align-items: center; gap: 12px; }
    .chat-header .chat-avatar { width: 42px; height: 42px; font-size: 1rem; }
    .chat-header-name { font-weight: 600; color: #0f172a; font-size: 1rem; }
    .chat-header-status { font-size: 0.8rem; color: #64748b; margin-top: 1px; }
    .chat-header-status.live { color: #22c55e; display: inline-flex; align-items: center; gap: 4px; }
    .chat-header-status.live::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #22c55e; animation: livePulse 1.5s ease-in-out infinite; }
    @keyframes livePulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
    .chat-header-status.typing { color: #0891b2; font-style: italic; }
    .chat-body { flex: 1; overflow-y: auto; padding: 1rem; display: flex; flex-direction: column; gap: 10px; scroll-behavior: smooth; background: #f8fafc; }
    .chat-body .msg-bubble { max-width: 78%; padding: 10px 14px; border-radius: 16px; position: relative; box-shadow: 0 1px 2px rgba(0,0,0,0.06); }
    .chat-body .msg-bubble.mine { background: #0891b2; color: #fff; margin-left: auto; border-bottom-right-radius: 4px; }
    .chat-body .msg-bubble.theirs { background: #fff; color: #0f172a; margin-right: auto; border-bottom-left-radius: 4px; border: 1px solid #e2e8f0; }
    .chat-body .msg-sender { font-size: 0.7rem; font-weight: 600; margin-bottom: 4px; opacity: 0.9; }
    .chat-body .msg-text { word-break: break-word; white-space: pre-wrap; font-size: 0.95rem; line-height: 1.4; }
    .chat-body .msg-time { font-size: 0.7rem; margin-top: 6px; opacity: 0.85; }
    .chat-body .msg-bubble.msg-enter { animation: msgEnter 0.35s ease-out forwards; }
    @keyframes msgEnter { from { opacity: 0; transform: translateY(6px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
    .chat-body .msg-attachments { margin-top: 8px; display: flex; flex-wrap: wrap; gap: 6px; }
    .chat-body .msg-attachments img { max-width: 200px; max-height: 180px; border-radius: 10px; object-fit: cover; }
    .typing-indicator { padding: 10px 14px; background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; align-self: flex-start; display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; color: #64748b; }
    .typing-dots { display: inline-flex; gap: 4px; }
    .typing-dots span { width: 6px; height: 6px; border-radius: 50%; background: #94a3b8; animation: typingBounce 1.2s ease-in-out infinite both; }
    .typing-dots span:nth-child(1) { animation-delay: 0s; }
    .typing-dots span:nth-child(2) { animation-delay: 0.15s; }
    .typing-dots span:nth-child(3) { animation-delay: 0.3s; }
    @keyframes typingBounce { 0%, 80%, 100% { transform: translateY(0); } 40% { transform: translateY(-5px); } }
    .chat-footer { padding: 12px 1rem; border-top: 1px solid #e2e8f0; background: #fff; flex-shrink: 0; }
    .chat-footer form { display: flex; gap: 8px; align-items: flex-end; flex-wrap: wrap; }
    .chat-footer input[type="text"] { flex: 1; min-width: 120px; border-radius: 20px; padding: 10px 16px; border: 1px solid #e2e8f0; }
    .chat-footer input[type="text"]:focus { border-color: #0891b2; box-shadow: 0 0 0 3px rgba(8,145,178,0.15); outline: none; }
    .chat-footer .btn-send { width: 42px; height: 42px; border-radius: 50%; background: #0891b2; border: none; color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.15s, background 0.2s; }
    .chat-footer .btn-send:hover { background: #0e7490; transform: scale(1.05); }
    .chat-footer .btn-send:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
    .msg-unsent-text { font-size: 0.85rem; font-style: italic; color: #64748b; }
    .msg-offered-product { display: block; padding: 8px 10px; background: rgba(0,0,0,0.05); border-radius: 10px; margin-bottom: 6px; text-decoration: none; color: inherit; font-size: 0.9rem; }
    .msg-offered-product:hover { background: rgba(0,0,0,0.08); }
    @media (max-width: 768px) {
        .messages-app { flex-direction: column; height: auto; min-height: 80vh; }
        .messages-sidebar { width: 100%; max-height: 40vh; }
        .chat-panel.active + .messages-empty { display: none; }
    }
</style>
@endpush

@section('content')
<div class="container py-3">
    <nav aria-label="breadcrumb" class="mb-2">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trading.index') }}">Trading</a></li>
            <li class="breadcrumb-item active">Messages</li>
        </ol>
    </nav>

    @if($conversations->isEmpty())
        <div class="rounded-3 border border-2 border-dashed p-5 text-center bg-light">
            <i class="bi bi-chat-dots text-muted" style="font-size: 3rem;"></i>
            <h5 class="mt-3 fw-bold">No conversations yet</h5>
            <p class="text-muted mb-0">Message a seller from a trade listing to start chatting.</p>
            <a href="{{ route('trading.index') }}" class="btn btn-primary mt-3">Browse Listings</a>
        </div>
    @else
        <div class="messages-app" id="messagesApp">
            <div class="messages-sidebar">
                <div class="messages-sidebar-header">
                    <h1><i class="bi bi-chat-dots-fill me-2 text-primary"></i>Messages</h1>
                </div>
                <div class="messages-sidebar-list" id="convList">
                    @foreach($conversations as $conv)
                        @php $other = $conv->getOtherUser(Auth::id()); @endphp
                        <button type="button" class="conv-row" data-conv-id="{{ $conv->id }}" data-other-name="{{ $other?->name ?? 'User' }}">
                            <div class="conv-avatar">{{ $other ? strtoupper(substr($other->name ?? '?', 0, 1)) : '?' }}</div>
                            <div class="conv-info">
                                <div class="conv-name">{{ $other?->name ?? 'User' }}</div>
                                <div class="conv-preview">
                                    @if($conv->trade_listing_id && $conv->tradeListing)
                                        {{ Str::limit($conv->tradeListing->title, 30) }}
                                    @elseif($conv->trade_id)
                                        Trade #{{ $conv->trade_id }}
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>
                            <div class="conv-meta">
                                <div class="conv-time">@if($conv->last_message_at){{ $conv->last_message_at->diffForHumans() }}@endif</div>
                                @if(($conv->unread_count ?? 0) > 0)<span class="unread-dot" title="{{ $conv->unread_count }} new"></span>@endif
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="messages-main">
                <div class="chat-panel" id="chatPanel">
                    <div class="chat-header">
                        <div class="chat-header-left">
                            <div class="chat-avatar" id="chatHeaderAvatar">?</div>
                            <div>
                                <div class="chat-header-name" id="chatHeaderName">—</div>
                                <div class="chat-header-status" id="chatHeaderStatus">—</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success d-none" id="liveBadge"><i class="bi bi-broadcast me-1"></i>Live</span>
                            <a href="#" id="reportLink" class="btn btn-sm btn-outline-secondary d-none" target="_blank">Report</a>
                        </div>
                    </div>
                    <div class="chat-body" id="chatBody"></div>
                    <div id="typingIndicator" class="typing-indicator" style="display:none; margin: 0 1rem 0.5rem;">
                        <span class="typing-dots"><span></span><span></span><span></span></span>
                        <span id="typingUserName"></span> is typing...
                    </div>
                    <div class="chat-footer">
                        <form id="messageForm" class="w-100">
                            @csrf
                            <div class="d-flex gap-2 align-items-end w-100">
                                <input type="text" name="message" id="messageInput" placeholder="Type a message..." maxlength="5000" autocomplete="off">
                                <button type="submit" class="btn-send" id="sendBtn" title="Send"><i class="bi bi-send-fill"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="messages-empty" id="messagesEmpty">
                    <i class="bi bi-chat-left-text"></i>
                    <p>Select a conversation to start messaging</p>
                </div>
            </div>
        </div>
    @endif
</div>

@php
    $broadcastDefault = config('broadcasting.default');
    $broadcastConfig = null;
    if ($broadcastDefault && $broadcastDefault !== 'null') {
        $conn = config('broadcasting.connections.' . $broadcastDefault);
        $broadcastConfig = [
            'key' => $conn['key'] ?? ($conn['app_id'] ?? null),
            'host' => $conn['options']['host'] ?? 'localhost',
            'port' => $conn['options']['port'] ?? 8080,
            'scheme' => $conn['options']['scheme'] ?? 'http',
        ];
    }
@endphp
<script>
window.AUTH_ID = {{ Auth::id() }};
window.APP_TIMEZONE = @json(config('app.timezone', 'Asia/Manila'));
window.BROADCAST_CONFIG = @json($broadcastConfig);
window.CONVERSATIONS_INDEX_URL = @json(route('trading.conversations.index'));
</script>
@vite(['resources/js/app.js'])
@if($conversations->isNotEmpty())
    @vite(['resources/js/messages-app.js'])
@endif
@endsection
