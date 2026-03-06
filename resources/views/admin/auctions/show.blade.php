@extends('layouts.admin-new')

@section('title', 'Auction #' . $auction->id . ' - Admin')
@section('page-title', 'Auction Details')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <h4>{{ $auction->title }}</h4>
        <p class="text-muted mb-0">{{ $auction->description }}</p>
        <p class="mt-2 mb-0"><strong>Status:</strong> <span class="badge bg-secondary">{{ $auction->status }}</span></p>
        <p class="mb-0"><strong>Seller:</strong> {{ $auction->auctionSellerProfile?->user?->name }}</p>
        <p class="mb-0"><strong>Starting Bid:</strong> ₱{{ number_format($auction->starting_bid, 0) }}</p>
        <p class="mb-0"><strong>Start:</strong> {{ $auction->start_at?->format('M d, Y H:i') }}</p>
        <p class="mb-0"><strong>End:</strong> {{ $auction->end_at?->format('M d, Y H:i') }}</p>
    </div>
</div>

@if($auction->status === 'pending_approval')
<form method="POST" action="{{ ($context ?? null) === 'moderator' ? route('moderator.auctions.reject', $auction) : route('admin.auctions.reject', $auction) }}" class="d-inline">
    @csrf
    <div class="input-group mb-2">
        <input type="text" name="rejection_reason" class="form-control" placeholder="Rejection reason (required)" required>
        <button type="submit" class="btn btn-danger">Reject</button>
    </div>
</form>
<form method="POST" action="{{ ($context ?? null) === 'moderator' ? route('moderator.auctions.approve', $auction) : route('admin.auctions.approve', $auction) }}" class="d-inline">
    @csrf
    <button type="submit" class="btn btn-success">Approve</button>
</form>
@endif

@if(($context ?? null) !== 'moderator' && $auction->status !== 'ended')
<form method="POST" action="{{ route('admin.auctions.cancel', $auction) }}" class="d-inline">
    @csrf
    <button type="submit" class="btn btn-warning" onclick="return confirm('Cancel this auction?')">Cancel</button>
</form>
@endif

<a href="{{ ($context ?? null) === 'moderator' ? route('moderator.auctions.index') : route('admin.auctions.index') }}" class="btn btn-secondary">Back</a>
@endsection
