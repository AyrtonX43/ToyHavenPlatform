@extends('layouts.toyshop')
@section('title', $listing->title . ' - ToyHaven Trade')
@section('content')
<div class="container py-4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="row">
        <div class="col-lg-6 mb-4">
            @if($listing->images->count() > 0)
            <img src="{{ asset('storage/' . $listing->images->first()->image_path) }}" alt="{{ $listing->title }}" class="img-fluid rounded-3 w-100">
            @else
            <div class="bg-light rounded-3 d-flex align-items-center justify-content-center" style="height: 300px;">
                <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
            </div>
            @endif
        </div>
        <div class="col-lg-6">
            <span class="badge bg-{{ $listing->trade_type === 'cash' ? 'success' : ($listing->trade_type === 'exchange_with_cash' ? 'info' : 'primary') }}">{{ ucfirst(str_replace('_', ' ', $listing->trade_type)) }}</span>
            <h1 class="h3 mt-2">{{ $listing->title }}</h1>
            <p class="text-muted">by {{ $listing->user->name }}</p>
            @if($listing->cash_amount)
            <p class="h4 text-primary">₱{{ number_format($listing->cash_amount, 0) }}</p>
            @endif
            <p class="mb-2"><strong>Condition:</strong> {{ $listing->condition }}</p>
            @if($listing->location)
            <p class="mb-2"><strong>Location:</strong> {{ $listing->location }}</p>
            @endif
            <hr>
            <p>{{ $listing->description }}</p>

            @auth
            @if(!auth()->user()->isTradeSuspended())
            @if($listing->user_id !== auth()->id())
                @if($listing->canAcceptOffers())
                <form action="{{ route('trading.conversations.store-from-listing.post', $listing->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary"><i class="bi bi-chat-dots me-1"></i>Contact Seller</button>
                </form>
                @endif
            @else
                <a href="{{ route('trading.listings.edit', $listing->id) }}" class="btn btn-outline-primary">Edit</a>
                @if($listing->status === 'active')
                <form action="{{ route('trading.listings.mark-sold', $listing->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Mark as sold?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary">Mark Sold</button>
                </form>
                @endif
            @endif
            @endif
            @endauth
        </div>
    </div>
</div>
@endsection
