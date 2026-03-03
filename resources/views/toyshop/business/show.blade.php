@extends('layouts.toyshop')

@section('title', ($pageSettings->page_name ?? $seller->business_name) . ' - ToyHaven')

@push('styles')
<style>
/* Business Page - Full viewport, maximized width and height */
.bp-wrapper {
    min-height: 100vh;
    width: 100%;
    max-width: 100vw;
    background: #f8fafc;
    padding-bottom: 4rem;
}

/* Hero Banner - only when seller has custom banner; no blue gradient fallback */
.bp-hero {
    position: relative;
    width: 100%;
    height: 180px;
    overflow: hidden;
    background: #f1f5f9;
}
.bp-hero img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.bp-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 50%, rgba(0,0,0,0.08) 100%);
    pointer-events: none;
}

/* Store Identity Card - full width, maximized */
.bp-identity {
    width: 100%;
    max-width: 100%;
    margin: 0 auto;
    padding: 1.5rem clamp(1.5rem, 4vw, 3rem) 0;
}
.bp-identity.has-hero { margin-top: -60px; }
.bp-identity-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.08), 0 4px 16px rgba(14, 165, 233, 0.06);
    border: 1px solid #e2e8f0;
    overflow: hidden;
    padding: 2rem;
}
@media (min-width: 768px) {
    .bp-identity-card { padding: 2.5rem 3rem; }
}
.bp-identity-inner {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 1.5rem 2rem;
}
.bp-logo {
    width: 100px;
    height: 100px;
    min-width: 100px;
    border-radius: 16px;
    overflow: hidden;
    background: #f1f5f9;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    flex-shrink: 0;
}
.bp-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.bp-logo-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: #94a3b8;
    background: linear-gradient(135deg, #e2e8f0, #f1f5f9);
}
.bp-meta {
    flex: 1;
    min-width: 200px;
}
.bp-store-name {
    font-size: 1.5rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 0.5rem 0;
    letter-spacing: -0.02em;
    line-height: 1.3;
}
@media (min-width: 768px) {
    .bp-store-name { font-size: 1.75rem; }
}
.bp-location {
    font-size: 0.9rem;
    color: #64748b;
    margin-bottom: 0.75rem;
}
.bp-location i {
    margin-right: 0.35rem;
    opacity: 0.8;
}
.bp-stats-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1.25rem 2rem;
    font-size: 0.875rem;
    color: #64748b;
}
.bp-stats-row .bp-stat {
    font-weight: 700;
    color: #334155;
}
.bp-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.3rem 0.75rem;
    border-radius: 10px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    color: #b45309;
}

/* Main Content Grid - full width, maximized */
.bp-main {
    width: 100%;
    max-width: 100%;
    margin: 0 auto;
    padding: 2rem clamp(1.5rem, 4vw, 3rem) 0;
}
@media (min-width: 992px) {
    .bp-main {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 2.5rem;
        align-items: start;
    }
}

/* Products Section - full width, centered, larger cards */
.bp-products-section {
    min-width: 0;
    width: 100%;
}
.bp-products-section h2 {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 1.5rem;
    letter-spacing: -0.02em;
}
.bp-product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1.5rem;
    justify-items: center;
    align-items: stretch;
}
@media (min-width: 768px) {
    .bp-product-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.75rem;
    }
}
@media (min-width: 1200px) {
    .bp-product-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 2rem;
    }
}
.bp-product-card {
    width: 100%;
    background: #fff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.bp-product-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 40px rgba(14, 165, 233, 0.12);
    border-color: #0ea5e9;
}
.bp-product-img {
    aspect-ratio: 1;
    background: #f8fafc;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 220px;
}
.bp-product-img img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center;
    transition: transform 0.35s ease;
}
.bp-product-card:hover .bp-product-img img {
    transform: scale(1.05);
}
.bp-product-img .bp-no-img {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #cbd5e1;
    font-size: 2.5rem;
}
.bp-product-body {
    padding: 1.25rem 1.25rem 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.bp-product-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #1e293b;
    line-height: 1.4;
    margin-bottom: 0.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.bp-product-price {
    font-size: 1.125rem;
    font-weight: 700;
    color: #0891b2;
    margin-bottom: 1rem;
}
.bp-product-actions {
    margin-top: auto;
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.bp-product-actions .btn {
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.85rem;
    padding: 0.5rem 1rem;
}
.bp-product-actions .btn-icon {
    width: 38px;
    height: 38px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
}
.bp-empty-products {
    background: #fff;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    padding: 4rem 2rem;
    text-align: center;
    color: #94a3b8;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.bp-empty-products i {
    font-size: 3.5rem;
    display: block;
    margin-bottom: 1rem;
    opacity: 0.5;
}
.bp-empty-products p {
    margin: 0;
    font-size: 1rem;
}

/* Sidebar */
.bp-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
.bp-sidebar-card {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.bp-sidebar-card .bp-card-header {
    padding: 1rem 1.25rem;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #64748b;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}
.bp-sidebar-card .bp-card-body {
    padding: 1.25rem 1.5rem;
}
.bp-about-wrapper {
    max-height: 200px;
    overflow-y: auto;
}
.bp-about-text {
    font-size: 0.9rem;
    line-height: 1.6;
    color: #475569;
    margin: 0;
    text-align: justify;
    hyphens: auto;
    letter-spacing: 0.01em;
}
.bp-social-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.bp-social-btn {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    color: #fff;
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.bp-social-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.2);
    color: #fff;
}
.bp-social-btn.fb { background: #1877f2; }
.bp-social-btn.ig { background: linear-gradient(135deg, #f09433, #e6683c, #dc2743); }
.bp-social-btn.tk { background: #000; }
.bp-social-btn.tw { background: #1da1f2; }
.bp-social-btn.yt { background: #ff0000; }
.bp-social-btn.linkedin { background: #0a66c2; }
.bp-social-btn.other { background: #64748b; }

/* Reviews */
.bp-review-item {
    padding: 1rem 0;
    border-bottom: 1px solid #f1f5f9;
}
.bp-review-item:last-child { border-bottom: none; padding-bottom: 0; }
.bp-review-item:first-child { padding-top: 0; }
.bp-review-head {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}
.bp-review-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0891b2, #06b6d4);
    color: #fff;
    font-size: 0.9rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.bp-review-name {
    font-weight: 600;
    font-size: 0.9rem;
    color: #1e293b;
}
.bp-review-stars {
    color: #f59e0b;
    font-size: 0.8rem;
    margin-left: auto;
}
.bp-review-text {
    font-size: 0.85rem;
    color: #64748b;
    line-height: 1.55;
    margin: 0 0 0.35rem 0;
}
.bp-review-date {
    font-size: 0.75rem;
    color: #94a3b8;
}
.bp-reviews-empty {
    text-align: center;
    padding: 2rem;
    color: #94a3b8;
    font-size: 0.9rem;
}
.bp-reviews-empty i {
    font-size: 2.5rem;
    display: block;
    margin-bottom: 0.75rem;
    opacity: 0.5;
}

/* Pagination */
.bp-pagination-wrap {
    margin-top: 2rem;
    display: flex;
    justify-content: center;
}
</style>
@endpush

@section('content')
<div class="bp-wrapper">
    @php
        $isNew = ($stats['total_sales'] ?? 0) == 0 && ($stats['total_reviews'] ?? 0) == 0;
    @endphp

    {{-- Hero Banner - only shown when seller has custom banner (no blue block) --}}
    @if($pageSettings && $pageSettings->banner_path)
    <div class="bp-hero bp-hero-has-img">
        <img src="{{ asset('storage/' . $pageSettings->banner_path) }}" alt="">
        <div class="bp-hero-overlay"></div>
    </div>
    @endif

    {{-- Store Identity Card --}}
    <div class="bp-identity {{ ($pageSettings && $pageSettings->banner_path) ? 'has-hero' : '' }}">
        <div class="bp-identity-card">
            <div class="bp-identity-inner">
                <div class="bp-logo">
                    @if($pageSettings && $pageSettings->logo_path)
                        <img src="{{ asset('storage/' . $pageSettings->logo_path) }}" alt="">
                    @elseif($seller->logo)
                        <img src="{{ asset('storage/' . $seller->logo) }}" alt="">
                    @else
                        <div class="bp-logo-placeholder"><i class="bi bi-shop"></i></div>
                    @endif
                </div>
                <div class="bp-meta">
                    <h1 class="bp-store-name" style="{{ $pageSettings && $pageSettings->primary_color ? 'color: ' . $pageSettings->primary_color . ' !important' : '' }}">
                        {{ $pageSettings->page_name ?? $seller->business_name }}
                    </h1>
                    <div class="bp-location">
                        <span><i class="bi bi-geo-alt"></i>{{ $seller->city ?? '—' }}, {{ $seller->province ?? '—' }}</span>
                        @if($seller->phone)
                            <span class="ms-3"><i class="bi bi-telephone"></i>{{ $seller->phone }}</span>
                        @endif
                    </div>
                    <div class="bp-stats-row">
                        <span><span class="bp-stat">{{ $stats['total_products'] }}</span> products</span>
                        @if($isNew)
                            <span class="bp-badge">{{ $seller->getRankingBadge() }}</span>
                        @else
                            <span><span class="bp-stat">{{ $stats['total_sales'] }}</span> sales</span>
                            <span><span class="bp-stat">{{ number_format($stats['rating'] ?? 0, 1) }}</span> rating · {{ $stats['total_reviews'] }} reviews</span>
                            <span class="bp-badge">{{ $seller->getRankingBadge() }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="bp-main">
        <div class="bp-products-section">
            <h2>Products</h2>
            @if($products->count() > 0)
                <div class="bp-product-grid">
                    @foreach($products as $product)
                        <div class="bp-product-card" onclick="window.location.href='{{ route('toyshop.products.show', $product->slug) }}'">
                            <div class="bp-product-img">
                                @if($product->images->first())
                                    <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" alt="{{ $product->name }}">
                                @else
                                    <div class="bp-no-img"><i class="bi bi-image"></i></div>
                                @endif
                            </div>
                            <div class="bp-product-body">
                                <h3 class="bp-product-title">{{ Str::limit($product->name, 50) }}</h3>
                                <p class="bp-product-price">₱{{ number_format($product->price, 2) }}</p>
                                <div class="bp-product-actions" onclick="event.stopPropagation();">
                                    <a href="{{ route('toyshop.products.show', $product->slug) }}" class="btn btn-primary" onclick="event.stopPropagation();">View</a>
                                    @auth
                                        @php
                                            $inWishlist = in_array($product->id, $wishlistProductIds ?? []);
                                            $wishlistId = $inWishlist && isset($wishlistItems[$product->id]) ? $wishlistItems[$product->id]['id'] : null;
                                        @endphp
                                        @if($inWishlist && $wishlistId)
                                            <form action="{{ route('wishlist.remove', $wishlistId) }}" method="POST" class="d-inline" onclick="event.stopPropagation();">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-icon" title="Remove from Wishlist"><i class="bi bi-heart-fill"></i></button>
                                            </form>
                                        @else
                                            <form action="{{ route('wishlist.add') }}" method="POST" class="d-inline" onclick="event.stopPropagation();">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                <button type="submit" class="btn btn-outline-danger btn-icon" title="Add to Wishlist"><i class="bi bi-heart"></i></button>
                                            </form>
                                        @endif
                                        <form action="{{ route('cart.add') }}" method="POST" class="d-inline" onclick="event.stopPropagation();">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn btn-outline-primary btn-icon" {{ !$product->isInStock() ? 'disabled' : '' }} title="Add to Cart"><i class="bi bi-cart-plus"></i></button>
                                        </form>
                                    @else
                                        <a href="{{ route('login') }}" class="btn btn-outline-danger btn-icon" title="Login to add to Wishlist"><i class="bi bi-heart"></i></a>
                                        <a href="{{ route('login') }}" class="btn btn-outline-primary btn-icon" title="Login to add to Cart"><i class="bi bi-cart-plus"></i></a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="bp-pagination-wrap">
                    {{ $products->links() }}
                </div>
            @else
                <div class="bp-empty-products">
                    <i class="bi bi-inbox"></i>
                    <p>No products available yet</p>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <aside class="bp-sidebar">
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

            @if(($pageSettings && ($pageSettings->business_description ?? null)) || $seller->description)
                <div class="bp-sidebar-card">
                    <div class="bp-card-header">About</div>
                    <div class="bp-card-body">
                        <div class="bp-about-wrapper">
                            <p class="bp-about-text">{{ $pageSettings->business_description ?? $seller->description }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($allSocialLinks->count() > 0)
                <div class="bp-sidebar-card">
                    <div class="bp-card-header">Connect</div>
                    <div class="bp-card-body">
                        <div class="bp-social-row">
                            @foreach($allSocialLinks as $link)
                                @php
                                    $cls = match($link->platform) {
                                        'facebook' => 'fb', 'instagram' => 'ig', 'tiktok' => 'tk',
                                        'twitter' => 'tw', 'youtube' => 'yt', 'linkedin' => 'linkedin',
                                        default => 'other'
                                    };
                                @endphp
                                <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="bp-social-btn {{ $cls }}" title="{{ $link->display_name }}">
                                    <i class="bi {{ $link->icon }}"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div class="bp-sidebar-card">
                <div class="bp-card-header">Recent Reviews</div>
                <div class="bp-card-body">
                    @if($recentReviews->count() > 0)
                        @foreach($recentReviews as $review)
                            <div class="bp-review-item">
                                <div class="bp-review-head">
                                    <div class="bp-review-avatar">{{ strtoupper(substr($review->user->name ?? 'U', 0, 1)) }}</div>
                                    <span class="bp-review-name">{{ $review->user->name ?? 'Customer' }}</span>
                                    <span class="bp-review-stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi bi-star{{ $i <= $review->overall_rating ? '-fill' : '' }}"></i>
                                        @endfor
                                    </span>
                                </div>
                                @if($review->review_text)
                                    <p class="bp-review-text">{{ Str::limit($review->review_text, 140) }}</p>
                                @endif
                                <span class="bp-review-date">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                        @endforeach
                    @else
                        <div class="bp-reviews-empty">
                            <i class="bi bi-chat-dots"></i>
                            No reviews yet
                        </div>
                    @endif
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
