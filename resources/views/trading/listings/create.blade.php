@extends('layouts.toyshop')
@section('title', 'Create Listing - ToyHaven Trade')
@section('content')
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<style>
:root { --th-primary: #0ea5e9; --th-primary-dark: #0284c7; --th-surface: #ffffff; --th-surface-alt: #f8fafc; --th-border: #e2e8f0; --th-text: #0f172a; --th-text-muted: #64748b; }
.text-justify { text-align: justify; }
.create-listing-card { background: var(--th-surface); border-radius: 16px; box-shadow: 0 4px 24px rgba(15,23,42,0.06); border: 1px solid var(--th-border); margin-bottom: 1.75rem; overflow: hidden; }
.create-listing-card .card-header { background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); padding: 1rem 1.5rem; font-weight: 600; font-size: 0.9375rem; color: var(--th-text); border-bottom: 1px solid var(--th-border); letter-spacing: 0.01em; }
.create-listing-card .card-body { padding: 1.5rem; }
#map { height: 400px; border-radius: 12px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.06); }
.map-toolbar { display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; margin-bottom: 0.75rem; }
.category-dropdown .dropdown-toggle { min-height: 42px; text-align: left; border-radius: 10px; }
.category-dropdown .dropdown-menu { max-height: 280px; overflow-y: auto; padding: 0.5rem; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.12); }
.category-dropdown .form-check { padding: 0.5rem 0.75rem; margin: 0; border-radius: 8px; }
.category-dropdown .form-check:hover { background: #f1f5f9; }
.image-zone { border: 2px dashed #cbd5e1; border-radius: 14px; background: #fafbfc; padding: 2rem; text-align: center; transition: all 0.25s ease; }
.image-zone:hover, .image-zone.dragover { border-color: var(--th-primary); background: #f0f9ff; }
.image-zone .upload-btn-wrap { margin-top: 1rem; }
.image-preview-list { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-start; margin-top: 1.25rem; min-height: 60px; }
.image-preview-item { position: relative; flex-shrink: 0; cursor: grab; }
.image-preview-item:active { cursor: grabbing; }
.image-preview-item.dragging { opacity: 0.5; }
.image-preview-item img { width: 92px; height: 92px; object-fit: cover; border-radius: 10px; border: 2px solid var(--th-border); display: block; transition: transform 0.2s; cursor: zoom-in; }
.image-preview-item img:hover { transform: scale(1.03); }
.image-preview-item .thumb-badge { position: absolute; top: -6px; left: -6px; z-index: 2; width: 26px; height: 26px; padding: 0; border-radius: 50%; font-size: 12px; line-height: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
.image-preview-item .btn-remove { position: absolute; top: -8px; right: -8px; width: 26px; height: 26px; padding: 0; border-radius: 50%; font-size: 14px; line-height: 1; z-index: 2; box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
.image-preview-item .drag-handle { position: absolute; bottom: -4px; left: 50%; transform: translateX(-50%); font-size: 10px; color: var(--th-text-muted); }
.search-results-dropdown { position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid var(--th-border); border-top: none; border-radius: 0 0 12px 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); max-height: 260px; overflow-y: auto; z-index: 1000; margin-top: -2px; }
.search-results-dropdown .search-result-item { padding: 0.75rem 1rem; cursor: pointer; border-bottom: 1px solid #f1f5f9; transition: background 0.15s; }
.search-results-dropdown .search-result-item:last-child { border-bottom: none; }
.search-results-dropdown .search-result-item:hover { background: #f8fafc; }
.search-results-dropdown .search-result-item small { color: var(--th-text-muted); }
.search-results-dropdown .search-result-item .bi-geo { font-size: 0.875rem; }
.search-wrapper { position: relative; }
#imageLightboxModal .modal-dialog { max-width: 95vw; }
#imageLightboxModal .modal-content { background: transparent; border: none; }
#imageLightboxModal .modal-header { position: absolute; top: 0; right: 0; z-index: 10; border: none; }
#imageLightboxModal .modal-header .btn-close { filter: brightness(0) invert(1); opacity: 0.9; }
#imageLightboxModal .modal-body { padding: 2rem 1rem 1rem; text-align: center; }
#imageLightboxModal .modal-body img { max-width: 100%; max-height: 85vh; object-fit: contain; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.4); }
#imageLightboxModal .modal-backdrop { background-color: rgba(0,0,0,0.85); }
</style>
@endpush
<div class="container py-5">
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="rounded-3 bg-white shadow-sm p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
            <i class="bi bi-plus-square-dotted text-primary fs-4"></i>
        </div>
        <div>
            <h1 class="h4 mb-0 fw-bold" style="color: var(--th-text);">Create Trade Listing</h1>
            <p class="text-muted small mb-0">Add your item to the marketplace</p>
        </div>
    </div>
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
                    <input type="text" name="title" class="form-control form-control-lg rounded-3" value="{{ old('title') }}" placeholder="e.g. Lego Star Wars Set" required>
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
                        <input type="text" name="brand" class="form-control rounded-3" value="{{ old('brand') }}" placeholder="Optional">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Condition</label>
                        <select name="condition" class="form-select rounded-3" required>
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
                        <input type="number" name="cash_amount" class="form-control rounded-3" value="{{ old('cash_amount') }}" min="0" step="0.01" placeholder="For cash or add-cash" id="cashAmountInput">
                    </div>
                </div>
                <div class="mb-0 mt-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control text-justify rounded-3" rows="4" required placeholder="Describe your item...">{{ old('description') }}</textarea>
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
                <div class="search-wrapper mb-3" id="searchWrapper">
                    <div class="input-group rounded-3 overflow-hidden shadow-sm">
                        <span class="input-group-text bg-white border-end-0 rounded-0"><i class="bi bi-geo-alt-fill text-primary"></i></span>
                        <input type="text" id="locationSearch" class="form-control border-start-0 rounded-0" placeholder="Search location in Philippines..." autocomplete="off">
                        <button type="button" class="btn btn-primary rounded-0" id="btnSearch"><i class="bi bi-search me-1"></i>Search</button>
                    </div>
                    <div class="search-results-dropdown d-none" id="searchResults"></div>
                </div>
                <p class="small text-muted mb-2"><i class="bi bi-hand-index me-1"></i>Drag the pin to adjust the exact meetup location</p>
                <div class="map-toolbar">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnCenterMap" title="Center map on pin"><i class="bi bi-geo-alt me-1"></i>Center to pin</button>
                    <span class="text-muted small">Meetup radius: <strong id="radiusValue">5</strong> km</span>
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
                    <input type="text" name="meet_up_references" class="form-control rounded-3" value="{{ old('meet_up_references') }}" placeholder="Landmarks, preferred spots...">
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap pt-2">
            <button type="submit" class="btn btn-primary btn-lg px-4 shadow-sm"><i class="bi bi-check2-circle me-2"></i>Submit for review</button>
            <a href="{{ route('trading.index') }}" class="btn btn-outline-secondary btn-lg px-4">Cancel</a>
        </div>
    </form>
</div>

{{-- Image full-view modal --}}
<div class="modal fade" id="imageLightboxModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img src="" alt="Full view" id="lightboxImage" class="w-100" style="cursor: zoom-in;">
            </div>
        </div>
    </div>
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
            img.addEventListener('click', function(ev) {
                ev.preventDefault();
                ev.stopPropagation();
                var modal = document.getElementById('imageLightboxModal');
                var lightboxImg = document.getElementById('lightboxImage');
                if (modal && lightboxImg) {
                    lightboxImg.src = img.src;
                    var bsModal = typeof bootstrap !== 'undefined' && bootstrap.Modal ? new bootstrap.Modal(modal) : null;
                    if (bsModal) bsModal.show();
                }
            });
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

    var searchInput = document.getElementById('locationSearch');
    var searchResults = document.getElementById('searchResults');
    var searchWrapper = document.getElementById('searchWrapper');
    var searchDebounce = null;

    function showSearchResults(data) {
        searchResults.innerHTML = '';
        searchResults.classList.remove('d-none');
        searchWrapper.classList.add('has-results');
        if (!data || data.length === 0) {
            searchResults.innerHTML = '<div class="p-3 text-muted small text-center">No locations found. Try a different search.</div>';
            return;
        }
        data.forEach(function(r) {
            var div = document.createElement('div');
            div.className = 'search-result-item';
            div.innerHTML = '<div><i class="bi bi-geo-alt-fill me-2 text-primary"></i>' + (r.display_name || '') + '</div><small class="ms-4">' + (r.type || '') + '</small>';
            div.addEventListener('click', function() {
                moveMap(parseFloat(r.lat), parseFloat(r.lon), r.display_name);
                searchInput.value = r.display_name;
                searchResults.classList.add('d-none');
                searchWrapper.classList.remove('has-results');
                searchResults.innerHTML = '';
            });
            searchResults.appendChild(div);
        });
    }

    function doSearch() {
        var q = searchInput.value.trim();
        if (q.length < 2) {
            searchResults.classList.add('d-none');
            searchWrapper.classList.remove('has-results');
            return;
        }
        var url = 'https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(q + ', Philippines') + '&countrycodes=ph&limit=5';
        fetch(url, { headers: { 'Accept-Language': 'en' } })
            .then(function(res) { return res.json(); })
            .then(function(data) { showSearchResults(data); })
            .catch(function() { showSearchResults([]); });
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(doSearch, 400);
    });
    searchInput.addEventListener('focus', function() {
        if (searchInput.value.trim().length >= 2) doSearch();
    });
    document.getElementById('btnSearch').addEventListener('click', function() {
        doSearch();
    });
    document.addEventListener('click', function(e) {
        if (!searchWrapper.contains(e.target)) {
            searchResults.classList.add('d-none');
            searchWrapper.classList.remove('has-results');
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
