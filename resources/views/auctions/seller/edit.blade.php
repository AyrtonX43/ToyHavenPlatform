@extends('layouts.toyshop')

@section('title', 'Edit Auction - ' . $auction->title)

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auctions.index') }}">Auctions</a></li>
            <li class="breadcrumb-item"><a href="{{ route('auctions.seller.index') }}">My Listings</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white p-4 border-bottom">
                    <h3 class="mb-1 fw-bold"><i class="bi bi-pencil text-primary me-2"></i>Edit Auction Listing</h3>
                    <p class="text-muted mb-0">Update your auction details and resubmit for approval</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('auctions.seller.update', $auction) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-1-circle me-1"></i> Product Information</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="{{ old('title', $auction->title) }}" required>
                                @error('title') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Categories <span class="text-danger">*</span></label>
                                @php $selectedCats = old('category_ids', $auction->categories->pluck('id')->toArray()); @endphp
                                <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                                    @foreach($categories as $cat)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="category_ids[]" value="{{ $cat->id }}" id="cat_{{ $cat->id }}"
                                                {{ in_array($cat->id, $selectedCats) ? 'checked' : '' }}>
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
                                <textarea name="description" class="form-control" rows="4" required>{{ old('description', $auction->description) }}</textarea>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-2-circle me-1"></i> Condition & Authenticity</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Box Condition <span class="text-danger">*</span></label>
                                <select name="box_condition" class="form-select" required>
                                    @foreach(['sealed' => 'Sealed / Mint in Box', 'opened_complete' => 'Opened - Complete', 'opened_incomplete' => 'Opened - Incomplete', 'no_box' => 'No Box / Loose'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('box_condition', $auction->box_condition) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Provenance</label>
                                <input type="text" name="provenance" class="form-control" value="{{ old('provenance', $auction->provenance) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Authenticity Marks</label>
                                <textarea name="authenticity_marks" class="form-control" rows="2">{{ old('authenticity_marks', $auction->authenticity_marks) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Known Defects</label>
                                <textarea name="known_defects" class="form-control" rows="2">{{ old('known_defects', $auction->known_defects) }}</textarea>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-3-circle me-1"></i> Photos & Video</h5>
                        @if($auction->images->where('image_type', 'standard')->count())
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Current Photos <small class="text-muted">(click image to view full screen)</small></label>
                                <div class="d-flex flex-wrap gap-2" id="currentPhotos">
                                    @foreach($auction->images->where('image_type', 'standard')->sortBy('display_order') as $img)
                                        <div class="existing-img-wrap" id="img-wrap-{{ $img->id }}">
                                            <img src="{{ asset('storage/' . $img->path) }}" class="rounded fullscreen-img existing-thumb" title="Click to view full screen">
                                            @if($img->display_order === 0)
                                                <span class="badge bg-primary thumb-badge">Primary</span>
                                            @else
                                                <form action="{{ route('auctions.seller.set-primary-image', [$auction, $img]) }}" method="POST" class="d-inline primary-form">
                                                    @csrf
                                                    <button type="submit" class="btn-set-primary" title="Set as primary"><i class="bi bi-star"></i></button>
                                                </form>
                                            @endif
                                            <form action="{{ route('auctions.seller.delete-image', [$auction, $img]) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-delete-img" title="Delete image" onclick="return confirm('Delete this image?')"><i class="bi bi-x-lg"></i></button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if($auction->verification_video_path)
                            <div class="mb-3" id="currentVideoWrap">
                                <label class="form-label fw-semibold">Current Video</label>
                                <div class="d-flex align-items-start gap-2">
                                    <video src="{{ asset('storage/' . $auction->verification_video_path) }}" controls class="rounded fullscreen-video" style="max-height: 200px; max-width: 100%; cursor: pointer;" title="Click to view full screen"></video>
                                    <form action="{{ route('auctions.seller.delete-video', $auction) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete video" onclick="return confirm('Delete this video? You can upload a new one below.')">
                                            <i class="bi bi-trash me-1"></i>Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Add More Photos</label>
                                <input type="file" name="new_images[]" id="imageInput" class="form-control" accept="image/jpeg,image/png,image/webp" multiple>
                                <div class="form-text">Click a thumbnail to view full screen.</div>
                                <div id="imagePreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">{{ $auction->verification_video_path ? 'Replace' : 'Upload' }} Verification Video</label>
                                <input type="file" name="verification_video" id="videoInput" class="form-control" accept="video/mp4,video/webm">
                                <div id="videoPreview" class="mt-2"></div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-4-circle me-1"></i> Auction Settings</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Starting Price (₱) <span class="text-danger">*</span></label>
                                <input type="number" name="starting_bid" class="form-control" step="0.01" min="1" value="{{ old('starting_bid', $auction->starting_bid) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Reserve Price (₱)</label>
                                <input type="number" name="reserve_price" class="form-control" step="0.01" min="0" value="{{ old('reserve_price', $auction->reserve_price) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Bid Increment (₱) <span class="text-danger">*</span></label>
                                <input type="number" name="bid_increment" class="form-control" step="0.01" min="1" value="{{ old('bid_increment', $auction->bid_increment) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Auction Type <span class="text-danger">*</span></label>
                                <select name="auction_type" class="form-select" id="auctionType" required>
                                    <option value="timed" {{ old('auction_type', $auction->auction_type) === 'timed' ? 'selected' : '' }}>Timed Auction</option>
                                    <option value="live_event" {{ old('auction_type', $auction->auction_type) === 'live_event' ? 'selected' : '' }}>Live Event</option>
                                </select>
                            </div>
                            <div class="col-md-4" id="startAtGroup">
                                <label class="form-label fw-semibold">Start Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="start_at" id="startAt" class="form-control"
                                       value="{{ old('start_at', $auction->start_at?->format('Y-m-d\TH:i')) }}"
                                       min="{{ now()->format('Y-m-d\TH:i') }}"
                                       max="{{ now()->addDays(5)->endOfDay()->format('Y-m-d\TH:i') }}" required>
                                <small class="text-muted">Select from now up to 5 days ahead</small>
                                @error('start_at') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4" id="endAtGroup">
                                <label class="form-label fw-semibold">End Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="end_at" id="endAt" class="form-control"
                                       value="{{ old('end_at', $auction->end_at?->format('Y-m-d\TH:i')) }}" required>
                                <small class="text-muted" id="endAtHint">Must be 1–2 days after start</small>
                                @error('end_at') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4" id="durationGroup" style="display: none;">
                                <label class="form-label fw-semibold">Duration (minutes)</label>
                                <input type="number" name="duration_minutes" class="form-control" min="5" max="480" value="{{ old('duration_minutes', $auction->duration_minutes ?? 30) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Who Can Bid?</label>
                                <div class="d-flex flex-wrap gap-3">
                                    @php $currentPlans = old('allowed_bidder_plans', $auction->allowed_bidder_plans ?? ['all']); @endphp
                                    @foreach(['all' => 'All Members', 'basic' => 'Basic', 'pro' => 'Pro', 'vip' => 'VIP'] as $val => $label)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="allowed_bidder_plans[]" value="{{ $val }}" id="plan_{{ $val }}"
                                                {{ in_array($val, $currentPlans) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="plan_{{ $val }}">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('auctions.seller.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-send me-1"></i>Update & Resubmit
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
    .existing-img-wrap {
        position: relative;
        display: inline-block;
    }
    .existing-thumb {
        width: 100px;
        height: 100px;
        object-fit: cover;
        cursor: pointer;
        border: 2px solid #e2e8f0;
        transition: border-color 0.2s, transform 0.2s;
    }
    .existing-thumb:hover {
        border-color: #0891b2;
        transform: scale(1.03);
    }
    .existing-img-wrap .thumb-badge {
        position: absolute;
        top: 4px;
        left: 4px;
        font-size: 0.65rem;
        padding: 1px 5px;
    }
    .btn-delete-img {
        position: absolute;
        top: 2px;
        right: 2px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: rgba(220,53,69,0.85);
        border: none;
        color: #fff;
        font-size: 0.7rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        transition: background 0.2s;
    }
    .btn-delete-img:hover { background: #dc3545; }
    .btn-set-primary {
        position: absolute;
        bottom: 2px;
        left: 2px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: rgba(13,110,253,0.75);
        border: none;
        color: #fff;
        font-size: 0.7rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        transition: background 0.2s;
    }
    .btn-set-primary:hover { background: #0d6efd; }
    .media-thumb-wrap .btn-remove-file {
        position: absolute;
        top: 2px;
        right: 2px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: rgba(220,53,69,0.85);
        border: none;
        color: #fff;
        font-size: 0.65rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        transition: background 0.2s;
    }
    .media-thumb-wrap .btn-remove-file:hover { background: #dc3545; }
    .video-preview-wrap {
        position: relative;
        display: inline-block;
    }
    .video-preview-wrap .btn-remove-video {
        position: absolute;
        top: 6px;
        right: 6px;
        z-index: 5;
        background: rgba(220,53,69,0.85);
        border: none;
        color: #fff;
        font-size: 0.75rem;
        padding: 3px 8px;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .video-preview-wrap .btn-remove-video:hover { background: #dc3545; }
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

    function rebuildFileList(input, filesArray) {
        var dt = new DataTransfer();
        filesArray.forEach(function(f) { dt.items.add(f); });
        input.files = dt.files;
    }

    function setupImagePreview(input, container) {
        var currentFiles = [];

        function renderPreviews() {
            container.innerHTML = '';
            currentFiles.forEach(function(file, i) {
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
                        openOverlay('<img src="' + img.src + '" alt="Preview">');
                    });
                    wrap.appendChild(img);

                    var removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn-remove-file';
                    removeBtn.title = 'Remove';
                    removeBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
                    removeBtn.addEventListener('click', function() {
                        currentFiles.splice(i, 1);
                        rebuildFileList(input, currentFiles);
                        renderPreviews();
                    });
                    wrap.appendChild(removeBtn);

                    container.appendChild(wrap);
                };
                reader.readAsDataURL(file);
            });
        }

        input.addEventListener('change', function() {
            currentFiles = Array.from(this.files);
            renderPreviews();
        });
    }

    function setupVideoPreview(input, container) {
        input.addEventListener('change', function() {
            container.innerHTML = '';
            if (!this.files.length) return;
            var file = this.files[0];
            if (!file.type.startsWith('video/')) return;
            var url = URL.createObjectURL(file);

            var wrap = document.createElement('div');
            wrap.className = 'video-preview-wrap';

            var video = document.createElement('video');
            video.src = url;
            video.controls = true;
            video.style.maxHeight = '200px';
            video.style.maxWidth = '100%';
            video.style.cursor = 'pointer';
            video.className = 'rounded';
            video.title = 'Click to view full screen';
            video.addEventListener('click', function(ev) {
                ev.preventDefault();
                openOverlay('<video src="' + url + '" controls autoplay style="max-width:92vw;max-height:92vh;border-radius:6px;box-shadow:0 0 40px rgba(0,0,0,0.5);"></video>');
            });
            wrap.appendChild(video);

            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn-remove-video';
            removeBtn.title = 'Remove video';
            removeBtn.innerHTML = '<i class="bi bi-x-lg me-1"></i>Remove';
            removeBtn.addEventListener('click', function() {
                var dt = new DataTransfer();
                input.files = dt.files;
                container.innerHTML = '';
            });
            wrap.appendChild(removeBtn);

            container.appendChild(wrap);
        });
    }

    var imageInput = document.getElementById('imageInput');
    var imagePreview = document.getElementById('imagePreview');
    if (imageInput && imagePreview) setupImagePreview(imageInput, imagePreview);

    var videoInput = document.getElementById('videoInput');
    var videoPreview = document.getElementById('videoPreview');
    if (videoInput && videoPreview) setupVideoPreview(videoInput, videoPreview);

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
