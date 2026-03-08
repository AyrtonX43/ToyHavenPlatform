@extends('layouts.toyshop')

@section('title', 'Edit Auction Listing - ToyHaven')

@push('styles')
<style>
.auction-image-zone { border: 2px dashed #cbd5e1; border-radius: 12px; padding: 2rem; text-align: center; background: #f8fafc; cursor: pointer; transition: all 0.2s; }
.auction-image-zone:hover, .auction-image-zone.dragover { border-color: #0ea5e9; background: #f0f9ff; }
.auction-image-preview { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1rem; }
.auction-image-item { position: relative; width: 90px; height: 90px; border-radius: 8px; overflow: hidden; border: 2px solid #e2e8f0; }
.auction-image-item img { width: 100%; height: 100%; object-fit: cover; }
.auction-image-item .primary-badge { position: absolute; top: 4px; left: 4px; background: #0ea5e9; color: white; font-size: 0.65rem; padding: 2px 6px; border-radius: 4px; }
.auction-image-item .actions { position: absolute; bottom: 0; left: 0; right: 0; display: flex; justify-content: center; gap: 4px; background: rgba(0,0,0,0.6); padding: 4px; }
.auction-image-item .actions button { background: transparent; border: none; color: white; padding: 2px 6px; font-size: 0.75rem; cursor: pointer; }
.auction-image-item .actions button:hover { color: #93c5fd; }
.auction-image-item.dragging { opacity: 0.5; }
</style>
@endpush

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auction.index') }}">Auction</a></li>
            <li class="breadcrumb-item"><a href="{{ route('auction.seller.dashboard') }}">Seller Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('auction.listings.index') }}">My Listings</a></li>
            <li class="breadcrumb-item active">Edit Listing</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Auction Listing</h4>
                            <p class="mb-0 small opacity-90">{{ $listing->title }}</p>
                        </div>
                        <a href="{{ route('auction.listings.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if($listing->rejection_reason)
                        <div class="alert alert-warning">
                            <strong>Feedback from reviewer:</strong><br>{{ $listing->rejection_reason }}
                        </div>
                    @endif

                    @php
                        $defaultEnd = old('scheduled_end_at');
                        if (!$defaultEnd && $listing->scheduled_end_at) {
                            $defaultEnd = $listing->scheduled_end_at->format('Y-m-d\TH:i');
                        }
                        if (!$defaultEnd) {
                            $hours = $listing->duration_hours ?? 24;
                            $defaultEnd = now()->addHours($hours)->format('Y-m-d\TH:i');
                        }
                        $listingCategories = old('category_ids', $listing->categories->pluck('id')->toArray());
                    @endphp

                    <form action="{{ route('auction.listings.update', $listing) }}" method="POST" enctype="multipart/form-data" id="auction-form">
                        @csrf
                        @method('PUT')

                        <h6 class="text-uppercase text-muted mb-3">Item Details</h6>
                        <div class="mb-4">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title', $listing->title) }}" required maxlength="255">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4"
                                maxlength="5000">{{ old('description', $listing->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Item Condition <span class="text-danger">*</span></label>
                            <select name="condition" class="form-select @error('condition') is-invalid @enderror" required>
                                @foreach(\App\Models\Auction::CONDITIONS as $value => $label)
                                    <option value="{{ $value }}" {{ old('condition', $listing->condition ?? 'good') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('condition')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <h6 class="text-uppercase text-muted mb-3 mt-4">Images</h6>
                        <div class="mb-4">
                            <label class="form-label">Item Images <span class="text-danger">*</span></label>
                            <div class="auction-image-zone" id="dropZone" onclick="document.getElementById('imagesInput').click()">
                                <i class="bi bi-cloud-arrow-up display-4 text-muted"></i>
                                <p class="mb-0 mt-2 text-muted">Drag & drop images here or click to browse</p>
                                <small class="text-muted">Max 10 images. JPEG, PNG, WebP. 5MB each.</small>
                            </div>
                            <input type="file" name="images[]" id="imagesInput" class="d-none" accept="image/jpeg,image/png,image/jpg,image/webp" multiple>
                            <div class="auction-image-preview" id="imagePreview"></div>
                            <input type="hidden" name="primary_index" id="primaryIndex" value="0">
                            <input type="hidden" name="keep_image_ids" id="keepImageIds" value="">
                            <input type="hidden" name="delete_image_ids" id="deleteImageIds" value="">
                            @error('images')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <h6 class="text-uppercase text-muted mb-3 mt-4">Pricing</h6>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Starting Bid (₱) <span class="text-danger">*</span></label>
                                <input type="number" name="starting_bid" class="form-control @error('starting_bid') is-invalid @enderror"
                                    value="{{ old('starting_bid', $listing->starting_bid) }}" required min="1" step="0.01">
                                @error('starting_bid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Reserve Price (₱) <small class="text-muted">Optional</small></label>
                                <input type="number" name="reserve_price" class="form-control @error('reserve_price') is-invalid @enderror"
                                    value="{{ old('reserve_price', $listing->reserve_price) }}" min="0" step="0.01" placeholder="Leave empty for no reserve">
                                @error('reserve_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Bid Increment (₱) <span class="text-danger">*</span></label>
                                <input type="number" name="bid_increment" class="form-control @error('bid_increment') is-invalid @enderror"
                                    value="{{ old('bid_increment', $listing->bid_increment) }}" required min="1" step="0.01">
                                @error('bid_increment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Auction End Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="scheduled_end_at" class="form-control @error('scheduled_end_at') is-invalid @enderror"
                                    value="{{ $defaultEnd }}" required min="{{ now()->addHour()->format('Y-m-d\TH:i') }}">
                                <small class="text-muted">When you want the auction to end (after approval)</small>
                                @error('scheduled_end_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <h6 class="text-uppercase text-muted mb-3 mt-4">Categories</h6>
                        <div class="mb-4">
                            <label class="form-label">Categories <small class="text-muted">Select up to 3</small></label>
                            <select name="category_ids[]" class="form-select @error('category_ids') is-invalid @enderror" multiple size="6">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ in_array($cat->id, $listingCategories) ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple. Max 3.</small>
                            @error('category_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Save Changes
                        </button>
                        <a href="{{ route('auction.listings.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewImageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-0 text-center">
                <img id="viewImageSrc" src="" alt="" class="img-fluid" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const MAX_IMAGES = 10;
    const dropZone = document.getElementById('dropZone');
    const input = document.getElementById('imagesInput');
    const preview = document.getElementById('imagePreview');
    const primaryIndex = document.getElementById('primaryIndex');
    const keepIds = document.getElementById('keepImageIds');
    const deleteIds = document.getElementById('deleteImageIds');
    const existingImages = @json($listing->images->map(fn($i) => ['id' => $i->id, 'url' => asset('storage/' . $i->image_path), 'primary' => $i->is_primary]));
    let fileList = [];
    let keptIds = existingImages.map(i => i.id);
    let deletedIds = [];
    let primaryIdx = existingImages.findIndex(i => i.primary) >= 0 ? existingImages.findIndex(i => i.primary) : 0;

    function getTotalCount() { return keptIds.length + fileList.length; }
    function renderPreview() {
        preview.innerHTML = '';
        const items = [];
        keptIds.forEach((id, idx) => {
            const ex = existingImages.find(i => i.id === id);
            if (!ex) return;
            items.push({ type: 'existing', id, url: ex.url, idx: items.length });
        });
        fileList.forEach((file, idx) => {
            items.push({ type: 'file', file, idx: items.length });
        });
        primaryIndex.value = primaryIdx;
        keepIds.value = keptIds.join(',');
        deleteIds.value = deletedIds.join(',');

        items.forEach((item, idx) => {
            const div = document.createElement('div');
            div.className = 'auction-image-item';
            div.dataset.index = idx;
            div.draggable = true;
            let url, isPrimary = idx === primaryIdx;
            if (item.type === 'existing') {
                url = item.url;
                div.innerHTML = '<span class="primary-badge" style="' + (isPrimary ? '' : 'display:none') + '">Thumbnail</span>' +
                    '<img src="' + url.replace(/'/g, "\\'") + '" alt="">' +
                    '<div class="actions">' +
                    '<button type="button" onclick="event.preventDefault();setPrimary(' + idx + ')" title="Set as thumbnail"><i class="bi bi-star-fill"></i></button>' +
                    '<button type="button" onclick="event.preventDefault();viewImage(this.closest(\'.auction-image-item\').querySelector(\'img\').src)" title="View"><i class="bi bi-eye"></i></button>' +
                    '<button type="button" onclick="event.preventDefault();removeExisting(' + item.id + ')" title="Delete"><i class="bi bi-trash"></i></button>' +
                    '</div>';
            } else {
                url = URL.createObjectURL(item.file);
                const urlSafe = url.replace(/'/g, "\\'");
                const fileIdx = items.filter(i=>i.type==='file').indexOf(item);
                div.innerHTML = '<span class="primary-badge" style="' + (isPrimary ? '' : 'display:none') + '">Thumbnail</span>' +
                    '<img src="' + urlSafe + '" alt="">' +
                    '<div class="actions">' +
                    '<button type="button" onclick="event.preventDefault();setPrimary(' + idx + ')" title="Set as thumbnail"><i class="bi bi-star-fill"></i></button>' +
                    '<button type="button" onclick="event.preventDefault();viewImage(this.closest(\'.auction-image-item\').querySelector(\'img\').src)" title="View"><i class="bi bi-eye"></i></button>' +
                    '<button type="button" onclick="event.preventDefault();removeFile(' + fileIdx + ')" title="Delete"><i class="bi bi-trash"></i></button>' +
                    '</div>';
            }
            div.addEventListener('dragstart', (e) => { e.dataTransfer.setData('text/plain', idx); div.classList.add('dragging'); });
            div.addEventListener('dragend', () => div.classList.remove('dragging'));
            preview.appendChild(div);
        });
        syncInput();
    }

    window.setPrimary = function(idx) { primaryIdx = idx; primaryIndex.value = idx; renderPreview(); };
    window.viewImage = function(url) {
        document.getElementById('viewImageSrc').src = url;
        new bootstrap.Modal(document.getElementById('viewImageModal')).show();
    };
    window.removeExisting = function(id) {
        keptIds = keptIds.filter(x => x !== id);
        deletedIds.push(id);
        if (primaryIdx >= keptIds.length + fileList.length) primaryIdx = Math.max(0, keptIds.length + fileList.length - 2);
        renderPreview();
    };
    window.removeFile = function(fileIdx) {
        const arr = fileList;
        fileList = arr.filter((_, i) => i !== fileIdx);
        if (primaryIdx >= keptIds.length + fileList.length) primaryIdx = Math.max(0, keptIds.length + fileList.length - 1);
        renderPreview();
    };

    function syncInput() {
        const dt = new DataTransfer();
        fileList.forEach(f => dt.items.add(f));
        input.files = dt.files;
    }
    function addFiles(files) {
        const toAdd = Array.from(files).filter(f => f.type.startsWith('image/'));
        const remaining = MAX_IMAGES - getTotalCount();
        fileList = fileList.concat(toAdd.slice(0, remaining));
        if (getTotalCount() > MAX_IMAGES) {
            const excess = getTotalCount() - MAX_IMAGES;
            fileList = fileList.slice(0, fileList.length - excess);
        }
        renderPreview();
    }
    input.addEventListener('change', function() { addFiles(this.files); this.value = ''; });
    dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('dragover'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
    dropZone.addEventListener('drop', (e) => { e.preventDefault(); dropZone.classList.remove('dragover'); addFiles(e.dataTransfer.files); });

    document.getElementById('auction-form').addEventListener('submit', function() {
        if (getTotalCount() === 0) { alert('Please keep or add at least one image.'); return false; }
        syncInput();
        keepIds.value = keptIds.join(',');
        deleteIds.value = deletedIds.join(',');
    });
    document.querySelector('select[name="category_ids[]"]').addEventListener('change', function() {
        if (this.selectedOptions.length > 3) Array.from(this.selectedOptions).slice(3).forEach(o => o.selected = false);
    });

    renderPreview();
})();
</script>
@endpush
