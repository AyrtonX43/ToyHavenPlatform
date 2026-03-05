@extends('layouts.toyshop')
@section('title', 'My Offers - ToyHaven Trade')
@section('content')
<div class="container py-4">
    <h1 class="h3 mb-4">My Offers</h1>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    @if($offers->count() > 0)
    <div class="list-group">
        @foreach($offers as $offer)
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <strong>{{ $offer->tradeListing->title }}</strong>
                <span class="badge bg-{{ $offer->status === 'pending' ? 'warning' : ($offer->status === 'accepted' ? 'success' : 'secondary') }} ms-2">{{ ucfirst($offer->status) }}</span>
                @if($offer->cash_amount)<span class="text-muted ms-2">₱{{ number_format($offer->cash_amount, 0) }}</span>@endif
            </div>
            <a href="{{ $offer->status === 'accepted' ? route('trading.trades.show', $offer->tradeListing->trade?->id) : route('trading.offers.show', $offer->id) }}" class="btn btn-sm btn-outline-primary">View</a>
        </div>
        @endforeach
    </div>
    {{ $offers->links() }}
    @else
    <p class="text-muted">No offers yet.</p>
    @endif
</div>
@endsection
