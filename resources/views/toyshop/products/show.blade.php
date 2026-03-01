@extends('layouts.toyshop')

@section('title', $product->name . ' - ToyHaven')

@push('styles')
<style>
    /* --- Product detail: professional layout --- */
    .product-detail-page {
        --pd-radius: 12px;
        --pd-shadow: 0 1px 3px rgba(0,0,0,0.06);
        --pd-shadow-lg: 0 4px 14px rgba(0,0,0,0.08);
        --pd-border: 1px solid #f0e6dc;
        --pd-label: #64748b;
        --pd-text: #0f172a;
        font-feature-settings: "kern" 1, "liga" 1;
    }
    .product-detail-page .container { max-width: 1200px; }
    
    .breadcrumb-nav { padding: 0.5rem 0; margin-bottom: 1.25rem; background: transparent; }
    .breadcrumb { margin: 0; background: transparent; font-size: 0.8125rem; }
    .breadcrumb-item + .breadcrumb-item::before { color: #94a3b8; font-weight: 400; }
    .breadcrumb-item a { color: #475569; text-decoration: none; font-weight: 500; }
    .breadcrumb-item a:hover { color: #0f172a; }
    .breadcrumb-item.active { color: #64748b; font-weight: 500; }
    
    .product-gallery {
        background: #fff;
        border-radius: var(--pd-radius);
        overflow: hidden;
        box-shadow: var(--pd-shadow);
        border: var(--pd-border);
    }
    .main-image-container { position: relative; width: 100%; background: #fff; overflow: hidden; }
    .main-image-wrap { position: relative; width: 100%; height: 680px; overflow: hidden; }
    .main-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: #fff;
        cursor: zoom-in;
        transition: opacity 0.35s ease, transform 0.45s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        display: block;
    }
    .main-image.main-image-slide { opacity: 0; transform: translateX(24%); }
    .main-image.main-image-slide.visible { opacity: 1; transform: translateX(0); }
    .main-image.main-image-slide-prev { opacity: 0; transform: translateX(-24%); }
    .main-image:hover { opacity: 0.98; }
    /* Hover zoom lens - larger for better detail view */
    .product-zoom-lens {
        position: absolute;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        border: 3px solid rgba(255,255,255,0.9);
        box-shadow: 0 4px 20px rgba(0,0,0,0.35);
        pointer-events: none;
        z-index: 20;
        display: none;
        overflow: hidden;
    }
    .product-zoom-lens.active { display: block; }
    .product-zoom-lens .zoom-lens-image {
        position: absolute;
        background-repeat: no-repeat;
        background-color: #fafafa;
    }
    .image-zoom-hint {
        position: absolute;
        top: 12px;
        right: 12px;
        background: rgba(15, 23, 42, 0.75);
        color: #fff;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 0.8125rem;
        font-weight: 500;
        z-index: 10;
        display: flex;
        align-items: center;
        gap: 6px;
        pointer-events: none;
    }
    .thumbnail-gallery {
        display: flex;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        overflow-x: auto;
        background: #f8fafc;
        border-top: var(--pd-border);
        scrollbar-width: thin;
    }
    .thumbnail-gallery::-webkit-scrollbar { height: 4px; }
    .thumbnail-gallery::-webkit-scrollbar-track { background: #f1f5f9; }
    .thumbnail-gallery::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 2px; }
    .thumbnail {
        width: 72px;
        height: 72px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        border: 2px solid transparent;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        flex-shrink: 0;
    }
    .thumbnail:hover { border-color: #94a3b8; }
    .thumbnail.active { border-color: #475569; box-shadow: 0 0 0 1px #475569; }
    .thumbnail-video.thumbnail { min-width: 72px; min-height: 72px; background: #334155; }
    
    .product-info-card {
        background: #fff;
        border-radius: var(--pd-radius);
        padding: 1.5rem 1.75rem;
        box-shadow: var(--pd-shadow);
        border: var(--pd-border);
        height: fit-content;
        position: sticky;
        top: 88px;
    }
    .product-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--pd-text);
        margin-bottom: 0.625rem;
        line-height: 1.35;
        letter-spacing: -0.02em;
    }
    .product-rating-section {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: var(--pd-border);
    }
    .rating-display { display: flex; align-items: center; gap: 0.375rem; flex-wrap: wrap; }
    .rating-stars { color: #ca8a04; font-size: 1rem; }
    .product-rating-section .text-muted { font-size: 0.8125rem; color: var(--pd-label) !important; }
    
    .price-section {
        margin-bottom: 1.25rem;
        padding-bottom: 1.25rem;
        border-bottom: var(--pd-border);
    }
    .current-price {
        font-size: 2rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
        letter-spacing: -0.02em;
    }
    .original-price { font-size: 1rem; color: #94a3b8; text-decoration: line-through; margin-right: 0.75rem; }
    .discount-badge {
        background: #059669;
        color: #fff;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .stock-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        margin-bottom: 1.25rem;
    }
    .stock-badge.bg-success { background: #dcfce7 !important; color: #166534 !important; }
    .stock-badge.bg-danger { background: #fee2e2 !important; color: #991b1b !important; }
    
    .seller-card {
        background: #f8fafc;
        border-radius: 8px;
        padding: 1rem 1.25rem;
        margin-bottom: 1rem;
        border: var(--pd-border);
    }
    .seller-card .text-muted { font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--pd-label) !important; }
    .seller-name { font-size: 0.9375rem; font-weight: 600; color: var(--pd-text); margin-bottom: 0.375rem; }
    .seller-card .btn-sm { font-size: 0.8125rem; font-weight: 500; border-radius: 6px; padding: 0.375rem 0.75rem; }
    
    .action-buttons { display: flex; gap: 0.5rem; margin-bottom: 0.75rem; flex-wrap: wrap; }
    .action-buttons .btn {
        flex: 1;
        min-width: 140px;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-weight: 600;
        font-size: 0.9375rem;
        transition: background 0.2s, border-color 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    .action-buttons .btn-primary { background: #0f172a; border: none; }
    .action-buttons .btn-primary:hover { background: #ee5a5a; }
    .action-buttons .btn-outline-danger:hover { background: #fef2f2; }
    
    .quantity-selector {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
        padding: 0.75rem 0;
    }
    .quantity-selector label { font-weight: 600; color: var(--pd-text); margin: 0; font-size: 0.875rem; }
    .quantity-input-group {
        display: inline-flex;
        border: var(--pd-border);
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
    }
    .quantity-btn {
        background: #fff;
        border: none;
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        color: #475569;
        font-weight: 600;
        font-size: 1rem;
        transition: background 0.2s;
    }
    .quantity-btn:hover { background: #f1f5f9; }
    .quantity-input {
        border: none;
        width: 56px;
        text-align: center;
        font-weight: 600;
        padding: 0.5rem;
        font-size: 0.9375rem;
    }
    .quantity-input:focus { outline: none; }
    
    .product-details-card {
        background: #fff;
        border-radius: var(--pd-radius);
        padding: 1.75rem;
        box-shadow: var(--pd-shadow);
        border: var(--pd-border);
        margin-top: 1.5rem;
    }
    .product-details-card .section-label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--pd-label);
        margin-bottom: 0.5rem;
    }
    .product-details-card .section-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--pd-text);
        margin-bottom: 1rem;
    }
    .product-description-text {
        line-height: 1.75;
        white-space: pre-wrap;
        text-align: justify;
        color: #475569;
        font-size: 0.9375rem;
    }
    .details-table { width: 100%; border-collapse: collapse; }
    .details-table tr { border-bottom: var(--pd-border); }
    .details-table tr:last-child { border-bottom: none; }
    .details-table th {
        width: 120px;
        color: var(--pd-label);
        font-weight: 500;
        font-size: 0.875rem;
        padding: 0.625rem 0;
        vertical-align: top;
    }
    .details-table td { color: var(--pd-text); font-size: 0.9375rem; padding: 0.625rem 0; }
    .details-table code { font-size: 0.8125rem; background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px; color: #475569; font-weight: 500; }
    .details-table .badge { font-weight: 500; font-size: 0.75rem; }
    
    .reviews-section {
        background: #fff;
        border-radius: var(--pd-radius);
        padding: 1.75rem;
        box-shadow: var(--pd-shadow);
        border: var(--pd-border);
        margin-top: 1.5rem;
    }
    .reviews-section .section-label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--pd-label); margin-bottom: 0.25rem; }
    .reviews-section .section-title { font-size: 1.125rem; font-weight: 700; color: var(--pd-text); margin-bottom: 1rem; }
    .review-item {
        padding: 1.25rem 0;
        border-bottom: var(--pd-border);
    }
    .review-item:last-child { border-bottom: none; }
    .review-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; }
    .review-author { font-weight: 600; font-size: 0.9375rem; color: var(--pd-text); }
    .review-date { color: var(--pd-label); font-size: 0.8125rem; }
    
    .related-products { margin-top: 2.5rem; }
    .related-products .section-label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--pd-label); margin-bottom: 0.25rem; }
    .related-products .section-title { font-size: 1.125rem; font-weight: 700; color: var(--pd-text); margin-bottom: 1rem; }
    .related-products .product-card {
        background: #fff;
        border: var(--pd-border);
        border-radius: var(--pd-radius);
        overflow: hidden;
        box-shadow: var(--pd-shadow);
        transition: box-shadow 0.2s ease;
        height: 100%;
    }
    .related-products .product-card:hover { box-shadow: var(--pd-shadow-lg); }
    .related-products .card-img-top { height: 180px; object-fit: cover; background: #f8fafc; }
    .related-products .card-body { padding: 1rem; }
    .related-products .card-title { font-size: 0.9375rem; font-weight: 600; margin-bottom: 0.5rem; }
    .related-products .card-title a { color: var(--pd-text); }
    .related-products .card-title a:hover { color: #475569; }
    
    /* Fullscreen Image Viewer - Must be outside all containers */
    .fullscreen-viewer {
        display: none !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        min-width: 100vw !important;
        min-height: 100vh !important;
        max-width: 100vw !important;
        max-height: 100vh !important;
        margin: 0 !important;
        padding: 0 !important;
        background: rgba(0, 0, 0, 0.98) !important;
        backdrop-filter: blur(10px);
        z-index: 999999 !important;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.3s ease;
        overflow: hidden !important;
        box-sizing: border-box !important;
        transform: none !important;
    }
    
    /* Ensure no parent container affects it */
    body > .fullscreen-viewer {
        position: fixed !important;
    }
    
    .fullscreen-viewer.active {
        display: flex !important;
        align-items: center;
        justify-content: center;
        opacity: 1;
    }
    
    .fullscreen-image-container {
        position: relative !important;
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: default;
        padding: 60px 20px 120px 20px;
        box-sizing: border-box !important;
        margin: 0 !important;
    }
    
    .fullscreen-image {
        max-width: calc(100vw - 40px) !important;
        max-height: calc(100vh - 180px) !important;
        width: auto !important;
        height: auto !important;
        object-fit: contain !important;
        border-radius: 8px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8);
        transition: opacity 0.4s ease, transform 0.2s ease-out;
        animation: fadeInImage 0.4s ease;
        display: block !important;
        margin: 0 !important;
        transform-origin: center center;
        cursor: grab;
    }
    .fullscreen-image.zooming { cursor: grab; }
    .fullscreen-image.zooming:active { cursor: grabbing; }
    
    /* Override any container constraints */
    .container .fullscreen-viewer,
    .container-fluid .fullscreen-viewer,
    .row .fullscreen-viewer,
    [class*="col-"] .fullscreen-viewer {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 999999 !important;
    }
    
    @keyframes fadeInImage {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    .fullscreen-controls {
        position: absolute;
        top: 20px;
        right: 20px;
        display: flex;
        gap: 12px;
        z-index: 10000;
    }
    
    .fullscreen-controls button {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        color: white;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }
    
    .fullscreen-controls button:hover {
        background: rgba(255, 255, 255, 0.25);
        border-color: rgba(255, 255, 255, 0.6);
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
    }
    
    .fullscreen-controls button:active {
        transform: scale(0.95);
    }
    
    .fullscreen-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        color: white;
        width: 64px;
        height: 64px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 2rem;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }
    
    .fullscreen-nav:hover {
        background: rgba(255, 255, 255, 0.25);
        border-color: rgba(255, 255, 255, 0.6);
        transform: translateY(-50%) scale(1.15);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
    }
    
    .fullscreen-nav:active {
        transform: translateY(-50%) scale(0.95);
    }
    
    .fullscreen-nav.prev {
        left: 20px;
    }
    
    .fullscreen-nav.next {
        right: 20px;
    }
    
    .fullscreen-counter {
        position: absolute;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        color: white;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(10px);
        padding: 12px 24px;
        border-radius: 30px;
        font-size: 1rem;
        font-weight: 600;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    
    @media (max-width: 768px) {
        .product-title { font-size: 1.375rem; }
        .current-price { font-size: 1.75rem; }
        .main-image-wrap { height: 420px; }
        .main-image { height: 420px; }
        #mainVideoContainer .ratio { height: 420px !important; max-height: 420px !important; }
        #mainVideoContainer video { max-height: 420px !important; }
        #mainVideoContainer a[style*="height"] { height: 420px !important; }
        
        .image-zoom-hint {
            display: none;
        }
        .product-zoom-lens {
            display: none !important;
        }
        .fullscreen-image-container {
            padding: 10px;
        }
        
        .fullscreen-counter {
            bottom: 20px;
            font-size: 0.875rem;
            padding: 8px 16px;
        }
        
        
        .action-buttons {
            flex-direction: column;
        }
        
        .product-info-card {
            position: static;
        }
        
        .fullscreen-nav {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
        }
        
        .fullscreen-nav.prev {
            left: 10px;
        }
        
        .fullscreen-nav.next {
            right: 10px;
        }
        
        .fullscreen-controls {
            top: 10px;
            right: 10px;
        }
        
        .fullscreen-controls button {
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
        }
    }

    @media (max-width: 575px) {
        .product-detail-page .container { padding-left: 0.75rem; padding-right: 0.75rem; }
        .product-title { font-size: 1.1875rem; }
        .current-price { font-size: 1.5rem; }
        .main-image-wrap { height: 300px; }
        .main-image { height: 300px; }
        #mainVideoContainer .ratio { height: 300px !important; max-height: 300px !important; }
        #mainVideoContainer video { max-height: 300px !important; }
        #mainVideoContainer a[style*="height"] { height: 300px !important; }
        .breadcrumb { font-size: 0.75rem; }
        .product-gallery { border-radius: 10px; }
        .fullscreen-nav { width: 40px; height: 40px; font-size: 1.25rem; }
        .fullscreen-controls button { width: 36px; height: 36px; font-size: 1rem; }
    }

    @media (max-width: 399px) {
        .product-title { font-size: 1.0625rem; }
        .current-price { font-size: 1.375rem; }
        .main-image-wrap { height: 250px; }
        .main-image { height: 250px; }
        #mainVideoContainer .ratio { height: 250px !important; max-height: 250px !important; }
        #mainVideoContainer video { max-height: 250px !important; }
    }
</style>
@endpush

@section('content')
<div class="container py-4 product-detail-page">
    <nav aria-label="breadcrumb" class="breadcrumb-nav reveal">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('toyshop.products.index') }}">Products</a></li>
            @if($product->categories->count() > 0)
                <li class="breadcrumb-item">
                    <a href="{{ route('toyshop.products.index', ['category' => $product->categories->first()->id]) }}">
                        {{ $product->categories->first()->name }}
                    </a>
                </li>
                @if($product->categories->count() > 1)
                    <li class="breadcrumb-item">
                        <span class="text-muted">+{{ $product->categories->count() - 1 }} more</span>
                    </li>
                @endif
            @elseif($product->category)
                <li class="breadcrumb-item">
                    <a href="{{ route('toyshop.products.index', ['category' => $product->category->id]) }}">
                        {{ $product->category->name }}
                    </a>
                </li>
            @endif
            <li class="breadcrumb-item active">{{ Str::limit($product->name, 30) }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Product Gallery (Images + Video) -->
        <div class="col-lg-6">
            <div class="product-gallery reveal">
                @php
                    $hasVideo = !empty(trim($product->video_url ?? ''));
                    $videoUrlLower = $hasVideo ? strtolower(trim($product->video_url)) : '';
                    $isYoutube = $hasVideo && (str_contains($videoUrlLower, 'youtube.com') || str_contains($videoUrlLower, 'youtu.be'));
                    $isVimeo = $hasVideo && str_contains($videoUrlLower, 'vimeo.com');
                    $youtubeId = null;
                    $vimeoId = null;
                    if ($hasVideo && $isYoutube && preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\n?#]+)/i', $product->video_url, $m)) { $youtubeId = trim($m[1]); }
                    if ($hasVideo && $isVimeo && preg_match('/vimeo\.com\/(\d+)/i', $product->video_url, $m)) { $vimeoId = $m[1]; }
                    $videoEmbedUrl = $youtubeId ? 'https://www.youtube.com/embed/' . $youtubeId : ($vimeoId ? 'https://player.vimeo.com/video/' . $vimeoId : null);
                    $isEmbedVideo = $videoEmbedUrl !== null;
                    $videoFileSrc = null;
                    if ($hasVideo && !$videoEmbedUrl) {
                        $videoFileSrc = (str_starts_with($product->video_url, 'http://') || str_starts_with($product->video_url, 'https://')) ? $product->video_url : asset(ltrim($product->video_url, '/'));
                    }
                    $hasImages = $product->images->count() > 0;
                    $showVideoFirst = $hasVideo && !$hasImages;
                @endphp

                <div id="mainVideoContainer" class="main-image-container" style="{{ $showVideoFirst ? 'background: #000;' : 'display: none; background: #000;' }}">
                    @if($hasVideo)
                        @if($videoEmbedUrl)
                            <div class="ratio ratio-16x9" style="height: 680px; max-height: 680px;">
                                <iframe id="galleryVideoIframe" src="{{ $videoEmbedUrl }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="position: absolute; inset: 0; width: 100%; height: 100%;"></iframe>
                            </div>
                        @elseif($videoFileSrc)
                            <video id="galleryVideoFile" controls class="w-100" style="max-height: 680px; background: #000;" src="{{ $videoFileSrc }}"></video>
                        @else
                            <a href="{{ $product->video_url }}" target="_blank" rel="noopener noreferrer" class="d-flex align-items-center justify-content-center btn btn-outline-dark" style="height: 680px; border-radius: 8px;">
                                <i class="bi bi-play-circle me-2" style="font-size: 3rem;"></i> Watch Video
                            </a>
                        @endif
                    @endif
                </div>

                @if($hasImages)
                    @php
                        $firstImageUrl = $imageDisplayUrls[0] ?? asset('storage/' . $product->images->first()->image_path);
                    @endphp
                    <div id="mainImageContainer" class="main-image-container" style="{{ $showVideoFirst ? 'display: none;' : '' }}">
                        <div class="image-zoom-hint">
                            <i class="bi bi-zoom-in"></i>
                            <span>Hover to zoom · Click for fullscreen</span>
                        </div>
                        <div id="mainImageWrap" class="main-image-wrap">
                            <img id="mainImage" src="{{ $firstImageUrl }}" 
                                 class="main-image" 
                                 alt="{{ $product->name }}"
                                 loading="eager">
                            <div id="productZoomLens" class="product-zoom-lens" aria-hidden="true">
                                <div id="zoomLensImage" class="zoom-lens-image"></div>
                            </div>
                        </div>
                    </div>
                @elseif(!$hasVideo)
                    <div class="d-flex align-items-center justify-content-center" style="height: 680px; background: #fff;">
                        <i class="bi bi-image" style="font-size: 4rem; color: #cbd5e1;"></i>
                    </div>
                @endif

                @if($hasVideo || $hasImages)
                    <div class="thumbnail-gallery">
                        @if($hasVideo)
                            <div class="thumbnail thumbnail-video {{ $showVideoFirst ? 'active' : '' }}" onclick="showMainVideo(this);" onmouseenter="showMainVideo(this);" style="cursor: pointer; width: 72px; height: 72px; display: flex; align-items: center; justify-content: center; background: #334155; border-radius: 8px; border: 2px solid transparent;" title="Video">
                                <i class="bi bi-play-circle-fill text-white" style="font-size: 1.75rem;"></i>
                            </div>
                        @endif
                        @foreach($product->images as $index => $image)
                            @php
                                $thumbUrl = $imageDisplayUrls[$index] ?? asset('storage/' . $image->image_path);
                            @endphp
                            <img src="{{ asset('storage/' . $image->image_path) }}" 
                                 class="thumbnail {{ (!$hasVideo || !$showVideoFirst) && $index === 0 ? 'active' : '' }}" 
                                 alt="{{ $product->name }}"
                                 data-index="{{ $index }}"
                                 data-display-url="{{ $thumbUrl }}"
                                 onclick="showMainImage('{{ $thumbUrl }}', this, {{ $index }});"
                                 onmouseenter="showMainImage('{{ $thumbUrl }}', this, {{ $index }});"
                                 style="cursor: pointer;">
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-lg-6">
            <div class="product-info-card reveal" style="animation-delay: 0.1s;">
                <h1 class="product-title">{{ $product->name }}</h1>
                
                <div class="product-rating-section">
                    <div class="rating-display">
                        <div class="rating-stars">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= round($product->rating) ? '-fill' : '' }}"></i>
                            @endfor
                        </div>
                        @if($product->reviews_count > 0)
                            <span class="fw-semibold" style="color: var(--pd-text); font-size: 0.9375rem;">{{ number_format($product->rating, 1) }}</span>
                            <span class="text-muted">{{ $product->reviews_count }} {{ Str::plural('review', $product->reviews_count) }}</span>
                        @else
                            <span class="text-muted">No reviews yet</span>
                        @endif
                    </div>
                </div>

                <!-- Price -->
                <div class="price-section">
                    @php
                        $isDiscounted = $product->amazon_reference_price && $product->price < $product->amazon_reference_price;
                        $discountPercentage = $isDiscounted ? $product->getPriceDifferencePercentage() : null;
                    @endphp
                    
                    @if($isDiscounted)
                        <div class="current-price">₱{{ number_format($product->price, 2) }}</div>
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <span class="original-price">₱{{ number_format($product->amazon_reference_price, 2) }}</span>
                            @if($discountPercentage)
                                <span class="discount-badge">
                                    <i class="bi bi-tag-fill me-1"></i>Save {{ number_format(abs($discountPercentage), 0) }}%
                                </span>
                            @endif
                        </div>
                    @else
                        <div class="current-price">₱{{ number_format($product->price, 2) }}</div>
                    @endif
                </div>

                <!-- Product options (variations: color, size, model, etc.) -->
                @if($product->variations->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 0.875rem;">Choose option <span class="text-danger">*</span></label>
                        <select id="productVariationSelect" name="product_variation_id" class="form-select" style="max-width: 280px; border-radius: 8px;" required>
                            <option value="">-- Select option --</option>
                            @foreach($product->variations as $v)
                                @if($v->is_available)
                                    <option value="{{ $v->id }}"
                                            data-price-adjustment="{{ $v->price_adjustment }}"
                                            data-stock="{{ $v->stock_quantity }}"
                                            data-label="{{ $v->variation_type }}: {{ $v->variation_value }}">
                                        {{ $v->variation_type }}: {{ $v->variation_value }}
                                        @if((float)$v->price_adjustment != 0)
                                            ({{ (float)$v->price_adjustment > 0 ? '+' : '' }}₱{{ number_format($v->price_adjustment, 2) }})
                                        @endif
                                        — {{ $v->stock_quantity }} in stock
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <div id="variationPriceNote" class="small text-muted mt-1" style="display: none;"></div>
                    </div>
                @endif

                <!-- Stock Status (when no variations) -->
                <div id="productStockSection">
                    @if($product->variations->isEmpty())
                        @if($product->isInStock())
                            <span class="stock-badge bg-success text-white">
                                <i class="bi bi-check-circle-fill"></i>
                                In Stock ({{ $product->stock_quantity }} available)
                            </span>
                        @else
                            <span class="stock-badge bg-danger text-white">
                                <i class="bi bi-x-circle-fill"></i>
                                Out of Stock
                            </span>
                        @endif
                    @else
                        <span class="stock-badge bg-secondary text-white" id="variationStockBadge" style="display: none;">
                            <i class="bi bi-box-seam"></i>
                            <span id="variationStockText">Select an option</span>
                        </span>
                    @endif
                </div>

                <div class="seller-card">
                    <div class="text-muted mb-1"><i class="bi bi-shop me-1"></i>Sold by</div>
                    <div class="seller-name">{{ $product->seller->business_name }}</div>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="rating-stars" style="font-size: 1rem;">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= $product->seller->rating ? '-fill' : '' }}"></i>
                            @endfor
                        </div>
                        <span class="text-muted">{{ number_format($product->seller->rating, 1) }} ({{ $product->seller->total_reviews }} reviews)</span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('toyshop.business.show', $product->seller->business_slug) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-store me-1"></i>View Store
                        </a>
                        @auth
                            <a href="{{ route('reports.create', ['type' => 'seller', 'id' => $product->seller->id]) }}" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-flag me-1"></i>Report
                            </a>
                        @endauth
                    </div>
                </div>

                <!-- Add to Cart -->
                @auth
                    <form action="{{ route('cart.add') }}" method="POST" class="mb-3" id="addToCartForm">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        @if($product->variations->isNotEmpty())
                            <input type="hidden" name="product_variation_id" id="form_product_variation_id" value="">
                        @endif
                        <div class="quantity-selector">
                            <label class="fw-bold mb-0">Quantity:</label>
                            <div class="quantity-input-group">
                                <button type="button" class="quantity-btn" onclick="decreaseQuantity()" id="decreaseQtyBtn" {{ !$product->isInStock() && $product->variations->isEmpty() ? 'disabled' : '' }}>
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" name="quantity" id="quantityInput" class="quantity-input" value="1" min="1" max="{{ $product->variations->isNotEmpty() ? 1 : $product->stock_quantity }}" {{ !$product->isInStock() && $product->variations->isEmpty() ? 'disabled' : '' }}>
                                <button type="button" class="quantity-btn" onclick="increaseQuantity()" id="increaseQtyBtn" {{ !$product->isInStock() && $product->variations->isEmpty() ? 'disabled' : '' }}>
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="action-buttons">
                            <button type="submit" class="btn btn-primary" id="addToCartBtn" {{ ($product->variations->isNotEmpty() || !$product->isInStock()) ? 'disabled' : '' }}>
                                <i class="bi bi-cart-plus me-2"></i>Add to Cart
                            </button>
                            @if($inWishlist)
                                <form action="{{ route('wishlist.remove', $wishlistItem->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-heart-fill me-2"></i>Wishlisted
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('wishlist.add') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-heart me-2"></i>Add to Wishlist
                                    </button>
                                </form>
                            @endif
                        </div>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary w-100 mb-2" style="padding: 0.75rem 1rem; font-size: 0.9375rem; font-weight: 600; border-radius: 8px;">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign in to purchase
                    </a>
                @endauth
                @auth
                    <a href="{{ route('reports.create', ['type' => 'product', 'id' => $product->id]) }}" class="btn btn-outline-secondary w-100" style="font-size: 0.875rem; border-radius: 8px; padding: 0.5rem 1rem;">
                        <i class="bi bi-flag me-2"></i>Report this product
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <div class="product-details-card reveal">
        <div class="section-label">Specifications</div>
        <h2 class="section-title">Product Details</h2>
        <table class="details-table">
            <tr>
                <th>Brand:</th>
                <td>{{ $product->brand ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>SKU:</th>
                <td><code>{{ $product->sku }}</code></td>
            </tr>
            <tr>
                <th>Condition:</th>
                <td><span class="badge bg-info">{{ ucfirst($product->condition ?? 'new') }}</span></td>
            </tr>
            @if($product->weight)
                <tr>
                    <th>Weight:</th>
                    <td>{{ $product->weight }} kg</td>
                </tr>
            @endif
            <tr>
                <th>Categories:</th>
                <td>
                    @if($product->categories->count() > 0)
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($product->categories as $category)
                                <a href="{{ route('toyshop.products.index', ['category' => $category->id]) }}" 
                                   class="badge bg-primary text-decoration-none" 
                                   style="font-size: 0.875rem; padding: 0.5rem 0.75rem;">
                                    <i class="bi bi-tag me-1"></i>{{ $category->name }}
                                </a>
                            @endforeach
                        </div>
                    @elseif($product->category)
                        <a href="{{ route('toyshop.products.index', ['category' => $product->category->id]) }}" 
                           class="badge bg-primary text-decoration-none" 
                           style="font-size: 0.875rem; padding: 0.5rem 0.75rem;">
                            <i class="bi bi-tag me-1"></i>{{ $product->category->name }}
                        </a>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="product-details-card reveal">
        <div class="section-label">About this item</div>
        <h2 class="section-title">Description</h2>
        <div class="product-description-text">{{ $product->description }}</div>
    </div>

    <div class="reviews-section reveal">
        <div class="section-label">Feedback</div>
        <h2 class="section-title">Customer Reviews ({{ $product->reviews_count }})</h2>
        
        @if($product->reviews->count() > 0)
            @foreach($product->reviews->take(10) as $review)
                <div class="review-item">
                    <div class="review-header">
                        <div>
                            <div class="review-author">{{ $review->user->name }}</div>
                            <div class="rating-stars" style="font-size: 0.875rem; margin-top: 0.25rem;">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                @endfor
                            </div>
                        </div>
                        <div class="review-date">{{ $review->created_at->format('M d, Y') }}</div>
                    </div>
                    <p class="mt-1 mb-0">{{ $review->review_text }}</p>
                </div>
            @endforeach
        @else
            <div class="text-center py-5">
                <i class="bi bi-chat-left-text" style="font-size: 2.5rem; color: #cbd5e1; display: block; margin-bottom: 0.75rem;"></i>
                <p class="mb-0" style="color: #64748b; font-size: 0.9375rem;">No reviews yet. Be the first to review this product.</p>
            </div>
        @endif
        
        @auth
            @php
                $userOrder = \App\Models\Order::where('user_id', auth()->id())
                    ->whereHas('items', function($q) use ($product) {
                        $q->where('product_id', $product->id);
                    })
                    ->where('status', 'delivered')
                    ->first();
            @endphp
            @if($userOrder && !$product->reviews->where('user_id', auth()->id())->count())
                <div class="mt-4 pt-4" style="border-top: 1px solid #f0e6dc;">
                    <div class="section-label">Share your experience</div>
                    <h3 class="section-title" style="margin-bottom: 1rem;">Write a Review</h3>
                    <form action="{{ route('reviews.product.store', $product->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $userOrder->id }}">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.875rem;">Rating <span class="text-danger">*</span></label>
                            <select name="rating" class="form-select" required style="max-width: 220px; border-radius: 8px;">
                                <option value="5">5 – Excellent</option>
                                <option value="4">4 – Very Good</option>
                                <option value="3">3 – Good</option>
                                <option value="2">2 – Fair</option>
                                <option value="1">1 – Poor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.875rem;">Your Review</label>
                            <textarea name="review_text" class="form-control" rows="4" placeholder="Share your experience with this product..." style="border-radius: 8px;"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="border-radius: 8px; font-weight: 600;">
                            <i class="bi bi-check-circle me-2"></i>Submit Review
                        </button>
                    </form>
                </div>
            @endif
        @endauth
    </div>

    @if(isset($recommendedByPreferences) && $recommendedByPreferences->count() > 0)
        <div class="related-products reveal">
            <div class="section-label">Recommended for you</div>
            <h2 class="section-title">Based on your preferred categories</h2>
            <div class="row g-3">
                @foreach($recommendedByPreferences as $relatedProduct)
                    <div class="col-6 col-md-3">
                        <div class="card product-card">
                            <a href="{{ route('toyshop.products.show', $relatedProduct->slug) }}" class="text-decoration-none">
                                @if($relatedProduct->images->first())
                                    <img src="{{ asset('storage/' . $relatedProduct->images->first()->image_path) }}" 
                                         class="card-img-top" 
                                         alt="{{ $relatedProduct->name }}">
                                @else
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px;"><i class="bi bi-image text-muted" style="font-size: 2rem;"></i></div>
                                @endif
                            </a>
                            <div class="card-body">
                                <h6 class="card-title">
                                    <a href="{{ route('toyshop.products.show', $relatedProduct->slug) }}" class="text-decoration-none">
                                        {{ Str::limit($relatedProduct->name, 45) }}
                                    </a>
                                </h6>
                                <p class="mb-2 fw-semibold" style="color: #0f172a; font-size: 0.9375rem;">₱{{ number_format($relatedProduct->price, 2) }}</p>
                                <a href="{{ route('toyshop.products.show', $relatedProduct->slug) }}" class="btn btn-sm btn-outline-dark w-100" style="border-radius: 6px; font-weight: 500;">View</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($relatedProducts->count() > 0)
        <div class="related-products reveal">
            <div class="section-label">You may also like</div>
            <h2 class="section-title">Related Products</h2>
            <div class="row g-3">
                @foreach($relatedProducts as $relatedProduct)
                    <div class="col-6 col-md-3">
                        <div class="card product-card">
                            <a href="{{ route('toyshop.products.show', $relatedProduct->slug) }}" class="text-decoration-none">
                                @if($relatedProduct->images->first())
                                    <img src="{{ asset('storage/' . $relatedProduct->images->first()->image_path) }}" 
                                         class="card-img-top" 
                                         alt="{{ $relatedProduct->name }}">
                                @else
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px;"><i class="bi bi-image text-muted" style="font-size: 2rem;"></i></div>
                                @endif
                            </a>
                            <div class="card-body">
                                <h6 class="card-title">
                                    <a href="{{ route('toyshop.products.show', $relatedProduct->slug) }}" class="text-decoration-none">
                                        {{ Str::limit($relatedProduct->name, 45) }}
                                    </a>
                                </h6>
                                <p class="mb-2 fw-semibold" style="color: #0f172a; font-size: 0.9375rem;">₱{{ number_format($relatedProduct->price, 2) }}</p>
                                <a href="{{ route('toyshop.products.show', $relatedProduct->slug) }}" class="btn btn-sm btn-outline-dark w-100" style="border-radius: 6px; font-weight: 500;">View</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<!-- Fullscreen Image Viewer - Outside container for true fullscreen -->
<div id="fullscreenViewer" class="fullscreen-viewer" onclick="closeFullscreen()">
    <div class="fullscreen-image-container" onclick="event.stopPropagation();">
        <button class="fullscreen-nav prev" onclick="event.stopPropagation(); previousImage();" title="Previous">
            <i class="bi bi-chevron-left"></i>
        </button>
        <img id="fullscreenImage" class="fullscreen-image" src="" alt="{{ $product->name }}">
        <button class="fullscreen-nav next" onclick="event.stopPropagation(); nextImage();" title="Next">
            <i class="bi bi-chevron-right"></i>
        </button>
        <div class="fullscreen-controls">
            <button onclick="event.stopPropagation(); zoomIn();" title="Zoom in">
                <i class="bi bi-zoom-in"></i>
            </button>
            <button onclick="event.stopPropagation(); zoomOut();" title="Zoom out">
                <i class="bi bi-zoom-out"></i>
            </button>
            <button onclick="event.stopPropagation(); resetZoom();" title="Reset zoom">
                <i class="bi bi-fullscreen"></i>
            </button>
            <button onclick="event.stopPropagation(); closeFullscreen();" title="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="fullscreen-counter">
            <span id="imageCounter">1 / {{ $product->images->count() }}</span>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Product images data (HD display URLs when available)
    const productImages = [
        @foreach($product->images as $index => $image)
            '{{ $imageDisplayUrls[$index] ?? asset('storage/' . $image->image_path) }}',
        @endforeach
    ];
    
    let currentImageIndex = 0;
    let gallerySlideshowInterval = null; // Non-fullscreen gallery slideshow (every 4s)
    const GALLERY_SLIDESHOW_MS = 4000;
    let showingVideo = @json($showVideoFirst ?? false);
    // Fullscreen zoom/pan
    let fsScale = 1, fsTranslateX = 0, fsTranslateY = 0;
    let fsDragStart = null, fsDragStartTranslate = { x: 0, y: 0 };
    
    function initProductGallery() {
        if (productImages.length === 0) return;
        if (currentImageIndex < 0 || currentImageIndex >= productImages.length) currentImageIndex = 0;
        // Move fullscreen viewer to body immediately so it's never inside a constrained container
        var viewer = document.getElementById('fullscreenViewer');
        if (viewer && viewer.parentElement && viewer.parentElement !== document.body) {
            document.body.appendChild(viewer);
        }
        // Make whole main image area open fullscreen (image + hint) so click always works
        var mainContainer = document.getElementById('mainImageContainer');
        if (mainContainer) {
            mainContainer.style.cursor = 'zoom-in';
            mainContainer.onclick = function(e) {
                if (productImages.length && !showingVideo) {
                    e.preventDefault();
                    e.stopPropagation();
                    openFullscreen(currentImageIndex);
                }
            };
        }
        var mainImage = document.getElementById('mainImage');
        if (mainImage) {
            mainImage.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (productImages.length && !showingVideo) openFullscreen(currentImageIndex);
            };
        }
        // Start non-fullscreen gallery slideshow (every 4 seconds when multiple images and not video)
        if (productImages.length > 1 && !showingVideo) startGallerySlideshow();
        // Hover zoom on main image
        initHoverZoom();
    }
    
    function initHoverZoom() {
        var wrap = document.getElementById('mainImageWrap');
        var mainImg = document.getElementById('mainImage');
        var lens = document.getElementById('productZoomLens');
        var lensImage = document.getElementById('zoomLensImage');
        if (!wrap || !mainImg || !lens || !lensImage) return;
        var lensSize = 280;
        var zoomFactor = 2.8;
        lens.style.width = lensSize + 'px';
        lens.style.height = lensSize + 'px';
        lensImage.style.width = (lensSize * zoomFactor) + 'px';
        lensImage.style.height = (lensSize * zoomFactor) + 'px';
        lensImage.style.left = '50%';
        lensImage.style.top = '50%';
        lensImage.style.marginLeft = (-lensSize * zoomFactor / 2) + 'px';
        lensImage.style.marginTop = (-lensSize * zoomFactor / 2) + 'px';
        wrap.addEventListener('mouseenter', function(e) {
            if (showingVideo || productImages.length === 0) return;
            lensImage.style.backgroundImage = 'url("' + (mainImg.currentSrc || mainImg.src) + '")';
            lensImage.style.backgroundSize = (zoomFactor * 100) + '% ' + (zoomFactor * 100) + '%';
            lens.classList.add('active');
            // Pause slideshow when hovering over the main image
            stopGallerySlideshow();
        });
        wrap.addEventListener('mouseleave', function() {
            lens.classList.remove('active');
            // Resume slideshow when mouse leaves (if multiple images and not in fullscreen)
            if (productImages.length > 1 && !showingVideo) {
                var viewer = document.getElementById('fullscreenViewer');
                if (!viewer || !viewer.classList.contains('active')) startGallerySlideshow();
            }
        });
        wrap.addEventListener('mousemove', function(e) {
            if (!lens.classList.contains('active')) return;
            var rect = wrap.getBoundingClientRect();
            var x = e.clientX - rect.left;
            var y = e.clientY - rect.top;
            var pctX = (x / rect.width) * 100;
            var pctY = (y / rect.height) * 100;
            lens.style.left = Math.max(0, Math.min(rect.width - lensSize, x - lensSize/2)) + 'px';
            lens.style.top = Math.max(0, Math.min(rect.height - lensSize, y - lensSize/2)) + 'px';
            lensImage.style.backgroundPosition = (pctX) + '% ' + (pctY) + '%';
            lensImage.style.backgroundImage = 'url("' + (mainImg.currentSrc || mainImg.src) + '")';
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProductGallery);
    } else {
        initProductGallery();
    }
    
    function startGallerySlideshow() {
        stopGallerySlideshow();
        gallerySlideshowInterval = setInterval(function() {
            if (showingVideo) return;
            var viewer = document.getElementById('fullscreenViewer');
            if (viewer && viewer.classList.contains('active')) return;
            var mainImg = document.getElementById('mainImage');
            var mainContainer = document.getElementById('mainImageContainer');
            if (!mainImg || !mainContainer || mainContainer.style.display === 'none') return;
            currentImageIndex = (currentImageIndex + 1) % productImages.length;
            mainImg.classList.add('main-image-slide');
            mainImg.classList.remove('visible', 'main-image-slide-prev');
            mainImg.style.transform = 'translateX(24%)';
            setTimeout(function() {
                mainImg.src = productImages[currentImageIndex];
                mainImg.classList.add('visible');
                mainImg.style.transform = 'translateX(0)';
                requestAnimationFrame(function() {
                    setTimeout(function() {
                        mainImg.classList.remove('main-image-slide');
                    }, 450);
                });
            }, 50);
            // Sync thumbnails: clear all, then set active only on the current image thumbnail
            document.querySelectorAll('.thumbnail').forEach(function(t) { t.classList.remove('active'); });
            document.querySelectorAll('.thumbnail[data-index]').forEach(function(thumb) {
                if (parseInt(thumb.getAttribute('data-index'), 10) === currentImageIndex) thumb.classList.add('active');
            });
        }, GALLERY_SLIDESHOW_MS);
    }
    
    function stopGallerySlideshow() {
        if (gallerySlideshowInterval) {
            clearInterval(gallerySlideshowInterval);
            gallerySlideshowInterval = null;
        }
    }
    
    function isVideoVisible() {
        const vc = document.getElementById('mainVideoContainer');
        return vc && vc.style.display !== 'none';
    }
    
    function showMainVideo(thumbElement) {
        const mainVideoContainer = document.getElementById('mainVideoContainer');
        const mainImageContainer = document.getElementById('mainImageContainer');
        if (mainVideoContainer) mainVideoContainer.style.display = '';
        if (mainImageContainer) mainImageContainer.style.display = 'none';
        document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
        if (thumbElement) thumbElement.classList.add('active');
        showingVideo = true;
    }
    
    function showMainImage(src, element, index) {
        if (productImages.length && (index < 0 || index >= productImages.length)) return;
        showingVideo = false;
        const mainVideoContainer = document.getElementById('mainVideoContainer');
        const mainImageContainer = document.getElementById('mainImageContainer');
        if (mainVideoContainer) mainVideoContainer.style.display = 'none';
        if (mainImageContainer) mainImageContainer.style.display = '';
        const mainImage = document.getElementById('mainImage');
        if (mainImage && src) {
            mainImage.classList.remove('main-image-slide');
            mainImage.classList.add('visible');
            mainImage.src = src;
        }
        document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
        if (element) element.classList.add('active');
        currentImageIndex = index;
        // Reset gallery slideshow timer when user picks a thumbnail
        if (productImages.length > 1 && !document.getElementById('fullscreenViewer').classList.contains('active')) {
            stopGallerySlideshow();
            startGallerySlideshow();
        }
    }
    
    function changeMainImage(src, element, index) {
        if (index < 0 || index >= productImages.length) return;
        showMainImage(src, element, index);
    }
    
    function openFullscreen(index) {
        if (productImages.length === 0) {
            console.warn('No images to display');
            return;
        }
        
        // Validate index
        if (index < 0 || index >= productImages.length) {
            index = 0;
        }
        
        currentImageIndex = index;
        let viewer = document.getElementById('fullscreenViewer');
        const fullscreenImage = document.getElementById('fullscreenImage');
        const imageCounter = document.getElementById('imageCounter');
        
        if (!viewer || !fullscreenImage || !imageCounter) {
            console.error('Fullscreen viewer elements not found');
            return;
        }
        
        if (viewer.parentElement !== document.body) {
            document.body.appendChild(viewer);
        }
        
        fullscreenImage.src = productImages[index];
        imageCounter.textContent = (index + 1) + ' / ' + productImages.length;
        
        document.body.style.overflow = 'hidden';
        document.body.style.position = 'fixed';
        document.body.style.width = '100%';
        document.documentElement.style.overflow = 'hidden';
        
        viewer.style.cssText = 'display:flex!important;position:fixed!important;top:0!important;left:0!important;right:0!important;bottom:0!important;width:100vw!important;height:100vh!important;min-width:100vw!important;min-height:100vh!important;max-width:100vw!important;max-height:100vh!important;margin:0!important;padding:0!important;background:rgba(0,0,0,0.98)!important;z-index:999999!important;opacity:1!important;overflow:hidden!important;box-sizing:border-box!important;transform:none!important;align-items:center;justify-content:center;';
        viewer.classList.add('active');
        
        stopGallerySlideshow();
        preloadAdjacentImages(index);
        resetZoom();
        
        if (fullscreenImage) {
            fullscreenImage.addEventListener('wheel', onFullscreenWheel, { passive: false });
            fullscreenImage.addEventListener('mousedown', onFullscreenDragStart);
        }
        document.addEventListener('mousemove', onFullscreenDragMove);
        document.addEventListener('mouseup', onFullscreenDragEnd);
    }
    
    function onFullscreenWheel(e) {
        const viewer = document.getElementById('fullscreenViewer');
        if (!viewer || !viewer.classList.contains('active')) return;
        e.preventDefault();
        if (e.deltaY < 0) fsScale = Math.min(fsScale + 0.15, 4);
        else fsScale = Math.max(fsScale - 0.15, 0.5);
        applyFullscreenTransform();
    }
    
    function onFullscreenDragStart(e) {
        if (e.button !== 0) return;
        fsDragStart = { x: e.clientX, y: e.clientY };
        fsDragStartTranslate = { x: fsTranslateX, y: fsTranslateY };
    }
    
    function onFullscreenDragMove(e) {
        if (!fsDragStart || e.buttons !== 1) return;
        fsTranslateX = fsDragStartTranslate.x + (e.clientX - fsDragStart.x);
        fsTranslateY = fsDragStartTranslate.y + (e.clientY - fsDragStart.y);
        applyFullscreenTransform();
    }
    
    function onFullscreenDragEnd() {
        fsDragStart = null;
    }
    
    function preloadAdjacentImages(currentIndex) {
        const nextIndex = (currentIndex + 1) % productImages.length;
        const prevIndex = (currentIndex - 1 + productImages.length) % productImages.length;
        
        const nextImg = new Image();
        nextImg.src = productImages[nextIndex];
        
        const prevImg = new Image();
        prevImg.src = productImages[prevIndex];
    }
    
    function closeFullscreen() {
        const viewer = document.getElementById('fullscreenViewer');
        if (!viewer) return;
        
        const fullscreenImage = document.getElementById('fullscreenImage');
        if (fullscreenImage) fullscreenImage.removeEventListener('wheel', onFullscreenWheel);
        fullscreenImage && fullscreenImage.removeEventListener('mousedown', onFullscreenDragStart);
        document.removeEventListener('mousemove', onFullscreenDragMove);
        document.removeEventListener('mouseup', onFullscreenDragEnd);
        
        viewer.classList.remove('active');
        viewer.style.cssText = 'display: none !important;';
        
        document.body.style.overflow = '';
        document.body.style.position = '';
        document.body.style.width = '';
        document.documentElement.style.overflow = '';
        
        resetZoom();
        if (productImages.length > 1 && !showingVideo) startGallerySlideshow();
    }
    
    function nextImage() {
        if (productImages.length === 0) return;
        var viewer = document.getElementById('fullscreenViewer');
        if (!viewer || !viewer.classList.contains('active')) {
            currentImageIndex = (currentImageIndex + 1) % productImages.length;
            var thumb = document.querySelector('.thumbnail[data-index="' + currentImageIndex + '"]');
            var mainImg = document.getElementById('mainImage');
            if (mainImg && thumb) changeMainImage(productImages[currentImageIndex], thumb, currentImageIndex);
            return;
        }
        currentImageIndex = (currentImageIndex + 1) % productImages.length;
        updateFullscreenImage();
    }
    
    function previousImage() {
        if (productImages.length === 0) return;
        var viewer = document.getElementById('fullscreenViewer');
        if (!viewer || !viewer.classList.contains('active')) {
            currentImageIndex = (currentImageIndex - 1 + productImages.length) % productImages.length;
            var thumb = document.querySelector('.thumbnail[data-index="' + currentImageIndex + '"]');
            var mainImg = document.getElementById('mainImage');
            if (mainImg && thumb) changeMainImage(productImages[currentImageIndex], thumb, currentImageIndex);
            return;
        }
        currentImageIndex = (currentImageIndex - 1 + productImages.length) % productImages.length;
        updateFullscreenImage();
    }
    
    function updateFullscreenImage() {
        const viewer = document.getElementById('fullscreenViewer');
        if (!viewer || !viewer.classList.contains('active')) return;
        
        const fullscreenImage = document.getElementById('fullscreenImage');
        const imageCounter = document.getElementById('imageCounter');
        
        if (!fullscreenImage || !imageCounter) return;
        
        fullscreenImage.style.opacity = '0';
        
        setTimeout(() => {
            fullscreenImage.src = productImages[currentImageIndex];
            imageCounter.textContent = `${currentImageIndex + 1} / ${productImages.length}`;
            resetZoom();
            
            const thumbnails = document.querySelectorAll('.thumbnail');
            thumbnails.forEach((thumb, index) => {
                thumb.classList.toggle('active', index === currentImageIndex);
            });
            
            setTimeout(() => {
                fullscreenImage.style.opacity = '1';
            }, 50);
        }, 150);
        
        preloadAdjacentImages(currentImageIndex);
    }
    
    // --- Fullscreen zoom/pan ---
    function applyFullscreenTransform() {
        const img = document.getElementById('fullscreenImage');
        if (!img) return;
        img.style.transform = 'translate(' + fsTranslateX + 'px, ' + fsTranslateY + 'px) scale(' + fsScale + ')';
    }
    
    function zoomIn() {
        fsScale = Math.min(fsScale + 0.5, 4);
        applyFullscreenTransform();
    }
    
    function zoomOut() {
        fsScale = Math.max(fsScale - 0.5, 0.5);
        applyFullscreenTransform();
    }
    
    function resetZoom() {
        fsScale = 1;
        fsTranslateX = 0;
        fsTranslateY = 0;
        applyFullscreenTransform();
    }
    
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        const viewer = document.getElementById('fullscreenViewer');
        if (!viewer || !viewer.classList.contains('active')) return;
        
        switch(e.key) {
            case 'Escape':
                e.preventDefault();
                closeFullscreen();
                break;
            case 'ArrowLeft':
                e.preventDefault();
                previousImage();
                break;
            case 'ArrowRight':
                e.preventDefault();
                nextImage();
                break;
        }
    });
    
    // Prevent body scroll when fullscreen is active
    document.addEventListener('wheel', function(e) {
        const viewer = document.getElementById('fullscreenViewer');
        if (viewer && viewer.classList.contains('active')) {
            e.preventDefault();
        }
    }, { passive: false });
    
    // Touch gestures for mobile
    let touchStartX = 0;
    let touchStartY = 0;
    let touchEndX = 0;
    let touchEndY = 0;
    
    const fullscreenViewer = document.getElementById('fullscreenViewer');
    if (fullscreenViewer) {
        fullscreenViewer.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
            touchStartY = e.changedTouches[0].screenY;
        }, { passive: true });
        
        fullscreenViewer.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            touchEndY = e.changedTouches[0].screenY;
            handleSwipe();
        }, { passive: true });
    }
    
    function handleSwipe() {
        const deltaX = touchEndX - touchStartX;
        const deltaY = touchEndY - touchStartY;
        
        // Only handle horizontal swipes (ignore vertical scrolling)
        if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 50) {
            if (deltaX > 0) {
                // Swipe right - previous image
                previousImage();
            } else {
                // Swipe left - next image
                nextImage();
            }
        }
    }
    
    // Handle image loading errors
    document.addEventListener('error', function(e) {
        if (e.target && e.target.id === 'fullscreenImage') {
            console.error('Failed to load image:', e.target.src);
            e.target.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="300"%3E%3Crect width="400" height="300" fill="%23f0f0f0"/%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" dy=".3em" fill="%23999"%3EImage not found%3C/text%3E%3C/svg%3E';
        }
    }, true);
    
    // Add loading indicator for images
    const fullscreenImage = document.getElementById('fullscreenImage');
    if (fullscreenImage) {
        fullscreenImage.addEventListener('loadstart', function() {
            this.style.opacity = '0.5';
        });
        
        fullscreenImage.addEventListener('load', function() {
            this.style.opacity = '1';
        });
    }
    
    function increaseQuantity() {
        const input = document.getElementById('quantityInput');
        const max = parseInt(input.max, 10) || 999;
        const current = parseInt(input.value, 10) || 1;
        if (current < max) {
            input.value = current + 1;
        }
    }
    
    function decreaseQuantity() {
        const input = document.getElementById('quantityInput');
        const current = parseInt(input.value, 10) || 1;
        if (current > 1) {
            input.value = current - 1;
        }
    }
    
    // Product options (variations): sync select to hidden input, update quantity max and stock display
    (function() {
        const select = document.getElementById('productVariationSelect');
        const formVariationInput = document.getElementById('form_product_variation_id');
        const quantityInput = document.getElementById('quantityInput');
        const addToCartBtn = document.getElementById('addToCartBtn');
        const stockBadge = document.getElementById('variationStockBadge');
        const stockText = document.getElementById('variationStockText');
        if (!select || !formVariationInput) return;
        
        function onVariationChange() {
            const val = select.value;
            formVariationInput.value = val || '';
            const decreaseBtn = document.getElementById('decreaseQtyBtn');
            const increaseBtn = document.getElementById('increaseQtyBtn');
            
            if (!val) {
                if (quantityInput) { 
                    quantityInput.max = 1; 
                    quantityInput.value = 1;
                    quantityInput.disabled = true; 
                }
                if (decreaseBtn) decreaseBtn.disabled = true;
                if (increaseBtn) increaseBtn.disabled = true;
                if (addToCartBtn) addToCartBtn.disabled = true;
                if (stockBadge) stockBadge.style.display = 'none';
                return;
            }
            const opt = select.options[select.selectedIndex];
            const stock = parseInt(opt.getAttribute('data-stock'), 10) || 0;
            if (quantityInput) {
                quantityInput.max = stock;
                quantityInput.disabled = stock <= 0;
                if (parseInt(quantityInput.value, 10) > stock) quantityInput.value = Math.min(1, stock);
                if (quantityInput.value < 1) quantityInput.value = 1;
            }
            if (decreaseBtn) decreaseBtn.disabled = stock <= 0;
            if (increaseBtn) increaseBtn.disabled = stock <= 0;
            if (addToCartBtn) addToCartBtn.disabled = stock <= 0;
            if (stockBadge && stockText) {
                stockBadge.style.display = 'inline-flex';
                stockText.textContent = stock > 0 ? (stock + ' in stock') : 'Out of stock';
                stockBadge.className = 'stock-badge ' + (stock > 0 ? 'bg-success text-white' : 'bg-danger text-white');
            }
        }
        
        select.addEventListener('change', onVariationChange);
        onVariationChange();
        
        document.getElementById('addToCartForm').addEventListener('submit', function(e) {
            if (select.value === '' || select.value === null) {
                e.preventDefault();
                select.focus();
                return;
            }
        });
    })();
</script>
@endpush
@endsection
