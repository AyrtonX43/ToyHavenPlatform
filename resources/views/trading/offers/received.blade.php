@extends('layouts.toyshop')
@section('title', 'Offers Received - ToyHaven Trade')
@section('content')
<div class="container py-4">
    <h1 class="h3 mb-4">Offers Received</h1>
    @if($listings->count() > 0)
    @foreach($listings as $listing)
    <div class="card mb-3">
        <div class="card-header"><strong>{{ $listing->title }}</strong></div>
        <div class="card-body">
            @foreach($listing->activeOffers as $offer)
            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                <div>
                    <span>{{ $offer->offerer->name }}</span>
                    @if($offer->cash_amount)<span class="text-primary ms-2">₱{{ number_format($offer->cash_amount, 0) }}</span>@endif
                    @if($offer->message)<p class="small text-muted mb-0">{{ Str::limit($offer->message, 80) }}</p>@endif
                </div>
                <div>
                    <form action="{{ route('trading.offers.accept', $offer->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success">Accept</button>
                    </form>
                    <form action="{{ route('trading.offers.reject', $offer->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
    {{ $listings->links() }}
    @else
    <p class="text-muted">No pending offers.</p>
    @endif
</div>
@endsection
