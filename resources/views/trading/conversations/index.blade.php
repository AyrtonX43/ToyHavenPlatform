@extends('layouts.toyshop')

@section('title', 'Messages - ToyHaven Trading')

@push('styles')
<style>
    .conversations-page { max-width: 800px; margin: 0 auto; }
    .conversations-header { background: white; border-radius: 14px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 1.5rem; }
    .conversations-header h1 { font-size: 1.35rem; font-weight: 700; margin: 0; color: #0f172a; }
    .conv-card { border-radius: 12px; margin-bottom: 0.5rem; transition: background 0.2s; }
    .conv-card:hover { background: #f8fafc; }
    .conv-avatar { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #0891b2, #0e7490); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem; }
    .conv-preview { color: #64748b; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }
    .conv-time { font-size: 0.8rem; color: #94a3b8; }
    .unread-badge { background: #0891b2; color: white; font-size: 0.7rem; min-width: 1.25rem; height: 1.25rem; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; }
    .empty-conversations { text-align: center; padding: 3rem 2rem; background: #f8fafc; border-radius: 14px; border: 1px dashed #e2e8f0; }
</style>
@endpush

@section('content')
<div class="container my-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trading.index') }}">Trading</a></li>
            <li class="breadcrumb-item active">Messages</li>
        </ol>
    </nav>

    <div class="conversations-header">
        <h1><i class="bi bi-chat-dots me-2"></i>Messages</h1>
        <p class="text-muted mb-0 small">Your trade conversations — newest first</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($conversations->isEmpty())
        <div class="empty-conversations">
            <div class="text-muted mb-3"><i class="bi bi-chat-dots" style="font-size: 3rem;"></i></div>
            <h5 class="fw-bold text-dark">No conversations yet</h5>
            <p class="text-muted mb-0">Message a seller from a trade listing to start chatting.</p>
            <a href="{{ route('trading.index') }}" class="btn btn-primary mt-3">Browse Listings</a>
        </div>
    @else
        <div class="list-group">
            @foreach($conversations as $conv)
                @php $other = $conv->getOtherUser(Auth::id()); @endphp
                <a href="{{ route('trading.conversations.show', $conv) }}" class="conv-card list-group-item list-group-item-action border border-1 border-light d-flex align-items-center gap-3 p-3">
                    <div class="conv-avatar flex-shrink-0">
                        {{ $other ? strtoupper(substr($other->name ?? '?', 0, 1)) : '?' }}
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark">{{ $other?->name ?? 'User' }}</span>
                            <span class="conv-time flex-shrink-0">
                                @if($conv->last_message_at)
                                    {{ $conv->last_message_at->diffForHumans() }}
                                @endif
                            </span>
                        </div>
                        <div class="conv-preview">
                            @if($conv->trade_listing_id)
                                <span class="text-muted">Listing</span>
                                @if($conv->tradeListing)
                                    · {{ Str::limit($conv->tradeListing->title, 35) }}
                                @endif
                            @elseif($conv->trade_id)
                                <span class="text-muted">Trade #{{ $conv->trade_id }}</span>
                            @else
                                —
                            @endif
                        </div>
                    </div>
                    @if(($conv->unread_count ?? 0) > 0)
                        <span class="unread-badge">{{ $conv->unread_count > 99 ? '99+' : $conv->unread_count }}</span>
                    @endif
                </a>
            @endforeach
        </div>
        <div class="mt-3">{{ $conversations->links() }}</div>
    @endif
</div>
@endsection
