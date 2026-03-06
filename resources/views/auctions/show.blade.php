@extends('layouts.toyshop')

@section('title', $auction->title . ' - ToyHaven Auctions')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auctions.index') }}">Auctions</a></li>
            <li class="breadcrumb-item active">{{ Str::limit($auction->title, 40) }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            @if($auction->images->first())
                <img src="{{ asset('storage/' . $auction->images->first()->path) }}" class="img-fluid rounded mb-3" alt="{{ $auction->title }}">
            @endif
            <h2>{{ $auction->title }}</h2>
            <p class="text-muted">{{ $auction->description }}</p>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5>Current Bid</h5>
                    <p class="display-6 text-primary">₱{{ number_format($bids->max('amount') ?? $auction->starting_bid, 0) }}</p>
                    <p class="small text-muted">Starting bid: ₱{{ number_format($auction->starting_bid, 0) }}</p>
                    <p class="small text-muted">Ends: {{ $auction->end_at?->format('M d, Y H:i') }}</p>

                    @if($canBid)
                        <form action="{{ route('auctions.bids.store', $auction) }}" method="POST" class="mt-3">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Your bid (₱)</label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="{{ (($bids->max('amount') ?? $auction->starting_bid) + $auction->bid_increment) }}" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Place Bid</button>
                        </form>
                    @elseif(auth()->guest())
                        <p class="text-muted small">Sign in and have an active membership to bid.</p>
                        <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn btn-outline-primary">Sign In</a>
                    @elseif(!auth()->user()->hasActiveMembership())
                        <p class="text-muted small">Active membership required to bid.</p>
                        <a href="{{ route('membership.index') }}" class="btn btn-outline-primary">Join Membership</a>
                    @elseif(auth()->user()->isAuctionSuspended())
                        <p class="text-warning small">You are suspended from auction bidding.</p>
                    @endif

                    @auth
                    <form action="{{ route('auctions.save', $auction) }}" method="POST" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">Save for later</button>
                    </form>
                    @endauth
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <h6>Bid History</h6>
                    <ul class="list-unstyled mb-0">
                        @foreach($bids as $b)
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span>{{ $b['anonymous_display_id'] }}</span>
                                <span>₱{{ number_format($b['amount'], 0) }}</span>
                            </li>
                        @endforeach
                        @if($bids->isEmpty())
                            <li class="text-muted">No bids yet</li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
