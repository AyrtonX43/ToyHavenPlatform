@extends('layouts.toyshop')

@section('title', $auction->title . ' - Live Room')

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auctions.index') }}">Auctions</a></li>
            <li class="breadcrumb-item"><a href="{{ route('auctions.show', $auction) }}">{{ Str::limit($auction->title, 30) }}</a></li>
            <li class="breadcrumb-item active">Live Room</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-lg-8">
            @if($auction->primaryImage())
                <img src="{{ Storage::url($auction->primaryImage()->image_path) }}" class="img-fluid rounded-3 mb-3" alt="{{ $auction->title }}" style="max-height:400px;object-fit:contain">
            @endif
            <h2>{{ $auction->title }}</h2>
            <p class="text-muted">Ends {{ $auction->end_at?->format('M j, Y g:i A') }}</p>
        </div>
        <div class="col-lg-4">
            <div class="bg-light rounded-3 p-4">
                <h4>Current Bid: ₱{{ number_format($auction->currentPrice(), 2) }}</h4>
                <p class="mb-2">Next min: ₱{{ number_format($auction->nextMinBid(), 2) }}</p>
                @if($auction->canBid() && auth()->check() && auth()->user()->hasActiveMembership())
                    <a href="{{ route('auctions.show', $auction) }}" class="btn btn-success w-100">Place Bid</a>
                @endif
            </div>
            <div class="mt-3">
                <h5>Recent Bids</h5>
                <ul class="list-group list-group-flush">
                    @foreach($auction->bids->take(10) as $bid)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $bid->displayName() }}</span>
                            <strong>₱{{ number_format($bid->amount, 2) }}</strong>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
