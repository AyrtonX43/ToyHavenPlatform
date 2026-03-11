@extends('layouts.toyshop')

@section('title', 'Auction Hub - ToyHaven')

@push('styles')
<link href="{{ asset('css/auction.css') }}" rel="stylesheet">
<style>
    .auction-listing-card { transition: transform 0.2s ease; }
    .auction-listing-card:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(2, 132, 199, 0.15); }
    .live-badge { display: inline-flex; align-items: center; gap: 4px; font-size: .65rem; font-weight: 700; color: #ef4444; text-transform: uppercase; letter-spacing: .5px; }
    .live-dot { width: 6px; height: 6px; border-radius: 50%; background: #ef4444; animation: livePulse 1.5s infinite; }
    @keyframes livePulse { 0%,100% { opacity: 1; } 50% { opacity: .4; } }
    .card-countdown { font-size: .8rem; font-weight: 600; }
    .card-countdown.urgent { color: #ef4444; }
</style>
@endpush

@section('content')
<div class="auction-hero">
    <div class="container text-center">
        <h1 class="mb-2 fw-bold">
            <i class="bi bi-hammer me-2"></i>Auction Hub
        </h1>
        <p class="mb-0 opacity-90">Welcome, {{ auth()->user()->name }}. Your membership gives you access to auctions.</p>
    </div>
</div>

<div class="container py-4 pb-5">
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <h4 class="mb-3"><i class="bi bi-list-ul me-2"></i>Active Listings</h4>
            @if($activeListings->count() > 0)
                <div class="row g-3">
                    @foreach($activeListings as $a)
                        <div class="col-md-6">
                            <a href="{{ route('auction.show', $a) }}" class="text-decoration-none text-dark">
                                <div class="card h-100 auction-card auction-listing-card border-0">
                                    @php $primaryImg = $a->images->firstWhere('is_primary', true) ?? $a->images->first(); @endphp
                                    @if($primaryImg)
                                        <div class="position-relative">
                                            <img src="{{ asset('storage/' . $primaryImg->image_path) }}" alt="{{ Str::limit($a->title, 50) }}" class="card-img-top auction-card-img">
                                            @if($a->isActive())
                                                <span class="position-absolute top-0 start-0 m-2 px-2 py-1 rounded-pill bg-white shadow-sm live-badge">
                                                    <span class="live-dot"></span> LIVE
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                    <div class="card-body auction-card-body">
                                        <h6 class="card-title fw-semibold">{{ Str::limit($a->title, 50) }}</h6>
                                        <p class="mb-1"><strong>Current bid:</strong> ₱{{ number_format($a->winning_amount ?? $a->starting_bid, 2) }}</p>
                                        <p class="mb-1 small text-muted">{{ $a->bids_count }} bid{{ $a->bids_count !== 1 ? 's' : '' }}</p>
                                        @if($a->end_at)
                                            <p class="mb-0 card-countdown" x-data="auctionCardTimer('{{ $a->end_at->toIso8601String() }}')" x-text="text" :class="{ 'urgent': urgent }"></p>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3">{{ $activeListings->links() }}</div>
            @else
                <div class="auction-empty">
                    <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                    <p class="text-muted mb-0">No active auctions at the moment. Check back soon for new listings from sellers.</p>
                </div>
            @endif

            @if(isset($pendingListings) && $pendingListings->count() > 0)
                <h5 class="mb-3 mt-4"><i class="bi bi-hourglass-split me-2"></i>Pending Approval</h5>
                <p class="small text-muted mb-2">These listings are awaiting approval. Save them to get notified when they go live.</p>
                <div class="row g-3">
                    @foreach($pendingListings as $a)
                        <div class="col-md-6 col-lg-4">
                            <a href="{{ route('auction.show', $a) }}" class="text-decoration-none text-dark">
                                <div class="card h-100 auction-card border-0 border-2 border-warning">
                                    @php $primaryImg = $a->images->firstWhere('is_primary', true) ?? $a->images->first(); @endphp
                                    @if($primaryImg)
                                        <img src="{{ asset('storage/' . $primaryImg->image_path) }}" alt="{{ Str::limit($a->title, 40) }}" class="card-img-top auction-card-img" style="height:140px;">
                                    @endif
                                    <div class="card-body auction-card-body">
                                        <h6 class="card-title">{{ Str::limit($a->title, 40) }}</h6>
                                        <p class="mb-0 small"><strong>Starting:</strong> ₱{{ number_format($a->starting_bid, 2) }}</p>
                                        <span class="badge bg-warning text-dark mt-1">Pending</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="col-lg-4">
            @php
                $user = auth()->user();
                $plan = $user->currentPlan();
                $isVip = $plan && (($plan->can_register_individual_seller ?? false) || ($plan->can_register_business_seller ?? false));
                $hasBusiness = $user->hasApprovedBusinessAuctionSeller();
                $hasIndividual = $user->hasApprovedIndividualAuctionSeller();
                $isApprovedSeller = $hasIndividual || $hasBusiness;
            @endphp

            @if($isApprovedSeller)
                <h4 class="mb-3"><i class="bi bi-speedometer2 me-2"></i>Seller Tools</h4>
                <div class="d-flex flex-column gap-2 mb-4">
                    <a href="{{ route('auction.seller.dashboard') }}" class="auction-shortcut">
                        <i class="bi bi-grid fs-4 me-2 text-primary"></i>
                        <strong>Seller Dashboard</strong>
                        <span class="d-block small text-muted mt-1">Manage your auction business</span>
                    </a>
                    @if($isVip)
                        <a href="{{ route('auction.listings.create') }}" class="auction-shortcut">
                            <i class="bi bi-plus-circle fs-4 me-2 text-primary"></i>
                            <strong>Add Auction Listing</strong>
                            <span class="d-block small text-muted mt-1">Create a new auction</span>
                        </a>
                    @endif
                </div>
            @endif

            @if(!$hasBusiness)
                <h4 class="mb-3"><i class="bi bi-person-badge me-2"></i>Become a Seller</h4>
                @if($isVip)
                    <div class="d-flex flex-column gap-2 mb-4">
                        @if(!$hasIndividual)
                            <a href="{{ route('auction.seller-registration.individual') }}" class="auction-shortcut">
                                <i class="bi bi-person fs-4 me-2 text-primary"></i>
                                <strong>Individual Seller</strong>
                                <span class="d-block small text-muted mt-1">Register with ID and bank docs</span>
                            </a>
                        @endif
                        <a href="{{ route('auction.seller-registration.business') }}" class="auction-shortcut">
                            <i class="bi bi-shop fs-4 me-2 text-primary"></i>
                            <strong>Business Seller</strong>
                            <span class="d-block small text-muted mt-1">Register your business store</span>
                        </a>
                    </div>
                @else
                    <div class="card border-2 border-warning mb-4">
                        <div class="card-body text-center py-4">
                            <i class="bi bi-gem text-warning fs-1 mb-2"></i>
                            <h6 class="mb-2">Upgrade to VIP</h6>
                            <p class="small text-muted mb-3">Auction seller registration is available for VIP members only.</p>
                            <a href="{{ route('membership.upgrade', 'vip') }}" class="btn btn-warning btn-sm">Upgrade to VIP</a>
                        </div>
                    </div>
                @endif
            @endif

            <h4 class="mb-3"><i class="bi bi-link-45deg me-2"></i>Shortcuts</h4>
            <div class="d-flex flex-column gap-2">
                <a href="{{ route('auction.history') }}" class="auction-shortcut">
                    <i class="bi bi-clock-history fs-4 me-2 text-primary"></i>
                    <strong>Auction History</strong>
                </a>
                <a href="{{ route('auction.bids') }}" class="auction-shortcut">
                    <i class="bi bi-tag fs-4 me-2 text-primary"></i>
                    <strong>My Bids</strong>
                </a>
                <a href="{{ route('auction.saved') }}" class="auction-shortcut">
                    <i class="bi bi-bookmark-star fs-4 me-2 text-primary"></i>
                    <strong>Saved Auctions</strong>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function auctionCardTimer(endAtIso) {
    return {
        text: '',
        urgent: false,
        interval: null,
        init() {
            this.update();
            this.interval = setInterval(() => this.update(), 1000);
        },
        update() {
            const diff = new Date(endAtIso).getTime() - Date.now();
            if (diff <= 0) {
                this.text = 'Ended';
                this.urgent = false;
                if (this.interval) clearInterval(this.interval);
                return;
            }
            this.urgent = diff <= 300000;
            const d = Math.floor(diff / 86400000);
            const h = Math.floor((diff % 86400000) / 3600000);
            const m = Math.floor((diff % 3600000) / 60000);
            const s = Math.floor((diff % 60000) / 1000);
            let parts = [];
            if (d > 0) parts.push(d + 'd');
            if (h > 0) parts.push(h + 'h');
            parts.push(m + 'm');
            parts.push(s + 's');
            this.text = 'Ends in ' + parts.join(' ');
        },
        destroy() {
            if (this.interval) clearInterval(this.interval);
        }
    };
}
</script>
@endpush
