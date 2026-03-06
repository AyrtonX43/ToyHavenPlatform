@extends('layouts.toyshop')

@section('title', $auction->title . ' - ToyHaven Auctions')

@push('styles')
<style>
    .bid-box { background: #f8fafc; border-radius: 16px; padding: 1.5rem; }
    .bid-row { font-size: 0.9rem; }
</style>
@endpush

@section('content')
<div class="container py-5">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auctions.index') }}">Auctions</a></li>
            <li class="breadcrumb-item active">{{ Str::limit($auction->title, 40) }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <div class="mb-4">
                @if($auction->images->isNotEmpty())
                    <div id="auctionCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner rounded-3 overflow-hidden">
                            @foreach($auction->images as $i => $img)
                                <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                                    <img src="{{ Storage::url($img->image_path) }}" class="d-block w-100" style="max-height:400px;object-fit:contain;background:#f1f5f9" alt="{{ $auction->title }}">
                                </div>
                            @endforeach
                        </div>
                        @if($auction->images->count() > 1)
                            <button class="carousel-control-prev" type="button" data-bs-target="#auctionCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                            <button class="carousel-control-next" type="button" data-bs-target="#auctionCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                        @endif
                    </div>
                @else
                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center" style="height:300px"><i class="bi bi-image text-muted display-1"></i></div>
                @endif
            </div>
            <h1 class="h2 mb-2">{{ $auction->title }}</h1>
            @if($auction->category)<span class="badge bg-light text-dark">{{ $auction->category->name }}</span>@endif
            <div class="mt-3 text-muted"><small>Ends {{ $auction->end_at?->format('M j, Y g:i A') }}</small></div>
            <hr>
            <div class="prose">{!! nl2br(e($auction->description)) !!}</div>
        </div>
        <div class="col-lg-4">
            <div class="bid-box sticky-top">
                <h4 class="mb-3">Current Bid</h4>
                <p class="fs-3 fw-bold text-success mb-0">₱{{ number_format($auction->currentPrice(), 2) }}</p>
                @if($auction->currentWinningBid())
                    <p class="text-muted small mb-3">Winning bidder: {{ $auction->currentWinningBid()->displayName() }}</p>
                @endif
                <div class="bid-row mb-2">Min next bid: <strong>₱{{ number_format($auction->nextMinBid(), 2) }}</strong></div>
                <div class="bid-row mb-2">Bid increment: ₱{{ number_format($auction->bid_increment, 0) }}</div>
                <div class="bid-row mb-3">Bids: {{ $auction->bids_count }}</div>

                @if($auction->canBid())
                    @if($canBid)
                        <form action="{{ route('auctions.bids.store', $auction) }}" method="POST" class="mb-3">
                            @csrf
                            <div class="mb-2">
                                <label for="amount" class="form-label">Your bid (₱)</label>
                                <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="{{ $auction->nextMinBid() }}" value="{{ $auction->nextMinBid() }}" required>
                                @error('amount')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <button type="submit" class="btn btn-success w-100">Place Bid</button>
                        </form>
                    @else
                        <a href="{{ route('membership.index') }}" class="btn btn-primary w-100">Join Membership to Bid</a>
                    @endif
                @else
                    <p class="text-muted mb-0">Bidding has ended.</p>
                @endif

                <a href="{{ route('auctions.live-room', $auction) }}" class="btn btn-outline-primary w-100 mt-2">Live Room</a>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <h5>Bid History</h5>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>Bidder</th><th>Amount</th><th>Time</th></tr></thead>
                <tbody>
                    @forelse($auction->bids as $bid)
                        <tr>
                            <td>{{ $bid->displayName() }}</td>
                            <td>₱{{ number_format($bid->amount, 2) }}</td>
                            <td>{{ $bid->created_at->format('M j, g:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted">No bids yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
