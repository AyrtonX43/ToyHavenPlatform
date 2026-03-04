@extends('layouts.toyshop')

@section('title', $displayName . ' - Auction Seller - ToyHaven')

@push('styles')
<style>
    .abp-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0e7490 100%);
        border-radius: 20px;
        padding: 2.5rem;
        color: white;
        margin-bottom: 2rem;
    }
    .abp-hero h1 { font-size: 1.75rem; font-weight: 800; margin-bottom: 0.5rem; }
    .abp-stats {
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
        margin-top: 1.5rem;
    }
    .abp-stat { text-align: center; }
    .abp-stat-num { font-size: 1.5rem; font-weight: 800; color: #38bdf8; }
    .abp-stat-label { font-size: 0.85rem; opacity: 0.9; }
    .abp-auction-card {
        border-radius: 14px;
        overflow: hidden;
        border: 2px solid #e2e8f0;
        transition: all 0.3s;
    }
    .abp-auction-card:hover {
        border-color: #0891b2;
        box-shadow: 0 8px 24px rgba(8, 145, 178, 0.15);
    }
    .abp-auction-img {
        height: 180px;
        background: #f8fafc;
        overflow: hidden;
    }
    .abp-auction-img img { width: 100%; height: 100%; object-fit: cover; }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="abp-hero">
        <h1><i class="bi bi-building me-2"></i>{{ $displayName }}</h1>
        <p class="mb-0 opacity-90">Auction seller on ToyHaven</p>
        <div class="abp-stats">
            <div class="abp-stat">
                <div class="abp-stat-num">{{ $stats['total_auctions'] }}</div>
                <div class="abp-stat-label">Total Auctions</div>
            </div>
            <div class="abp-stat">
                <div class="abp-stat-num">{{ $stats['live_count'] }}</div>
                <div class="abp-stat-label">Live Now</div>
            </div>
            <div class="abp-stat">
                <div class="abp-stat-num">{{ $stats['ended_count'] }}</div>
                <div class="abp-stat-label">Completed</div>
            </div>
        </div>
    </div>

    @if($liveAuctions->isNotEmpty())
        <h3 class="h4 fw-bold mb-3"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Live Auctions</h3>
        <div class="row g-4 mb-5">
            @foreach($liveAuctions as $auction)
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('auctions.show', $auction) }}" class="text-decoration-none text-dark">
                        <div class="card abp-auction-card h-100">
                            <div class="abp-auction-img">
                                @if($imgUrl = $auction->getPrimaryImageUrl())
                                    <img src="{{ $imgUrl }}" alt="{{ $auction->title }}">
                                @else
                                    <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                        <i class="bi bi-image" style="font-size: 2.5rem;"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body">
                                <span class="badge bg-success mb-2">Live</span>
                                <h6 class="fw-bold mb-2 text-truncate" title="{{ $auction->title }}">{{ $auction->title }}</h6>
                                <div class="fw-bold text-primary">₱{{ number_format($auction->getCurrentPrice(), 0) }}</div>
                                @if($auction->end_at)
                                    <small class="text-muted">Ends {{ $auction->end_at->format('M d, H:i') }}</small>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    @if($endedAuctions->isNotEmpty())
        <h3 class="h4 fw-bold mb-3"><i class="bi bi-check2-circle text-secondary me-2"></i>Past Auctions</h3>
        <div class="row g-4">
            @foreach($endedAuctions as $auction)
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('auctions.show', $auction) }}" class="text-decoration-none text-dark">
                        <div class="card abp-auction-card h-100">
                            <div class="abp-auction-img">
                                @if($imgUrl = $auction->getPrimaryImageUrl())
                                    <img src="{{ $imgUrl }}" alt="{{ $auction->title }}">
                                @else
                                    <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                        <i class="bi bi-image" style="font-size: 2.5rem;"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body">
                                <span class="badge bg-secondary mb-2">Ended</span>
                                <h6 class="fw-bold mb-2 text-truncate" title="{{ $auction->title }}">{{ $auction->title }}</h6>
                                <div class="fw-bold text-success">₱{{ number_format($auction->winning_amount ?? $auction->getCurrentPrice(), 0) }}</div>
                                @if($auction->winner)
                                    <small class="text-muted">Sold</small>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    @if($liveAuctions->isEmpty() && $endedAuctions->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3 fw-bold text-muted">No auctions yet</h4>
            <p class="text-muted">This seller has not listed any auctions.</p>
        </div>
    @endif
</div>
@endsection
