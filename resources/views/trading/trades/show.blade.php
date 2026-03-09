@extends('layouts.toyshop')
@section('title', 'Trade #' . $trade->id . ' - ToyHaven')
@section('content')
<div class="container py-4">
    <h1 class="h3 mb-4">Trade #{{ $trade->id }}</h1>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Status:</strong> {{ $trade->getStatusLabel() }}</p>
            <p><strong>Listing:</strong> {{ $trade->tradeListing->title }}</p>
            <p><strong>Other party:</strong> {{ $trade->getOtherParty(auth()->id())->name }}</p>
            @if($trade->conversation)
            <a href="{{ route('trading.conversations.show', $trade->conversation->id) }}" class="btn btn-primary"><i class="bi bi-chat-dots me-1"></i>Open Chat</a>
            @endif
        </div>
    </div>

    @if(in_array($trade->status, ['pending_meetup', 'meetup_scheduled', 'meetup_completed']))
        @if($trade->initiator_id === auth()->id() || $trade->participant_id === auth()->id())
            @php
                $userRequestedCancel = ($trade->isInitiator(auth()->id()) && $trade->initiator_cancel_requested_at) || ($trade->isParticipant(auth()->id()) && $trade->participant_cancel_requested_at);
                $otherRequestedCancel = $trade->hasPendingCancelRequest() && !$userRequestedCancel;
            @endphp
            @if(!$trade->dispute)
                <a href="{{ route('trading.trades.dispute-form', $trade->id) }}" class="btn btn-outline-danger me-2">Open Dispute</a>
            @endif
            @if($otherRequestedCancel)
                <form method="POST" action="{{ route('trading.trades.cancel', $trade->id) }}" class="d-inline" onsubmit="return confirm('Accept the cancellation? The trade will be cancelled and both listings will return to active.');">
                    @csrf
                    <button type="submit" class="btn btn-danger">Accept cancel</button>
                </form>
                <span class="text-muted small ms-2">The other party requested to cancel. You have 24 hours to respond.</span>
            @else
                <form method="POST" action="{{ route('trading.trades.cancel', $trade->id) }}" class="d-inline" onsubmit="return confirm('Request to cancel? The other party will be notified and has 24 hours to respond.');">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary">{{ $userRequestedCancel ? 'Request cancel again' : 'Request cancel' }}</button>
                </form>
                @if($userRequestedCancel && !$trade->bothRequestedCancel())
                    <span class="text-muted small ms-2">Cancel requested. Waiting for the other party (24h).</span>
                @endif
            @endif
        @endif
    @endif

    @if($trade->status === 'disputed' && $trade->dispute)
    <div class="alert alert-warning">This trade is in dispute. A moderator will resolve it.</div>
    @endif
</div>
@endsection
