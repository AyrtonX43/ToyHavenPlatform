@extends('layouts.toyshop')

@section('title', 'Search Results - ToyHaven')

@push('styles')
<style>
    .search-header {
        background: #0f172a;
        color: white;
        padding: 3rem 0;
        margin-bottom: 2rem;
    }
    
    .search-results-container {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
    }
    
    .search-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 2rem;
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 1rem;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .search-tabs::-webkit-scrollbar { display: none; }
    
    .search-tab {
        padding: 0.625rem 1.25rem;
        border-radius: 10px;
        background: transparent;
        border: none;
        color: #64748b;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        white-space: nowrap;
        flex-shrink: 0;
        font-size: 0.875rem;
    }
    
    .search-tab:hover {
        color: #0d9488;
        background: rgba(102, 126, 234, 0.1);
    }
    
    .search-tab.active {
        color: #0d9488;
        background: rgba(102, 126, 234, 0.1);
    }
    
    .search-tab.active::after {
        content: '';
        position: absolute;
        bottom: -1rem;
        left: 0;
        right: 0;
        height: 3px;
        background: #0d9488;
        border-radius: 3px 3px 0 0;
    }
    
    .result-section {
        margin-bottom: 3rem;
    }
    
    .result-section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .result-section-title i {
        color: #0d9488;
    }
    
    .result-count {
        font-size: 0.875rem;
        color: #64748b;
        font-weight: 500;
        margin-left: auto;
    }
    
    .result-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .result-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.15);
    }
    
    .result-image-wrapper {
        position: relative;
        width: 100%;
        height: 200px;
        overflow: hidden;
        background: #f8fafc;
    }
    
    .result-image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .result-card:hover .result-image-wrapper img {
        transform: scale(1.1);
    }
    
    .result-card-body {
        padding: 1.25rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    
    .result-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }
    
    .result-title a {
        color: inherit;
        text-decoration: none;
    }
    
    .result-title a:hover {
        color: #0d9488;
    }
    
    .result-description {
        font-size: 0.875rem;
        color: #64748b;
        margin-bottom: 1rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .result-price {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0d9488;
        margin-top: auto;
    }
    
    .result-badge {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-toyshop {
        background: rgba(102, 126, 234, 0.9);
        color: white;
    }
    
    .badge-trade {
        background: rgba(59, 130, 246, 0.9);
        color: white;
    }
    
    .badge-auction {
        background: rgba(245, 158, 11, 0.9);
        color: white;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
    }
    
    .empty-state-icon {
        font-size: 4rem;
        color: #cbd5e1;
        margin-bottom: 1rem;
    }
    
    .view-all-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #0d9488;
        font-weight: 600;
        text-decoration: none;
        margin-top: 1rem;
    }
    
    .view-all-link:hover {
        color: #0f766e;
        text-decoration: underline;
    }

    /* Responsive */
    @media (max-width: 991px) {
        .search-header { padding: 2rem 0; }
        .search-header h1 { font-size: 1.5rem; }
        .search-results-container { padding: 1.25rem; }
    }
    @media (max-width: 767px) {
        .search-header { padding: 1.5rem 0; margin-bottom: 1rem; }
        .search-header h1 { font-size: 1.25rem; }
        .search-results-container { padding: 1rem; border-radius: 12px; }
        .search-tab { padding: 0.5rem 0.875rem; font-size: 0.8125rem; }
        .result-section-title { font-size: 1.125rem; flex-wrap: wrap; }
        .result-count { margin-left: 0; width: 100%; font-size: 0.8125rem; }
        .result-image-wrapper { height: 160px; }
        .result-card-body { padding: 1rem; }
        .result-title { font-size: 0.9375rem; }
    }
    @media (max-width: 575px) {
        .search-header { padding: 1.25rem 0; }
        .search-header h1 { font-size: 1.125rem; }
        .search-results-container { padding: 0.75rem; border-radius: 10px; }
        .search-tab { padding: 0.4rem 0.75rem; font-size: 0.75rem; border-radius: 8px; }
        .search-tabs { gap: 0.375rem; margin-bottom: 1.25rem; padding-bottom: 0.75rem; }
        .result-image-wrapper { height: 140px; }
        .result-card-body { padding: 0.75rem; }
        .result-title { font-size: 0.875rem; }
        .result-description { font-size: 0.75rem; -webkit-line-clamp: 1; }
        .result-price { font-size: 1rem; }
    }
</style>
@endpush

@section('content')
<div class="search-header">
    <div class="container">
        <h1 class="mb-3">
            <i class="bi bi-search me-2"></i>Search Results
        </h1>
        <p class="mb-0 opacity-90">Found {{ $counts['total'] }} results for "<strong>{{ $query }}</strong>"</p>
    </div>
</div>

<div class="container">
    <div class="search-results-container">
        <div class="search-tabs">
            <button class="search-tab {{ $type === 'all' ? 'active' : '' }}" onclick="filterResults('all')">
                <i class="bi bi-grid me-2"></i>All Results ({{ $counts['total'] }})
            </button>
            <button class="search-tab {{ $type === 'toyshop' ? 'active' : '' }}" onclick="filterResults('toyshop')">
                <i class="bi bi-shop me-2"></i>Toyshop ({{ $counts['toyshop'] }})
            </button>
            <button class="search-tab {{ $type === 'businesses' ? 'active' : '' }}" onclick="filterResults('businesses')">
                <i class="bi bi-building me-2"></i>Stores ({{ $counts['businesses'] }})
            </button>
            <button class="search-tab {{ $type === 'trade' ? 'active' : '' }}" onclick="filterResults('trade')">
                <i class="bi bi-arrow-left-right me-2"></i>Trade ({{ $counts['trade'] }})
            </button>
            <button class="search-tab {{ $type === 'auction' ? 'active' : '' }}" onclick="filterResults('auction')">
                <i class="bi bi-hammer me-2"></i>Auction ({{ $counts['auction'] }})
            </button>
        </div>

        @if($type === 'all' || $type === 'toyshop')
        <div class="result-section" id="toyshop-results">
            <div class="result-section-title">
                <i class="bi bi-shop"></i>
                <span>Toyshop Products</span>
                <span class="result-count">{{ $results['toyshop']->count() }} found</span>
            </div>
            
            @if($results['toyshop']->count() > 0)
                <div class="row g-4">
                    @foreach($results['toyshop'] as $product)
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="result-card reveal">
                                <div class="result-image-wrapper">
                                    @if($product->images->first())
                                        <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" alt="{{ $product->name }}">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100">
                                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                        </div>
                                    @endif
                                    <span class="result-badge badge-toyshop">
                                        <i class="bi bi-shop me-1"></i>Toyshop
                                    </span>
                                </div>
                                <div class="result-card-body">
                                    <h5 class="result-title">
                                        <a href="{{ route('toyshop.products.show', $product->slug) }}">{{ $product->name }}</a>
                                    </h5>
                                    <p class="result-description">{{ Str::limit($product->description, 100) }}</p>
                                    <div class="result-price">₱{{ number_format($product->price, 2) }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($results['toyshop']->count() >= 12)
                    <div class="text-center mt-4">
                        <a href="{{ route('toyshop.products.index', ['search' => $query]) }}" class="view-all-link">
                            View All Toyshop Results <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                @endif
            @else
                <div class="empty-state">
                    <i class="bi bi-inbox empty-state-icon"></i>
                    <h5>No toyshop products found</h5>
                    <p class="text-muted">Try different search terms</p>
                </div>
            @endif
        </div>
        @endif

        @if($type === 'all' || $type === 'businesses')
        <div class="result-section" id="business-results">
            <div class="result-section-title">
                <i class="bi bi-building"></i>
                <span>Stores / Business Pages</span>
                <span class="result-count">{{ $results['businesses']->count() }} found</span>
            </div>

            @if($results['businesses']->count() > 0)
                <div class="row g-4">
                    @foreach($results['businesses'] as $seller)
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="result-card reveal">
                                <div class="result-image-wrapper">
                                    @if($seller->logo)
                                        <img src="{{ asset('storage/' . $seller->logo) }}" alt="{{ $seller->business_name }}">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100 bg-secondary">
                                            <i class="bi bi-shop text-white" style="font-size: 3rem;"></i>
                                        </div>
                                    @endif
                                    <span class="result-badge badge-toyshop">
                                        <i class="bi bi-building me-1"></i>Store
                                    </span>
                                </div>
                                <div class="result-card-body">
                                    <h5 class="result-title">
                                        <a href="{{ route('toyshop.business.show', $seller->business_slug) }}">{{ $seller->business_name }}</a>
                                    </h5>
                                    <p class="result-description">{{ Str::limit($seller->description ?? 'Store on ToyHaven', 100) }}</p>
                                    <a href="{{ route('toyshop.business.show', $seller->business_slug) }}" class="view-all-link mt-2">
                                        View Store <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-inbox empty-state-icon"></i>
                    <h5>No stores found</h5>
                    <p class="text-muted">Try different search terms</p>
                </div>
            @endif
        </div>
        @endif

        @if($type === 'all' || $type === 'trade')
        <div class="result-section" id="trade-results">
            <div class="result-section-title">
                <i class="bi bi-arrow-left-right"></i>
                <span>Trade Listings</span>
                <span class="result-count">{{ $results['trade']->count() }} found</span>
            </div>
            
            @if($results['trade']->count() > 0)
                <div class="row g-4">
                    @foreach($results['trade'] as $listing)
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="result-card reveal">
                                <div class="result-image-wrapper">
                                    @php
                                        $image = $listing->product ? $listing->product->images->first() : ($listing->userProduct ? $listing->userProduct->images->first() : null);
                                    @endphp
                                    @if($image)
                                        <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $listing->title }}">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100">
                                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                        </div>
                                    @endif
                                    <span class="result-badge badge-trade">
                                        <i class="bi bi-arrow-left-right me-1"></i>Trade
                                    </span>
                                </div>
                                <div class="result-card-body">
                                    <h5 class="result-title">
                                        <a href="{{ route('trading.listings.show', $listing->id) }}">{{ $listing->title }}</a>
                                    </h5>
                                    <p class="result-description">{{ Str::limit($listing->description, 100) }}</p>
                                    @if($listing->cash_difference)
                                        <div class="result-price">+₱{{ number_format($listing->cash_difference, 2) }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($results['trade']->count() >= 12)
                    <div class="text-center mt-4">
                        <a href="{{ route('trading.index', ['search' => $query]) }}" class="view-all-link">
                            View All Trade Results <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                @endif
            @else
                <div class="empty-state">
                    <i class="bi bi-inbox empty-state-icon"></i>
                    <h5>No trade listings found</h5>
                    <p class="text-muted">Try different search terms</p>
                </div>
            @endif
        </div>
        @endif

        @if($type === 'all' || $type === 'auction')
        <div class="result-section" id="auction-results">
            <div class="result-section-title">
                <i class="bi bi-hammer"></i>
                <span>Auction Listings</span>
                <span class="result-count">{{ $results['auction']->count() }} found</span>
            </div>
            
            <div class="empty-state">
                <i class="bi bi-hourglass-split empty-state-icon"></i>
                <h5>Auction feature coming soon</h5>
                <p class="text-muted">We're working on bringing you exciting auction features!</p>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function filterResults(type) {
        const url = new URL(window.location.href);
        url.searchParams.set('type', type);
        window.location.href = url.toString();
    }
</script>
@endpush
@endsection
