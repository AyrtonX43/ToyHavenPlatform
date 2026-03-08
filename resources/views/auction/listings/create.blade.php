@extends('layouts.toyshop')

@section('title', 'Add Auction Listing - ToyHaven')

@push('styles')
<style>
.auction-image-zone {
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.2s;
}
.auction-image-zone:hover, .auction-image-zone.dragover {
    border-color: #0ea5e9;
    background: #f0f9ff;
}
.auction-image-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 1rem;
}
.auction-image-item {
    position: relative;
    width: 90px;
    height: 90px;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid #e2e8f0;
}
.auction-image-item img { width: 100%; height: 100%; object-fit: cover; }
.auction-image-item .primary-badge {
    position: absolute; top: 4px; left: 4px;
    background: #0ea5e9; color: white;
    font-size: 0.65rem; padding: 2px 6px; border-radius: 4px;
}
.auction-image-item .actions {
    position: absolute; bottom: 0; left: 0; right: 0;
    display: flex; justify-content: center; gap: 4px;
    background: rgba(0,0,0,0.6); padding: 4px;
}
.auction-image-item .actions button {
    background: transparent; border: none; color: white;
    padding: 2px 6px; font-size: 0.75rem; cursor: pointer;
}
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
            <li class="breadcrumb-item active">Add Auction Listing</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add Auction Listing</h4>
                            <p class="mb-0 small opacity-90">Create a new auction for your item</p>
                        </div>
                        <a href="{{ route('auction.seller.dashboard') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('auction.listings.store') }}" method="POST" enctype="multipart/form-data" id="auction-form">
                        @csrf

                        <h6 class="text-uppercase text-muted mb-3">Item Details</h6>
                        <div class="mb-4">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title') }}" required maxlength="255" placeholder="e.g. Vintage LEGO Star Wars Set">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4"
                                maxlength="5000" placeholder="Describe your item, condition, and any details bidders should know.">{{ old('description') }}</textarea>
                            <small class="text-muted">Max 5000 characters</small>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Item Condition <span class="text-danger">*</span></label>
                            <select name="condition" class="form-select @error('condition') is-invalid @enderror" required>
                                @foreach(\App\Models\Auction::CONDITIONS as $value => $label)
                                    <option value="{{ $value }}" {{ old('condition', 'good') === $value ? 'selected' : '' }}>{{ $label }}</option>
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
                            @error('images')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @error('images.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <h6 class="text-uppercase text-muted mb-3 mt-4">Pricing</h6>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Starting Bid (₱) <span class="text-danger">*</span></label>
                                <input type="number" name="starting_bid" class="form-control @error('starting_bid') is-invalid @enderror"
                                    value="{{ old('starting_bid') }}" required min="1" step="0.01" placeholder="0.00">
                                @error('starting_bid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Reserve Price (₱) <small class="text-muted">Optional, hidden from bidders</small></label>
                                <input type="number" name="reserve_price" class="form-control @error('reserve_price') is-invalid @enderror"
                                    value="{{ old('reserve_price') }}" min="0" step="0.01" placeholder="Leave empty for no reserve">
                                @error('reserve_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Bid Increment (₱) <span class="text-danger">*</span></label>
                                <input type="number" name="bid_increment" class="form-control @error('bid_increment') is-invalid @enderror"
                                    value="{{ old('bid_increment', 10) }}" required min="1" step="0.01" placeholder="10">
                                @error('bid_increment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Auction End Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="scheduled_end_at" class="form-control @error('scheduled_end_at') is-invalid @enderror"
                                    value="{{ old('scheduled_end_at') }}" required min="{{ now()->addHour()->format('Y-m-d\TH:i') }}">
                                <small class="text-muted">When you want the auction to end (after approval)</small>
                                @error('scheduled_end_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <h6 class="text-uppercase text-muted mb-3 mt-4">Categories</h6>
                        <div class="mb-4">
                            <label class="form-label">Categories <small class="text-muted">Select up to 3</small></label>
                            <select name="category_ids[]" class="form-select @error('category_ids') is-invalid @enderror" multiple size="6">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ in_array($cat->id, old('category_ids', [])) ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple. Max 3.</small>
                            @error('category_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @error('category_ids.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="alert alert-secondary">
                            <small><i class="bi bi-info-circle me-1"></i>Your listing will be saved as a draft. You can edit and submit it for approval later.</small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Create Draft Listing
                        </button>
                        <a href="{{ route('auction.seller.dashboard') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
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
    let fileList = [];

    function getMinDatetime() {
        const d = new Date();
        d.setHours(d.getHours() + 1);
        d.setMinutes(0, 0, 0);
        return d.toISOString().slice(0, 16);
    }
    const dt = document.querySelector('input[name="scheduled_end_at"]');
    if (dt && !dt.value) dt.min = getMinDatetime();

    function renderPreview() {
        preview.innerHTML = '';
        const files = Array.from(fileList);
        files.forEach((file, idx) => {
            const url = URL.createObjectURL(file);
            const div = document.createElement('div');
            div.className = 'auction-image-item';
            div.dataset.index = idx;
            div.draggable = true;
            div.innerHTML = '<span class="primary-badge" style="' + (idx === parseInt(primaryIndex.value) ? '' : 'display:none') + '">Thumbnail</span>' +
                '<img src="' + url + '" alt="">' +
                '<div class="actions">' +
                '<button type="button" onclick="event.preventDefault();setPrimary(' + idx + ')" title="Set as thumbnail"><i class="bi bi-star-fill"></i></button>' +
                '<button type="button" onclick="event.preventDefault();viewImage(this.closest(\'.auction-image-item\').querySelector(\'img\').src)" title="View"><i class="bi bi-eye"></i></button>' +
                '<button type="button" onclick="event.preventDefault();removeImage(' + idx + ')" title="Delete"><i class="bi bi-trash"></i></button>' +
                '</div>';
            div.addEventListener('dragstart', (e) => { e.dataTransfer.setData('text/plain', idx); div.classList.add('dragging'); });
            div.addEventListener('dragend', () => div.classList.remove('dragging'));
            div.addEventListener('dragover', (e) => e.preventDefault());
            div.addEventListener('drop', (e) => {
                e.preventDefault();
                const from = parseInt(e.dataTransfer.getData('text/plain'));
                if (from === idx) return;
                const arr = [...fileList];
                const [removed] = arr.splice(from, 1);
                arr.splice(idx, 0, removed);
                fileList = arr;
                let newPrimary = parseInt(primaryIndex.value);
                if (newPrimary === from) newPrimary = idx;
                else if (from < newPrimary && idx >= newPrimary) newPrimary--;
                else if (from > newPrimary && idx <= newPrimary) newPrimary++;
                primaryIndex.value = newPrimary;
                renderPreview();
                syncInput();
            });
            preview.appendChild(div);
        });
        syncInput();
    }

    function setPrimary(idx) {
        primaryIndex.value = idx;
        renderPreview();
    }
    window.setPrimary = setPrimary;

    function viewImage(url) {
        document.getElementById('viewImageSrc').src = url;
        new bootstrap.Modal(document.getElementById('viewImageModal')).show();
    }
    window.viewImage = viewImage;

    function removeImage(idx) {
        fileList.splice(idx, 1);
        let p = parseInt(primaryIndex.value);
        if (idx < p) primaryIndex.value = p - 1;
        else if (idx === p) primaryIndex.value = Math.min(0, fileList.length - 1);
        renderPreview();
        syncInput();
    }
    window.removeImage = removeImage;

    function syncInput() {
        const dt = new DataTransfer();
        fileList.forEach(f => dt.items.add(f));
        input.files = dt.files;
    }

    function addFiles(files) {
        const toAdd = Array.from(files).filter(f => f.type.startsWith('image/'));
        const remaining = MAX_IMAGES - fileList.length;
        const added = toAdd.slice(0, remaining);
        fileList = fileList.concat(added);
        if (fileList.length > MAX_IMAGES) fileList = fileList.slice(0, MAX_IMAGES);
        renderPreview();
        syncInput();
    }

    input.addEventListener('change', function() {
        addFiles(this.files);
        this.value = '';
    });

    dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('dragover'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        addFiles(e.dataTransfer.files);
    });

    document.getElementById('auction-form').addEventListener('submit', function() {
        if (fileList.length === 0) {
            alert('Please add at least one image.');
            return false;
        }
        syncInput();
        primaryIndex.value = Math.min(parseInt(primaryIndex.value), fileList.length - 1);
    });

    document.querySelector('select[name="category_ids[]"]').addEventListener('change', function() {
        const opts = Array.from(this.selectedOptions);
        if (opts.length > 3) {
            opts.slice(3).forEach(o => o.selected = false);
        }
    });
})();
</script>
@endpush
