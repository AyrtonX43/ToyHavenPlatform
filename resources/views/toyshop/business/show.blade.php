@extends('layouts.toyshop')

@section('title', ($pageSettings->page_name ?? $seller->business_name) . ' - ToyHaven')

@push('styles')
<style>
    /* Business page - professional layout with original brand colors */
    .business-page { background: linear-gradient(180deg, #f1f5f9 0%, #ffffff 120px); min-height: 100vh; padding: 0 0 3rem; }
    .business-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
        color: #fff;
        padding: 2rem 0 2.5rem;
        margin-bottom: -1.5rem;
        position: relative;
    }
    .business-logo-wrap {
        width: 100px; height: 100px;
        border-radius: 14px;
        overflow: hidden;
        border: 3px solid rgba(255,255,255,0.2);
        background: rgba(255,255,255,0.08);
        flex-shrink: 0;
    }
    .business-logo-wrap img { width: 100%; height: 100%; object-fit: cover; }
    .business-logo-wrap .placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 2rem; opacity: 0.5; }
    .business-name { font-size: 1.5rem; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 0.5rem; }
    .business-meta { font-size: 0.875rem; opacity: 0.85; }
    .business-meta i { opacity: 0.7; margin-right: 0.35rem; }
    .rating-stars { color: #fbbf24; font-size: 0.95rem; }
    .rating-badge { background: rgba(251, 191, 36, 0.2); color: #fbbf24; padding: 0.2rem 0.5rem; border-radius: 8px; font-size: 0.75rem; font-weight: 600; }
    .about-us-text { text-align: justify; line-height: 1.75; color: #475569; font-size: 0.95rem; }
    .section-card { background: #fff; border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.04); overflow: hidden; }
    .section-card .card-body { padding: 1.5rem 1.5rem; }
    .section-title { font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem; }
    .social-icon-btn {
        width: 42px; height: 42px;
        border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.1rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .social-icon-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.12); }
    .social-icon-btn.facebook { background: #1877f2; color: #fff; }
    .social-icon-btn.instagram { background: linear-gradient(135deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); color: #fff; }
    .social-icon-btn.tiktok { background: #000; color: #fff; }
    .social-icon-btn.other { background: #64748b; color: #fff; }
    .social-icon-btn.twitter { background: #1da1f2; color: #fff; }
    .social-icon-btn.youtube { background: #ff0000; color: #fff; }
    .stat-box {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        padding: 1.25rem 1rem;
        text-align: center;
        transition: all 0.2s;
    }
    .stat-box:hover { border-color: #0ea5e9; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.1); }
    .stat-box .value { font-size: 1.4rem; font-weight: 700; letter-spacing: -0.02em; }
    .stat-box .label { font-size: 0.8rem; color: #64748b; font-weight: 500; margin-top: 0.25rem; }
    .product-grid-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        transition: all 0.25s ease;
        cursor: pointer;
    }
    .product-grid-card:hover { border-color: #0ea5e9; box-shadow: 0 8px 24px rgba(14, 165, 233, 0.12); transform: translateY(-2px); }
    .product-grid-card .img-wrap { height: 200px; overflow: hidden; background: #f8fafc; }
    .product-grid-card .img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
    .product-grid-card:hover .img-wrap img { transform: scale(1.04); }
    .product-grid-card .card-body { padding: 1.25rem; }
    .product-grid-card .card-title { font-size: 0.95rem; font-weight: 600; color: #0f172a; line-height: 1.4; }
    .product-grid-card .price { font-size: 1.05rem; font-weight: 700; color: #0ea5e9; }
    .review-item {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        border: 1px solid #e2e8f0;
        margin-bottom: 0.75rem;
    }
    .products-heading { font-size: 1.2rem; font-weight: 700; color: #0f172a; margin-bottom: 1rem; }
    .reviews-heading { font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-bottom: 0.75rem; }
</style>
@endpush

@section('content')
<div class="business-page">
    <!-- Hero Header -->
    <div class="business-hero">
        <div class="container">
            <div class="d-flex flex-column flex-md-row align-items-center gap-4">
                <div class="business-logo-wrap">
                    @if($pageSettings && $pageSettings->logo_path)
                        <img src="{{ asset('storage/' . $pageSettings->logo_path) }}" alt="">
                    @elseif($seller->logo)
                        <img src="{{ asset('storage/' . $seller->logo) }}" alt="">
                    @else
                        <div class="placeholder"><i class="bi bi-shop"></i></div>
                    @endif
                </div>
                <div class="flex-grow-1 text-center text-md-start">
                    <h1 class="business-name" style="{{ $pageSettings && $pageSettings->primary_color ? 'color: ' . $pageSettings->primary_color . ' !important' : '' }}">{{ $pageSettings->page_name ?? $seller->business_name }}</h1>
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-2 mb-2">
                        <div class="rating-stars">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= $seller->rating ? '-fill' : '' }}"></i>
                            @endfor
                        </div>
                        <span class="business-meta">{{ $seller->rating }} · {{ $stats['total_reviews'] }} reviews</span>
                        <span class="rating-badge">{{ $seller->getRankingBadge() }}</span>
                    </div>
                    <div class="business-meta">
                        <span><i class="bi bi-geo-alt"></i>{{ $seller->city }}, {{ $seller->province }}</span>
                        <span class="mx-2">·</span>
                        <span><i class="bi bi-telephone"></i>{{ $seller->phone }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="container">

    @if($pageSettings && $pageSettings->banner_path)
        <div class="mb-4 rounded-3 overflow-hidden shadow-sm" style="max-height: 240px;">
            <img src="{{ asset('storage/' . $pageSettings->banner_path) }}" class="img-fluid w-100" alt="Store banner" style="height: 240px; object-fit: cover;">
        </div>
    @endif

    <!-- About Us -->
    @if($pageSettings && ($pageSettings->business_description ?? null))
        <div class="section-card mb-4">
            <div class="card-body">
                <h5 class="section-title"><i class="bi bi-info-circle text-primary"></i> About Us</h5>
                <p class="mb-0 about-us-text">{{ $pageSettings->business_description }}</p>
            </div>
        </div>
    @elseif($seller->description)
        <div class="section-card mb-4">
            <div class="card-body">
                <h5 class="section-title"><i class="bi bi-info-circle text-primary"></i> About Us</h5>
                <p class="mb-0 about-us-text">{{ $seller->description }}</p>
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
        <div class="section-card mb-4">
            <div class="card-body">
                <h5 class="section-title"><i class="bi bi-share text-primary"></i> Connect With Us</h5>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($allSocialLinks as $link)
                        @php
                            $btnClass = match($link->platform) {
                                'facebook' => 'facebook',
                                'instagram' => 'instagram',
                                'tiktok' => 'tiktok',
                                'twitter' => 'twitter',
                                'youtube' => 'youtube',
                                default => 'other'
                            };
                        @endphp
                        <a href="{{ $link->url }}" target="_blank" class="social-icon-btn {{ $btnClass }}" rel="noopener noreferrer" title="{{ $link->display_name }}">
                            <i class="bi {{ $link->icon }}"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Statistics -->
    <div class="row mb-4 g-3">
        <div class="col-6 col-lg-3">
            <div class="stat-box">
                <div class="value text-primary">{{ $stats['total_products'] }}</div>
                <div class="label">Products</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-box">
                <div class="value text-success">{{ $stats['total_sales'] }}</div>
                <div class="label">Total Sales</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-box">
                <div class="value text-warning">{{ $stats['rating'] }}</div>
                <div class="label">Rating</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-box">
                <div class="value text-info">{{ $stats['total_reviews'] }}</div>
                <div class="label">Reviews</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Products -->
        <div class="col-lg-8">
            <h3 class="products-heading">Products</h3>
            @if($products->count() > 0)
                <div class="row g-3">
                    @foreach($products as $product)
                        <div class="col-sm-6 col-xl-4">
                            <div class="card product-grid-card h-100" onclick="window.location.href='{{ route('toyshop.products.show', $product->slug) }}'">
                                <div class="img-wrap">
                                    @if($product->images->first())
                                        <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" alt="{{ $product->name }}">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                            <i class="bi bi-image" style="font-size: 3rem;"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title">{{ Str::limit($product->name, 50) }}</h6>
                                    <p class="price mb-2">₱{{ number_format($product->price, 2) }}</p>
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
                <div class="section-card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mb-0 mt-2">No products available yet</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Reviews -->
        <div class="col-lg-4 mt-4 mt-lg-0">
            <h3 class="reviews-heading">Recent Reviews</h3>
            @if($recentReviews->count() > 0)
                <div class="d-flex flex-column gap-2">
                    @foreach($recentReviews as $review)
                        <div class="review-item">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <strong class="text-dark">{{ $review->user->name }}</strong>
                                <div class="text-warning small">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $review->overall_rating ? '-fill' : '' }}"></i>
                                    @endfor
                                </div>
                            </div>
                            @if($review->review_text)
                                <p class="mb-1 text-secondary small lh-sm">{{ Str::limit($review->review_text, 120) }}</p>
                            @endif
                            <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="review-item text-center py-4">
                    <i class="bi bi-chat-dots text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mb-0 mt-2 small">No reviews yet</p>
                </div>
            @endif
        </div>
    </div>
</div>
</div>
@endsection
