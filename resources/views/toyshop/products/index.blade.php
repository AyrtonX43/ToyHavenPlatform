@extends('layouts.toyshop')

@section('title', 'Products - ToyHaven')

@push('styles')
<style>
    .filter-sidebar {
        position: sticky;
        top: 90px;
        max-height: calc(100vh - 120px);
        overflow-y: auto;
    }
    
    .filter-card {
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    
    .filter-card .card-header {
        background: #fefcf8;
        color: #1e293b;
        border-bottom: 2px solid #e2e8f0;
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        font-size: 1rem;
    }
    
    .filter-card .card-body {
        padding: 1.5rem;
    }
    
    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }
    
    .form-select, .form-control {
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }
    
    .form-select:focus, .form-control:focus {
        border-color: #0891b2;
        box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.2);
    }
    
    .products-header {
        background: #fff;
        border-radius: 16px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        border: 2px solid #e2e8f0;
        margin-bottom: 1.5rem;
    }
    
    .products-header h2 {
        font-size: 1.375rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: #1e293b;
    }
    
    /* Recommended section */
    .recommended-section {
        margin-bottom: 2rem;
    }
    
    .recommended-section .recommended-header {
        background: linear-gradient(135deg, #fff5f0 0%, #fefcf8 100%);
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.25rem;
    }
    
    .recommended-section .recommended-header h5 {
        font-size: 1.125rem;
        font-weight: 800;
        color: #1e293b;
        letter-spacing: -0.01em;
        margin-bottom: 0.25rem;
    }
    
    .recommended-section .recommended-header .small {
        font-size: 0.875rem;
        color: #64748b;
    }
    
    .recommended-section .product-card {
        border: 2px solid #e2e8f0;
    }
    
    .recommended-section .product-card-body {
        padding: 1rem 1.25rem;
    }
    
    .product-card {
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        transition: box-shadow 0.25s ease, border-color 0.2s ease, transform 0.2s ease;
        background: #fff;
        height: 100%;
        display: flex;
        flex-direction: column;
        cursor: pointer;
        position: relative;
    }
    
    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0,0,0,0.08);
        border-color: #a5f3fc;
    }
    
    .product-card .product-actions,
    .product-card .product-actions * {
        position: relative;
        z-index: 10;
    }
    
    .product-image-wrapper {
        position: relative;
        overflow: hidden;
        background: #fff;
        height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem;
    }
    
    .product-image-wrapper img {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
        object-position: center;
        transition: transform 0.35s ease;
    }
    
    .product-card:hover .product-image-wrapper img {
        transform: scale(1.05);
    }
    
    /* Recommended block: smaller image area, same centering */
    .recommended-image-wrapper {
        height: 220px !important;
    }
    
    .recommended-image-placeholder {
        min-height: 220px !important;
    }
    
    .product-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 10;
        pointer-events: none;
    }
    
    .product-card-body {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    
    .product-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.75rem;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .product-description {
        font-size: 0.875rem;
        color: #64748b;
        margin-bottom: 1rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .product-price-section {
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
    }
    
    .product-price {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0891b2;
        margin-bottom: 0.5rem;
    }
    
    .product-original-price {
        font-size: 0.875rem;
        color: #94a3b8;
        text-decoration: line-through;
    }
    
    .product-rating {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
    }
    
    .rating-stars {
        color: #fbbf24;
        font-size: 0.875rem;
    }
    
    .rating-text {
        font-size: 0.875rem;
        color: #64748b;
    }
    
    .product-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
    }
    
    .product-actions .btn {
        flex: 1;
        border-radius: 10px;
        font-weight: 600;
        padding: 0.75rem;
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
    
    .pagination-wrapper {
        margin-top: 2.5rem;
    }
    
    .page-link {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        margin: 0 0.15rem;
        color: #0891b2;
        font-weight: 500;
    }
    
    .page-link:hover {
        background: #0891b2;
        border-color: #0891b2;
        color: white;
    }
    
    .page-item.active .page-link {
        background: #0891b2;
        border-color: #0891b2;
    }
    
    /* Tablet */
    @media (max-width: 991px) {
        .filter-card .card-header { padding: 1rem 1.25rem; }
        .filter-card .card-body { padding: 1.25rem; }
        .products-header { padding: 1.25rem; }
        .products-header h2 { font-size: 1.25rem; }
    }

    @media (max-width: 768px) {
        .filter-sidebar {
            position: static;
            margin-bottom: 1.5rem;
        }
        
        .products-header {
            padding: 1rem 1.25rem;
        }
        
        .recommended-section .recommended-header {
            padding: 1rem 1.25rem;
        }
        
        .product-image-wrapper {
            height: 220px;
        }
        
        .recommended-image-wrapper {
            height: 160px !important;
        }
        
        .recommended-image-placeholder {
            min-height: 160px !important;
        }

        .product-card-body { padding: 0.875rem 1rem; }
        .product-title { font-size: 0.9375rem; margin-bottom: 0.5rem; }
        .product-description { font-size: 0.8125rem; margin-bottom: 0.75rem; }
        .product-price { font-size: 1.25rem; }
        .product-actions .btn { padding: 0.5rem 0.625rem; font-size: 0.8125rem; }
    }

    /* Small phones */
    @media (max-width: 575px) {
        .product-image-wrapper {
            height: 180px;
        }
        .recommended-image-wrapper {
            height: 140px !important;
        }
        .recommended-image-placeholder {
            min-height: 140px !important;
        }
        .product-card-body { padding: 0.625rem 0.75rem; }
        .product-title { font-size: 0.8125rem; line-height: 1.3; }
        .product-description { display: none; }
        .product-price { font-size: 1.0625rem; margin-bottom: 0.25rem; }
        .product-original-price { font-size: 0.75rem; }
        .product-rating { margin-bottom: 0.375rem; }
        .product-rating .rating-stars { font-size: 0.6875rem; }
        .product-rating .rating-text { font-size: 0.6875rem; }
        .product-price-section { padding-top: 0.5rem; }
        .product-actions { gap: 0.25rem; margin-top: 0.5rem; }
        .product-actions .btn { padding: 0.375rem 0.5rem; font-size: 0.75rem; border-radius: 8px; }

        .products-header { padding: 0.875rem 1rem; border-radius: 12px; margin-bottom: 1rem; }
        .products-header h2 { font-size: 1.125rem; }
        .filter-card { border-radius: 12px; }
        .empty-state { padding: 2.5rem 1.25rem; }
        .empty-state-icon { font-size: 2.5rem; }
        .empty-state h4 { font-size: 1.0625rem; }
    }

    /* Extra small phones */
    @media (max-width: 399px) {
        .product-image-wrapper { height: 150px; }
        .product-card-body { padding: 0.5rem 0.625rem; }
        .product-title { font-size: 0.75rem; }
        .product-price { font-size: 0.9375rem; }
        .product-actions .btn { padding: 0.3rem 0.4rem; font-size: 0.6875rem; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3 py-lg-4 px-2 px-sm-3 px-lg-4">
    <div class="row g-3 g-lg-4">
        <!-- Sidebar Filters -->
        <div class="col-xl-2 col-lg-3 col-md-4 mb-3 mb-lg-0">
            <div class="filter-sidebar">
                <div class="card filter-card reveal">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-funnel-fill me-2"></i>Filter Products</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('toyshop.products.index') }}" id="filterForm">
                            <!-- Category -->
                            <div class="mb-4">
                                <label class="form-label"><i class="bi bi-tags me-1"></i>Category</label>
                                <select name="category" class="form-select">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Price Range -->
                            <div class="mb-4">
                                <label class="form-label"><i class="bi bi-currency-dollar me-1"></i>Price Range</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" name="min_price" class="form-control" value="{{ request('min_price') }}" min="0" placeholder="Min">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="max_price" class="form-control" value="{{ request('max_price') }}" min="0" placeholder="Max">
                                    </div>
                                </div>
                            </div>

                            <!-- Sort -->
                            <div class="mb-4">
                                <label class="form-label"><i class="bi bi-sort-down me-1"></i>Sort By</label>
                                <select name="sort" class="form-select">
                                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                                    <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Highest Rated</option>
                                    <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Most Popular</option>
                                </select>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Apply Filters
                                </button>
                                <a href="{{ route('toyshop.products.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-counterclockwise me-2"></i>Reset Filters
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-xl-10 col-lg-9 col-md-8">
            @if(isset($recommendedProducts) && $recommendedProducts->count() > 0)
                <div class="recommended-section reveal">
                    <div class="recommended-header">
                        <h5 class="mb-0"><i class="bi bi-heart-fill text-danger me-2"></i>Recommended for you</h5>
                        <p class="text-muted mb-0 small">Based on your preferred toy categories</p>
                    </div>
                    <div class="row g-3">
                        @foreach($recommendedProducts as $recProduct)
                            <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                                <div class="card product-card h-100 reveal" style="animation-delay: {{ min($loop->index, 4) * 0.05 }}s;" onclick="window.location.href='{{ route('toyshop.products.show', $recProduct->slug) }}'">
                                    <div class="product-image-wrapper recommended-image-wrapper">
                                        @if($recProduct->images->first())
                                            <img src="{{ asset('storage/' . $recProduct->images->first()->image_path) }}" alt="{{ $recProduct->name }}">
                                        @else
                                            <div class="d-flex align-items-center justify-content-center bg-white align-self-stretch w-100 recommended-image-placeholder">
                                                <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                                            </div>
                                        @endif
                                        @if($recProduct->isInStock())
                                            <span class="badge bg-success product-badge" style="font-size: 0.7rem;"><i class="bi bi-check-circle me-1"></i>In Stock</span>
                                        @endif
                                    </div>
                                    <div class="product-card-body">
                                        <h6 class="product-title" style="-webkit-line-clamp: 2; font-size: 1rem;">{{ Str::limit($recProduct->name, 50) }}</h6>
                                        <div class="product-price mb-2" style="font-size: 1.1rem;">₱{{ number_format($recProduct->price, 2) }}</div>
                                        <a href="{{ route('toyshop.products.show', $recProduct->slug) }}" class="btn btn-sm btn-primary w-100" onclick="event.stopPropagation();">View</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="products-header reveal">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h2 class="fw-bold mb-1" style="color: #1e293b;">Discover Products</h2>
                        <p class="text-muted mb-0">Find the perfect toys and collectibles</p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-primary px-3 py-2" style="font-size: 0.8125rem; font-weight: 600; border-radius: 8px;">
                            <i class="bi bi-box-seam me-1"></i>{{ $products->total() }} {{ Str::plural('product', $products->total()) }}
                        </span>
                    </div>
                </div>
            </div>

            @if($products->count() > 0)
                <div class="row g-3 g-lg-4">
                    @foreach($products as $product)
                        <div class="col-6 col-md-4 col-lg-4 col-xl-3">
                            <div class="card product-card reveal h-100" 
                                 style="animation-delay: {{ min($loop->index, 5) * 0.1 }}s;"
                                 onclick="window.location.href='{{ route('toyshop.products.show', $product->slug) }}'">
                                <div class="product-image-wrapper">
                                    @if($product->images->first())
                                        <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" 
                                             alt="{{ $product->name }}">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center bg-white align-self-stretch w-100" style="min-height: 300px;">
                                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                        </div>
                                    @endif
                                    
                                    @if($product->isInStock())
                                        <span class="badge bg-success product-badge">
                                            <i class="bi bi-check-circle me-1"></i>In Stock
                                        </span>
                                    @else
                                        <span class="badge bg-danger product-badge">
                                            <i class="bi bi-x-circle me-1"></i>Out of Stock
                                        </span>
                                    @endif
                                    
                                    @php
                                        $isDiscounted = $product->amazon_reference_price && $product->price < $product->amazon_reference_price;
                                        $discountPercentage = $isDiscounted ? $product->getPriceDifferencePercentage() : null;
                                    @endphp
                                    @if($isDiscounted && $discountPercentage)
                                        <span class="badge bg-warning product-badge" style="top: 50px;">
                                            <i class="bi bi-tag-fill me-1"></i>Save {{ number_format(abs($discountPercentage), 0) }}%
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="product-card-body">
                                    <h5 class="product-title">
                                        {{ $product->name }}
                                    </h5>
                                    
                                    <p class="product-description">{{ Str::limit($product->description, 100) }}</p>
                                    
                                    @php
                                        $rating = $product->rating ?? 0;
                                        $reviewsCount = $product->reviews_count ?? 0;
                                    @endphp
                                    @if($reviewsCount > 0)
                                        <div class="product-rating">
                                            <div class="rating-stars">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="bi bi-star{{ $i <= $rating ? '-fill' : '' }}"></i>
                                                @endfor
                                            </div>
                                            <span class="rating-text">({{ $reviewsCount }})</span>
                                        </div>
                                    @else
                                        <div class="product-rating">
                                            <div class="rating-stars text-muted">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="bi bi-star"></i>
                                                @endfor
                                            </div>
                                            <span class="rating-text text-muted">No reviews yet</span>
                                        </div>
                                    @endif
                                    
                                    <div class="product-price-section">
                                        @php
                                            $isDiscounted = $product->amazon_reference_price && $product->price < $product->amazon_reference_price;
                                        @endphp
                                        
                                        @if($isDiscounted)
                                            <div class="product-price">₱{{ number_format($product->price, 2) }}</div>
                                            <div class="product-original-price">₱{{ number_format($product->amazon_reference_price, 2) }}</div>
                                        @else
                                            <div class="product-price">₱{{ number_format($product->price, 2) }}</div>
                                        @endif
                                        
                                        <div class="product-actions" onclick="event.stopPropagation();">
                                            <a href="{{ route('toyshop.products.show', $product->slug) }}" class="btn btn-primary" onclick="event.stopPropagation();">
                                                <i class="bi bi-eye me-1"></i>View
                                            </a>
                                            @auth
                                                @php
                                                    $inWishlist = in_array($product->id, $wishlistProductIds ?? []);
                                                    $wishlistId = $inWishlist && isset($wishlistItems[$product->id]) ? $wishlistItems[$product->id]['id'] : null;
                                                @endphp
                                                
                                                @if($inWishlist && $wishlistId)
                                                    <form action="{{ route('wishlist.remove', $wishlistId) }}" method="POST" class="d-inline" onclick="event.stopPropagation();" id="wishlist-form-{{ $product->id }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Remove from Wishlist" onclick="event.stopPropagation();">
                                                            <i class="bi bi-heart-fill"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('wishlist.add') }}" method="POST" class="d-inline" onclick="event.stopPropagation();" id="wishlist-form-{{ $product->id }}">
                                                        @csrf
                                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                        <button type="submit" class="btn btn-outline-danger" title="Add to Wishlist" onclick="event.stopPropagation();">
                                                            <i class="bi bi-heart"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                <form action="{{ route('cart.add') }}" method="POST" class="d-inline" onclick="event.stopPropagation();">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" class="btn btn-outline-primary" {{ !$product->isInStock() ? 'disabled' : '' }} title="Add to Cart" onclick="event.stopPropagation();">
                                                        <i class="bi bi-cart-plus"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <a href="{{ route('login') }}" class="btn btn-outline-danger" title="Login to Add to Wishlist" onclick="event.stopPropagation();">
                                                    <i class="bi bi-heart"></i>
                                                </a>
                                                <a href="{{ route('login') }}" class="btn btn-outline-primary" title="Login to Add to Cart" onclick="event.stopPropagation();">
                                                    <i class="bi bi-box-arrow-in-right"></i>
                                                </a>
                                            @endauth
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="pagination-wrapper">
                    {{ $products->links() }}
                </div>
            @else
                <div class="empty-state reveal">
                    <i class="bi bi-inbox empty-state-icon"></i>
                    <h4 class="fw-bold mb-2">No products found</h4>
                    <p class="text-muted mb-4">Try adjusting your filters or search terms to find what you're looking for.</p>
                    <a href="{{ route('toyshop.products.index') }}" class="btn btn-primary">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>Clear All Filters
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
