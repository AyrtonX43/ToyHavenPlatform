@extends('layouts.toyshop')

@section('title', 'Trading Marketplace - ToyHaven')

@push('styles')
<style>
    .marketplace-header {
        background: white;
        border-radius: 14px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        margin-bottom: 1.5rem;
    }
    
    .marketplace-header h2 {
        font-size: 1.375rem;
        font-weight: 700;
        color: #1e293b;
        letter-spacing: -0.02em;
        margin-bottom: 0.25rem;
    }
    
    .marketplace-header .text-muted {
        font-size: 0.9375rem;
    }
    
    .filter-tabs {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-top: 1.25rem;
    }
    
    .filter-tab {
        padding: 0.5rem 1rem;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #64748b;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    
    .filter-tab:hover {
        background: #fff5f3;
        border-color: #0891b2;
        color: #0891b2;
    }
    
    .filter-tab.active {
        background: #0891b2;
        border-color: #0891b2;
        color: white;
    }
    
    .product-card-marketplace {
        background: white;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        transition: box-shadow 0.25s ease, border-color 0.2s ease, transform 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        cursor: pointer;
    }
    
    .product-card-marketplace:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        border-color: #e2e8f0;
        transform: translateY(-2px);
    }
    
    .product-image-wrapper {
        position: relative;
        width: 100%;
        height: 240px;
        overflow: hidden;
        background: #f8fafc;
    }
    
    .product-image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.35s ease;
    }
    
    .product-card-marketplace:hover .product-image-wrapper img {
        transform: scale(1.05);
    }
    
    .product-badge {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        background: #0891b2;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .product-card-body {
        padding: 1.25rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    
    .product-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.5rem;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .product-price {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0891b2;
        margin-bottom: 0.5rem;
    }
    
    .product-location {
        font-size: 0.875rem;
        color: #64748b;
        margin-bottom: 0.75rem;
    }
    
    .product-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: auto;
        padding-top: 0.75rem;
        border-top: 1px solid #e2e8f0;
        font-size: 0.8125rem;
        color: #64748b;
    }
    
    .btn-marketplace {
        border-radius: 10px;
        font-weight: 600;
        padding: 0.5rem 1.25rem;
        font-size: 0.875rem;
    }
    
    .btn-marketplace-primary {
        background: #0891b2;
        border: none;
        color: white;
    }
    
    .btn-marketplace-primary:hover {
        background: #0e7490;
        color: white;
    }
    
    .btn-marketplace-outline {
        background: white;
        border: 1px solid #e2e8f0;
        color: #475569;
    }
    
    .btn-marketplace-outline:hover {
        background: #f8fafc;
        border-color: #0891b2;
        color: #0891b2;
    }
    
    .sidebar-filters {
        background: white;
        border-radius: 14px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        position: sticky;
        top: 90px;
        max-height: calc(100vh - 120px);
        overflow-y: auto;
    }
    
    .sidebar-filters h5 {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 1.25rem;
    }
    
    .filter-section {
        margin-bottom: 1.5rem;
    }
    
    .filter-section-title {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 0.5rem;
    }
    
    .form-control-marketplace {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .form-control-marketplace:focus {
        border-color: #0891b2;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 14px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
    }
    
    .empty-state-icon {
        font-size: 3.5rem;
        color: #cbd5e1;
        margin-bottom: 1.25rem;
    }
    
    .empty-state h4 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
    }
    
    .create-listing-card {
        background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
        border-radius: 14px;
        padding: 2rem;
        color: white;
        text-align: center;
        margin-bottom: 2rem;
        border: none;
    }
    
    .create-listing-card h3 {
        color: white;
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .create-listing-card p {
        color: rgba(255,255,255,0.92);
        margin-bottom: 1.5rem;
        font-size: 0.9375rem;
    }
    
    .create-listing-card .btn-light {
        border-radius: 10px;
        font-weight: 600;
        padding: 0.625rem 1.5rem;
    }

    /* Responsive */
    @media (max-width: 991px) {
        .sidebar-filters { position: static; max-height: none; margin-bottom: 1.5rem; }
    }
    @media (max-width: 767px) {
        .marketplace-header { padding: 1.25rem; border-radius: 12px; }
        .marketplace-header h2 { font-size: 1.1875rem; }
        .filter-tabs { gap: 0.375rem; }
        .filter-tab { padding: 0.375rem 0.75rem; font-size: 0.8125rem; }
        .product-image-wrapper { height: 180px; }
        .product-card-body { padding: 0.875rem; }
        .product-title { font-size: 0.875rem; }
        .product-price { font-size: 1.0625rem; }
        .product-meta { font-size: 0.75rem; padding-top: 0.5rem; }
        .sidebar-filters { padding: 1.25rem; border-radius: 12px; }
        .create-listing-card { padding: 1.5rem; border-radius: 12px; }
        .create-listing-card h5 { font-size: 1.0625rem; }
    }
    @media (max-width: 575px) {
        .marketplace-header { padding: 1rem; }
        .marketplace-header h2 { font-size: 1.0625rem; }
        .product-image-wrapper { height: 160px; }
        .product-card-body { padding: 0.625rem 0.75rem; }
        .product-title { font-size: 0.8125rem; -webkit-line-clamp: 1; }
        .product-price { font-size: 0.9375rem; }
        .product-location { font-size: 0.75rem; margin-bottom: 0.5rem; }
        .product-meta { font-size: 0.6875rem; }
        .btn-marketplace { padding: 0.375rem 0.875rem; font-size: 0.8125rem; }
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-4"></i>
            <div>
                <strong>Success!</strong> {{ session('success') }}
                <p class="mb-0 small mt-1">Your product will be processed for admin approval. You will receive a notification by email once it has been reviewed.</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <!-- Marketplace Header -->
    <div class="marketplace-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-0">Trading Marketplace</h2>
                <p class="text-muted mb-0">Trade your toys and collectibles with other collectors</p>
            </div>
            <div class="col-md-4 text-end">
                @auth
                <a href="{{ route('trading.listings.create') }}" class="btn btn-marketplace btn-marketplace-primary me-2">
                    <i class="bi bi-plus-circle me-1"></i>Create Listing
                </a>
                <a href="{{ route('trading.listings.my') }}" class="btn btn-outline-secondary">My Listings</a>
                @else
                <a href="{{ route('login') }}" class="btn btn-marketplace btn-marketplace-primary">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Login to Trade
                </a>
                @endauth
            </div>
        </div>
        
        <!-- Filter Tabs -->
        <div class="filter-tabs mt-3">
            <a href="{{ route('trading.index') }}" class="filter-tab {{ !request('trade_type') ? 'active' : '' }}">
                All Items
            </a>
            <a href="{{ route('trading.index', ['trade_type' => 'barter']) }}" class="filter-tab {{ request('trade_type') == 'barter' ? 'active' : '' }}">
                Barter
            </a>
            <a href="{{ route('trading.index', ['trade_type' => 'barter_with_cash']) }}" class="filter-tab {{ request('trade_type') == 'barter_with_cash' ? 'active' : '' }}">
                Barter + Cash
            </a>
            <a href="{{ route('trading.index', ['trade_type' => 'cash']) }}" class="filter-tab {{ request('trade_type') == 'cash' ? 'active' : '' }}">
                Cash
            </a>
        </div>
    </div>

    @auth
    @if($suggestedListings->isNotEmpty())
    <div class="mb-4">
        <h5 class="fw-bold mb-3 text-dark">Suggested for you</h5>
        <div class="row g-3">
            @foreach($suggestedListings as $listing)
            <div class="col-lg-3 col-md-4 col-6">
                <a href="{{ route('trading.listings.show', $listing->id) }}" class="text-decoration-none">
                    <div class="product-card-marketplace">
                        <div class="product-image-wrapper">
                            @php
                                $item = $listing->getItem();
                                $primaryImage = $listing->images->first() ?? ($item ? ($item->images->first() ?? null) : null);
                                if (!$primaryImage && $listing->image_path) {
                                    $primaryImage = (object)['image_path' => $listing->image_path];
                                }
                            @endphp
                            @if($primaryImage)
                            <img src="{{ asset('storage/' . $primaryImage->image_path) }}" alt="{{ $listing->title }}">
                            @else
                            <div class="d-flex align-items-center justify-content-center h-100">
                                <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                            </div>
                            @endif
                            <div class="product-badge">{{ ucfirst(str_replace('_', ' ', $listing->trade_type)) }}</div>
                        </div>
                        <div class="product-card-body">
                            <div class="product-title">{{ Str::limit($listing->title, 40) }}</div>
                            @if($item)
                            <div class="product-price small">
                                @if($item instanceof \App\Models\Product)
                                    ₱{{ number_format($item->price, 0) }}
                                @elseif($item instanceof \App\Models\UserProduct && $item->estimated_value)
                                    ₱{{ number_format($item->estimated_value, 0) }}
                                @else
                                    Trade
                                @endif
                            </div>
                            @elseif($listing->cash_difference)
                            <div class="product-price small">₱{{ number_format($listing->cash_difference, 0) }}</div>
                            @endif
                            <div class="product-meta">
                                <span><i class="bi bi-chat-dots me-1"></i>{{ $listing->offers_count }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endauth

    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="sidebar-filters">
                <h5 class="mb-3">Filters</h5>
                <form method="GET" action="{{ route('trading.index') }}" id="filterForm">
                    @if(request('trade_type'))
                    <input type="hidden" name="trade_type" value="{{ request('trade_type') }}">
                    @endif
                    @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @if(request('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                    @endif
                    
                    <div class="filter-section">
                        <div class="filter-section-title">Category</div>
                        <select name="category_id" class="form-select form-control-marketplace">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="filter-section">
                        <div class="filter-section-title">Condition</div>
                        <select name="condition" class="form-select form-control-marketplace">
                            <option value="">All Conditions</option>
                            <option value="new" {{ request('condition') == 'new' ? 'selected' : '' }}>New</option>
                            <option value="used" {{ request('condition') == 'used' ? 'selected' : '' }}>Used</option>
                            <option value="refurbished" {{ request('condition') == 'refurbished' ? 'selected' : '' }}>Refurbished</option>
                        </select>
                    </div>
                    
                    <div class="filter-section">
                        <div class="filter-section-title">Sort By</div>
                        <select name="sort" class="form-select form-control-marketplace">
                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                            <option value="most_offers" {{ request('sort') == 'most_offers' ? 'selected' : '' }}>Most Offers</option>
                            <option value="most_views" {{ request('sort') == 'most_views' ? 'selected' : '' }}>Most Views</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-marketplace btn-marketplace-primary">
                            Apply Filters
                        </button>
                        <a href="{{ route('trading.index') }}" class="btn btn-marketplace btn-marketplace-outline">
                            Clear All
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-lg-9 col-md-8">
            @auth
            @if($listings->count() == 0 && !request()->hasAny(['search', 'category_id', 'condition', 'trade_type']))
            <div class="create-listing-card">
                <h3><i class="bi bi-megaphone me-2"></i>Start Trading Today!</h3>
                <p>List your toys and collectibles to trade with other collectors</p>
                <a href="{{ route('trading.listings.create') }}" class="btn btn-light btn-lg">
                    <i class="bi bi-plus-circle me-2"></i>Create Your First Listing
                </a>
            </div>
            @endif
            @endauth

            @if($listings->count() > 0)
            <div class="row g-3">
                @foreach($listings as $listing)
                <div class="col-lg-4 col-md-6">
                    <a href="{{ route('trading.listings.show', $listing->id) }}" class="text-decoration-none">
                        <div class="product-card-marketplace">
                            <div class="product-image-wrapper">
                                @php
                                    $item = $listing->getItem();
                                    $primaryImage = $listing->images->first() ?? ($item ? ($item->images->first() ?? null) : null);
                                    if (!$primaryImage && $listing->image_path) {
                                        $primaryImage = (object)['image_path' => $listing->image_path];
                                    }
                                @endphp
                                @if($primaryImage)
                                <img src="{{ asset('storage/' . $primaryImage->image_path) }}" alt="{{ $listing->title }}">
                                @else
                                <div class="d-flex align-items-center justify-content-center h-100">
                                    <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                </div>
                                @endif
                                <div class="product-badge">
                                    {{ ucfirst(str_replace('_', ' ', $listing->trade_type)) }}
                                </div>
                            </div>
                            <div class="product-card-body">
                                <div class="product-title">{{ $listing->title }}</div>
                                @if($item)
                                <div class="product-price">
                                    @if($item instanceof \App\Models\Product)
                                        ₱{{ number_format($item->price, 2) }}
                                    @elseif($item instanceof \App\Models\UserProduct && $item->estimated_value)
                                        ₱{{ number_format($item->estimated_value, 2) }}
                                    @else
                                        Trade Only
                                    @endif
                                </div>
                                @elseif($listing->cash_difference)
                                <div class="product-price">₱{{ number_format($listing->cash_difference, 2) }}</div>
                                @endif
                                <div class="product-location">
                                    <i class="bi bi-geo-alt me-1"></i>{{ $listing->user->city ?? 'Philippines' }}
                                </div>
                                <div class="product-meta">
                                    <span><i class="bi bi-eye me-1"></i>{{ $listing->views_count }} views</span>
                                    <span><i class="bi bi-chat-dots me-1"></i>{{ $listing->offers_count }} offers</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-center">
                {{ $listings->links() }}
            </div>
            @else
            <div class="empty-state">
                <i class="bi bi-inbox empty-state-icon"></i>
                <h4 class="text-muted">No listings found</h4>
                <p class="text-muted">Try adjusting your filters or create a new listing</p>
                @auth
                <a href="{{ route('trading.listings.create') }}" class="btn btn-marketplace btn-marketplace-primary mt-3">
                    <i class="bi bi-plus-circle me-1"></i>Create Listing
                </a>
                @endauth
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('filterForm');
    if (form) {
        form.querySelectorAll('select').forEach(function(select) {
            select.addEventListener('change', function() {
                form.submit();
            });
        });
    }
});
</script>
@endpush
@endsection
