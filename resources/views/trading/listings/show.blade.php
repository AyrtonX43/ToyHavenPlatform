@extends('layouts.toyshop')
@section('title', $listing->title . ' - ToyHaven Trade')
@section('content')
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<style>
.trade-listing-page { font-family: 'Quicksand', sans-serif; max-width: 100%; padding: 0 2rem; margin: 0 auto; }
.trade-listing-page .text-justify { text-align: justify; }

/* Maximized layout */
.trade-listing-page .trade-content { max-width: 1440px; margin: 0 auto; }

/* Image gallery - hover zoom */
.listing-gallery-main {
    aspect-ratio: 4/3;
    background: #f8fafc;
    border-radius: 16px;
    overflow: hidden;
    position: relative;
    border: 1px solid #e2e8f0;
    cursor: zoom-in;
}
.listing-gallery-main .main-image-wrapper {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.listing-gallery-main .listing-main-img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: transform 0.15s ease-out;
    transform-origin: center center;
}
.listing-zoom-hint {
    position: absolute;
    bottom: 12px;
    right: 12px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.8125rem;
    z-index: 10;
}
.listing-thumbnails { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
.listing-thumb {
    width: 72px; height: 72px; border-radius: 10px; object-fit: cover; cursor: pointer;
    border: 2px solid transparent; transition: border-color 0.2s, box-shadow 0.2s;
}
.listing-thumb:hover, .listing-thumb.active { border-color: #0ea5e9; box-shadow: 0 0 0 1px #0ea5e9; }

/* Content cards */
.listing-card { background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 2px 12px rgba(0,0,0,0.04); overflow: hidden; }
.listing-card-header { font-weight: 700; font-size: 1rem; color: #1e293b; padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; background: linear-gradient(180deg, #fafbfc 0%, #fff 100%); }
.listing-card-body { padding: 1.25rem; }
.listing-condition { display: inline-flex; align-items: center; gap: 6px; padding: 0.35rem 0.75rem; background: #f1f5f9; border-radius: 10px; font-size: 0.875rem; font-weight: 600; color: #475569; }
.trade-type-pill { display: inline-flex; padding: 0.35rem 0.85rem; border-radius: 20px; font-size: 0.8125rem; font-weight: 600; }
.trade-type-pill.exchange { background: #dbeafe; color: #1d4ed8; }
.trade-type-pill.exchange_with_cash { background: #e0e7ff; color: #4338ca; }
.trade-type-pill.cash { background: #d1fae5; color: #047857; }
.listing-price { font-size: 1.5rem; font-weight: 700; color: #0ea5e9; letter-spacing: -0.02em; }
.listing-description { line-height: 1.7; }

/* Map */
#listingMap { height: 300px; border-radius: 12px; border: 1px solid #e2e8f0; min-height: 280px; }

/* Seller card */
.seller-card { background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 1px solid #bae6fd; border-radius: 14px; padding: 1rem 1.25rem; }
.btn-contact { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); border: none; font-weight: 600; padding: 0.6rem 1.5rem; border-radius: 12px; transition: transform 0.2s, box-shadow 0.2s; }
.btn-contact:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(14, 165, 233, 0.4); }

@media (max-width: 768px) { .trade-listing-page { padding: 0 1rem; } }
</style>
@endpush

<div class="trade-listing-page py-4">
    @if(session('success'))<div class="alert alert-success rounded-3">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger rounded-3">{{ session('error') }}</div>@endif

    <div class="trade-content">
        <div class="row g-4">
            <div class="col-lg-8">
                {{-- Image Gallery (no lightbox, hover zoom + slideshow) --}}
                <div class="listing-card mb-4">
                    <div class="listing-card-body">
                        @php
                            $thumbnailImg = $listing->images->firstWhere('is_thumbnail', true) ?? $listing->images->first();
                            $mainImg = $thumbnailImg ?? $listing->images->first();
                            $hasImages = $listing->images->isNotEmpty();
                            $imageUrls = $listing->images->map(fn($i) => asset('storage/' . $i->image_path))->values()->all();
                        @endphp
                        <div class="listing-gallery-main" id="listingGalleryMain">
                            <div class="main-image-wrapper">
                                @if($mainImg)
                                <img src="{{ asset('storage/' . $mainImg->image_path) }}" alt="{{ $listing->title }}" id="mainListingImg" class="listing-main-img">
                                @else
                                <div class="d-flex align-items-center justify-content-center h-100 w-100">
                                    <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                                </div>
                                @endif
                            </div>
                            @if($hasImages)
                            <div class="listing-zoom-hint"><i class="bi bi-zoom-in me-1"></i>Hover to zoom</div>
                            @endif
                        </div>
                        @if($listing->images->count() > 1)
                        <div class="listing-thumbnails">
                            @foreach($listing->images as $idx => $img)
                            <img src="{{ asset('storage/' . $img->image_path) }}" alt="" class="listing-thumb {{ $idx === 0 ? 'active' : '' }}"
                                 data-src="{{ asset('storage/' . $img->image_path) }}"
                                 onclick="listingSetMainImage({{ $idx }}, this)">
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Title & Meta --}}
                <div class="listing-card mb-4">
                    <div class="listing-card-body">
                        <h1 class="h3 fw-bold mb-3" style="color: #1e293b;">{{ $listing->title }}</h1>
                        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                            @if($listing->brand)<span class="text-muted">{{ $listing->brand }}</span>@if($listing->categories->isNotEmpty())<span class="text-muted">•</span>@endif @endif
                            @if($listing->categories->isNotEmpty())<span class="text-muted">{{ $listing->categories->pluck('name')->implode(', ') }}</span>@elseif($listing->category)<span class="text-muted">{{ $listing->category->name }}</span>@endif
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="listing-condition"><i class="bi bi-tag"></i>{{ ucfirst(str_replace('_', ' ', $listing->condition ?? 'N/A')) }}</span>
                            <span class="trade-type-pill {{ $listing->trade_type ?? 'exchange' }}">{{ ucfirst(str_replace('_', ' ', $listing->trade_type ?? 'exchange')) }}</span>
                        </div>
                        @if($listing->cash_amount)<p class="listing-price mb-0">₱{{ number_format($listing->cash_amount, 0) }}</p>@endif
                    </div>
                </div>

                {{-- Description (fix: render line breaks as HTML) --}}
                <div class="listing-card mb-4">
                    <div class="listing-card-header"><i class="bi bi-text-paragraph me-2"></i>Description</div>
                    <div class="listing-card-body">
                        <div class="listing-description text-justify">
                            @if($listing->description)
                            {!! nl2br(e($listing->description)) !!}
                            @else
                            <span class="text-muted">No description provided.</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Meetup Area (Center on pin only, no search) --}}
                @if($listing->location_lat && $listing->location_lng)
                <div class="listing-card mb-4">
                    <div class="listing-card-header"><i class="bi bi-geo-alt me-2"></i>Meetup Area</div>
                    <div class="listing-card-body">
                        <button type="button" class="btn btn-sm btn-outline-secondary mb-2" id="btnCenterMap"><i class="bi bi-geo-alt-fill me-1"></i>Center on pin</button>
                        <div id="listingMap"></div>
                        @if($listing->location || $listing->meetup_radius_km)
                        <div class="mt-3 d-flex flex-wrap gap-3 small text-muted">
                            @if($listing->location)<span><i class="bi bi-pin-map me-1"></i>{{ $listing->location }}</span>@endif
                            @if($listing->meetup_radius_km)<span><i class="bi bi-bullseye me-1"></i>Within {{ $listing->meetup_radius_km }} km</span>@endif
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                @if($listing->meet_up_references)
                <div class="listing-card mb-4">
                    <div class="listing-card-header"><i class="bi bi-chat-square-text me-2"></i>Meetup Notes</div>
                    <div class="listing-card-body"><p class="mb-0">{{ $listing->meet_up_references }}</p></div>
                </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="listing-card sticky-top" style="top: 1rem;">
                    <div class="listing-card-header"><i class="bi bi-person me-2"></i>Listed by</div>
                    <div class="listing-card-body">
                        @auth
                        @if($listing->user_id !== auth()->id())
                        <div class="mb-3">
                            @if($isSaved ?? false)
                            <form action="{{ route('trading.listings.unsave', $listing->id) }}" method="POST" class="d-inline">@csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100 rounded-3"><i class="bi bi-bookmark-fill me-2"></i>Saved – Remove</button>
                            </form>
                            @else
                            <form action="{{ route('trading.listings.save', $listing->id) }}" method="POST" class="d-inline">@csrf
                                <button type="submit" class="btn btn-outline-secondary btn-sm w-100 rounded-3"><i class="bi bi-bookmark me-2"></i>Save listing</button>
                            </form>
                            @endif
                        </div>
                        @endif
                        @endauth
                        <div class="seller-card mb-4"><p class="fw-semibold mb-0" style="color: #0c4a6e;">{{ $listing->user->name }}</p></div>
                        @auth
                        @if(!auth()->user()->isTradeSuspended())
                            @if($listing->user_id !== auth()->id())
                                @if($listing->canAcceptOffers())
                                <form action="{{ route('trading.conversations.store-from-listing.post', $listing->id) }}" method="POST">@csrf
                                    <button type="submit" class="btn btn-primary btn-contact w-100"><i class="bi bi-chat-dots me-2"></i>Contact Seller</button>
                                </form>
                                @endif
                            @else
                            <a href="{{ route('trading.listings.edit', $listing->id) }}" class="btn btn-outline-primary rounded-3 d-block mb-2"><i class="bi bi-pencil me-2"></i>Edit Listing</a>
                            @if($listing->status === 'active')
                            <form action="{{ route('trading.listings.mark-sold', $listing->id) }}" method="POST" onsubmit="return confirm('Mark as sold?');">@csrf
                                <button type="submit" class="btn btn-outline-secondary rounded-3 w-100"><i class="bi bi-check-circle me-2"></i>Mark Sold</button>
                            </form>
                            @endif
                            @endif
                        @endif
                        @endauth
                        @guest
                        <a href="{{ route('login') }}?redirect={{ urlencode(request()->url()) }}" class="btn btn-primary btn-contact w-100"><i class="bi bi-box-arrow-in-right me-2"></i>Log in to Contact Seller</a>
                        @endguest
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Trade listing images array
var listingImages = @json($imageUrls ?? []);
var listingCurrentIndex = 0;
var listingSlideshowTimer = null;
var LISTING_SLIDESHOW_INTERVAL = 10000;

function listingSetMainImage(idx, thumbEl) {
    if (idx < 0 || idx >= listingImages.length) return;
    listingCurrentIndex = idx;
    var main = document.getElementById('mainListingImg');
    if (main) main.src = listingImages[idx];
    document.querySelectorAll('.listing-thumb').forEach(function(t, i) { t.classList.toggle('active', i === idx); });
    listingResetSlideshow();
}

function listingAdvanceSlide() {
    if (listingImages.length <= 1) return;
    var next = (listingCurrentIndex + 1) % listingImages.length;
    listingSetMainImage(next, null);
}

function listingStartSlideshow() {
    listingStopSlideshow();
    if (listingImages.length > 1) listingSlideshowTimer = setInterval(listingAdvanceSlide, LISTING_SLIDESHOW_INTERVAL);
}
function listingStopSlideshow() { if (listingSlideshowTimer) { clearInterval(listingSlideshowTimer); listingSlideshowTimer = null; } }
function listingResetSlideshow() { listingStopSlideshow(); if (listingImages.length > 1) listingSlideshowTimer = setInterval(listingAdvanceSlide, LISTING_SLIDESHOW_INTERVAL); }

// Hover zoom (like toyshop)
var galleryMain = document.getElementById('listingGalleryMain');
var mainImgEl = document.getElementById('mainListingImg');
if (galleryMain && mainImgEl) {
    galleryMain.addEventListener('mousemove', function(e) {
        var rect = galleryMain.getBoundingClientRect();
        var x = ((e.clientX - rect.left) / rect.width) * 100;
        var y = ((e.clientY - rect.top) / rect.height) * 100;
        mainImgEl.style.transformOrigin = x + '% ' + y + '%';
        mainImgEl.style.transform = 'scale(2.2)';
    });
    galleryMain.addEventListener('mouseleave', function() {
        mainImgEl.style.transform = 'scale(1)';
        mainImgEl.style.transformOrigin = 'center center';
    });
    galleryMain.addEventListener('mouseenter', listingStopSlideshow);
    galleryMain.addEventListener('mouseleave', listingStartSlideshow);
}
listingStartSlideshow();
</script>
@if($listing->location_lat && $listing->location_lng)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
(function() {
    var lat = {{ $listing->location_lat }};
    var lng = {{ $listing->location_lng }};
    var radius = {{ $listing->meetup_radius_km ?? 5 }};
    var mapEl = document.getElementById('listingMap');
    if (!mapEl) return;
    var map = L.map('listingMap', { minZoom: 9, maxZoom: 18 }).setView([lat, lng], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
    L.marker([lat, lng]).addTo(map);
    L.circle([lat, lng], { radius: radius * 1000, color: '#0ea5e9', fillColor: '#0ea5e9', fillOpacity: 0.15 }).addTo(map);

    document.getElementById('btnCenterMap')?.addEventListener('click', function() { map.setView([lat, lng], 12); });
})();
</script>
@endif
@endpush
@endsection
