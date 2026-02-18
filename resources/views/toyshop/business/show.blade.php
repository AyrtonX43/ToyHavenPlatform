@extends('layouts.toyshop')

@section('title', ($pageSettings->page_name ?? $seller->business_name) . ' - ToyHaven')

@section('content')
<div class="container">
    <!-- Business Banner (from page settings) -->
    @if($pageSettings && $pageSettings->banner_path)
        <div class="mb-3 rounded overflow-hidden">
            <img src="{{ asset('storage/' . $pageSettings->banner_path) }}" 
                 class="img-fluid w-100" 
                 alt="Store banner" 
                 style="max-height: 300px; object-fit: cover;">
        </div>
    @endif

    <!-- Business Header -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    @if($pageSettings && $pageSettings->logo_path)
                        <img src="{{ asset('storage/' . $pageSettings->logo_path) }}" class="img-fluid rounded" alt="{{ $pageSettings->page_name ?? $seller->business_name }}" style="max-height: 150px;">
                    @elseif($seller->logo)
                        <img src="{{ asset('storage/' . $seller->logo) }}" class="img-fluid rounded" alt="{{ $seller->business_name }}" style="max-height: 150px;">
                    @else
                        <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 150px; height: 150px; margin: 0 auto;">
                            <i class="bi bi-shop text-white" style="font-size: 4rem;"></i>
                        </div>
                    @endif
                </div>
                <div class="col-md-10">
                    <h2 style="{{ $pageSettings && $pageSettings->primary_color ? 'color: ' . $pageSettings->primary_color : '' }}">{{ $pageSettings->page_name ?? $seller->business_name }}</h2>
                    <div class="mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi bi-star{{ $i <= $seller->rating ? '-fill text-warning' : '' }}"></i>
                        @endfor
                        <span class="ms-2">{{ $seller->rating }} ({{ $stats['total_reviews'] }} reviews)</span>
                        <span class="badge bg-primary ms-2">{{ $seller->getRankingBadge() }}</span>
                    </div>
                    <p class="mb-0">
                        <i class="bi bi-geo-alt"></i> {{ $seller->city }}, {{ $seller->province }}<br>
                        <i class="bi bi-envelope"></i> {{ $seller->email }}<br>
                        <i class="bi bi-telephone"></i> {{ $seller->phone }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- About Us -->
    @if($pageSettings && ($pageSettings->business_description ?? null))
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">About Us</h5>
                <p class="text-muted mb-0">{{ $pageSettings->business_description }}</p>
            </div>
        </div>
    @elseif($seller->description)
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">About Us</h5>
                <p class="text-muted mb-0">{{ $seller->description }}</p>
            </div>
        </div>
    @endif

    <!-- Social Media Links -->
    @if($socialLinks && $socialLinks->count() > 0)
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Follow Us</h5>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($socialLinks as $link)
                        <a href="{{ $link->url }}" target="_blank" class="btn btn-outline-primary btn-sm" rel="noopener noreferrer">
                            <i class="bi {{ $link->getPlatformIcon() }} me-1"></i>
                            {{ $link->display_name ?? ucfirst($link->platform) }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-primary">{{ $stats['total_products'] }}</h4>
                    <p class="text-muted mb-0">Products</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-success">{{ $stats['total_sales'] }}</h4>
                    <p class="text-muted mb-0">Total Sales</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-warning">{{ $stats['rating'] }}</h4>
                    <p class="text-muted mb-0">Rating</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-info">{{ $stats['total_reviews'] }}</h4>
                    <p class="text-muted mb-0">Reviews</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Products -->
        <div class="col-md-8">
            <h3 class="mb-4">Products</h3>
            @if($products->count() > 0)
                <div class="row g-4">
                    @foreach($products as $product)
                        <div class="col-md-4 mb-4">
                            <div class="card product-card h-100" style="cursor: pointer; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08); transition: all 0.3s ease;" onclick="window.location.href='{{ route('toyshop.products.show', $product->slug) }}'">
                                <div style="height: 200px; overflow: hidden; background: #f8fafc;">
                                    @if($product->images->first())
                                        <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" 
                                             class="card-img-top" 
                                             alt="{{ $product->name }}"
                                             style="height: 200px; width: 100%; object-fit: cover;">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title">{{ Str::limit($product->name, 50) }}</h6>
                                    <p class="text-primary mb-2 fw-bold">₱{{ number_format($product->price, 2) }}</p>
                                    <div class="product-actions mt-auto d-flex gap-1 flex-wrap" onclick="event.stopPropagation();">
                                        <a href="{{ route('toyshop.products.show', $product->slug) }}" class="btn btn-sm btn-primary" onclick="event.stopPropagation();">
                                            <i class="bi bi-eye me-1"></i>View
                                        </a>
                                        @auth
                                            @php
                                                $inWishlist = in_array($product->id, $wishlistProductIds ?? []);
                                                $wishlistId = $inWishlist && isset($wishlistItems[$product->id]) ? $wishlistItems[$product->id]['id'] : null;
                                            @endphp
                                            @if($inWishlist && $wishlistId)
                                                <form action="{{ route('wishlist.remove', $wishlistId) }}" method="POST" class="d-inline" onclick="event.stopPropagation();">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove from Wishlist" onclick="event.stopPropagation();">
                                                        <i class="bi bi-heart-fill"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('wishlist.add') }}" method="POST" class="d-inline" onclick="event.stopPropagation();">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Add to Wishlist" onclick="event.stopPropagation();">
                                                        <i class="bi bi-heart"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('cart.add') }}" method="POST" class="d-inline" onclick="event.stopPropagation();">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="btn btn-sm btn-outline-primary" {{ !$product->isInStock() ? 'disabled' : '' }} title="Add to Cart" onclick="event.stopPropagation();">
                                                    <i class="bi bi-cart-plus"></i>
                                                </button>
                                            </form>
                                        @else
                                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-danger" title="Login to Add to Wishlist" onclick="event.stopPropagation();">
                                                <i class="bi bi-heart"></i>
                                            </a>
                                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary" title="Login to Add to Cart" onclick="event.stopPropagation();">
                                                <i class="bi bi-cart-plus"></i>
                                            </a>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $products->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <p class="mb-0">No products available from this seller.</p>
                </div>
            @endif
        </div>

        <!-- Reviews -->
        <div class="col-md-4">
            <h3 class="mb-4">Recent Reviews</h3>
            @if($recentReviews->count() > 0)
                @foreach($recentReviews as $review)
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <strong>{{ $review->user->name }}</strong>
                                <div>
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $review->overall_rating ? '-fill text-warning' : '' }}"></i>
                                    @endfor
                                </div>
                            </div>
                            @if($review->review_text)
                                <p class="mb-0">{{ Str::limit($review->review_text, 100) }}</p>
                            @endif
                            <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="alert alert-info">
                    <p class="mb-0">No reviews yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
