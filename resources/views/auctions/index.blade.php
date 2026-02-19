@extends('layouts.toyshop')

@section('title', 'Auctions - ToyHaven')

@push('styles')
<style>
    .auctions-header {
        background: linear-gradient(135deg, #0891b2 0%, #06b6d4 50%, #fb923c 100%);
        border-radius: 16px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
        box-shadow: 0 8px 32px rgba(8, 145, 178, 0.3);
    }
    .auction-card {
        background: white;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        border: 2px solid #e2e8f0;
        transition: all 0.25s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .auction-card:hover {
        border-color: #0891b2;
        box-shadow: 0 8px 24px rgba(8, 145, 178, 0.12);
        transform: translateY(-4px);
    }
    .auction-card.teaser {
        opacity: 0.9;
        position: relative;
    }
    .auction-card.teaser::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, transparent 50%, rgba(0,0,0,0.3) 100%);
        pointer-events: none;
    }
    .auction-image-wrap {
        height: 200px;
        background: #f8fafc;
        overflow: hidden;
    }
    .auction-image-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .auction-price {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0891b2;
    }
    .members-only-badge {
        background: #f59e0b;
        color: white;
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="auctions-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-hammer me-2"></i>Live Auctions</h1>
            <p class="mb-0 opacity-90">Bid on rare toys and collectibles</p>
        </div>
        @if(!$hasMembership)
            <a href="{{ route('membership.index', ['intent' => 'auction']) }}" class="btn btn-light btn-lg fw-bold" style="color: #0891b2;">
                <i class="bi bi-gem me-1"></i>Join Membership to Bid
            </a>
        @else
            <a href="{{ route('auctions.my-bids') }}" class="btn btn-light btn-lg fw-bold" style="color: #0891b2;">
                <i class="bi bi-list-check me-1"></i>My Bids
            </a>
        @endif
    </div>

    @if($auctions->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-hammer text-muted" style="font-size: 4rem;"></i>
            <p class="mt-3 text-muted">No live auctions at the moment. Check back soon!</p>
            @if(!$hasMembership)
                <a href="{{ route('membership.index') }}" class="btn btn-primary mt-2" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">Join Membership</a>
            @endif
        </div>
    @else
        <div class="row g-4">
            @foreach($auctions as $auction)
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('auctions.show', $auction) }}" class="text-decoration-none text-dark">
                        <div class="auction-card {{ !$hasMembership ? 'teaser' : '' }}">
                            <div class="auction-image-wrap">
                                @if($imgUrl = $auction->getPrimaryImageUrl())
                                    <img src="{{ $imgUrl }}" alt="{{ $auction->title }}">
                                @else
                                    <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                        <i class="bi bi-image" style="font-size: 3rem;"></i>
                                    </div>
                                @endif
                                @if($auction->is_members_only)
                                    <span class="members-only-badge position-absolute top-0 end-0 m-2">Members Only</span>
                                @endif
                            </div>
                            <div class="p-3 flex-grow-1 d-flex flex-column">
                                <h5 class="fw-bold mb-2 text-truncate" title="{{ $auction->title }}">{{ $auction->title }}</h5>
                                <div class="auction-price mb-2">
                                    ₱{{ number_format($auction->getCurrentPrice(), 0) }}
                                    @if($auction->bids_count > 0)
                                        <small class="text-muted fw-normal">({{ $auction->bids_count }} bid{{ $auction->bids_count !== 1 ? 's' : '' }})</small>
                                    @endif
                                </div>
                                <div class="mt-auto text-muted small">
                                    <i class="bi bi-clock me-1"></i>Ends {{ $auction->end_at?->diffForHumans() ?? 'TBD' }}
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        <div class="d-flex justify-content-center mt-4">
            {{ $auctions->links() }}
        </div>
    @endif
</div>
@endsection
