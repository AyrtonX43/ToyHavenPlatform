@extends('layouts.toyshop')

@section('title', 'My Wishlist - ToyHaven')

@push('styles')
<style>
    .wishlist-header {
        background: white;
        border-radius: 14px;
        padding: 1.5rem 2rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        border: 1px solid #f0e6dc;
        margin-bottom: 2rem;
    }
    
    .wishlist-header h2 {
        font-size: 1.375rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: #2d2a26;
    }
    
    .wishlist-item-card {
        border: 1px solid #f0e6dc;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: white;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .wishlist-item-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    }
    
    .wishlist-image-wrapper {
        position: relative;
        overflow: hidden;
        background: #fff;
        height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem;
    }
    
    .wishlist-image-wrapper img {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
        object-position: center;
        transition: transform 0.35s ease;
    }
    
    .wishlist-item-card:hover .wishlist-image-wrapper img {
        transform: scale(1.05);
    }
    
    .wishlist-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 10;
    }
    
    .wishlist-card-body {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    
    .wishlist-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2d2a26;
        margin-bottom: 0.75rem;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .wishlist-description {
        font-size: 0.875rem;
        color: #64748b;
        margin-bottom: 1rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .wishlist-price-section {
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
    }
    
    .wishlist-price {
        font-size: 1.5rem;
        font-weight: 700;
        color: #ff6b6b;
        margin-bottom: 0.5rem;
    }
    
    .wishlist-original-price {
        font-size: 0.875rem;
        color: #94a3b8;
        text-decoration: line-through;
    }
    
    .wishlist-rating {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
    }
    
    .wishlist-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
    }
    
    .wishlist-actions .btn {
        flex: 1;
        border-radius: 10px;
        font-weight: 600;
        padding: 0.75rem;
    }
    
    .empty-wishlist {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }
    
    .empty-wishlist-icon {
        font-size: 5rem;
        color: #cbd5e1;
        margin-bottom: 1.5rem;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="wishlist-header reveal">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-heart-fill text-danger me-2"></i>My Wishlist</h2>
                <p class="text-muted mb-0">Save your favorite products for later</p>
            </div>
            <div>
                <span class="badge bg-primary px-3 py-2" style="font-size: 0.875rem; font-weight: 600;">
                    {{ $wishlists->total() }} items
                </span>
            </div>
        </div>
    </div>

    @if($wishlists->count() > 0)
        <div class="row g-4">
            @foreach($wishlists as $wishlist)
                @if(!$wishlist->product)
                    @continue
                @endif
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card wishlist-item-card reveal" style="animation-delay: {{ min($loop->index, 5) * 0.1 }}s;">
                        <div class="wishlist-image-wrapper">
                            @if($wishlist->product->images->first())
                                <a href="{{ route('toyshop.products.show', $wishlist->product->slug) }}">
                                    <img src="{{ asset('storage/' . $wishlist->product->images->first()->image_path) }}" 
                                         alt="{{ $wishlist->product->name }}">
                                </a>
                            @else
                                <div class="d-flex align-items-center justify-content-center h-100 bg-white w-100">
                                    <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                </div>
                            @endif
                            
                            @if($wishlist->product->isInStock())
                                <span class="badge bg-success wishlist-badge">
                                    <i class="bi bi-check-circle me-1"></i>In Stock
                                </span>
                            @else
                                <span class="badge bg-danger wishlist-badge">
                                    <i class="bi bi-x-circle me-1"></i>Out of Stock
                                </span>
                            @endif
                            
                            @if($wishlist->product->amazon_reference_price && $wishlist->product->getPriceDifferencePercentage())
                                <span class="badge bg-warning wishlist-badge" style="top: 50px;">
                                    <i class="bi bi-tag-fill me-1"></i>Save {{ number_format(abs($wishlist->product->getPriceDifferencePercentage()), 0) }}%
                                </span>
                            @endif
                        </div>
                        
                        <div class="wishlist-card-body">
                            <h5 class="wishlist-title">
                                <a href="{{ route('toyshop.products.show', $wishlist->product->slug) }}" class="text-decoration-none text-dark">
                                    {{ $wishlist->product->name }}
                                </a>
                            </h5>
                            
                            <p class="wishlist-description">{{ Str::limit($wishlist->product->description, 100) }}</p>
                            
                            <div class="wishlist-rating">
                                <div class="rating-stars" style="color: #fbbf24; font-size: 0.875rem;">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $wishlist->product->rating ? '-fill' : '' }}"></i>
                                    @endfor
                                </div>
                                <span class="text-muted" style="font-size: 0.875rem;">({{ $wishlist->product->reviews_count }})</span>
                            </div>
                            
                            <div class="wishlist-price-section">
                                <div class="wishlist-price">₱{{ number_format($wishlist->product->price, 2) }}</div>
                                @if($wishlist->product->amazon_reference_price)
                                    <div class="wishlist-original-price">₱{{ number_format($wishlist->product->amazon_reference_price, 2) }}</div>
                                @endif
                                
                                <div class="wishlist-actions">
                                    <a href="{{ route('toyshop.products.show', $wishlist->product->slug) }}" class="btn btn-primary">
                                        <i class="bi bi-eye me-1"></i>View
                                    </a>
                                    @if($wishlist->product->isInStock())
                                        <form action="{{ route('cart.add') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $wishlist->product->id }}">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn btn-outline-primary" title="Add to Cart">
                                                <i class="bi bi-cart-plus"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                
                                <form action="{{ route('wishlist.remove', $wishlist->id) }}" method="POST" class="mt-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger w-100 btn-sm">
                                        <i class="bi bi-heart-break me-1"></i>Remove from Wishlist
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $wishlists->links() }}
        </div>
    @else
        <div class="empty-wishlist reveal">
            <i class="bi bi-heart empty-wishlist-icon"></i>
            <h4 class="fw-bold mb-2">Your wishlist is empty</h4>
            <p class="text-muted mb-4">Start adding products to your wishlist to save them for later!</p>
            <a href="{{ route('toyshop.products.index') }}" class="btn btn-primary btn-lg">
                <i class="bi bi-bag me-2"></i>Browse Products
            </a>
        </div>
    @endif
</div>
@endsection
