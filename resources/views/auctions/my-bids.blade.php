@extends('layouts.toyshop')

@section('title', 'My Bids - Auctions')

@push('styles')
<style>
    .bid-card {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        margin-bottom: 1rem;
    }
    .bid-card.won {
        border-left: 4px solid #10b981;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <h1 class="h3 fw-bold mb-4"><i class="bi bi-hammer me-2"></i>My Bids</h1>

    @if($wonAuctions->isNotEmpty())
        <div class="mb-4">
            <h5 class="fw-bold mb-3 text-success"><i class="bi bi-trophy me-1"></i>Won Auctions</h5>
            @foreach($wonAuctions as $auction)
                <div class="bid-card won">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <a href="{{ route('auctions.show', $auction) }}" class="fw-semibold text-decoration-none">{{ $auction->title }}</a>
                            <p class="text-muted small mb-0">Won at ₱{{ number_format($auction->winning_amount ?? $auction->getCurrentPrice(), 2) }}</p>
                        </div>
                        <a href="{{ route('auctions.show', $auction) }}" class="btn btn-sm btn-outline-primary">View</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <h5 class="fw-bold mb-3">All Bids</h5>
    @forelse($myBids as $bid)
        <div class="bid-card">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <a href="{{ route('auctions.show', $bid->auction) }}" class="fw-semibold text-decoration-none">{{ $bid->auction->title }}</a>
                    <p class="text-muted small mb-0">
                        Your bid: ₱{{ number_format($bid->amount, 2) }}
                        @if($bid->is_winning && !$bid->auction->isEnded())
                            <span class="badge bg-success">Winning</span>
                        @elseif($bid->auction->isEnded())
                            <span class="text-muted">· Ended</span>
                        @endif
                    </p>
                </div>
                <a href="{{ route('auctions.show', $bid->auction) }}" class="btn btn-sm btn-outline-primary">View Auction</a>
            </div>
        </div>
    @empty
        <p class="text-muted">You haven't placed any bids yet. <a href="{{ route('auctions.index') }}">Browse auctions</a></p>
    @endforelse

    <div class="mt-3">
        {{ $myBids->links() }}
    </div>
</div>
@endsection
