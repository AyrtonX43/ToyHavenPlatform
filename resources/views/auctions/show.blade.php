@extends('layouts.toyshop')

@section('title', $auction->title . ' - Auctions')

@push('styles')
<style>
    .auction-detail-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 2px solid #e2e8f0;
        overflow: hidden;
    }
    .auction-main-image {
        width: 100%;
        max-height: 400px;
        object-fit: contain;
        background: #f8fafc;
    }
    .current-price {
        font-size: 2rem;
        font-weight: 800;
        color: #0891b2;
    }
    .bid-list {
        max-height: 300px;
        overflow-y: auto;
    }
    .countdown-badge {
        background: linear-gradient(135deg, #0891b2, #06b6d4);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 12px;
        font-weight: 700;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auctions.index') }}">Auctions</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($auction->title, 40) }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="auction-detail-card">
                <div class="p-3 bg-light border-bottom">
                    @if($auction->is_members_only)
                        <span class="badge bg-warning text-dark me-2">Members Only</span>
                    @endif
                    <span class="countdown-badge">
                        <i class="bi bi-clock me-1"></i>
                        @if($auction->isEnded())
                            Ended {{ $auction->end_at?->format('M j, Y H:i') ?? 'N/A' }}
                        @else
                            Ends {{ $auction->end_at?->diffForHumans() ?? 'TBD' }}
                        @endif
                    </span>
                </div>
                <div class="p-3 text-center">
                    @if($imgUrl = $auction->getPrimaryImageUrl())
                        <img src="{{ $imgUrl }}" alt="{{ $auction->title }}" class="auction-main-image">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 300px;">
                            <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                        </div>
                    @endif
                </div>
                <div class="p-4">
                    <h1 class="h3 fw-bold mb-3">{{ $auction->title }}</h1>
                    @if($auction->description)
                        <div class="mb-4">
                            {!! nl2br(e($auction->description)) !!}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="auction-detail-card p-4">
                <div class="current-price mb-2">
                    ₱{{ number_format($auction->getCurrentPrice(), 2) }}
                </div>
                <p class="text-muted small mb-3">
                    {{ $auction->bids_count }} bid(s) · Min bid: ₱{{ number_format($auction->getMinNextBid(), 2) }}
                </p>

                @if($canBid)
                    <form action="{{ route('auctions.bids.store', $auction) }}" method="POST" class="mb-4">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Your Bid (₱)</label>
                            <input type="number" name="amount" class="form-control form-control-lg" step="0.01" min="{{ $auction->getMinNextBid() }}" value="{{ $auction->getMinNextBid() }}" required>
                            @error('amount')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                            <i class="bi bi-hammer me-1"></i>Place Bid
                        </button>
                    </form>
                @elseif(!auth()->user() || !auth()->user()->hasActiveMembership())
                    <a href="{{ route('membership.index', ['intent' => 'auction']) }}" class="btn btn-primary w-100 btn-lg mb-4" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                        <i class="bi bi-gem me-1"></i>Join Membership to Bid
                    </a>
                @elseif($auction->isEnded())
                    <p class="text-muted mb-0">This auction has ended.</p>
                    @if($auction->winner_id && $auction->winner_id === auth()->id())
                        <p class="text-success fw-bold mt-2"><i class="bi bi-trophy me-1"></i>You won this auction!</p>
                    @endif
                @elseif($auction->user_id === auth()->id())
                    <p class="text-muted mb-0">You cannot bid on your own auction.</p>
                @endif

                <h6 class="fw-bold mt-4 mb-2">Bid History</h6>
                <div class="bid-list">
                    @forelse($auction->bids as $bid)
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span>
                                @if($bid->user_id === auth()->id())
                                    <strong>You</strong>
                                @else
                                    {{ Str::mask($bid->user?->name ?? 'Anonymous', '*', 2, 2) }}
                                @endif
                            </span>
                            <span class="fw-semibold">₱{{ number_format($bid->amount, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-muted small">No bids yet. Be the first!</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@if($canBid)
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Echo !== 'undefined') {
            Echo.channel('auction.{{ $auction->id }}')
                .listen('.AuctionBidPlaced', (e) => {
                    // Refresh page or update UI when new bid is placed
                    if (e.amount) {
                        const priceEl = document.querySelector('.current-price');
                        if (priceEl) priceEl.textContent = '₱' + parseFloat(e.amount).toLocaleString('en-PH', {minimumFractionDigits: 2});
                    }
                    location.reload(); // Simple approach; could use dynamic updates
                });
        }
    });
</script>
@endpush
@endif
@endsection
