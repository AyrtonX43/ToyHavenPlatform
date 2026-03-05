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

    @if(in_array($trade->status, ['pending_meetup', 'meetup_scheduled', 'meetup_completed']) && ($trade->initiator_id === auth()->id() || $trade->participant_id === auth()->id()))
    @if(!$trade->dispute)
    <a href="{{ route('trading.trades.dispute-form', $trade->id) }}" class="btn btn-outline-danger">Open Dispute</a>
    @endif
    @endif

    @if($trade->status === 'disputed' && $trade->dispute)
    <div class="alert alert-warning">This trade is in dispute. A moderator will resolve it.</div>
    @endif
</div>
@endsection
