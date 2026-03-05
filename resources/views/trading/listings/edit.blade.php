@extends('layouts.toyshop')
@section('title', 'Edit Listing - ToyHaven Trade')
@section('content')
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<style>
.text-justify { text-align: justify; }
#map { height: 350px; border-radius: 8px; }
</style>
@endpush
<div class="container py-4">
    <h1 class="h3 mb-4">Edit Listing</h1>
    @if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ route('trading.listings.update', $listing->id) }}" method="POST" enctype="multipart/form-data" id="editForm">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="{{ old('title', $listing->title) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Category <small class="text-muted">(Select 1–3)</small></label>
            <select name="category_ids[]" class="form-select" multiple size="6" id="categorySelect">
                @php $catIds = old('category_ids', $listing->category_ids ?? ($listing->category_id ? [$listing->category_id] : [])); @endphp
                @foreach($categories as $c)
                <option value="{{ $c->id }}" {{ in_array($c->id, $catIds) ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
            <small class="text-muted">Hold Ctrl/Cmd to select multiple. Max 3.</small>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Brand</label>
                <input type="text" name="brand" class="form-control" value="{{ old('brand', $listing->brand) }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Condition</label>
                <select name="condition" class="form-select" required>
                    <option value="new" {{ ($listing->condition ?? '') === 'new' ? 'selected' : '' }}>New</option>
                    <option value="like_new" {{ ($listing->condition ?? '') === 'like_new' ? 'selected' : '' }}>Like New</option>
                    <option value="good" {{ ($listing->condition ?? '') === 'good' ? 'selected' : '' }}>Good</option>
                    <option value="fair" {{ ($listing->condition ?? '') === 'fair' ? 'selected' : '' }}>Fair</option>
                    <option value="used" {{ ($listing->condition ?? '') === 'used' ? 'selected' : '' }}>Used</option>
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Trade Type</label>
            <select name="trade_type" class="form-select" id="tradeType" required>
                <option value="exchange" {{ $listing->trade_type === 'exchange' ? 'selected' : '' }}>Exchange (item for item)</option>
                <option value="exchange_with_cash" {{ $listing->trade_type === 'exchange_with_cash' ? 'selected' : '' }}>Exchange + Add Cash</option>
                <option value="cash" {{ $listing->trade_type === 'cash' ? 'selected' : '' }}>Cash only</option>
            </select>
        </div>
        <div class="mb-3" id="cashAmountField">
            <label class="form-label">Cash Amount (₱)</label>
            <input type="number" name="cash_amount" class="form-control" value="{{ old('cash_amount', $listing->cash_amount) }}" min="0" step="0.01" id="cashAmountInput">
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control text-justify" rows="5" required>{{ old('description', $listing->description) }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Replace Images <small class="text-muted">(optional, 1–10)</small></label>
            <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
            @if($listing->images->count() > 0)
            <p class="small text-muted mt-1">Current: {{ $listing->images->count() }} image(s). Upload new to replace.</p>
            @endif
        </div>
        <div class="mb-3">
            <label class="form-label">Location & Meetup Area</label>
            <div class="input-group mb-2">
                <input type="text" id="locationSearch" class="form-control" placeholder="Search in Philippines..." value="{{ old('location', $listing->location) }}" autocomplete="off">
                <button type="button" class="btn btn-outline-secondary" id="btnSearch"><i class="bi bi-search"></i> Search</button>
            </div>
            <div id="map"></div>
            <div class="mt-2">
                <label class="form-label mb-1">Meetup radius (km): <span id="radiusValue">{{ old('meetup_radius_km', $listing->meetup_radius_km ?? 5) }}</span></label>
                <input type="range" name="meetup_radius_km" id="radiusSlider" class="form-range" min="1" max="50" value="{{ old('meetup_radius_km', $listing->meetup_radius_km ?? 5) }}" step="0.5">
            </div>
            <input type="hidden" name="location" id="locationText" value="{{ old('location', $listing->location) }}">
            <input type="hidden" name="location_lat" id="locationLat" value="{{ old('location_lat', $listing->location_lat ?? 14.5995) }}">
            <input type="hidden" name="location_lng" id="locationLng" value="{{ old('location_lng', $listing->location_lng ?? 120.9842) }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Meetup References / Notes</label>
            <input type="text" name="meet_up_references" class="form-control" value="{{ old('meet_up_references', $listing->meet_up_references) }}" placeholder="Landmarks, preferred meetup spots...">
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('trading.listings.my') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
(function() {
    var lat = {{ old('location_lat', $listing->location_lat ?? 14.5995) }};
    var lng = {{ old('location_lng', $listing->location_lng ?? 120.9842) }};
    var tradeType = document.getElementById('tradeType');
    var cashField = document.getElementById('cashAmountField');
    var cashInput = document.getElementById('cashAmountInput');
    function toggleCash() {
        var isExchange = tradeType.value === 'exchange';
        cashField.classList.toggle('opacity-50', isExchange);
        cashInput.disabled = isExchange;
        if (isExchange) cashInput.value = '';
    }
    tradeType.addEventListener('change', toggleCash);
    toggleCash();

    var catSelect = document.getElementById('categorySelect');
    catSelect.addEventListener('change', function() {
        var opts = Array.from(this.selectedOptions);
        if (opts.length > 3) opts.slice(3).forEach(function(o) { o.selected = false; });
    });

    var map = L.map('map').setView([lat, lng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
    var marker = L.marker([lat, lng], { draggable: true }).addTo(map);
    var radiusSlider = document.getElementById('radiusSlider');
    var radiusValue = document.getElementById('radiusValue');
    var circle = L.circle([lat, lng], { radius: (parseFloat(radiusSlider.value) || 5) * 1000, color: '#0d6efd', fillColor: '#0d6efd', fillOpacity: 0.15 }).addTo(map);
    function updateCircle() {
        var km = parseFloat(radiusSlider.value);
        radiusValue.textContent = km;
        var latlng = marker.getLatLng();
        circle.setRadius(km * 1000);
        circle.setLatLng(latlng);
    }
    radiusSlider.addEventListener('input', updateCircle);
    marker.on('dragend', function() {
        var latlng = marker.getLatLng();
        document.getElementById('locationLat').value = latlng.lat.toFixed(6);
        document.getElementById('locationLng').value = latlng.lng.toFixed(6);
        document.getElementById('locationText').value = latlng.lat.toFixed(4) + ', ' + latlng.lng.toFixed(4);
        updateCircle();
    });
    document.getElementById('btnSearch').addEventListener('click', function() {
        var q = document.getElementById('locationSearch').value.trim();
        if (!q) return;
        fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(q + ', Philippines') + '&countrycodes=ph&limit=1')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && data[0]) {
                    var r = data[0];
                    var newLat = parseFloat(r.lat), newLng = parseFloat(r.lon);
                    marker.setLatLng([newLat, newLng]);
                    map.setView([newLat, newLng], 13);
                    document.getElementById('locationLat').value = newLat;
                    document.getElementById('locationLng').value = newLng;
                    document.getElementById('locationText').value = r.display_name;
                    circle.setLatLng([newLat, newLng]);
                    updateCircle();
                }
            });
    });
    document.getElementById('editForm').addEventListener('submit', function() {
        var cats = Array.from(catSelect.selectedOptions).map(function(o) { return o.value; });
        if (cats.length < 1 || cats.length > 3) {
            event.preventDefault();
            alert('Please select 1 to 3 categories.');
            return false;
        }
    });
})();
</script>
@endpush
@endsection
