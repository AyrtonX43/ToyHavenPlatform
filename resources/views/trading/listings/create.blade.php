@extends('layouts.toyshop')

@section('title', 'Create Trade Listing - ToyHaven')

@push('styles')
<style>
    .create-listing-page {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        min-height: 100%;
        padding: 2rem 0 3rem;
    }
    .create-listing-card {
        background: #fff;
        border-radius: 16px;
        padding: 2rem 2.5rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
    }
    .create-listing-card .page-header {
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 1.5rem;
        margin-bottom: 2rem;
    }
    .create-listing-card .page-header h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -0.025em;
        margin: 0;
    }
    .create-listing-card .page-header .subtitle {
        font-size: 0.9375rem;
        color: #64748b;
        margin-top: 0.25rem;
    }
    .form-section {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.5rem 1.75rem;
        margin-bottom: 1.5rem;
        border: 1px solid #e2e8f0;
    }
    .form-section-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .form-section-title i { color: #0891b2; }
    .form-label { font-weight: 600; color: #334155; font-size: 0.9375rem; }
    .form-control-marketplace {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        padding: 0.625rem 1rem;
        font-size: 0.9375rem;
    }
    .form-control-marketplace:focus {
        border-color: #0891b2;
        box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.12);
    }
    .form-control-marketplace:disabled { background-color: #e2e8f0; cursor: not-allowed; }
    .form-control-marketplace::placeholder { color: #94a3b8; }
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
    .upload-zone input[type="file"] {
        position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
    }
    .upload-zone .upload-placeholder { color: #64748b; font-size: 0.9375rem; }
    .upload-zone .upload-placeholder i { font-size: 2.5rem; color: #94a3b8; display: block; margin-bottom: 0.75rem; }
    .upload-zone .upload-hint { font-size: 0.8125rem; color: #94a3b8; margin-top: 0.5rem; }
    .preview-thumb {
        position: relative;
        width: 80px;
        height: 80px;
        border-radius: 10px;
        overflow: hidden;
        border: 2px solid #e2e8f0;
        display: inline-block;
    }
    .preview-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .preview-remove {
        position: absolute; top: 2px; right: 2px;
        width: 24px; height: 24px; border-radius: 50%;
        background: rgba(15, 23, 42, 0.8); color: #fff; border: none;
        display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 0.875rem;
    }
    .preview-remove:hover { background: #dc2626; }
    .btn-create-listing { background: #0891b2; border: none; font-weight: 600; padding: 0.75rem 1.5rem; border-radius: 10px; }
    .btn-create-listing:hover { background: #0e7490; color: #fff; }
    .listing-details-section { border-left: 4px solid #0891b2; }
    .btn-cancel-listing { border-radius: 10px; font-weight: 500; }
    .location-map-wrap { position: relative; }
    #listing-map { width: 100%; height: 280px; border-radius: 8px; border: 1px solid #e2e8f0; }
    .location-search-results { position: absolute; z-index: 1000; left: 0; right: 0; top: 100%; margin-top: 4px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); max-height: 220px; overflow-y: auto; }
    .location-search-results li { padding: 10px 12px; cursor: pointer; border-bottom: 1px solid #f1f5f9; list-style: none; font-size: 0.9rem; }
    .location-search-results li:last-child { border-bottom: none; }
    .location-search-results li:hover { background: #f8fafc; }
    .location-search-results .text-muted { font-size: 0.8rem; }
</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
@endpush

@section('content')
<div class="create-listing-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="create-listing-card">
                    <div class="page-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-sm-center gap-3">
                        <div>
                            <h1>Create Trade Listing</h1>
                            <p class="subtitle">List your item to trade. Add details to get more offers.</p>
                            <p class="subtitle mt-1 text-amber-600 dark:text-amber-400 small"><i class="bi bi-info-circle me-1"></i> New listings require admin approval before going live. You will be notified by email once your listing is reviewed.</p>
                        </div>
                        <a href="{{ route('trading.index') }}" class="btn btn-outline-secondary btn-cancel-listing">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </a>
                    </div>

                    <form method="POST" action="{{ route('trading.listings.store') }}" enctype="multipart/form-data" id="createListingForm">
                        @csrf

                        <!-- Product photos (max 10) -->
                        <div class="form-section">
                            <div class="form-section-title"><i class="bi bi-image"></i> Product Photos</div>
                            <label class="form-label d-block mb-2">Upload up to 10 images. Use the button below or drag photos onto the dashed area.</label>
                            <input type="file" name="images[]" id="listing_images" accept="image/jpeg,image/png,image/jpg,image/webp" multiple class="d-none">
                            <button type="button" id="btnAddPhotos" class="btn btn-outline-primary mb-3">
                                <i class="bi bi-plus-circle me-1"></i> Select images
                            </button>
                            <div class="upload-zone" id="uploadZone">
                                <div class="upload-placeholder" id="uploadPlaceholder">
                                    <i class="bi bi-cloud-arrow-up"></i>
                                    <span>Or drag photos here — max 10 images</span>
                                    <div class="upload-hint">JPEG, PNG or WebP. Max 5 MB each.</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 justify-content-center mt-2" id="previewList"></div>
                            </div>
                            <div class="small text-muted mt-2" id="imageCount">0 images selected</div>
                            @error('images')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            @error('images.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <!-- Listing details -->
                        <div class="form-section listing-details-section">
                            <div class="form-section-title"><i class="bi bi-pencil-square"></i> Listing Details</div>

                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" value="{{ old('title') }}" class="form-control form-control-marketplace" required placeholder="e.g. Vintage action figure, sealed box">
                                @error('title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Brand</label>
                                <input type="text" name="brand" value="{{ old('brand') }}" class="form-control form-control-marketplace" placeholder="e.g. Lego, Hasbro, Mattel">
                                @error('brand')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Product sell type</label>
                                <select name="trade_type" id="trade_type" class="form-select form-control-marketplace" required>
                                    <option value="barter" {{ old('trade_type') == 'barter' ? 'selected' : '' }}>Barter (item for item)</option>
                                    <option value="barter_with_cash" {{ old('trade_type') == 'barter_with_cash' ? 'selected' : '' }}>Barter with cash</option>
                                    <option value="cash" {{ old('trade_type') == 'cash' ? 'selected' : '' }}>Cash</option>
                                </select>
                                @error('trade_type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3" id="priceWrap">
                                <label class="form-label">Price (PHP)</label>
                                <input type="number" name="cash_difference" id="cash_difference" value="{{ old('cash_difference') }}" step="0.01" min="0" class="form-control form-control-marketplace" placeholder="0.00">
                                <small class="text-muted">Asking price or cash difference for barter with cash. Disabled for barter-only.</small>
                                @error('cash_difference')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select form-control-marketplace" required>
                                    <option value="">Select category</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Condition</label>
                                <select name="condition" class="form-select form-control-marketplace">
                                    <option value="">Select condition</option>
                                    <option value="new" {{ old('condition') == 'new' ? 'selected' : '' }}>New</option>
                                    <option value="used" {{ old('condition') == 'used' ? 'selected' : '' }}>Used</option>
                                    <option value="refurbished" {{ old('condition') == 'refurbished' ? 'selected' : '' }}>Refurbished</option>
                                </select>
                                @error('condition')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" rows="5" class="form-control form-control-marketplace" required placeholder="Describe the item and what you're looking for...">{{ old('description') }}</textarea>
                                @error('description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3 location-map-wrap">
                                <label class="form-label">Meet-up location</label>
                                <div class="d-flex gap-2 mb-2">
                                    <input type="text" name="location" id="location_address" value="{{ old('location') }}" class="form-control form-control-marketplace flex-grow-1" placeholder="Search address or place, e.g. Mall of Asia, Pasay City" autocomplete="off">
                                    <button type="button" id="btnSearchLocation" class="btn btn-outline-primary flex-shrink-0"><i class="bi bi-search me-1"></i> Search</button>
                                </div>
                                <div id="location_search_results" class="location-search-results d-none"></div>
                                <input type="hidden" name="location_lat" id="location_lat" value="{{ old('location_lat') }}">
                                <input type="hidden" name="location_lng" id="location_lng" value="{{ old('location_lng') }}">
                                <div id="listing-map" class="mt-2"></div>
                                <small class="text-muted d-block mt-1">Search for an address or click on the map to set your meet-up spot. The pin will be saved exactly where you set it.</small>
                                @error('location')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-0">
                                <label class="form-label">Preferred meet-up</label>
                                <textarea name="meet_up_references" rows="2" class="form-control form-control-marketplace" placeholder="Preferred meet-up spots, landmarks, or delivery options">{{ old('meet_up_references') }}</textarea>
                                @error('meet_up_references')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4 pt-3 border-top border-secondary">
                            <a href="{{ route('trading.index') }}" class="btn btn-outline-secondary btn-cancel-listing order-2 order-sm-1">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-create-listing order-1 order-sm-2">
                                <i class="bi bi-check-circle me-1"></i> Create listing
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var fileInput = document.getElementById('listing_images');
    var previewList = document.getElementById('previewList');
    var imageCount = document.getElementById('imageCount');
    var uploadPlaceholder = document.getElementById('uploadPlaceholder');
    var uploadZone = document.getElementById('uploadZone');
    var MAX = 10;
    var selectedFiles = [];

    function isImage(file) {
        if (!file || !file.type) return false;
        return /^image\/(jpeg|png|jpg|webp)$/i.test(file.type) || /\.(jpe?g|png|webp)$/i.test(file.name || '');
    }

    function syncFileInputToSelected() {
        try {
            var dt = new DataTransfer();
            for (var i = 0; i < selectedFiles.length; i++) dt.items.add(selectedFiles[i]);
            fileInput.files = dt.files;
        } catch (err) { console.warn('DataTransfer not supported', err); }
    }

    function renderPreview() {
        previewList.innerHTML = '';
        if (selectedFiles.length > 0 && uploadPlaceholder) {
            uploadPlaceholder.style.display = 'none';
            uploadZone.classList.add('has-image');
        } else {
            if (uploadPlaceholder) uploadPlaceholder.style.display = '';
            uploadZone.classList.remove('has-image');
        }
        selectedFiles.forEach(function(file, i) {
            var reader = new FileReader();
            reader.onload = (function(idx) {
                return function(e) {
                    var div = document.createElement('div');
                    div.className = 'preview-thumb';
                    div.innerHTML = '<img src="' + e.target.result + '" alt=""><button type="button" class="preview-remove" title="Remove"><i class="bi bi-x-lg"></i></button>';
                    previewList.appendChild(div);
                    div.querySelector('.preview-remove').addEventListener('click', function(ev) {
                        ev.preventDefault();
                        ev.stopPropagation();
                        selectedFiles.splice(idx, 1);
                        syncFileInputToSelected();
                        renderPreview();
                    });
                };
            })(i);
            reader.readAsDataURL(file);
        });
        imageCount.textContent = selectedFiles.length + ' image(s) selected (max ' + MAX + ')';
        syncFileInputToSelected();
    }

    function addFiles(fileList, fromDrop) {
        if (!fileList || !fileList.length) return;
        for (var i = 0; i < fileList.length && selectedFiles.length < MAX; i++) {
            if (isImage(fileList[i])) selectedFiles.push(fileList[i]);
        }
        renderPreview();
    }

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length) addFiles(this.files, false);
            this.value = '';
        });
    }
    var btnAddPhotos = document.getElementById('btnAddPhotos');
    if (btnAddPhotos && fileInput) {
        btnAddPhotos.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
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
            if (e.dataTransfer.files && e.dataTransfer.files.length) addFiles(e.dataTransfer.files, true);
        });
    }

    var tradeTypeSelect = document.getElementById('trade_type');
    var priceInput = document.getElementById('cash_difference');
    function togglePrice() {
        var isBarter = tradeTypeSelect && tradeTypeSelect.value === 'barter';
        priceInput.disabled = isBarter;
        if (isBarter) priceInput.value = '';
        priceInput.required = !isBarter && (tradeTypeSelect.value === 'cash' || tradeTypeSelect.value === 'barter_with_cash');
    }
    if (tradeTypeSelect) tradeTypeSelect.addEventListener('change', togglePrice);
    togglePrice();

    var form = document.getElementById('createListingForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            syncFileInputToSelected();
            if (selectedFiles.length < 1) {
                alert('Please upload at least 1 image.');
                return;
            }
            if (selectedFiles.length > MAX) {
                alert('Please upload at most ' + MAX + ' images.');
                return;
            }
            var submitBtn = form.querySelector('button[type="submit"]');
            var origBtnText = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Uploading...';
            }
            var formData = new FormData();
            formData.append('_token', form.querySelector('input[name="_token"]') ? form.querySelector('input[name="_token"]').value : '');
            var els = form.querySelectorAll('input, select, textarea');
            for (var n = 0; n < els.length; n++) {
                var el = els[n];
                if (!el.name || el.name === '_token' || el.id === 'listing_images') continue;
                if (el.type === 'file') continue;
                if (el.disabled) continue;
                if (el.type === 'checkbox' || el.type === 'radio') { if (el.checked) formData.append(el.name, el.value); }
                else formData.append(el.name, el.value);
            }
            for (var i = 0; i < selectedFiles.length; i++) {
                var f = selectedFiles[i];
                var name = (f.name && /\.(jpe?g|png|webp)$/i.test(f.name)) ? f.name : 'image-' + (i + 1) + '.jpg';
                formData.append('images[]', f, name);
            }
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin'
            }).then(function(res) {
                if (res.redirected) {
                    window.location.href = res.url;
                    return;
                }
                if (res.status === 422) {
                    return res.json().then(function(data) {
                        var msg = data.message || 'Please fix the errors below.';
                        if (data.errors && data.errors.images) {
                            var im = data.errors.images;
                            msg += ' Images: ' + (Array.isArray(im) ? im.join(' ') : im[0]);
                        }
                        alert(msg);
                    }).catch(function() { alert('Validation failed. Check all fields and images (max 10, 5MB each).'); });
                } else if (res.ok) {
                    window.location.href = res.url || '{{ route("trading.index") }}';
                } else {
                    return res.text().then(function() { alert('Something went wrong. Try again.'); });
                }
            }).catch(function(err) {
                alert('Upload failed. Check your connection and try again.');
                console.error(err);
            }).finally(function() {
                if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = origBtnText; }
            });
        });
    }
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
})();
</script>
@endpush
@endsection
