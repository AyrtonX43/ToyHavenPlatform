@extends('layouts.toyshop')
@section('title', $listing->title . ' - ToyHaven Trade')
@section('content')
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<style>
.trade-listing-detail { font-family: 'Quicksand', sans-serif; }
.trade-listing-detail .text-justify { text-align: justify; }

/* Image gallery */
.listing-gallery-main {
    aspect-ratio: 4/3;
    background: #f8fafc;
    border-radius: 16px;
    overflow: hidden;
    position: relative;
    border: 1px solid #e2e8f0;
}
.listing-gallery-main img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    cursor: zoom-in;
}
.listing-thumbnails {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 12px;
}
.listing-thumb {
    width: 72px;
    height: 72px;
    border-radius: 10px;
    object-fit: cover;
    cursor: pointer;
    border: 2px solid transparent;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.listing-thumb:hover, .listing-thumb.active {
    border-color: #0ea5e9;
    box-shadow: 0 0 0 1px #0ea5e9;
}

/* Content cards */
.listing-card {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    overflow: hidden;
}
.listing-card-header {
    font-weight: 700;
    font-size: 1rem;
    color: #1e293b;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f1f5f9;
    background: linear-gradient(180deg, #fafbfc 0%, #fff 100%);
}
.listing-card-body { padding: 1.25rem; }

/* Meta badges */
.listing-condition {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 0.35rem 0.75rem;
    background: #f1f5f9;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 600;
    color: #475569;
}
.trade-type-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.85rem;
    border-radius: 20px;
    font-size: 0.8125rem;
    font-weight: 600;
}
.trade-type-pill.exchange { background: #dbeafe; color: #1d4ed8; }
.trade-type-pill.exchange_with_cash { background: #e0e7ff; color: #4338ca; }
.trade-type-pill.cash { background: #d1fae5; color: #047857; }

/* Price block */
.listing-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0ea5e9;
    letter-spacing: -0.02em;
}

/* Map */
#listingMap {
    height: 280px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}

/* Seller card */
.seller-card {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border: 1px solid #bae6fd;
    border-radius: 14px;
    padding: 1rem 1.25rem;
}

/* Actions */
.btn-contact {
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    border: none;
    font-weight: 600;
    padding: 0.6rem 1.5rem;
    border-radius: 12px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.btn-contact:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(14, 165, 233, 0.4);
}

/* Lightbox */
#listingImageLightbox .modal-content {
    border: none;
    border-radius: 16px;
    box-shadow: 0 24px 48px rgba(0,0,0,0.12);
}
#listingImageLightbox .modal-body { padding: 0; }
#listingImageLightbox .modal-body img {
    max-width: 100%;
    max-height: 85vh;
    object-fit: contain;
    display: block;
    margin: 0 auto;
}
#listingImageLightbox .modal-header {
    border-bottom: none;
    padding: 0.75rem 1rem;
}
</style>
@endpush

<div class="container py-4 trade-listing-detail">
    @if(session('success'))<div class="alert alert-success rounded-3">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger rounded-3">{{ session('error') }}</div>@endif

    <div class="row g-4">
        {{-- Left: Gallery + Details --}}
        <div class="col-lg-8">
            {{-- Image Gallery --}}
            <div class="listing-card mb-4">
                <div class="listing-card-body">
                    @php
                        $thumbnailImg = $listing->images->firstWhere('is_thumbnail', true) ?? $listing->images->first();
                        $otherImages = $listing->images->filter(fn($i) => $i->id !== ($thumbnailImg?->id));
                        $mainImg = $thumbnailImg ?? $otherImages->first();
                        $hasImages = $listing->images->isNotEmpty();
                    @endphp
                    <div class="listing-gallery-main" @if($hasImages) data-bs-toggle="modal" data-bs-target="#listingImageLightbox" role="button" @endif>
                        @if($mainImg)
                        <img src="{{ asset('storage/' . $mainImg->image_path) }}" alt="{{ $listing->title }}" id="mainListingImg">
                        @else
                        <div class="d-flex align-items-center justify-content-center h-100">
                            <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                        </div>
                        @endif
                    </div>
                    @if($listing->images->count() > 1)
                    <div class="listing-thumbnails">
                        @foreach($listing->images as $idx => $img)
                        <img src="{{ asset('storage/' . $img->image_path) }}" alt="" class="listing-thumb {{ $idx === 0 ? 'active' : '' }}"
                             data-full="{{ asset('storage/' . $img->image_path) }}"
                             onclick="event.stopPropagation(); setMainImage(this, '{{ asset('storage/' . $img->image_path) }}');">
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
                        @if($listing->brand)
                        <span class="text-muted">{{ $listing->brand }}</span>
                        @if($listing->categories->isNotEmpty())<span class="text-muted">•</span>@endif
                        @endif
                        @if($listing->categories->isNotEmpty())
                        <span class="text-muted">{{ $listing->categories->pluck('name')->implode(', ') }}</span>
                        @elseif($listing->category)
                        <span class="text-muted">{{ $listing->category->name }}</span>
                        @endif
                    </div>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="listing-condition">
                            <i class="bi bi-tag"></i>
                            {{ ucfirst(str_replace('_', ' ', $listing->condition ?? 'N/A')) }}
                        </span>
                        <span class="trade-type-pill {{ $listing->trade_type ?? 'exchange' }}">
                            {{ ucfirst(str_replace('_', ' ', $listing->trade_type ?? 'exchange')) }}
                        </span>
                    </div>
                    @if($listing->cash_amount)
                    <p class="listing-price mb-0">₱{{ number_format($listing->cash_amount, 0) }}</p>
                    @endif
                </div>
            </div>

            {{-- Description --}}
            <div class="listing-card mb-4">
                <div class="listing-card-header"><i class="bi bi-text-paragraph me-2"></i>Description</div>
                <div class="listing-card-body">
                    <p class="text-justify mb-0">{{ nl2br(e($listing->description)) }}</p>
                </div>
            </div>

            {{-- Meetup Area --}}
            @if($listing->location_lat && $listing->location_lng)
            <div class="listing-card mb-4">
                <div class="listing-card-header"><i class="bi bi-geo-alt me-2"></i>Meetup Area</div>
                <div class="listing-card-body">
                    <div class="input-group mb-3">
                        <input type="text" id="locationSearch" class="form-control rounded-pill" placeholder="Search location in Philippines...">
                        <button type="button" class="btn btn-outline-primary rounded-pill ms-2" id="btnSearch"><i class="bi bi-search"></i> Search</button>
                    </div>
                    <div id="listingMap"></div>
                    @if($listing->location || $listing->meetup_radius_km)
                    <div class="mt-3 d-flex flex-wrap gap-3 small text-muted">
                        @if($listing->location)
                        <span><i class="bi bi-pin-map me-1"></i>{{ $listing->location }}</span>
                        @endif
                        @if($listing->meetup_radius_km)
                        <span><i class="bi bi-bullseye me-1"></i>Within {{ $listing->meetup_radius_km }} km</span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Meetup References --}}
            @if($listing->meet_up_references)
            <div class="listing-card mb-4">
                <div class="listing-card-header"><i class="bi bi-chat-square-text me-2"></i>Meetup Notes</div>
                <div class="listing-card-body">
                    <p class="mb-0">{{ $listing->meet_up_references }}</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Right: Sticky sidebar --}}
        <div class="col-lg-4">
            <div class="listing-card sticky-top" style="top: 1rem;">
                <div class="listing-card-header"><i class="bi bi-person me-2"></i>Listed by</div>
                <div class="listing-card-body">
                    <div class="seller-card mb-4">
                        <p class="fw-semibold mb-0" style="color: #0c4a6e;">{{ $listing->user->name }}</p>
                    </div>

                    @auth
                    @if(!auth()->user()->isTradeSuspended())
                        @if($listing->user_id !== auth()->id())
                            @if($listing->canAcceptOffers())
                            <form action="{{ route('trading.conversations.store-from-listing.post', $listing->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-contact w-100">
                                    <i class="bi bi-chat-dots me-2"></i>Contact Seller
                                </button>
                            </form>
                            @endif
                        @else
                        <div class="d-flex flex-column gap-2">
                            <a href="{{ route('trading.listings.edit', $listing->id) }}" class="btn btn-outline-primary rounded-3"><i class="bi bi-pencil me-2"></i>Edit Listing</a>
                            @if($listing->status === 'active')
                            <form action="{{ route('trading.listings.mark-sold', $listing->id) }}" method="POST" onsubmit="return confirm('Mark this listing as sold?');">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary rounded-3 w-100"><i class="bi bi-check-circle me-2"></i>Mark Sold</button>
                            </form>
                            @endif
                        </div>
                        @endif
                    @endif
                    @endauth

                    @guest
                    <a href="{{ route('login') }}?redirect={{ urlencode(request()->url()) }}" class="btn btn-primary btn-contact w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Log in to Contact Seller
                    </a>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Image lightbox --}}
@if($hasImages ?? false)
<div class="modal fade" id="listingImageLightbox" tabindex="-1" data-bs-backdrop="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title">View image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img src="{{ asset('storage/' . $mainImg->image_path) }}" alt="{{ $listing->title }}" id="lightboxImg">
            </div>
        </div>
    </div>
</div>
@endif

@if($listing->location_lat && $listing->location_lng)
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
(function() {
    var lat = {{ $listing->location_lat }};
    var lng = {{ $listing->location_lng }};
    var radius = {{ $listing->meetup_radius_km ?? 5 }};
    var mapEl = document.getElementById('listingMap');
    if (!mapEl) return;
    var map = L.map('listingMap').setView([lat, lng], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
    L.marker([lat, lng]).addTo(map);
    L.circle([lat, lng], { radius: radius * 1000, color: '#0ea5e9', fillColor: '#0ea5e9', fillOpacity: 0.15 }).addTo(map);
    document.getElementById('btnSearch')?.addEventListener('click', function() {
        var q = document.getElementById('locationSearch').value.trim();
        if (!q) return;
        fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(q + ', Philippines') + '&countrycodes=ph&limit=1')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && data[0]) {
                    var r = data[0];
                    map.setView([parseFloat(r.lat), parseFloat(r.lon)], 14);
                }
            });
    });
})();

function setMainImage(thumbEl, src) {
    var main = document.getElementById('mainListingImg');
    var lightbox = document.getElementById('lightboxImg');
    if (main) main.src = src;
    if (lightbox) lightbox.src = src;
    document.querySelectorAll('.listing-thumb').forEach(function(t) { t.classList.remove('active'); });
    if (thumbEl) thumbEl.classList.add('active');
}

document.getElementById('listingImageLightbox')?.addEventListener('show.bs.modal', function() {
    var main = document.getElementById('mainListingImg');
    var lightbox = document.getElementById('lightboxImg');
    if (main && lightbox) lightbox.src = main.src;
});
</script>
@endpush
@else
@push('scripts')
<script>
function setMainImage(thumbEl, src) {
    var main = document.getElementById('mainListingImg');
    var lightbox = document.getElementById('lightboxImg');
    if (main) main.src = src;
    if (lightbox) lightbox.src = src;
    document.querySelectorAll('.listing-thumb').forEach(function(t) { t.classList.remove('active'); });
    if (thumbEl) thumbEl.classList.add('active');
}
document.getElementById('listingImageLightbox')?.addEventListener('show.bs.modal', function() {
    var main = document.getElementById('mainListingImg');
    var lightbox = document.getElementById('lightboxImg');
    if (main && lightbox) lightbox.src = main.src;
});
</script>
@endpush
@endif
@endsection
