@extends('layouts.toyshop')

@section('title', 'Business Page Preview - ' . (optional($pageSettings)->page_name ?? $seller->business_name))

@push('styles')
<style>
/* Preview bar - seller-only */
.bp-preview-bar {
    background: linear-gradient(90deg, #0e7490, #0891b2);
    color: #fff;
    padding: 0.75rem 1.5rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    font-size: 0.9rem;
    box-shadow: 0 4px 12px rgba(14, 118, 144, 0.2);
}
.bp-preview-bar .btn {
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.85rem;
}
.bp-preview-bar .btn-light { color: #0e7490; }
.bp-preview-bar .btn-light:hover { background: #fff; color: #0e7490; }

/* Same styles as toyshop/business/show - ensure preview matches live */
.bp-wrapper { min-height: 100vh; width: 100%; max-width: 100vw; background: #f8fafc; padding-bottom: 4rem; }
.bp-hero { position: relative; width: 100%; height: 180px; overflow: hidden; background: #f1f5f9; }
.bp-hero img { width: 100%; height: 100%; object-fit: cover; }
.bp-hero-overlay { position: absolute; inset: 0; background: linear-gradient(180deg, transparent 50%, rgba(0,0,0,0.08) 100%); pointer-events: none; }
.bp-identity { width: 100%; max-width: none; margin: 0 auto; padding: 1.5rem clamp(1.5rem, 5vw, 4rem) 0; }
.bp-identity.has-hero { margin-top: -60px; }
.bp-identity-card { background: #fff; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.08), 0 4px 16px rgba(14, 165, 233, 0.06); border: 1px solid #e2e8f0; overflow: hidden; padding: 2rem; }
@media (min-width: 768px) { .bp-identity-card { padding: 2.5rem 3rem; } }
.bp-identity-inner { display: flex; flex-wrap: wrap; align-items: center; gap: 1.5rem 2rem; }
.bp-logo { width: 100px; height: 100px; min-width: 100px; border-radius: 16px; overflow: hidden; background: #f1f5f9; box-shadow: 0 4px 12px rgba(0,0,0,0.06); flex-shrink: 0; }
.bp-logo img { width: 100%; height: 100%; object-fit: cover; }
.bp-logo-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: #94a3b8; background: linear-gradient(135deg, #e2e8f0, #f1f5f9); }
.bp-meta { flex: 1; min-width: 200px; }
.bp-store-name { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0 0 0.5rem 0; letter-spacing: -0.02em; }
@media (min-width: 768px) { .bp-store-name { font-size: 1.75rem; } }
.bp-location { font-size: 0.9rem; color: #64748b; margin-bottom: 0.75rem; }
.bp-location i { margin-right: 0.35rem; }
.bp-stats-row { display: flex; flex-wrap: wrap; gap: 1.25rem 2rem; font-size: 0.875rem; color: #64748b; }
.bp-stats-row .bp-stat { font-weight: 700; color: #334155; }
.bp-badge { display: inline-flex; padding: 0.3rem 0.75rem; border-radius: 10px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background: linear-gradient(135deg, #fef3c7, #fde68a); color: #b45309; }
.bp-main { width: 100%; max-width: none; margin: 0 auto; padding: 2rem clamp(1.5rem, 5vw, 4rem) 0; }
@media (min-width: 992px) { .bp-main { display: grid; grid-template-columns: 1fr 340px; gap: 2.5rem; align-items: start; } }
.bp-products-section { min-width: 0; width: 100%; }
.bp-products-section h2 { font-size: 1.25rem; font-weight: 800; color: #0f172a; margin-bottom: 1.5rem; }
.bp-product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.5rem; justify-items: center; align-items: start; }
@media (min-width: 768px) { .bp-product-grid { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1.75rem; } }
@media (min-width: 1200px) { .bp-product-grid { grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); } }
.bp-product-card { width: 100%; background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; cursor: pointer; display: flex; flex-direction: column; box-shadow: 0 2px 8px rgba(0,0,0,0.04); transition: transform 0.25s, box-shadow 0.25s; }
.bp-product-card:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(14, 165, 233, 0.12); border-color: #0ea5e9; }
.bp-product-img { aspect-ratio: 1; background: #f8fafc; overflow: hidden; display: flex; align-items: center; justify-content: center; }
.bp-product-img img { width: 100%; height: 100%; object-fit: cover; object-position: center; }
.bp-product-img .bp-no-img { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #cbd5e1; font-size: 2.5rem; }
.bp-product-body { padding: 1.25rem; flex: 1; display: flex; flex-direction: column; }
.bp-product-title { font-size: 0.95rem; font-weight: 600; color: #1e293b; line-height: 1.4; margin-bottom: 0.5rem; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; display: -webkit-box; }
.bp-product-price { font-size: 1.125rem; font-weight: 700; color: #0891b2; margin-bottom: 1rem; }
.bp-product-actions { margin-top: auto; }
.bp-product-actions .btn { border-radius: 10px; font-weight: 600; font-size: 0.85rem; }
.bp-empty-products { background: #fff; border-radius: 20px; border: 1px solid #e2e8f0; padding: 4rem 2rem; text-align: center; color: #94a3b8; }
.bp-empty-products i { font-size: 3.5rem; display: block; margin-bottom: 1rem; opacity: 0.5; }
.bp-sidebar { display: flex; flex-direction: column; gap: 1.5rem; }
.bp-sidebar-card { background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.bp-sidebar-card .bp-card-header { padding: 1rem 1.25rem; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #64748b; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
.bp-sidebar-card .bp-card-body { padding: 1.25rem 1.5rem; }
.bp-about-text { font-size: 0.9rem; line-height: 1.75; color: #475569; margin: 0; text-align: justify; letter-spacing: 0.02em; }
.bp-social-row { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.bp-social-btn { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; color: #fff; text-decoration: none; transition: transform 0.2s; }
.bp-social-btn:hover { transform: translateY(-3px); color: #fff; }
.bp-social-btn.fb { background: #1877f2; }
.bp-social-btn.ig { background: linear-gradient(135deg, #f09433, #e6683c, #dc2743); }
.bp-social-btn.tk { background: #000; }
.bp-social-btn.tw { background: #1da1f2; }
.bp-social-btn.yt { background: #ff0000; }
.bp-social-btn.other { background: #64748b; }
.bp-review-item { padding: 1rem 0; border-bottom: 1px solid #f1f5f9; }
.bp-review-item:last-child { border-bottom: none; }
.bp-review-head { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem; }
.bp-review-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #0891b2, #06b6d4); color: #fff; font-size: 0.9rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.bp-review-name { font-weight: 600; font-size: 0.9rem; color: #1e293b; }
.bp-review-stars { color: #f59e0b; font-size: 0.8rem; margin-left: auto; }
.bp-review-text { font-size: 0.85rem; color: #64748b; line-height: 1.55; margin: 0; }
.bp-review-date { font-size: 0.75rem; color: #94a3b8; }
.bp-reviews-empty { text-align: center; padding: 2rem; color: #94a3b8; }
.bp-reviews-empty i { font-size: 2.5rem; display: block; margin-bottom: 0.75rem; opacity: 0.5; }
.bp-preview-info { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; padding: 1rem 1.5rem; margin: 0 1.5rem 1.5rem; font-size: 0.9rem; color: #0c4a6e; }
.bp-preview-info strong { color: #0369a1; }
</style>
@endpush

@section('content')
{{-- Preview Bar (seller only) --}}
<div class="bp-preview-bar">
    <span><i class="bi bi-eye me-2"></i>Preview — This is how your business page appears to customers</span>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('seller.business-page.index') }}" class="btn btn-light">
            <i class="bi bi-arrow-left me-1"></i> Back to Settings
        </a>
        <a href="{{ route('toyshop.business.show', $seller->business_slug) }}" class="btn btn-light" target="_blank">
            <i class="bi bi-box-arrow-up-right me-1"></i> View Live Page
        </a>
    </div>
</div>

@php
    $previewStats = [
        'total_products' => $seller->products()->where('status', 'active')->count(),
        'total_sales' => $seller->total_sales,
        'rating' => $seller->rating,
        'total_reviews' => $seller->total_reviews,
    ];
    $isNew = $previewStats['total_sales'] == 0 && $previewStats['total_reviews'] == 0;
@endphp

<div class="bp-preview-info">
    @if(!($pageSettings?->is_published ?? false))
        <strong>Your page is not yet published.</strong> Enable publishing in General Settings to make it visible to customers.
    @else
        <strong>Your page is published</strong> and visible to customers.
    @endif
</div>

<div class="bp-wrapper">
    {{-- Hero - only when seller has custom banner (no blue block) --}}
    @if($pageSettings && $pageSettings->banner_path)
    <div class="bp-hero bp-hero-has-img">
        <img src="{{ asset('storage/' . $pageSettings->banner_path) }}" alt="">
        <div class="bp-hero-overlay"></div>
    </div>
    @endif

    {{-- Identity Card --}}
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
                        <span><span class="bp-stat">{{ $previewStats['total_products'] }}</span> products</span>
                        @if($isNew)
                            <span class="bp-badge">{{ $seller->getRankingBadge() }}</span>
                        @else
                            <span><span class="bp-stat">{{ $previewStats['total_sales'] }}</span> sales</span>
                            <span><span class="bp-stat">{{ number_format($previewStats['rating'], 1) }}</span> rating · {{ $previewStats['total_reviews'] }} reviews</span>
                            <span class="bp-badge">{{ $seller->getRankingBadge() }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="bp-main">
        @php $previewProducts = $seller->products()->where('status', 'active')->with('images')->take(8)->get(); @endphp
        <div class="bp-products-section">
            <h2>Products</h2>
            @if($previewProducts->count() > 0)
                <div class="bp-product-grid">
                    @foreach($previewProducts as $product)
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
                                <div class="bp-product-actions">
                                    <a href="{{ route('toyshop.products.show', $product->slug) }}" class="btn btn-primary">View</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bp-empty-products">
                    <i class="bi bi-inbox"></i>
                    <p>No products available yet</p>
                </div>
            @endif
        </div>

        <aside class="bp-sidebar">
            @if(($pageSettings && ($pageSettings->business_description ?? null)) || $seller->description)
                <div class="bp-sidebar-card">
                    <div class="bp-card-header">About</div>
                    <div class="bp-card-body">
                        <p class="bp-about-text">{{ $pageSettings->business_description ?? $seller->description }}</p>
                    </div>
                </div>
            @endif

            @if($socialLinks && $socialLinks->count() > 0)
                <div class="bp-sidebar-card">
                    <div class="bp-card-header">Connect</div>
                    <div class="bp-card-body">
                        <div class="bp-social-row">
                            @foreach($socialLinks as $link)
                                @php
                                    $cls = match($link->platform) {
                                        'facebook' => 'fb', 'instagram' => 'ig', 'tiktok' => 'tk',
                                        'twitter' => 'tw', 'youtube' => 'yt', default => 'other'
                                    };
                                @endphp
                                <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="bp-social-btn {{ $cls }}" title="{{ $link->display_name ?? ucfirst($link->platform) }}">
                                    <i class="bi {{ $link->getPlatformIcon() }}"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div class="bp-sidebar-card">
                <div class="bp-card-header">Recent Reviews</div>
                <div class="bp-card-body">
                    @php $previewReviews = $seller->reviews()->with('user')->orderBy('created_at', 'desc')->limit(5)->get(); @endphp
                    @if($previewReviews->count() > 0)
                        @foreach($previewReviews as $review)
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
