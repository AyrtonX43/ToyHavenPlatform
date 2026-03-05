@extends('layouts.toyshop')
@section('title', $listing->title . ' - ToyHaven Trade')
@section('content')
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<style>
.text-justify { text-align: justify; }
#listingMap { height: 300px; border-radius: 8px; }
</style>
@endpush
<div class="container py-4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    {{-- Images --}}
    <div class="mb-4">
        @php
            $thumbnailImg = $listing->images->firstWhere('is_thumbnail', true) ?? $listing->images->first();
            $otherImages = $listing->images->filter(fn($i) => $i->id !== ($thumbnailImg?->id));
        @endphp
        <div class="row g-2">
            <div class="col-md-8">
                @if($thumbnailImg)
                <img src="{{ asset('storage/' . $thumbnailImg->image_path) }}" alt="{{ $listing->title }}" class="img-fluid rounded-3 w-100" style="max-height: 400px; object-fit: contain;">
                @else
                <div class="bg-light rounded-3 d-flex align-items-center justify-content-center" style="height: 300px;">
                    <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                </div>
                @endif
            </div>
            @if($otherImages->count() > 0)
            <div class="col-md-4">
                <div class="d-flex flex-wrap gap-2">
                    @foreach($otherImages as $img)
                    <img src="{{ asset('storage/' . $img->image_path) }}" alt="" class="rounded" style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;" onclick="document.querySelector('.col-md-8 img').src=this.src">
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Title --}}
    <h1 class="h3 mb-2">{{ $listing->title }}</h1>

    {{-- Brand | Category --}}
    <p class="text-muted mb-2">
        @if($listing->brand)
        <span>{{ $listing->brand }}</span>
        @if($listing->categories->isNotEmpty()) <span class="mx-1">|</span> @endif
        @endif
        @if($listing->categories->isNotEmpty())
        <span>{{ $listing->categories->pluck('name')->implode(', ') }}</span>
        @elseif($listing->category)
        <span>{{ $listing->category->name }}</span>
        @endif
    </p>

    {{-- Condition | Trade Type --}}
    <p class="mb-2">
        <strong>Condition:</strong> {{ ucfirst(str_replace('_', ' ', $listing->condition ?? 'N/A')) }}
        <span class="mx-2">|</span>
        <strong>Trade Type:</strong>
        <span class="badge bg-{{ $listing->trade_type === 'cash' ? 'success' : ($listing->trade_type === 'exchange_with_cash' ? 'info' : 'primary') }}">{{ ucfirst(str_replace('_', ' ', $listing->trade_type)) }}</span>
    </p>

    {{-- Cash Amount --}}
    @if($listing->cash_amount)
    <p class="h4 text-primary mb-3">₱{{ number_format($listing->cash_amount, 0) }}</p>
    @endif

    {{-- Description --}}
    <div class="mb-4">
        <p class="text-justify">{{ nl2br(e($listing->description)) }}</p>
    </div>

    {{-- Location Map with Search --}}
    @if($listing->location_lat && $listing->location_lng)
    <div class="mb-4">
        <h5 class="mb-2">Meetup Area</h5>
        <div class="input-group mb-2">
            <input type="text" id="locationSearch" class="form-control" placeholder="Search in Philippines...">
            <button type="button" class="btn btn-outline-secondary" id="btnSearch"><i class="bi bi-search"></i></button>
        </div>
        <div id="listingMap"></div>
        @if($listing->location)
        <p class="mt-2 mb-0"><strong>Location:</strong> {{ $listing->location }}</p>
        @endif
        @if($listing->meetup_radius_km)
        <p class="mb-0"><strong>Meetup radius:</strong> {{ $listing->meetup_radius_km }} km</p>
        @endif
    </div>
    @endif

    {{-- Meetup References / Notes --}}
    @if($listing->meet_up_references)
    <div class="mb-4">
        <h5 class="mb-2">Meetup References / Notes</h5>
        <p class="mb-0">{{ $listing->meet_up_references }}</p>
    </div>
    @endif

    <hr>
    <p class="text-muted small">Listed by {{ $listing->user->name }}</p>

    @auth
    @if(!auth()->user()->isTradeSuspended())
    @if($listing->user_id !== auth()->id())
        @if($listing->canAcceptOffers())
        <form action="{{ route('trading.conversations.store-from-listing.post', $listing->id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-primary"><i class="bi bi-chat-dots me-1"></i>Contact Seller</button>
        </form>
        @endif
    @else
        <a href="{{ route('trading.listings.edit', $listing->id) }}" class="btn btn-outline-primary">Edit</a>
        @if($listing->status === 'active')
        <form action="{{ route('trading.listings.mark-sold', $listing->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Mark as sold?');">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">Mark Sold</button>
        </form>
        @endif
    @endif
    @endif
    @endauth
</div>
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
    L.circle([lat, lng], { radius: radius * 1000, color: '#0d6efd', fillColor: '#0d6efd', fillOpacity: 0.15 }).addTo(map);
    document.getElementById('btnSearch')?.addEventListener('click', function() {
        var q = document.getElementById('locationSearch').value.trim();
        if (!q) return;
        fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(q + ', Philippines') + '&countrycodes=ph&limit=1')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && data[0]) {
                    var r = data[0];
                    var newLat = parseFloat(r.lat), newLng = parseFloat(r.lon);
                    map.setView([newLat, newLng], 14);
                }
            });
    });
})();
</script>
@endpush
@endif
@endsection
