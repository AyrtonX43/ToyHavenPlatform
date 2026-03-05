@extends('layouts.toyshop')
@section('title', 'Create Listing - ToyHaven Trade')
@section('content')
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<style>
.text-justify { text-align: justify; }
#map { height: 350px; border-radius: 8px; }
.image-preview-item { position: relative; display: inline-block; margin: 4px; }
.image-preview-item img { width: 80px; height: 80px; object-fit: cover; border-radius: 6px; border: 2px solid #dee2e6; }
.image-preview-item .thumb-badge { position: absolute; top: -4px; left: -4px; }
.image-preview-item .btn-remove { position: absolute; top: -8px; right: -8px; width: 22px; height: 22px; padding: 0; border-radius: 50%; font-size: 12px; line-height: 1; }
.image-preview-list { display: flex; flex-wrap: wrap; gap: 8px; align-items: flex-start; }
.image-preview-list .sortable-ghost { opacity: 0.4; }
</style>
@endpush
<div class="container py-4">
    <h1 class="h3 mb-4">Create Trade Listing</h1>
    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('trading.listings.store') }}" method="POST" enctype="multipart/form-data" id="listingForm">
        @csrf
        <div class="mb-3">
            <label class="form-label">Select Your Product</label>
            <select name="user_product_id" class="form-select" required>
                <option value="">-- Select --</option>
                @foreach($myProducts as $p)
                <option value="{{ $p->id }}" {{ old('user_product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->condition }})</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Category <small class="text-muted">(Select 1–3)</small></label>
            <select name="category_ids[]" class="form-select" multiple size="6" id="categorySelect">
                @foreach($categories as $c)
                <option value="{{ $c->id }}" {{ in_array($c->id, old('category_ids', [])) ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
            <small class="text-muted">Hold Ctrl/Cmd to select multiple. Max 3.</small>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Brand</label>
                <input type="text" name="brand" class="form-control" value="{{ old('brand') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Condition</label>
                <select name="condition" class="form-select" required>
                    <option value="new">New</option>
                    <option value="like_new">Like New</option>
                    <option value="good">Good</option>
                    <option value="fair">Fair</option>
                    <option value="used">Used</option>
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Trade Type</label>
            <select name="trade_type" class="form-select" id="tradeType" required>
                <option value="exchange">Exchange (item for item)</option>
                <option value="exchange_with_cash">Exchange + Add Cash</option>
                <option value="cash">Cash only</option>
            </select>
        </div>
        <div class="mb-3" id="cashAmountField">
            <label class="form-label">Cash Amount (₱)</label>
            <input type="number" name="cash_amount" class="form-control" value="{{ old('cash_amount') }}" min="0" step="0.01" placeholder="For cash or add-cash" id="cashAmountInput">
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control text-justify" rows="5" required>{{ old('description') }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Images <small class="text-muted">(1–10, reorder with arrows, star = thumbnail)</small></label>
            <input type="file" name="images[]" id="imageInput" class="form-control" accept="image/*" multiple>
            <div id="imagePreviews" class="image-preview-list mt-2"></div>
            <input type="hidden" name="thumbnail_index" id="thumbnailIndex" value="0">
        </div>
        <div class="mb-3">
            <label class="form-label">Location & Meetup Area</label>
            <div class="input-group mb-2">
                <input type="text" id="locationSearch" class="form-control" placeholder="Search in Philippines..." autocomplete="off">
                <button type="button" class="btn btn-outline-secondary" id="btnSearch"><i class="bi bi-search"></i> Search</button>
            </div>
            <div id="map"></div>
            <div class="mt-2">
                <label class="form-label mb-1">Meetup radius (km): <span id="radiusValue">5</span></label>
                <input type="range" name="meetup_radius_km" id="radiusSlider" class="form-range" min="1" max="50" value="{{ old('meetup_radius_km', 5) }}" step="0.5">
            </div>
            <input type="hidden" name="location" id="locationText" value="{{ old('location') }}">
            <input type="hidden" name="location_lat" id="locationLat" value="{{ old('location_lat', 14.5995) }}">
            <input type="hidden" name="location_lng" id="locationLng" value="{{ old('location_lng', 120.9842) }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Meetup References / Notes</label>
            <input type="text" name="meet_up_references" class="form-control" value="{{ old('meet_up_references') }}" placeholder="Landmarks, landmarks, preferred meetup spots...">
        </div>
        <button type="submit" class="btn btn-primary">Submit for Review</button>
        <a href="{{ route('trading.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
(function() {
    // Trade type: lock cash when exchange
    const tradeType = document.getElementById('tradeType');
    const cashField = document.getElementById('cashAmountField');
    const cashInput = document.getElementById('cashAmountInput');
    function toggleCash() {
        const isExchange = tradeType.value === 'exchange';
        cashField.classList.toggle('opacity-50', isExchange);
        cashField.querySelector('input').disabled = isExchange;
        if (isExchange) cashInput.value = '';
    }
    tradeType.addEventListener('change', toggleCash);
    toggleCash();

    // Category: max 3
    const catSelect = document.getElementById('categorySelect');
    catSelect.addEventListener('change', function() {
        const opts = Array.from(this.selectedOptions);
        if (opts.length > 3) {
            opts.slice(3).forEach(o => o.selected = false);
        }
    });

    // Image preview, reorder, thumbnail, remove
    const imageInput = document.getElementById('imageInput');
    const imagePreviews = document.getElementById('imagePreviews');
    const thumbnailIndex = document.getElementById('thumbnailIndex');
    let imageFiles = [];
    let thumbnailIdx = 0;

    function swap(i, j) {
        if (i < 0 || j < 0 || i >= imageFiles.length || j >= imageFiles.length) return;
        [imageFiles[i], imageFiles[j]] = [imageFiles[j], imageFiles[i]];
        if (thumbnailIdx === i) thumbnailIdx = j; else if (thumbnailIdx === j) thumbnailIdx = i;
        renderPreviews();
        updateFileInput();
    }

    function renderPreviews() {
        imagePreviews.innerHTML = '';
        imageFiles.forEach((f, i) => {
            const div = document.createElement('div');
            div.className = 'image-preview-item';
            div.dataset.index = i;
            const img = document.createElement('img');
            img.src = URL.createObjectURL(f);
            const star = document.createElement('button');
            star.type = 'button';
            star.className = 'btn btn-sm btn-' + (i === thumbnailIdx ? 'warning' : 'outline-secondary') + ' thumb-badge';
            star.innerHTML = '★';
            star.title = 'Set as thumbnail';
            star.onclick = () => { thumbnailIdx = i; thumbnailIndex.value = i; renderPreviews(); };
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-danger btn-sm btn-remove';
            remove.innerHTML = '×';
            remove.onclick = () => { imageFiles.splice(i, 1); if (thumbnailIdx >= imageFiles.length) thumbnailIdx = Math.max(0, imageFiles.length - 1); thumbnailIndex.value = thumbnailIdx; renderPreviews(); updateFileInput(); };
            const controls = document.createElement('div');
            controls.className = 'd-flex gap-1 mt-1 justify-content-center';
            const left = document.createElement('button');
            left.type = 'button';
            left.className = 'btn btn-sm btn-outline-secondary py-0';
            left.innerHTML = '◀';
            left.onclick = () => swap(i, i - 1);
            left.disabled = i === 0;
            const right = document.createElement('button');
            right.type = 'button';
            right.className = 'btn btn-sm btn-outline-secondary py-0';
            right.innerHTML = '▶';
            right.onclick = () => swap(i, i + 1);
            right.disabled = i === imageFiles.length - 1;
            controls.appendChild(left);
            controls.appendChild(right);
            div.appendChild(star);
            div.appendChild(img);
            div.appendChild(remove);
            div.appendChild(controls);
            imagePreviews.appendChild(div);
        });
        thumbnailIndex.value = thumbnailIdx;
    }

    function updateFileInput() {
        const dt = new DataTransfer();
        imageFiles.forEach(f => dt.items.add(f));
        imageInput.files = dt.files;
    }

    imageInput.addEventListener('change', function() {
        const newFiles = Array.from(this.files || []);
        const allowed = imageFiles.length + newFiles.length;
        if (allowed > 10) newFiles.splice(10 - imageFiles.length);
        imageFiles = imageFiles.concat(newFiles);
        if (imageFiles.length > 10) imageFiles = imageFiles.slice(0, 10);
        renderPreviews();
        updateFileInput();
    });

    // Map: PH only
    const phBounds = [[4.5, 116.9], [21.1, 126.6]];
    const map = L.map('map').setView([14.5995, 120.9842], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
    map.setMaxBounds(phBounds);
    const marker = L.marker([14.5995, 120.9842], { draggable: true }).addTo(map);
    const radiusSlider = document.getElementById('radiusSlider');
    const radiusValue = document.getElementById('radiusValue');
    let circle = L.circle([14.5995, 120.9842], { radius: 5000, color: '#0d6efd', fillColor: '#0d6efd', fillOpacity: 0.15 }).addTo(map);

    function updateCircle() {
        const km = parseFloat(radiusSlider.value);
        radiusValue.textContent = km;
        const latlng = marker.getLatLng();
        circle.setRadius(km * 1000);
        circle.setLatLng(latlng);
    }
    radiusSlider.addEventListener('input', updateCircle);

    marker.on('dragend', function() {
        const latlng = marker.getLatLng();
        document.getElementById('locationLat').value = latlng.lat.toFixed(6);
        document.getElementById('locationLng').value = latlng.lng.toFixed(6);
        document.getElementById('locationText').value = latlng.lat.toFixed(4) + ', ' + latlng.lng.toFixed(4);
        updateCircle();
    });

    function moveMap(lat, lng, locText) {
        marker.setLatLng([lat, lng]);
        map.setView([lat, lng], 13);
        document.getElementById('locationLat').value = lat;
        document.getElementById('locationLng').value = lng;
        document.getElementById('locationText').value = locText || (lat + ', ' + lng);
        circle.setLatLng([lat, lng]);
        updateCircle();
    }

    document.getElementById('btnSearch').addEventListener('click', async function() {
        const q = document.getElementById('locationSearch').value.trim();
        if (!q) return;
        const url = 'https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(q + ', Philippines') + '&countrycodes=ph&limit=5';
        try {
            const res = await fetch(url, { headers: { 'Accept-Language': 'en' } });
            const data = await res.json();
            if (data && data[0]) {
                const r = data[0];
                moveMap(parseFloat(r.lat), parseFloat(r.lon), r.display_name);
            } else {
                alert('Location not found in Philippines. Try a different search.');
            }
        } catch (e) {
            alert('Search failed. Please try again.');
        }
    });

    document.getElementById('listingForm').addEventListener('submit', function() {
        updateFileInput();
        if (imageFiles.length < 1) {
            event.preventDefault();
            alert('Please add at least one image.');
            return false;
        }
        if (imageFiles.length > 10) {
            imageFiles = imageFiles.slice(0, 10);
            updateFileInput();
        }
        const cats = Array.from(catSelect.selectedOptions).map(o => o.value);
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
