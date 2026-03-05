@extends('layouts.toyshop')
@section('title', 'Create Listing - ToyHaven Trade')
@section('content')
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<style>
.text-justify { text-align: justify; }
.create-listing-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 1.5rem; overflow: hidden; }
.create-listing-card .card-header { background: #f8fafc; padding: 0.75rem 1.25rem; font-weight: 600; border-bottom: 1px solid #e2e8f0; }
.create-listing-card .card-body { padding: 1.25rem; }
#map { height: 380px; border-radius: 10px; }
.map-toolbar { display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; margin-bottom: 0.5rem; }
.category-dropdown { position: relative; }
.category-dropdown .dropdown-toggle { min-height: 38px; text-align: left; }
.category-dropdown .dropdown-menu { max-height: 280px; overflow-y: auto; padding: 0.5rem; }
.category-dropdown .form-check { padding: 0.35rem 0.5rem; margin: 0; border-radius: 6px; }
.category-dropdown .form-check:hover { background: #f1f5f9; }
.image-zone { border: 2px dashed #cbd5e1; border-radius: 12px; background: #f8fafc; padding: 1.5rem; text-align: center; transition: border-color 0.2s, background 0.2s; }
.image-zone.dragover { border-color: #0d6efd; background: #eff6ff; }
.image-zone .upload-btn-wrap { margin-top: 0.75rem; }
.image-preview-list { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-start; margin-top: 1rem; min-height: 60px; }
.image-preview-item { position: relative; flex-shrink: 0; cursor: grab; }
.image-preview-item:active { cursor: grabbing; }
.image-preview-item.dragging { opacity: 0.5; }
.image-preview-item img { width: 88px; height: 88px; object-fit: cover; border-radius: 8px; border: 2px solid #e2e8f0; display: block; }
.image-preview-item .thumb-badge { position: absolute; top: -6px; left: -6px; z-index: 2; width: 24px; height: 24px; padding: 0; border-radius: 50%; font-size: 11px; line-height: 22px; }
.image-preview-item .btn-remove { position: absolute; top: -8px; right: -8px; width: 24px; height: 24px; padding: 0; border-radius: 50%; font-size: 14px; line-height: 1; z-index: 2; }
.image-preview-item .drag-handle { position: absolute; bottom: -6px; left: 50%; transform: translateX(-50%); font-size: 10px; color: #64748b; }
</style>
@endpush
<div class="container py-4">
    <h1 class="h4 mb-4 fw-bold">Create Trade Listing</h1>
    @if($errors->any())
    <div class="alert alert-danger mb-4">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('trading.listings.store') }}" method="POST" enctype="multipart/form-data" id="listingForm">
        @csrf

        <div class="create-listing-card">
            <div class="card-header">Listing details</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Title</label>
                    <input type="text" name="title" class="form-control form-control-lg" value="{{ old('title') }}" placeholder="e.g. Lego Star Wars Set" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Category <span class="text-muted fw-normal">(choose 1–3)</span></label>
                    <div class="category-dropdown">
                        <button type="button" class="form-select dropdown-toggle d-block w-100" id="categoryDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                            <span id="categorySelectedText">Select categories...</span>
                        </button>
                        <div id="categoryHiddenContainer"></div>
                        <ul class="dropdown-menu w-100" id="categoryDropdownMenu">
                            @foreach($categories as $c)
                            <li>
                                <label class="form-check d-block mb-0 category-option" data-id="{{ $c->id }}" data-name="{{ $c->name }}">
                                    <input type="checkbox" class="form-check-input" value="{{ $c->id }}"> {{ $c->name }}
                                </label>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Brand</label>
                        <input type="text" name="brand" class="form-control" value="{{ old('brand') }}" placeholder="Optional">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Condition</label>
                        <select name="condition" class="form-select" required>
                            <option value="new">New</option>
                            <option value="like_new">Like New</option>
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                            <option value="used">Used</option>
                        </select>
                    </div>
                </div>
                <div class="row g-3 mt-0">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Trade type</label>
                        <select name="trade_type" class="form-select" id="tradeType" required>
                            <option value="exchange">Exchange (item for item)</option>
                            <option value="exchange_with_cash">Exchange + Add Cash</option>
                            <option value="cash">Cash only</option>
                        </select>
                    </div>
                    <div class="col-md-6" id="cashAmountField">
                        <label class="form-label fw-semibold">Cash amount (₱)</label>
                        <input type="number" name="cash_amount" class="form-control" value="{{ old('cash_amount') }}" min="0" step="0.01" placeholder="For cash or add-cash" id="cashAmountInput">
                    </div>
                </div>
                <div class="mb-0 mt-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control text-justify" rows="4" required placeholder="Describe your item...">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <div class="create-listing-card">
            <div class="card-header">Photos</div>
            <div class="card-body">
                <div class="image-zone" id="imageZone">
                    <input type="file" name="images[]" id="imageInput" accept="image/*" multiple class="d-none">
                    <p class="mb-0 text-muted"><i class="bi bi-images fs-4 d-block mb-2"></i>Drag & drop images here or click to upload</p>
                    <div class="upload-btn-wrap">
                        <button type="button" class="btn btn-primary" id="uploadImagesBtn"><i class="bi bi-upload me-1"></i>Upload images</button>
                    </div>
                </div>
                <div id="imagePreviews" class="image-preview-list"></div>
                <p class="small text-muted mt-2 mb-0">First image is thumbnail. Drag to reorder. Max 10 images.</p>
                <input type="hidden" name="thumbnail_index" id="thumbnailIndex" value="0">
            </div>
        </div>

        <div class="create-listing-card">
            <div class="card-header">Meetup location</div>
            <div class="card-body">
                <div class="input-group mb-2">
                    <input type="text" id="locationSearch" class="form-control" placeholder="Search in Philippines..." autocomplete="off">
                    <button type="button" class="btn btn-outline-primary" id="btnSearch"><i class="bi bi-search me-1"></i>Search</button>
                </div>
                <div class="map-toolbar">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCenterMap" title="Center map on pin"><i class="bi bi-geo-alt me-1"></i>Center to pin</button>
                    <span class="text-muted small">Radius: <strong id="radiusValue">5</strong> km</span>
                </div>
                <div id="map"></div>
                <div class="mt-2">
                    <input type="range" name="meetup_radius_km" id="radiusSlider" class="form-range" min="1" max="50" value="{{ old('meetup_radius_km', 5) }}" step="0.5">
                </div>
                <input type="hidden" name="location" id="locationText" value="{{ old('location') }}">
                <input type="hidden" name="location_lat" id="locationLat" value="{{ old('location_lat', 14.5995) }}">
                <input type="hidden" name="location_lng" id="locationLng" value="{{ old('location_lng', 120.9842) }}">
                <div class="mt-3">
                    <label class="form-label fw-semibold">Meetup references / notes</label>
                    <input type="text" name="meet_up_references" class="form-control" value="{{ old('meet_up_references') }}" placeholder="Landmarks, preferred spots...">
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <button type="submit" class="btn btn-primary btn-lg">Submit for review</button>
            <a href="{{ route('trading.index') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
        </div>
    </form>
</div>
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
(function() {
    // —— Trade type: lock cash when exchange
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

    // —— Category: dropdown with 1–3 checkboxes
    var categoryDropdownBtn = document.getElementById('categoryDropdownBtn');
    var categorySelectedText = document.getElementById('categorySelectedText');
    var categoryIdsInput = document.getElementById('categoryIdsInput');
    var categoryOptions = document.querySelectorAll('.category-option');
    var selectedCategoryIds = [];

    function syncCategoryInput() {
        var inputs = document.querySelectorAll('#categoryDropdownMenu input[type=checkbox]:checked');
        selectedCategoryIds = Array.from(inputs).map(function(inp) { return inp.value; });
        if (selectedCategoryIds.length > 3) {
            var first = document.querySelector('#categoryDropdownMenu input[type=checkbox]:checked');
            if (first) first.checked = false;
            syncCategoryInput();
            return;
        }
        var container = document.getElementById('categoryHiddenContainer');
        container.innerHTML = '';
        selectedCategoryIds.forEach(function(id) {
            var h = document.createElement('input');
            h.type = 'hidden';
            h.name = 'category_ids[]';
            h.value = id;
            container.appendChild(h);
        });
        if (selectedCategoryIds.length === 0) {
            categorySelectedText.textContent = 'Select categories...';
        } else {
            var names = Array.from(inputs).map(function(inp) {
                var label = inp.closest('label');
                return label ? label.getAttribute('data-name') : '';
            }).filter(Boolean);
            categorySelectedText.textContent = names.join(', ') + ' (' + selectedCategoryIds.length + ')';
        }
    }

    categoryOptions.forEach(function(opt) {
        var cb = opt.querySelector('input[type=checkbox]');
        if (!cb) return;
        cb.addEventListener('change', function() {
            var checked = document.querySelectorAll('#categoryDropdownMenu input[type=checkbox]:checked');
            if (checked.length > 3) { this.checked = false; }
            syncCategoryInput();
        });
    });
    syncCategoryInput();

    // —— Images: drag zone, upload button, drag reorder
    var imageZone = document.getElementById('imageZone');
    var imageInput = document.getElementById('imageInput');
    var uploadImagesBtn = document.getElementById('uploadImagesBtn');
    var imagePreviews = document.getElementById('imagePreviews');
    var thumbnailIndex = document.getElementById('thumbnailIndex');
    var imageFiles = [];
    var thumbnailIdx = 0;

    imageZone.addEventListener('click', function(e) {
        if (e.target === uploadImagesBtn || uploadImagesBtn.contains(e.target)) return;
        imageInput.click();
    });
    uploadImagesBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        imageInput.click();
    });
    imageZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        imageZone.classList.add('dragover');
    });
    imageZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        imageZone.classList.remove('dragover');
    });
    imageZone.addEventListener('drop', function(e) {
        e.preventDefault();
        imageZone.classList.remove('dragover');
        var files = Array.from(e.dataTransfer.files || []).filter(function(f) { return f.type.indexOf('image/') === 0; });
        addImageFiles(files);
    });
    imageInput.addEventListener('change', function() {
        addImageFiles(Array.from(this.files || []));
        this.value = '';
    });

    function addImageFiles(files) {
        if (imageFiles.length + files.length > 10) files = files.slice(0, 10 - imageFiles.length);
        imageFiles = imageFiles.concat(files);
        if (imageFiles.length > 10) imageFiles = imageFiles.slice(0, 10);
        renderPreviews();
        updateFileInput();
    }

    function updateFileInput() {
        var dt = new DataTransfer();
        imageFiles.forEach(function(f) { dt.items.add(f); });
        imageInput.files = dt.files;
    }

    function renderPreviews() {
        imagePreviews.innerHTML = '';
        imageFiles.forEach(function(f, i) {
            var div = document.createElement('div');
            div.className = 'image-preview-item';
            div.draggable = true;
            div.dataset.index = i;
            div.innerHTML = '<span class="drag-handle">⋮⋮</span>';
            var img = document.createElement('img');
            img.src = URL.createObjectURL(f);
            var star = document.createElement('button');
            star.type = 'button';
            star.className = 'btn btn-sm ' + (i === thumbnailIdx ? 'btn-warning' : 'btn-outline-secondary') + ' thumb-badge';
            star.innerHTML = '★';
            star.title = 'Set as thumbnail';
            star.onclick = function(ev) { ev.preventDefault(); thumbnailIdx = i; thumbnailIndex.value = i; renderPreviews(); };
            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-danger btn-sm btn-remove';
            remove.innerHTML = '×';
            remove.onclick = function(ev) {
                ev.preventDefault();
                imageFiles.splice(i, 1);
                if (thumbnailIdx >= imageFiles.length) thumbnailIdx = Math.max(0, imageFiles.length - 1);
                thumbnailIndex.value = thumbnailIdx;
                renderPreviews();
                updateFileInput();
            };
            div.appendChild(star);
            div.appendChild(img);
            div.appendChild(remove);
            imagePreviews.appendChild(div);

            div.addEventListener('dragstart', function(ev) {
                ev.dataTransfer.setData('text/plain', i);
                ev.dataTransfer.effectAllowed = 'move';
                div.classList.add('dragging');
            });
            div.addEventListener('dragend', function() { div.classList.remove('dragging'); });
            div.addEventListener('dragover', function(ev) {
                ev.preventDefault();
                ev.dataTransfer.dropEffect = 'move';
            });
            div.addEventListener('drop', function(ev) {
                ev.preventDefault();
                var from = parseInt(ev.dataTransfer.getData('text/plain'), 10);
                if (from === i) return;
                var arr = imageFiles.slice();
                var item = arr.splice(from, 1)[0];
                arr.splice(i, 0, item);
                imageFiles = arr;
                if (thumbnailIdx === from) thumbnailIdx = i;
                else if (thumbnailIdx === i) thumbnailIdx = from;
                else if (from < thumbnailIdx && i >= thumbnailIdx) thumbnailIdx--;
                else if (from > thumbnailIdx && i <= thumbnailIdx) thumbnailIdx++;
                thumbnailIndex.value = thumbnailIdx;
                renderPreviews();
                updateFileInput();
            });
        });
        thumbnailIndex.value = thumbnailIdx;
    }

    // —— Map: zoom in to show radar, center button
    var defaultLat = 14.5995, defaultLng = 120.9842;
    var map = L.map('map').setView([defaultLat, defaultLng], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
    map.setMaxBounds([[4.5, 116.9], [21.1, 126.6]]);
    var marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);
    var radiusSlider = document.getElementById('radiusSlider');
    var radiusValue = document.getElementById('radiusValue');
    var radiusKm = parseFloat(radiusSlider.value) || 5;
    var circle = L.circle([defaultLat, defaultLng], { radius: radiusKm * 1000, color: '#0d6efd', fillColor: '#0d6efd', fillOpacity: 0.2 }).addTo(map);

    function updateCircle() {
        radiusKm = parseFloat(radiusSlider.value);
        radiusValue.textContent = radiusKm;
        var latlng = marker.getLatLng();
        circle.setRadius(radiusKm * 1000);
        circle.setLatLng(latlng);
    }
    function centerMapOnPin() {
        var latlng = marker.getLatLng();
        var latDelta = (radiusKm / 111) * 2.4;
        var lngDelta = (radiusKm / (111 * Math.cos(latlng.lat * Math.PI / 180))) * 2.4;
        var bounds = [[latlng.lat - latDelta, latlng.lng - lngDelta], [latlng.lat + latDelta, latlng.lng + lngDelta]];
        map.fitBounds(bounds, { maxZoom: 14, padding: [24, 24] });
    }
    radiusSlider.addEventListener('input', updateCircle);
    document.getElementById('btnCenterMap').addEventListener('click', centerMapOnPin);

    marker.on('dragend', function() {
        var latlng = marker.getLatLng();
        document.getElementById('locationLat').value = latlng.lat.toFixed(6);
        document.getElementById('locationLng').value = latlng.lng.toFixed(6);
        document.getElementById('locationText').value = latlng.lat.toFixed(4) + ', ' + latlng.lng.toFixed(4);
        updateCircle();
    });

    function moveMap(lat, lng, locText) {
        marker.setLatLng([lat, lng]);
        document.getElementById('locationLat').value = lat;
        document.getElementById('locationLng').value = lng;
        document.getElementById('locationText').value = locText || (lat + ', ' + lng);
        circle.setLatLng([lat, lng]);
        updateCircle();
        centerMapOnPin();
    }

    document.getElementById('btnSearch').addEventListener('click', async function() {
        var q = document.getElementById('locationSearch').value.trim();
        if (!q) return;
        var url = 'https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(q + ', Philippines') + '&countrycodes=ph&limit=1';
        try {
            var res = await fetch(url, { headers: { 'Accept-Language': 'en' } });
            var data = await res.json();
            if (data && data[0]) {
                var r = data[0];
                moveMap(parseFloat(r.lat), parseFloat(r.lon), r.display_name);
            } else {
                alert('Location not found in Philippines.');
            }
        } catch (e) {
            alert('Search failed.');
        }
    });

    setTimeout(centerMapOnPin, 300);

    document.getElementById('listingForm').addEventListener('submit', function(ev) {
        updateFileInput();
        if (imageFiles.length < 1) {
            ev.preventDefault();
            alert('Please add at least one image.');
            return false;
        }
        if (selectedCategoryIds.length < 1 || selectedCategoryIds.length > 3) {
            ev.preventDefault();
            alert('Please select 1 to 3 categories.');
            return false;
        }
    });
})();
</script>
@endpush
@endsection
