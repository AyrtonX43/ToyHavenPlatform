@extends('layouts.toyshop')

@section('title', 'Edit Listing - ToyHaven')

@push('styles')
<style>
    .create-listing-page { background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%); min-height: 100%; padding: 2rem 0 3rem; }
    .create-listing-card { background: #fff; border-radius: 16px; padding: 2rem 2.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
    .form-section { background: #f8fafc; border-radius: 12px; padding: 1.5rem 1.75rem; margin-bottom: 1.5rem; border: 1px solid #e2e8f0; }
    .form-section-title { font-size: 0.875rem; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem; }
    .form-control-marketplace { border-radius: 10px; border: 1px solid #e2e8f0; padding: 0.625rem 1rem; font-size: 0.9375rem; }
    .form-control-marketplace:focus { border-color: #0891b2; box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.12); }
    .form-section-title { display: flex; align-items: center; gap: 0.5rem; }
    .form-section-title i { color: #0891b2; }
    .form-label { font-weight: 600; color: #334155; font-size: 0.9375rem; }
    .upload-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        background: #fff;
        transition: border-color 0.2s, background 0.2s;
        cursor: pointer;
        position: relative;
    }
    .upload-zone:hover { border-color: #0891b2; background: #ecfeff; }
    .upload-zone.dragover { border-color: #0891b2; background: #ecfdf5; }
    .upload-zone.has-image { border-style: solid; border-color: #0891b2; background: #ecfeff; padding: 0.75rem; }
    .upload-zone input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
    .upload-zone .upload-placeholder { color: #64748b; font-size: 0.9375rem; }
    .upload-zone .upload-placeholder i { font-size: 2.5rem; color: #94a3b8; display: block; margin-bottom: 0.75rem; }
    .upload-zone .upload-hint { font-size: 0.8125rem; color: #94a3b8; margin-top: 0.5rem; }
    .preview-thumb { position: relative; width: 80px; height: 80px; border-radius: 10px; overflow: hidden; border: 2px solid #e2e8f0; display: inline-block; }
    .preview-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .preview-remove { position: absolute; top: 2px; right: 2px; width: 24px; height: 24px; border-radius: 50%; background: rgba(15,23,42,0.8); color: #fff; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 0.875rem; }
    .preview-remove:hover { background: #dc2626; }
    .btn-create-listing { background: #0891b2; border: none; font-weight: 600; padding: 0.75rem 1.5rem; border-radius: 10px; }
    .listing-details-section { border-left: 4px solid #0891b2; }
    .location-map-wrap { position: relative; }
    #listing-map { width: 100%; height: 280px; border-radius: 8px; border: 1px solid #e2e8f0; }
    .location-search-results { position: absolute; z-index: 1000; left: 0; right: 0; top: 100%; margin-top: 4px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); max-height: 220px; overflow-y: auto; }
    .location-search-results li { padding: 10px 12px; cursor: pointer; border-bottom: 1px solid #f1f5f9; list-style: none; font-size: 0.9rem; }
    .location-search-results li:last-child { border-bottom: none; }
    .location-search-results li:hover { background: #f8fafc; }
</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
@endpush

@section('content')
<div class="create-listing-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="create-listing-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h4 mb-0">Edit Listing</h1>
                        <a href="{{ route('trading.listings.show', $listing->id) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>

                    <div class="alert alert-info mb-4" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Re-approval required:</strong> Once you save your changes, this listing will be sent for admin approval. It will not be visible in the marketplace until approved.
                    </div>

                    <form method="POST" action="{{ route('trading.listings.update', $listing->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-section">
                            <div class="form-section-title"><i class="bi bi-image"></i> Product Photos</div>
                            <label class="form-label d-block mb-2">Upload up to 10 images. Use the button below or drag photos onto the dashed area. Click the X on current images to mark for removal.</label>
                            <input type="file" name="images[]" id="edit_listing_images" accept="image/jpeg,image/png,image/jpg,image/webp" multiple class="d-none">
                            <button type="button" id="btnAddPhotos" class="btn btn-outline-primary mb-3">
                                <i class="bi bi-plus-circle me-1"></i> Select images
                            </button>
                            <div class="upload-zone" id="uploadZone">
                                <div class="upload-placeholder" id="uploadPlaceholder">
                                    <i class="bi bi-cloud-arrow-up"></i>
                                    <span>Or drag photos here — max 10 images total</span>
                                    <div class="upload-hint">JPEG, PNG or WebP. Max 5 MB each.</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 justify-content-center mt-2" id="previewList">
                                    @foreach($listing->images as $img)
                                    <label class="preview-thumb mb-0 remove-thumb" style="cursor:pointer;">
                                        <img src="{{ asset('storage/' . $img->image_path) }}" alt="Listing image">
                                        <input type="checkbox" name="remove_images[]" value="{{ $img->id }}" class="d-none remove-cb">
                                        <span class="preview-remove remove-img-btn" title="Mark for removal"><i class="bi bi-x-lg"></i></span>
                                    </label>
                                    @endforeach
                                    @if($listing->images->isEmpty())
                                        @php
                                            $item = $listing->getItem();
                                            $fallbackImages = collect();
                                            if ($listing->image_path) {
                                                $fallbackImages->push((object)['image_path' => $listing->image_path, 'id' => null]);
                                            }
                                            if ($item && $item->images->isNotEmpty()) {
                                                foreach ($item->images as $pi) {
                                                    $fallbackImages->push((object)['image_path' => $pi->image_path, 'id' => null]);
                                                }
                                            }
                                        @endphp
                                        @foreach($fallbackImages as $img)
                                        <div class="preview-thumb mb-0">
                                            <img src="{{ asset('storage/' . $img->image_path) }}" alt="Listing image">
                                            <span class="badge bg-secondary position-absolute bottom-0 start-0 end-0 rounded-0 rounded-bottom" style="font-size: 0.65rem;">From product</span>
                                        </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            @php
                                $editCurrentCount = $listing->images->count();
                                if ($editCurrentCount === 0) {
                                    $editItem = $listing->getItem();
                                    if ($listing->image_path) $editCurrentCount++;
                                    if ($editItem && $editItem->images) $editCurrentCount += $editItem->images->count();
                                }
                            @endphp
                            <div class="small text-muted mt-2" id="imageCount"><span id="currentCountNum">{{ $editCurrentCount }}</span> current · <span id="newCountNum">0</span> new</div>
                            @error('images')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-section listing-details-section">
                            <div class="form-section-title"><i class="bi bi-pencil-square"></i> Listing Details</div>
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" value="{{ old('title', $listing->title) }}" class="form-control form-control-marketplace" required>
                                @error('title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Brand</label>
                                <input type="text" name="brand" value="{{ old('brand', $listing->brand) }}" class="form-control form-control-marketplace" placeholder="e.g. Lego, Hasbro, Mattel">
                                @error('brand')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Trade type</label>
                                <select name="trade_type" id="trade_type" class="form-select form-control-marketplace" required>
                                    <option value="barter" {{ old('trade_type', $listing->trade_type) == 'barter' ? 'selected' : '' }}>Barter (item for item)</option>
                                    <option value="barter_with_cash" {{ old('trade_type', $listing->trade_type) == 'barter_with_cash' ? 'selected' : '' }}>Barter with cash</option>
                                    <option value="cash" {{ old('trade_type', $listing->trade_type) == 'cash' ? 'selected' : '' }}>Cash</option>
                                </select>
                            </div>
                            <div class="mb-3" id="priceWrap">
                                <label class="form-label">Price (PHP)</label>
                                <input type="number" name="cash_difference" id="cash_difference" value="{{ old('cash_difference', $listing->cash_difference) }}" step="0.01" min="0" class="form-control form-control-marketplace" placeholder="0.00">
                                <small class="text-muted">Asking price or cash difference. Disabled for barter-only.</small>
                                @error('cash_difference')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select form-control-marketplace" required>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $listing->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Condition</label>
                                <select name="condition" class="form-select form-control-marketplace">
                                    <option value="">Select condition</option>
                                    <option value="new" {{ old('condition', $listing->condition) == 'new' ? 'selected' : '' }}>New</option>
                                    <option value="used" {{ old('condition', $listing->condition) == 'used' ? 'selected' : '' }}>Used</option>
                                    <option value="refurbished" {{ old('condition', $listing->condition) == 'refurbished' ? 'selected' : '' }}>Refurbished</option>
                                </select>
                                @error('condition')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" rows="5" class="form-control form-control-marketplace" required>{{ old('description', $listing->description) }}</textarea>
                                @error('description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3 location-map-wrap">
                                <label class="form-label">Meet-up location</label>
                                <div class="d-flex gap-2 mb-2">
                                    <input type="text" name="location" id="location_address" value="{{ old('location', $listing->location) }}" class="form-control form-control-marketplace flex-grow-1" placeholder="Search address or place, e.g. Mall of Asia, Pasay City" autocomplete="off">
                                    <button type="button" id="btnSearchLocation" class="btn btn-outline-primary flex-shrink-0"><i class="bi bi-search me-1"></i> Search</button>
                                </div>
                                <div id="location_search_results" class="location-search-results d-none"></div>
                                <input type="hidden" name="location_lat" id="location_lat" value="{{ old('location_lat', $listing->location_lat) }}">
                                <input type="hidden" name="location_lng" id="location_lng" value="{{ old('location_lng', $listing->location_lng) }}">
                                <div id="listing-map" class="mt-2"></div>
                                <small class="text-muted d-block mt-1">Search for an address or click on the map to set your meet-up spot. The pin will be saved exactly where you set it.</small>
                                @error('location')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Meet up references</label>
                                <textarea name="meet_up_references" rows="2" class="form-control form-control-marketplace" placeholder="Preferred meet-up spots, landmarks, or delivery options">{{ old('meet_up_references', $listing->meet_up_references) }}</textarea>
                                @error('meet_up_references')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-section-title">Expiry</div>
                            <input type="date" name="expires_at" value="{{ old('expires_at', $listing->expires_at?->format('Y-m-d')) }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" class="form-control form-control-marketplace">
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('trading.listings.show', $listing->id) }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-create-listing">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(function() {
    var currentCount = {{ $editCurrentCount }};
    var fileInput = document.getElementById('edit_listing_images');
    var previewList = document.getElementById('previewList');
    var imageCountEl = document.getElementById('imageCount');
    var newCountNum = document.getElementById('newCountNum');
    var uploadPlaceholder = document.getElementById('uploadPlaceholder');
    var uploadZone = document.getElementById('uploadZone');
    var MAX = 10;
    var selectedNewFiles = [];

    document.querySelectorAll('.remove-thumb').forEach(function(label) {
        label.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var cb = this.querySelector('.remove-cb');
            if (cb) {
                cb.checked = !cb.checked;
                this.classList.toggle('opacity-50', cb.checked);
                updateImageCount();
            }
        });
    });

    function countKeptCurrent() {
        var thumbs = previewList.querySelectorAll('.remove-thumb');
        var kept = 0;
        thumbs.forEach(function(t) { if (!t.classList.contains('opacity-50')) kept++; });
        var fallbacks = previewList.querySelectorAll('.preview-thumb:not(.remove-thumb):not(.new-preview)');
        return kept + fallbacks.length;
    }

    function updateImageCount() {
        var newNum = selectedNewFiles.length;
        if (newCountNum) newCountNum.textContent = newNum;
        var currEl = document.getElementById('currentCountNum');
        if (currEl) currEl.textContent = countKeptCurrent();
    }

    function syncFileInput() {
        try {
            var dt = new DataTransfer();
            for (var i = 0; i < selectedNewFiles.length; i++) dt.items.add(selectedNewFiles[i]);
            fileInput.files = dt.files;
        } catch (err) { console.warn('DataTransfer not supported', err); }
    }

    function isImage(file) {
        if (!file || !file.type) return false;
        return /^image\/(jpeg|png|jpg|webp)$/i.test(file.type) || /\.(jpe?g|png|webp)$/i.test(file.name || '');
    }

    function addNewFiles(fileList) {
        if (!fileList || !fileList.length) return;
        var currentTotal = countCurrentThumbs() + selectedNewFiles.length;
        for (var i = 0; i < fileList.length && currentTotal + selectedNewFiles.length < MAX; i++) {
            if (isImage(fileList[i])) {
                selectedNewFiles.push(fileList[i]);
            }
        }
        renderNewPreviews();
    }

    function renderNewPreviews() {
        previewList.querySelectorAll('.new-preview').forEach(function(el) { el.remove(); });
        selectedNewFiles.forEach(function(file, i) {
            var reader = new FileReader();
            reader.onload = (function(idx) {
                return function(e) {
                    var div = document.createElement('div');
                    div.className = 'preview-thumb mb-0 new-preview';
                    div.innerHTML = '<img src="' + e.target.result + '" alt=""><button type="button" class="preview-remove" title="Remove"><i class="bi bi-x-lg"></i></button>';
                    previewList.appendChild(div);
                    div.querySelector('.preview-remove').addEventListener('click', function(ev) {
                        ev.preventDefault();
                        ev.stopPropagation();
                        selectedNewFiles.splice(idx, 1);
                        renderNewPreviews();
                    });
                };
            })(i);
            reader.readAsDataURL(file);
        });
        var hasAny = previewList.querySelectorAll('.preview-thumb, .remove-thumb').length > 0;
        if (uploadPlaceholder) uploadPlaceholder.style.display = hasAny ? 'none' : '';
        if (uploadZone) uploadZone.classList.toggle('has-image', hasAny);
        updateImageCount();
        syncFileInput();
    }

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length) addNewFiles(Array.from(this.files));
            this.value = '';
        });
    }
    var btnAddPhotos = document.getElementById('btnAddPhotos');
    if (btnAddPhotos && fileInput) {
        btnAddPhotos.addEventListener('click', function(e) {
            e.preventDefault();
            fileInput.click();
        });
    }
    if (uploadZone) {
        uploadZone.addEventListener('dragover', function(e) { e.preventDefault(); e.stopPropagation(); this.classList.add('dragover'); });
        uploadZone.addEventListener('dragleave', function(e) { e.preventDefault(); this.classList.remove('dragover'); });
        uploadZone.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('dragover');
            if (e.dataTransfer.files && e.dataTransfer.files.length) addNewFiles(Array.from(e.dataTransfer.files));
        });
    }
    var hasInitial = previewList.querySelectorAll('.preview-thumb, .remove-thumb').length > 0;
    if (uploadPlaceholder) uploadPlaceholder.style.display = hasInitial ? 'none' : '';
    if (uploadZone) uploadZone.classList.toggle('has-image', hasInitial);
})();

// Price field: disable when barter, enable when barter_with_cash or cash
(function() {
    var tradeTypeSelect = document.getElementById('trade_type');
    var priceInput = document.getElementById('cash_difference');
    function togglePrice() {
        if (!tradeTypeSelect || !priceInput) return;
        var isBarter = tradeTypeSelect.value === 'barter';
        priceInput.disabled = isBarter;
        if (isBarter) priceInput.value = '';
        priceInput.removeAttribute('required');
        if (!isBarter && (tradeTypeSelect.value === 'cash' || tradeTypeSelect.value === 'barter_with_cash')) {
            priceInput.setAttribute('required', 'required');
        }
    }
    if (tradeTypeSelect) tradeTypeSelect.addEventListener('change', togglePrice);
    togglePrice();
})();
</script>
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
(function() {
    var mapEl = document.getElementById('listing-map');
    var inputEl = document.getElementById('location_address');
    var btnSearch = document.getElementById('btnSearchLocation');
    var resultsEl = document.getElementById('location_search_results');
    if (!mapEl || !inputEl) return;

    var defaultCenter = [14.5995, 120.9842];
    var map = L.map('listing-map').setView(defaultCenter, 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);
    var marker = L.marker(defaultCenter, { draggable: true }).addTo(map);
    var latInput = document.getElementById('location_lat');
    var lngInput = document.getElementById('location_lng');

    function syncLatLngToForm(lat, lng) {
        if (latInput) latInput.value = lat;
        if (lngInput) lngInput.value = lng;
    }

    marker.on('dragend', function() {
        var lat = marker.getLatLng().lat;
        var lng = marker.getLatLng().lng;
        syncLatLngToForm(lat, lng);
        reverseGeocode(lat, lng);
    });

    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        syncLatLngToForm(e.latlng.lat, e.latlng.lng);
        reverseGeocode(e.latlng.lat, e.latlng.lng);
    });

    function reverseGeocode(lat, lng) {
        fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng + '&addressdetails=1', {
            headers: { 'Accept': 'application/json', 'User-Agent': 'ToyHavenPlatform/1.0' }
        }).then(function(r) { return r.json(); })
        .then(function(data) {
            if (data && data.display_name) inputEl.value = data.display_name;
        }).catch(function() {});
    }

    function showResults(items) {
        if (!items || items.length === 0) {
            resultsEl.classList.add('d-none');
            resultsEl.innerHTML = '';
            return;
        }
        resultsEl.innerHTML = items.map(function(item) {
            return '<li data-lat="' + item.lat + '" data-lon="' + item.lon + '" data-name="' + (item.display_name || '').replace(/"/g, '&quot;') + '">' +
                '<span class="d-block">' + (item.display_name || '') + '</span></li>';
        }).join('');
        resultsEl.classList.remove('d-none');
        resultsEl.querySelectorAll('li').forEach(function(li) {
            li.addEventListener('click', function() {
                var lat = parseFloat(li.getAttribute('data-lat'));
                var lon = parseFloat(li.getAttribute('data-lon'));
                var name = li.getAttribute('data-name');
                marker.setLatLng([lat, lon]);
                map.setView([lat, lon], 16);
                syncLatLngToForm(lat, lon);
                inputEl.value = name.replace(/&quot;/g, '"');
                resultsEl.classList.add('d-none');
                resultsEl.innerHTML = '';
            });
        });
    }

    function doSearch() {
        var q = inputEl.value.trim();
        if (!q) return;
        resultsEl.innerHTML = '<li class="text-muted">Searching...</li>';
        resultsEl.classList.remove('d-none');
        fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(q) + '&limit=6&addressdetails=0', {
            headers: { 'Accept': 'application/json', 'User-Agent': 'ToyHavenPlatform/1.0' }
        }).then(function(r) { return r.json(); })
        .then(function(data) {
            showResults(data || []);
        }).catch(function() {
            showResults([]);
        });
    }

    if (btnSearch) btnSearch.addEventListener('click', doSearch);
    inputEl.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); doSearch(); }
    });

    document.addEventListener('click', function(e) {
        if (resultsEl && !resultsEl.classList.contains('d-none') &&
            !resultsEl.contains(e.target) && e.target !== inputEl && e.target !== btnSearch) {
            resultsEl.classList.add('d-none');
        }
    });

    // On load: show user's saved meet-up location using stored coordinates (accurate) or geocode address
    var initialLat = latInput && latInput.value ? parseFloat(latInput.value) : NaN;
    var initialLng = lngInput && lngInput.value ? parseFloat(lngInput.value) : NaN;
    if (!isNaN(initialLat) && !isNaN(initialLng)) {
        marker.setLatLng([initialLat, initialLng]);
        map.setView([initialLat, initialLng], 14);
    } else {
        var initialAddress = inputEl.value.trim();
        if (initialAddress) {
            fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(initialAddress) + '&limit=1', {
                headers: { 'Accept': 'application/json', 'User-Agent': 'ToyHavenPlatform/1.0' }
            }).then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && data[0]) {
                    var lat = parseFloat(data[0].lat);
                    var lon = parseFloat(data[0].lon);
                    marker.setLatLng([lat, lon]);
                    map.setView([lat, lon], 14);
                    syncLatLngToForm(lat, lon);
                }
            }).catch(function() {});
        }
    }
})();
</script>
@endpush
@endsection
