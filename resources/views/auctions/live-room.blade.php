@extends('layouts.toyshop')

@section('title', 'LIVE: ' . $auction->title)

@push('styles')
<style>
    .live-badge { animation: pulse 2s infinite; }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    .live-room-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 2px solid #e2e8f0;
        overflow: hidden;
    }
    .bid-feed {
        max-height: 400px;
        overflow-y: auto;
        scroll-behavior: smooth;
    }
    .bid-feed-item {
        padding: 0.5rem 0;
        border-bottom: 1px solid #f1f5f9;
        animation: slideIn 0.3s ease;
    }
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .current-price-live {
        font-size: 3rem;
        font-weight: 800;
        color: #0891b2;
        transition: all 0.3s ease;
    }
    .countdown-live {
        font-size: 2rem;
        font-weight: 700;
        color: #dc2626;
        font-family: monospace;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('auctions.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h2 class="fw-bold mb-0">
            <span class="badge bg-danger live-badge me-2">LIVE</span>
            {{ $auction->title }}
        </h2>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="live-room-card">
                <div class="p-3 text-center bg-light">
                    @if($imgUrl = $auction->getPrimaryImageUrl())
                        <img src="{{ $imgUrl }}" alt="{{ $auction->title }}" class="img-fluid rounded" style="max-height: 350px; object-fit: contain;">
                    @endif
                </div>
                <div class="p-4">
                    @if($auction->description)
                        <p class="text-muted mb-3">{{ Str::limit($auction->description, 200) }}</p>
                    @endif
                    @if($auction->box_condition)
                        <span class="badge bg-info me-1">{{ \App\Models\Auction::boxConditionLabel($auction->box_condition) }}</span>
                    @endif
                    @if($auction->category)
                        <span class="badge bg-secondary">{{ $auction->category->name }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="live-room-card p-4">
                <div class="text-center mb-3">
                    <div class="text-muted small mb-1">CURRENT HIGHEST BID</div>
                    <div class="current-price-live" id="currentPrice">
                        ₱{{ number_format($auction->getCurrentPrice(), 2) }}
                    </div>
                    <div class="text-muted" id="bidsCount">{{ $auction->bids_count }} bid(s)</div>
                </div>

                <div class="text-center mb-4">
                    <div class="text-muted small mb-1">TIME REMAINING</div>
                    <div class="countdown-live" id="countdown">
                        @if($auction->end_at)
                            {{ $auction->end_at->diffForHumans() }}
                        @else
                            Waiting to start...
                        @endif
                    </div>
                </div>

                @if($canBid)
                    <form action="{{ route('auctions.bids.store', $auction) }}" method="POST" id="bidForm" class="mb-4">
                        @csrf
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">₱</span>
                            <input type="number" name="amount" id="bidAmount" class="form-control" step="0.01"
                                min="{{ $auction->getMinNextBid() }}" value="{{ $auction->getMinNextBid() }}" required>
                            <button type="submit" class="btn btn-primary fw-bold px-4" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                                <i class="bi bi-hammer me-1"></i>BID
                            </button>
                        </div>
                        <div class="form-text text-center">Min bid: ₱<span id="minBid">{{ number_format($auction->getMinNextBid(), 2) }}</span></div>
                    </form>
                @elseif(!auth()->user() || !auth()->user()->hasActiveMembership())
                    <a href="{{ route('membership.index', ['intent' => 'auction']) }}" class="btn btn-primary btn-lg w-100 mb-4">
                        <i class="bi bi-gem me-1"></i>Join Membership to Bid
                    </a>
                @endif

                <h6 class="fw-bold mb-2"><i class="bi bi-lightning-charge me-1"></i>Live Bid Feed</h6>
                <div class="bid-feed" id="bidFeed">
                    @foreach($auction->bids as $bid)
                        <div class="bid-feed-item d-flex justify-content-between">
                            <span class="text-muted">
                                @if($bid->user_id === auth()->id())
                                    <strong class="text-primary">You</strong>
                                @else
                                    {{ $bid->user?->getAuctionAlias() ?? 'Anonymous' }}
                                @endif
                            </span>
                            <span class="fw-bold">₱{{ number_format($bid->amount, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const auctionId = {{ $auction->id }};
    const endAt = @json($auction->end_at?->toISOString());

    // Countdown timer
    if (endAt) {
        const endDate = new Date(endAt);
        const countdownEl = document.getElementById('countdown');

        function updateCountdown() {
            const now = new Date();
            const diff = endDate - now;

            if (diff <= 0) {
                countdownEl.textContent = 'ENDED';
                countdownEl.style.color = '#6b7280';
                return;
            }

            const hours = Math.floor(diff / 3600000);
            const mins = Math.floor((diff % 3600000) / 60000);
            const secs = Math.floor((diff % 60000) / 1000);

            countdownEl.textContent = `${String(hours).padStart(2, '0')}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;

            if (diff < 60000) {
                countdownEl.style.color = '#dc2626';
                countdownEl.style.animation = 'pulse 1s infinite';
            }
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
    }

    // Real-time bid updates via Echo
    if (typeof Echo !== 'undefined') {
        Echo.channel('auction.' + auctionId)
            .listen('.AuctionBidPlaced', function(e) {
                const priceEl = document.getElementById('currentPrice');
                const countEl = document.getElementById('bidsCount');
                const feedEl = document.getElementById('bidFeed');
                const minBidEl = document.getElementById('minBid');
                const bidAmountEl = document.getElementById('bidAmount');

                if (priceEl && e.amount) {
                    priceEl.textContent = '₱' + parseFloat(e.amount).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }

                if (countEl && e.bids_count) {
                    countEl.textContent = e.bids_count + ' bid(s)';
                }

                if (minBidEl && e.min_next_bid) {
                    minBidEl.textContent = parseFloat(e.min_next_bid).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    if (bidAmountEl) {
                        bidAmountEl.min = e.min_next_bid;
                        bidAmountEl.value = e.min_next_bid;
                    }
                }

                if (feedEl) {
                    const item = document.createElement('div');
                    item.className = 'bid-feed-item d-flex justify-content-between';
                    item.innerHTML = `<span class="text-muted">${e.user_alias || 'Anonymous'}</span><span class="fw-bold">₱${parseFloat(e.amount).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>`;
                    feedEl.insertBefore(item, feedEl.firstChild);
                }
            })
            .listen('.AuctionEnded', function(e) {
                const countdownEl = document.getElementById('countdown');
                if (countdownEl) {
                    countdownEl.textContent = 'ENDED';
                }
                const form = document.getElementById('bidForm');
                if (form) form.style.display = 'none';
            });
    }
});
</script>
@endpush
@endsection
