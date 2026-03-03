@extends('layouts.toyshop')

@section('title', ($pageSettings->page_name ?? $seller->business_name) . ' - ToyHaven')

@push('styles')
<style>
    /* Business page - full-width layout, professional design */
    .business-page {
        background: #f1f5f9;
        min-height: 100vh;
        padding-bottom: 3rem;
        width: 100%;
        max-width: 100%;
    }

    /* Override toyshop layout container for business page - maximize width */
    .business-page .business-container {
        width: 100%;
        max-width: 1680px;
        margin-left: auto;
        margin-right: auto;
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
    @media (max-width: 575px) {
        .business-page .business-container {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }

    /* Full-width banner */
    .business-banner {
        width: 100%;
        height: 200px;
        object-fit: cover;
        display: block;
        background: linear-gradient(135deg, #0e7490 0%, #0891b2 100%);
    }
    .business-banner-wrap {
        margin-bottom: 0;
        overflow: hidden;
        width: 100%;
    }
    .business-banner-fallback {
        height: 160px;
        background: linear-gradient(135deg, #0e7490 0%, #0891b2 100%);
    }

    /* Header card - professional store identity */
    .business-header {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        padding: 2rem 2rem;
        margin-top: -32px;
        margin-bottom: 2rem;
        position: relative;
        z-index: 2;
    }
    .business-header-inner {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1.5rem;
    }
    .business-logo {
        width: 88px;
        height: 88px;
        border-radius: 14px;
        overflow: hidden;
        flex-shrink: 0;
        background: #f8fafc;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .business-logo img { width: 100%; height: 100%; object-fit: cover; }
    .business-logo .placeholder {
        width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; color: #94a3b8;
    }
    .business-meta {
        flex: 1;
        min-width: 0;
    }
    .business-meta h1 {
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #0f172a;
        margin: 0 0 0.5rem 0;
        line-height: 1.3;
    }
    .business-meta .info-line {
        font-size: 0.875rem;
        color: #64748b;
    }
    .business-meta .info-line i { margin-right: 0.35rem; opacity: 0.85; }
    .ranking-badge {
        display: inline-flex;
        padding: 0.25rem 0.6rem;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        background: #fef3c7;
        color: #b45309;
    }
    .stats-inline {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem 2rem;
        font-size: 0.875rem;
        color: #64748b;
    }
    .stats-inline .stat { font-weight: 700; color: #334155; }

    /* Content grid - maximize main area, balanced sidebar */
    .content-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 2.5rem;
        align-items: start;
    }
    @media (min-width: 1400px) {
        .content-grid { grid-template-columns: 1fr 380px; gap: 3rem; }
    }
    @media (max-width: 991px) {
        .content-grid { grid-template-columns: 1fr; gap: 2rem; }
    }

    /* Sidebar sections */
    .sidebar-section {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .sidebar-section .section-header {
        padding: 1rem 1.5rem;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .sidebar-section .section-body { padding: 1.25rem 1.5rem; }
    .about-text {
        font-size: 0.9rem;
        line-height: 1.7;
        color: #475569;
        text-align: justify;
        margin: 0;
    }
    .social-links {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .social-link {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        color: #fff;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .social-link:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); color: #fff; }
    .social-link.fb { background: #1877f2; }
    .social-link.ig { background: linear-gradient(135deg, #f09433, #e6683c, #dc2743); }
    .social-link.tk { background: #000; }
    .social-link.tw { background: #1da1f2; }
    .social-link.yt { background: #ff0000; }
    .social-link.other { background: #64748b; }

    /* Products section - full-width grid */
    .products-section .section-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 1.25rem;
        letter-spacing: -0.02em;
    }
    .product-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        transition: all 0.25s ease;
        cursor: pointer;
        height: 100%;
        display: flex;
        flex-direction: column;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .product-card:hover {
        border-color: #0ea5e9;
        box-shadow: 0 12px 32px rgba(14, 165, 233, 0.12);
        transform: translateY(-4px);
    }
    .product-card .img-wrap {
        aspect-ratio: 1;
        overflow: hidden;
        background: #f8fafc;
    }
    .product-card .img-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }
    .product-card:hover .img-wrap img { transform: scale(1.05); }
    .product-card .card-body {
        padding: 1.25rem 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .product-card .card-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.4;
        margin-bottom: 0.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .product-card .price {
        font-size: 1.125rem;
        font-weight: 700;
        color: #0891b2;
        margin-bottom: 0.75rem;
    }
    .product-card .actions {
        margin-top: auto;
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .product-card .btn-view {
        flex: 1;
        min-width: 90px;
        font-size: 0.875rem;
        font-weight: 600;
        padding: 0.5rem 1rem;
    }
    .product-card .btn-icon {
        width: 38px;
        height: 38px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    /* Reviews */
    .review-card {
        padding: 1rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .review-card:last-child { border-bottom: none; padding-bottom: 0; }
    .review-card:first-child { padding-top: 0; }
    .review-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.5rem;
    }
    .review-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0891b2, #06b6d4);
        color: #fff;
        font-size: 0.85rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .review-name { font-weight: 600; font-size: 0.9rem; color: #1e293b; }
    .review-stars { color: #f59e0b; font-size: 0.8rem; margin-left: auto; }
    .review-text {
        font-size: 0.8125rem;
        color: #64748b;
        line-height: 1.55;
        margin: 0 0 0.35rem 0;
    }
    .review-date { font-size: 0.75rem; color: #94a3b8; }
    .reviews-empty {
        text-align: center;
        padding: 2rem;
        color: #94a3b8;
        font-size: 0.9rem;
    }
    .reviews-empty i { font-size: 2rem; display: block; margin-bottom: 0.5rem; opacity: 0.5; }

    /* Empty state */
    .empty-products {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        padding: 4rem;
        text-align: center;
        color: #94a3b8;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .empty-products i { font-size: 3rem; display: block; margin-bottom: 1rem; opacity: 0.4; }
</style>
@endpush

@section('content')
<div class="business-page">
    @php
        $isNew = ($stats['total_sales'] ?? 0) == 0 && ($stats['total_reviews'] ?? 0) == 0;
    @endphp
    {{-- Banner image (optional) --}}
    @if($pageSettings && $pageSettings->banner_path)
        <div class="business-banner-wrap">
            <img src="{{ asset('storage/' . $pageSettings->banner_path) }}" alt="" class="business-banner">
        </div>
    @else
        <div class="business-banner-wrap">
            <div class="business-banner business-banner-fallback"></div>
        </div>
    @endif

    {{-- Single header: logo, name, location, phone, stats (no duplication) --}}
    <div class="business-container">
        <div class="business-header">
            <div class="business-header-inner">
                <div class="business-logo">
                    @if($pageSettings && $pageSettings->logo_path)
                        <img src="{{ asset('storage/' . $pageSettings->logo_path) }}" alt="">
                    @elseif($seller->logo)
                        <img src="{{ asset('storage/' . $seller->logo) }}" alt="">
                    @else
                        <div class="placeholder"><i class="bi bi-shop"></i></div>
                    @endif
                </div>
                <div class="business-meta">
                    <h1 style="{{ $pageSettings && $pageSettings->primary_color ? 'color: ' . $pageSettings->primary_color . ' !important' : '' }}">
                        {{ $pageSettings->page_name ?? $seller->business_name }}
                    </h1>
                    <div class="info-line mb-1">
                        <span><i class="bi bi-geo-alt"></i>{{ $seller->city }}, {{ $seller->province }}</span>
                        @if($seller->phone)
                            <span class="ms-3"><i class="bi bi-telephone"></i>{{ $seller->phone }}</span>
                        @endif
                    </div>
                    <div class="stats-inline">
                        <span><span class="stat">{{ $stats['total_products'] }}</span> products</span>
                        @if($isNew)
                            <span class="ranking-badge">{{ $seller->getRankingBadge() }}</span>
                        @else
                            <span><span class="stat">{{ $stats['total_sales'] }}</span> sales</span>
                            <span><span class="stat">{{ number_format($stats['rating'] ?? 0, 1) }}</span> rating · {{ $stats['total_reviews'] }} reviews</span>
                            <span class="ranking-badge">{{ $seller->getRankingBadge() }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="business-container">
        <div class="content-grid">
            {{-- Main: Products --}}
            <div class="products-section">
                <h2 class="section-title">Products</h2>
                @if($products->count() > 0)
                    <div class="row g-4">
                        @foreach($products as $product)
                            <div class="col-6 col-md-4 col-lg-3 col-xl-3">
                                <div class="product-card" onclick="window.location.href='{{ route('toyshop.products.show', $product->slug) }}'">
                                    <div class="img-wrap">
                                        @if($product->images->first())
                                            <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" alt="{{ $product->name }}">
                                        @else
                                            <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                                <i class="bi bi-image" style="font-size: 2.5rem;"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        <h3 class="card-title">{{ Str::limit($product->name, 50) }}</h3>
                                        <p class="price">₱{{ number_format($product->price, 2) }}</p>
                                        <div class="actions" onclick="event.stopPropagation();">
                                            <a href="{{ route('toyshop.products.show', $product->slug) }}" class="btn btn-primary btn-view" onclick="event.stopPropagation();">View</a>
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
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-center mt-4">
                        {{ $products->links() }}
                    </div>
                @else
                    <div class="empty-products">
                        <i class="bi bi-inbox"></i>
                        <p class="mb-0">No products available yet</p>
                    </div>
                @endif
            </div>

            {{-- Sidebar: About, Social, Reviews --}}
            <aside>
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
                    <div class="sidebar-section">
                        <div class="section-header">About</div>
                        <div class="section-body">
                            <p class="about-text">{{ $pageSettings->business_description ?? $seller->description }}</p>
                        </div>
                    </div>
                @endif

                @if($allSocialLinks->count() > 0)
                    <div class="sidebar-section">
                        <div class="section-header">Connect</div>
                        <div class="section-body">
                            <div class="social-links">
                                @foreach($allSocialLinks as $link)
                                    @php
                                        $cls = match($link->platform) {
                                            'facebook' => 'fb', 'instagram' => 'ig', 'tiktok' => 'tk',
                                            'twitter' => 'tw', 'youtube' => 'yt', default => 'other'
                                        };
                                    @endphp
                                    <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="social-link {{ $cls }}" title="{{ $link->display_name }}">
                                        <i class="bi {{ $link->icon }}"></i>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <div class="sidebar-section">
                    <div class="section-header">Recent Reviews</div>
                    <div class="section-body">
                        @if($recentReviews->count() > 0)
                            @foreach($recentReviews as $review)
                                <div class="review-card">
                                    <div class="review-header">
                                        <div class="review-avatar">{{ strtoupper(substr($review->user->name ?? 'U', 0, 1)) }}</div>
                                        <span class="review-name">{{ $review->user->name ?? 'Customer' }}</span>
                                        <span class="review-stars">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="bi bi-star{{ $i <= $review->overall_rating ? '-fill' : '' }}"></i>
                                            @endfor
                                        </span>
                                    </div>
                                    @if($review->review_text)
                                        <p class="review-text">{{ Str::limit($review->review_text, 140) }}</p>
                                    @endif
                                    <span class="review-date">{{ $review->created_at->diffForHumans() }}</span>
                                </div>
                            @endforeach
                        @else
                            <div class="reviews-empty">
                                <i class="bi bi-chat-dots"></i>
                                No reviews yet
                            </div>
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection
