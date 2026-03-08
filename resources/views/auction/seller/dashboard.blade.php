@extends('layouts.toyshop')

@section('title', 'Auction Seller Dashboard - ToyHaven')

@push('styles')
<style>
    .auction-hero {
        background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 40%, #0284c7 100%);
        color: white;
        padding: 2rem 0;
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
</style>
@endpush

@section('content')
<div class="auction-hero">
    <div class="container text-center">
        <h1 class="mb-2 fw-bold">
            <i class="bi bi-speedometer2 me-2"></i>Auction Seller Dashboard
        </h1>
        <p class="mb-0 opacity-90">Manage your auction business and listings</p>
    </div>
</div>

<div class="container py-4 pb-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auction.index') }}">Auction</a></li>
            <li class="breadcrumb-item active">Seller Dashboard</li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php
        $plan = auth()->user()->currentPlan();
        $isVip = $plan && (($plan->can_register_individual_seller ?? false) || ($plan->can_register_business_seller ?? false));
    @endphp

    <h4 class="mb-3"><i class="bi bi-tools me-2"></i>Business Tools</h4>
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-4">
            @if($isVip)
                <a href="{{ route('auction.listings.create') }}" class="auction-shortcut">
                    <i class="bi bi-plus-circle fs-4 me-2 text-primary"></i>
                    <strong>Add Auction Listing</strong>
                    <span class="d-block small text-muted mt-1">Create a new auction</span>
                </a>
            @else
                <div class="auction-shortcut coming-soon">
                    <i class="bi bi-plus-circle fs-4 me-2 text-primary"></i>
                    <strong>Add Auction Listing</strong>
                    <span class="badge bg-secondary ms-2">VIP Only</span>
                    <span class="d-block small text-muted mt-1">Upgrade to VIP to create listings</span>
                </div>
            @endif
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('auction.listings.index') }}" class="auction-shortcut">
                <i class="bi bi-list-ul fs-4 me-2 text-primary"></i>
                <strong>My Listings</strong>
                <span class="d-block small text-muted mt-1">View and manage your auction listings</span>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="#" class="auction-shortcut coming-soon">
                <i class="bi bi-graph-up fs-4 me-2 text-primary"></i>
                <strong>Seller Stats</strong>
                <span class="badge bg-secondary ms-2">Coming Soon</span>
                <span class="d-block small text-muted mt-1">View your auction analytics</span>
            </a>
        </div>
    </div>

    <a href="{{ route('auction.index') }}" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left me-1"></i>Back to Auction Hub
    </a>
</div>
@endsection
