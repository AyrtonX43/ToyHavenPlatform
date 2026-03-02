@extends('layouts.toyshop')

@section('title', ($pageSettings->page_name ?? $seller->business_name) . ' - ToyHaven')

@push('styles')
<style>
    .business-page { background: #f8fafc; padding: 2rem 0; }
    .business-header-card { border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; }
    .business-logo { max-height: 140px; border-radius: 12px; object-fit: contain; }
    .business-logo-placeholder { width: 140px; height: 140px; background: linear-gradient(135deg, #0ea5e9, #38bdf8); border-radius: 12px; }
    .about-us-text { text-align: justify; line-height: 1.75; }
    .social-link-btn { border-radius: 10px; font-weight: 600; padding: 0.5rem 1rem; transition: all 0.2s; }
    .social-link-btn:hover { transform: translateY(-2px); }
    .stat-card { border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); transition: all 0.2s; }
    .stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
    .product-card-business { border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08); transition: all 0.3s; }
    .product-card-business:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
    .review-card { border-radius: 12px; border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
</style>
@endpush

@section('content')
<div class="business-page">
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
    <div class="card mb-4 business-header-card">
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
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="bi bi-info-circle me-2"></i>About Us</h5>
                <p class="text-muted mb-0 about-us-text">{{ $pageSettings->business_description }}</p>
            </div>
        </div>
    @elseif($seller->description)
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="bi bi-info-circle me-2"></i>About Us</h5>
                <p class="text-muted mb-0 about-us-text">{{ $seller->description }}</p>
            </div>
        </div>
    @endif

    <!-- Social Media Links - from business page settings and registration -->
    @php
        $allSocialLinks = collect();
        $platformsAdded = [];
        if ($socialLinks && $socialLinks->count() > 0) {
            foreach ($socialLinks->where('is_active', true) as $link) {
                $allSocialLinks->push((object)['platform' => $link->platform, 'url' => $link->url, 'display_name' => $link->display_name ?? ucfirst($link->platform), 'icon' => $link->getPlatformIcon()]);
                $platformsAdded[] = $link->platform;
            }
        }
        if ($seller->facebook_url && !in_array('facebook', $platformsAdded)) {
            $allSocialLinks->push((object)['platform' => 'facebook', 'url' => $seller->facebook_url, 'display_name' => 'Facebook', 'icon' => 'bi-facebook']);
        }
        if ($seller->instagram_url && !in_array('instagram', $platformsAdded)) {
            $allSocialLinks->push((object)['platform' => 'instagram', 'url' => $seller->instagram_url, 'display_name' => 'Instagram', 'icon' => 'bi-instagram']);
        }
        if ($seller->tiktok_url && !in_array('tiktok', $platformsAdded)) {
            $allSocialLinks->push((object)['platform' => 'tiktok', 'url' => $seller->tiktok_url, 'display_name' => 'TikTok', 'icon' => 'bi-tiktok']);
        }
        if ($seller->website_url && !in_array('website', $platformsAdded)) {
            $allSocialLinks->push((object)['platform' => 'other', 'url' => $seller->website_url, 'display_name' => 'Website', 'icon' => 'bi-link-45deg']);
        }
    @endphp
    @if($allSocialLinks->count() > 0)
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="bi bi-share me-2"></i>Connect With Us</h5>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($allSocialLinks as $link)
                        <a href="{{ $link->url }}" target="_blank" class="btn social-link-btn {{ $link->platform === 'facebook' ? 'btn-outline-primary' : ($link->platform === 'instagram' ? 'btn-outline-danger' : ($link->platform === 'tiktok' ? 'btn-outline-dark' : 'btn-outline-secondary')) }}" rel="noopener noreferrer">
                            <i class="bi {{ $link->icon }} me-1"></i>
                            {{ $link->display_name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Statistics -->
    <div class="row mb-4 g-3">
        <div class="col-md-3 col-6">
            <div class="card text-center stat-card h-100">
                <div class="card-body py-4">
                    <h4 class="text-primary mb-1">{{ $stats['total_products'] }}</h4>
                    <p class="text-muted mb-0 small fw-semibold">Products</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center stat-card h-100">
                <div class="card-body py-4">
                    <h4 class="text-success mb-1">{{ $stats['total_sales'] }}</h4>
                    <p class="text-muted mb-0 small fw-semibold">Total Sales</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center stat-card h-100">
                <div class="card-body py-4">
                    <h4 class="text-warning mb-1">{{ $stats['rating'] }}</h4>
                    <p class="text-muted mb-0 small fw-semibold">Rating</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center stat-card h-100">
                <div class="card-body py-4">
                    <h4 class="text-info mb-1">{{ $stats['total_reviews'] }}</h4>
                    <p class="text-muted mb-0 small fw-semibold">Reviews</p>
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
                            <div class="card product-card-business h-100" style="cursor: pointer;" onclick="window.location.href='{{ route('toyshop.products.show', $product->slug) }}'">
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
                    <div class="card mb-3 review-card">
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
</div>
@endsection
