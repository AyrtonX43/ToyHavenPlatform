@extends('layouts.toyshop')

@section('title', 'Seller Stats - ToyHaven Auction')

@section('content')
<div class="container py-4 pb-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auction.index') }}">Auction Hub</a></li>
            <li class="breadcrumb-item"><a href="{{ route('auction.seller.dashboard') }}">Seller Dashboard</a></li>
            <li class="breadcrumb-item active">Seller Stats</li>
        </ol>
    </nav>

    <h2 class="mb-4"><i class="bi bi-graph-up me-2"></i>Seller Statistics</h2>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card bg-success text-white shadow-sm h-100 border-0">
                <div class="card-body">
                    <h6 class="card-title text-white-50"><i class="bi bi-cash-coin me-2"></i>Total Revenue</h6>
                    <h3 class="mb-0">₱{{ number_format($stats['total_revenue'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="card bg-primary text-white shadow-sm h-100 border-0">
                <div class="card-body">
                    <h6 class="card-title text-white-50"><i class="bi bi-box-seam me-2"></i>Items Sold</h6>
                    <h3 class="mb-0">{{ number_format($stats['items_sold'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="card bg-warning text-dark shadow-sm h-100 border-0">
                <div class="card-body">
                    <h6 class="card-title text-dark-50"><i class="bi bi-truck me-2"></i>Pending Shipment</h6>
                    <h3 class="mb-0">{{ number_format($stats['pending_shipment'] ?? 0) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card bg-info text-white shadow-sm h-100 border-0">
                <div class="card-body">
                    <h6 class="card-title text-white-50"><i class="bi bi-list-ul me-2"></i>Active Listings</h6>
                    <h3 class="mb-0">{{ number_format($stats['active_listings'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center py-5 bg-light rounded">
        <i class="bi bi-bar-chart fs-1 text-muted mb-3 d-block"></i>
        <p class="text-muted">More detailed analytics coming in a future update.</p>
    </div>
</div>
@endsection
