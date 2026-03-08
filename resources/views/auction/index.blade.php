@extends('layouts.toyshop')

@section('title', 'Auction Hub - ToyHaven')

@push('styles')
<style>
    .auction-hero {
        background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 40%, #0284c7 100%);
        color: white;
        padding: 3rem 0;
        margin-bottom: 2rem;
        border-radius: 0 0 24px 24px;
        box-shadow: 0 12px 40px rgba(2, 132, 199, 0.2);
    }
    .auction-shortcut {
        display: block;
        padding: 1.25rem;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        text-decoration: none !important;
        color: inherit;
        transition: all 0.25s ease;
    }
    .auction-shortcut:hover {
        border-color: #0284c7;
        background: #f0f9ff;
        color: inherit;
    }
    .auction-shortcut.coming-soon {
        opacity: 0.7;
        cursor: not-allowed;
    }
    .auction-shortcut.coming-soon:hover {
        border-color: #e2e8f0;
        background: #f8fafc;
    }
    .empty-state {
        padding: 3rem 2rem;
        text-align: center;
        background: #f8fafc;
        border-radius: 16px;
        border: 2px dashed #e2e8f0;
    }
    .hover-shadow:hover { box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1); }
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
                                <div class="card h-100 shadow-sm border-0 overflow-hidden hover-shadow">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ Str::limit($a->title, 50) }}</h6>
                                        <p class="mb-1"><strong>Current bid:</strong> ₱{{ number_format($a->winning_amount ?? $a->starting_bid, 2) }}</p>
                                        <p class="mb-1 small text-muted">{{ $a->bids_count }} bid(s)</p>
                                        <p class="mb-0 small text-primary">Ends {{ $a->end_at?->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3">{{ $activeListings->links() }}</div>
            @else
                <div class="empty-state">
                    <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                    <p class="text-muted mb-0">No active auctions at the moment. Check back soon for new listings from sellers.</p>
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
                <a href="#" class="auction-shortcut coming-soon">
                    <i class="bi bi-clock-history fs-4 me-2 text-primary"></i>
                    <strong>Auction History</strong>
                    <span class="badge bg-secondary ms-2">Coming Soon</span>
                </a>
                <a href="#" class="auction-shortcut coming-soon">
                    <i class="bi bi-tag fs-4 me-2 text-primary"></i>
                    <strong>My Bids</strong>
                    <span class="badge bg-secondary ms-2">Coming Soon</span>
                </a>
                <a href="#" class="auction-shortcut coming-soon">
                    <i class="bi bi-bookmark-star fs-4 me-2 text-primary"></i>
                    <strong>Saved Auctions</strong>
                    <span class="badge bg-secondary ms-2">Coming Soon</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
