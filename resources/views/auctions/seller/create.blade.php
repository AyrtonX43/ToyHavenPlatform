@extends('layouts.toyshop')

@section('title', 'Create Auction Listing - ToyHaven')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auctions.index') }}">Auctions</a></li>
            <li class="breadcrumb-item"><a href="{{ route('auctions.seller.index') }}">My Listings</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white p-4 border-bottom">
                    <h3 class="mb-1 fw-bold"><i class="bi bi-hammer text-primary me-2"></i>Create Auction Listing</h3>
                    <p class="text-muted mb-0">Fill in the details about your item to list it for auction</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('auctions.seller.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Step 1: Product Info --}}
                        <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-1-circle me-1"></i> Product Information</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                                @error('title') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Categories <span class="text-danger">*</span></label>
                                <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                                    @foreach($categories as $cat)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="category_ids[]" value="{{ $cat->id }}" id="cat_{{ $cat->id }}"
                                                {{ in_array($cat->id, old('category_ids', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="cat_{{ $cat->id }}">{{ $cat->name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">Select one or more categories</small>
                                @error('category_ids') <div class="text-danger small">{{ $message }}</div> @enderror
                                @error('category_ids.*') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="4" required>{{ old('description') }}</textarea>
                                @error('description') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Step 2: Condition & Authenticity --}}
                        <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-2-circle me-1"></i> Condition & Authenticity</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Box Condition <span class="text-danger">*</span></label>
                                <select name="box_condition" class="form-select" required>
                                    <option value="">Select Condition</option>
                                    <option value="sealed" {{ old('box_condition') === 'sealed' ? 'selected' : '' }}>Sealed / Mint in Box</option>
                                    <option value="opened_complete" {{ old('box_condition') === 'opened_complete' ? 'selected' : '' }}>Opened - Complete</option>
                                    <option value="opened_incomplete" {{ old('box_condition') === 'opened_incomplete' ? 'selected' : '' }}>Opened - Incomplete</option>
                                    <option value="no_box" {{ old('box_condition') === 'no_box' ? 'selected' : '' }}>No Box / Loose</option>
                                </select>
                                @error('box_condition') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Provenance <span class="text-muted small">(origin/history)</span></label>
                                <input type="text" name="provenance" class="form-control" value="{{ old('provenance') }}" placeholder="e.g. Original owner since 1995">
                                @error('provenance') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Authenticity Marks</label>
                                <textarea name="authenticity_marks" class="form-control" rows="2" placeholder="Describe logos, stamps, serial numbers...">{{ old('authenticity_marks') }}</textarea>
                                @error('authenticity_marks') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Known Defects</label>
                                <textarea name="known_defects" class="form-control" rows="2" placeholder="Any scratches, missing parts, discoloration...">{{ old('known_defects') }}</textarea>
                                @error('known_defects') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Step 3: Media --}}
                        <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-3-circle me-1"></i> Photos & Video</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Product Photos <span class="text-danger">*</span></label>
                                <input type="file" name="images[]" id="imageInput" class="form-control" accept="image/jpeg,image/png,image/webp" multiple required>
                                <div class="form-text">Upload 1-20 photos. First photo will be the primary image. Click a thumbnail to view full screen.</div>
                                @error('images') <div class="text-danger small">{{ $message }}</div> @enderror
                                @error('images.*') <div class="text-danger small">{{ $message }}</div> @enderror
                                <div id="imagePreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">360° Photos <span class="text-muted small">(optional, up to 36 photos)</span></label>
                                <input type="file" name="photo_360[]" id="photo360Input" class="form-control" accept="image/jpeg,image/png,image/webp" multiple>
                                <div class="form-text">Upload multiple angle photos for a 360° view of your product.</div>
                                @error('photo_360') <div class="text-danger small">{{ $message }}</div> @enderror
                                <div id="photo360Preview" class="d-flex flex-wrap gap-2 mt-2"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Verification Video <span class="text-muted small">(10 sec, holding the item)</span></label>
                                <input type="file" name="verification_video" id="videoInput" class="form-control" accept="video/mp4,video/webm">
                                <div class="form-text">Record a short video (max 10 seconds, max 50MB) showing yourself holding the product.</div>
                                @error('verification_video') <div class="text-danger small">{{ $message }}</div> @enderror
                                <div id="videoPreview" class="mt-2"></div>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Step 4: Auction Settings --}}
                        <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-4-circle me-1"></i> Auction Settings</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Starting Price (₱) <span class="text-danger">*</span></label>
                                <input type="number" name="starting_bid" class="form-control" step="0.01" min="1" value="{{ old('starting_bid') }}" required>
                                @error('starting_bid') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Reserve Price (₱) <span class="text-muted small">(optional)</span></label>
                                <input type="number" name="reserve_price" class="form-control" step="0.01" min="0" value="{{ old('reserve_price') }}">
                                <div class="form-text">Minimum price you'll accept. Hidden from bidders.</div>
                                @error('reserve_price') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Bid Increment (₱) <span class="text-danger">*</span></label>
                                <input type="number" name="bid_increment" class="form-control" step="0.01" min="1" value="{{ old('bid_increment', 10) }}" required>
                                @error('bid_increment') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Auction Type <span class="text-danger">*</span></label>
                                <select name="auction_type" class="form-select" id="auctionType" required>
                                    <option value="timed" {{ old('auction_type', 'timed') === 'timed' ? 'selected' : '' }}>Timed Auction</option>
                                    <option value="live_event" {{ old('auction_type') === 'live_event' ? 'selected' : '' }}>Live Event</option>
                                </select>
                                @error('auction_type') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4" id="startAtGroup">
                                <label class="form-label fw-semibold">Start Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="start_at" id="startAt" class="form-control"
                                       value="{{ old('start_at') }}"
                                       min="{{ now()->format('Y-m-d\TH:i') }}"
                                       max="{{ now()->addDays(5)->endOfDay()->format('Y-m-d\TH:i') }}" required>
                                <small class="text-muted">Select from now up to 5 days ahead</small>
                                @error('start_at') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4" id="endAtGroup">
                                <label class="form-label fw-semibold">End Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="end_at" id="endAt" class="form-control"
                                       value="{{ old('end_at') }}" required>
                                <small class="text-muted" id="endAtHint">Must be 1–2 days after start</small>
                                @error('end_at') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4" id="durationGroup" style="display: none;">
                                <label class="form-label fw-semibold">Duration (minutes) <span class="text-danger">*</span></label>
                                <input type="number" name="duration_minutes" class="form-control" min="5" max="480" value="{{ old('duration_minutes', 30) }}">
                                @error('duration_minutes') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Who Can Bid?</label>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach(['all' => 'All Members', 'basic' => 'Basic', 'pro' => 'Pro', 'vip' => 'VIP'] as $val => $label)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="allowed_bidder_plans[]" value="{{ $val }}" id="plan_{{ $val }}"
                                                {{ in_array($val, old('allowed_bidder_plans', ['all'])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="plan_{{ $val }}">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('allowed_bidder_plans') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('auctions.seller.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-send me-1"></i>Submit for Approval
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .media-thumb {
        width: 90px;
        height: 90px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #e2e8f0;
        cursor: pointer;
        transition: border-color 0.2s, transform 0.2s;
    }
    .media-thumb:hover {
        border-color: #0891b2;
        transform: scale(1.05);
    }
    .media-thumb-wrap {
        position: relative;
        display: inline-block;
    }
    .media-thumb-wrap .thumb-badge {
        position: absolute;
        top: 4px;
        left: 4px;
        font-size: 0.65rem;
        padding: 1px 5px;
    }
    .media-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100vw; height: 100vh;
        background: rgba(0,0,0,0.95);
        z-index: 99999;
        align-items: center;
        justify-content: center;
        cursor: zoom-out;
    }
    .media-overlay.active { display: flex; }
    .media-overlay img,
    .media-overlay video {
        max-width: 92vw;
        max-height: 92vh;
        object-fit: contain;
        border-radius: 6px;
        box-shadow: 0 0 40px rgba(0,0,0,0.5);
    }
    .media-overlay .overlay-close {
        position: fixed;
        top: 18px; right: 24px;
        z-index: 100000;
        background: rgba(255,255,255,0.15);
        border: none; color: #fff;
        font-size: 2rem;
        width: 48px; height: 48px;
        border-radius: 50%;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        backdrop-filter: blur(4px);
        transition: background 0.2s;
    }
    .media-overlay .overlay-close:hover { background: rgba(255,255,255,0.3); }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Media preview & fullscreen ---
    var overlay = document.createElement('div');
    overlay.className = 'media-overlay';
    overlay.innerHTML = '<button class="overlay-close" title="Close">&times;</button><div id="overlayContent"></div>';
    document.body.appendChild(overlay);

    var overlayContent = document.getElementById('overlayContent');

    function openOverlay(html) {
        overlayContent.innerHTML = html;
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeOverlay() {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
        overlayContent.innerHTML = '';
    }
    overlay.querySelector('.overlay-close').addEventListener('click', function(e) { e.stopPropagation(); closeOverlay(); });
    overlay.addEventListener('click', function(e) { if (e.target === overlay) closeOverlay(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeOverlay(); });

    function previewImages(input, container) {
        input.addEventListener('change', function() {
            container.innerHTML = '';
            if (!this.files.length) return;
            Array.from(this.files).forEach(function(file, i) {
                if (!file.type.startsWith('image/')) return;
                var reader = new FileReader();
                reader.onload = function(e) {
                    var wrap = document.createElement('div');
                    wrap.className = 'media-thumb-wrap';
                    var img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'media-thumb';
                    img.title = 'Click to view full screen';
                    img.addEventListener('click', function() {
                        openOverlay('<img src="' + this.src + '" alt="Preview">');
                    });
                    wrap.appendChild(img);
                    if (i === 0) {
                        var badge = document.createElement('span');
                        badge.className = 'badge bg-primary thumb-badge';
                        badge.textContent = 'Primary';
                        wrap.appendChild(badge);
                    }
                    container.appendChild(wrap);
                };
                reader.readAsDataURL(file);
            });
        });
    }

    function previewVideo(input, container) {
        input.addEventListener('change', function() {
            container.innerHTML = '';
            if (!this.files.length) return;
            var file = this.files[0];
            if (!file.type.startsWith('video/')) return;
            var url = URL.createObjectURL(file);
            var video = document.createElement('video');
            video.src = url;
            video.controls = true;
            video.style.maxHeight = '200px';
            video.style.maxWidth = '100%';
            video.style.cursor = 'pointer';
            video.className = 'rounded';
            video.title = 'Click to view full screen';
            video.addEventListener('click', function(e) {
                e.preventDefault();
                openOverlay('<video src="' + url + '" controls autoplay style="max-width:92vw;max-height:92vh;border-radius:6px;box-shadow:0 0 40px rgba(0,0,0,0.5);"></video>');
            });
            container.appendChild(video);
        });
    }

    var imageInput = document.getElementById('imageInput');
    var imagePreview = document.getElementById('imagePreview');
    if (imageInput && imagePreview) previewImages(imageInput, imagePreview);

    var photo360Input = document.getElementById('photo360Input');
    var photo360Preview = document.getElementById('photo360Preview');
    if (photo360Input && photo360Preview) previewImages(photo360Input, photo360Preview);

    var videoInput = document.getElementById('videoInput');
    var videoPreview = document.getElementById('videoPreview');
    if (videoInput && videoPreview) previewVideo(videoInput, videoPreview);

    document.querySelectorAll('.fullscreen-img').forEach(function(img) {
        img.addEventListener('click', function() {
            openOverlay('<img src="' + this.src + '" alt="Full View">');
        });
    });
    document.querySelectorAll('.fullscreen-video').forEach(function(vid) {
        vid.addEventListener('click', function(e) {
            e.preventDefault();
            openOverlay('<video src="' + this.src + '" controls autoplay style="max-width:92vw;max-height:92vh;border-radius:6px;box-shadow:0 0 40px rgba(0,0,0,0.5);"></video>');
        });
    });

    // --- Auction date/type logic ---
    var typeSelect = document.getElementById('auctionType');
    var endAtGroup = document.getElementById('endAtGroup');
    var durationGroup = document.getElementById('durationGroup');
    var startAt = document.getElementById('startAt');
    var endAt = document.getElementById('endAt');

    function pad(n) { return n < 10 ? '0' + n : n; }
    function toLocal(d) {
        return d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    function updateStartMin() {
        var now = new Date();
        startAt.min = toLocal(now);
        var maxStart = new Date(now.getTime() + 5 * 24 * 60 * 60 * 1000);
        startAt.max = toLocal(maxStart);

        if (startAt.value && new Date(startAt.value) < now) {
            startAt.value = toLocal(now);
            updateEndLimits();
        }
    }

    function updateEndLimits() {
        if (!startAt.value) {
            endAt.min = '';
            endAt.max = '';
            document.getElementById('endAtHint').textContent = 'Select start date first';
            return;
        }
        var start = new Date(startAt.value);
        var minEnd = new Date(start.getTime() + 24 * 60 * 60 * 1000);
        var maxEnd = new Date(start.getTime() + 2 * 24 * 60 * 60 * 1000);
        endAt.min = toLocal(minEnd);
        endAt.max = toLocal(maxEnd);
        document.getElementById('endAtHint').textContent = 'Between ' + minEnd.toLocaleDateString() + ' and ' + maxEnd.toLocaleDateString();

        if (endAt.value && new Date(endAt.value) < minEnd) {
            endAt.value = toLocal(minEnd);
        }
        if (endAt.value && new Date(endAt.value) > maxEnd) {
            endAt.value = toLocal(maxEnd);
        }
    }

    updateStartMin();
    setInterval(updateStartMin, 30000);

    startAt.addEventListener('focus', updateStartMin);
    startAt.addEventListener('change', function() {
        updateStartMin();
        updateEndLimits();
    });
    updateEndLimits();

    function toggleFields() {
        if (typeSelect.value === 'live_event') {
            durationGroup.style.display = '';
            endAtGroup.style.display = 'none';
            endAt.removeAttribute('required');
        } else {
            durationGroup.style.display = 'none';
            endAtGroup.style.display = '';
            endAt.setAttribute('required', 'required');
        }
    }

    typeSelect.addEventListener('change', toggleFields);
    toggleFields();
});
</script>
@endpush
@endsection
