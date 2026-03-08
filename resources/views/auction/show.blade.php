@extends('layouts.toyshop')

@section('title', $auction->title . ' - ToyHaven Auction')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auction.index') }}">Auction</a></li>
            <li class="breadcrumb-item active">{{ Str::limit($auction->title, 40) }}</li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h1 class="h4 mb-3">{{ $auction->title }}</h1>
                    @if($auction->description)
                        <div class="mb-4">{!! nl2br(e($auction->description)) !!}</div>
                    @endif
                    <p class="text-muted small mb-0">
                        Listed by {{ $auction->user?->name }} · {{ $auction->category?->name ?? 'Uncategorized' }}
                    </p>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Bid History</h5>
                </div>
                <div class="card-body">
                    @if($auction->bids->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($auction->bids as $bid)
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Bidder #{{ $bid->rank_at_bid }}</span>
                                    <span>₱{{ number_format($bid->amount, 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">No bids yet. Be the first to bid!</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4 sticky-top">
                <div class="card-body">
                    @if($auction->isActive())
                        <p class="mb-2">
                            <strong>Current bid:</strong>
                            <span class="fs-4 text-primary">₱{{ number_format($auction->winning_amount ?? $auction->starting_bid, 2) }}</span>
                        </p>
                        <p class="mb-2 text-muted small">Minimum next bid: ₱{{ number_format($auction->next_min_bid, 2) }}</p>
                        <p class="mb-3">
                            <i class="bi bi-clock me-1"></i>
                            Ends {{ $auction->end_at?->format('M d, Y H:i') }}
                            ({{ $auction->end_at?->diffForHumans() }})
                        </p>

                        @if($auction->user_id !== auth()->id())
                            <form action="{{ route('auction.bid.store', $auction) }}" method="POST" class="mb-0">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Your bid (₱)</label>
                                    <input type="number" name="amount" class="form-control form-control-lg @error('amount') is-invalid @enderror"
                                        value="{{ old('amount', $auction->next_min_bid) }}" min="{{ $auction->next_min_bid }}" step="0.01" required>
                                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="bi bi-hammer me-1"></i>Place Bid
                                </button>
                            </form>
                        @else
                            <p class="text-muted mb-0">This is your listing. You cannot bid on it.</p>
                        @endif
                    @else
                        <p class="mb-2"><strong>Final price:</strong> ₱{{ number_format($auction->winning_amount ?? $auction->starting_bid, 2) }}</p>
                        <p class="mb-0 text-muted">This auction has ended.</p>
                        @if($auction->winner_id === auth()->id())
                            <p class="text-success mt-2 mb-0">Congratulations! You won this auction.</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('auction.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Auctions
    </a>
</div>
@endsection
