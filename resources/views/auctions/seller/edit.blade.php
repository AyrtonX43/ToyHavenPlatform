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
                                <label class="form-label fw-semibold">Current Photos</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($auction->images->where('image_type', 'standard') as $img)
                                        <img src="{{ asset('storage/' . $img->path) }}" class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Add More Photos</label>
                                <input type="file" name="new_images[]" class="form-control" accept="image/jpeg,image/png,image/webp" multiple>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Update Verification Video</label>
                                <input type="file" name="verification_video" class="form-control" accept="video/mp4,video/webm">
                                @if($auction->verification_video_path)
                                    <div class="form-text text-success"><i class="bi bi-check-circle me-1"></i>Video already uploaded</div>
                                @endif
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
                            @php
                                $editStartDays = '';
                                if ($auction->start_at) {
                                    $diff = (int) now()->startOfDay()->diffInDays($auction->start_at->startOfDay(), false);
                                    $editStartDays = max(0, min(5, $diff));
                                }
                                $editStartTime = $auction->start_at ? $auction->start_at->format('H:i') : '09:00';
                                $editEndTime = $auction->end_at ? $auction->end_at->format('H:i') : '21:00';
                            @endphp
                            <div class="col-md-4" id="startAtGroup">
                                <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                                <select name="start_days" id="startDays" class="form-select" required>
                                    <option value="">Select Start Date</option>
                                    <option value="0" {{ old('start_days', $editStartDays) === '0' || old('start_days', $editStartDays) === 0 ? 'selected' : '' }}>Start Now (Today)</option>
                                    @for($d = 1; $d <= 5; $d++)
                                        <option value="{{ $d }}" {{ old('start_days', $editStartDays) == $d ? 'selected' : '' }}>
                                            {{ now()->addDays($d)->format('l, M d, Y') }} ({{ $d }} {{ $d === 1 ? 'day' : 'days' }} from now)
                                        </option>
                                    @endfor
                                </select>
                                @error('start_days') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4" id="startTimeGroup" style="display: none;">
                                <label class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
                                <input type="time" name="start_time" id="startTime" class="form-control" value="{{ old('start_time', $editStartTime) }}">
                                <small class="text-muted" id="startTimeHint">Time the auction will begin</small>
                                @error('start_time') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4" id="endAtGroup">
                                <label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                                <select name="end_days" id="endDays" class="form-select" required>
                                    <option value="">Select Start Date first</option>
                                </select>
                                @error('end_days') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4" id="endTimeGroup" style="display: none;">
                                <label class="form-label fw-semibold">End Time <span class="text-danger">*</span></label>
                                <input type="time" name="end_time" id="endTime" class="form-control" value="{{ old('end_time', $editEndTime) }}">
                                <small class="text-muted">Time the auction will close</small>
                                @error('end_time') <div class="text-danger small">{{ $message }}</div> @enderror
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var typeSelect = document.getElementById('auctionType');
    var endAtGroup = document.getElementById('endAtGroup');
    var endTimeGroup = document.getElementById('endTimeGroup');
    var startTimeGroup = document.getElementById('startTimeGroup');
    var durationGroup = document.getElementById('durationGroup');
    var startDays = document.getElementById('startDays');
    var endDays = document.getElementById('endDays');

    @php
        $existingEndDays = '';
        if ($auction->start_at && $auction->end_at) {
            $existingEndDays = max(1, min(2, (int) $auction->start_at->startOfDay()->diffInDays($auction->end_at->startOfDay())));
        }
    @endphp
    var oldEndDays = '{{ old("end_days", $existingEndDays) }}';

    var dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    var monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    function formatDate(d) {
        return dayNames[d.getDay()] + ', ' + monthNames[d.getMonth()] + ' ' + String(d.getDate()).padStart(2,'0') + ', ' + d.getFullYear();
    }

    function updateEndDays() {
        var sv = startDays.value;
        endDays.innerHTML = '';
        if (sv === '' || sv === null) {
            endDays.innerHTML = '<option value="">Select Start Date first</option>';
            endTimeGroup.style.display = 'none';
            startTimeGroup.style.display = 'none';
            return;
        }
        sv = parseInt(sv);
        startTimeGroup.style.display = '';
        if (sv === 0) {
            document.getElementById('startTimeHint').textContent = 'Starts immediately when approved';
        } else {
            document.getElementById('startTimeHint').textContent = 'Time the auction will begin';
        }

        endDays.innerHTML = '<option value="">Select End Date</option>';
        for (var i = 1; i <= 2; i++) {
            var totalDays = sv + i;
            var d = new Date();
            d.setDate(d.getDate() + totalDays);
            var opt = document.createElement('option');
            opt.value = i;
            opt.textContent = formatDate(d) + ' (' + i + (i === 1 ? ' day' : ' days') + ' after start)';
            if (oldEndDays == i) opt.selected = true;
            endDays.appendChild(opt);
        }
        oldEndDays = '';
        updateEndTimeVisibility();
    }

    function updateEndTimeVisibility() {
        if (endDays.value && endDays.value !== '') {
            endTimeGroup.style.display = '';
        } else {
            endTimeGroup.style.display = 'none';
        }
    }

    endDays.addEventListener('change', updateEndTimeVisibility);
    startDays.addEventListener('change', updateEndDays);
    updateEndDays();

    function toggleFields() {
        if (typeSelect.value === 'live_event') {
            durationGroup.style.display = '';
            endAtGroup.style.display = 'none';
            endTimeGroup.style.display = 'none';
            endDays.removeAttribute('required');
        } else {
            durationGroup.style.display = 'none';
            endAtGroup.style.display = '';
            endDays.setAttribute('required', 'required');
            updateEndTimeVisibility();
        }
    }

    typeSelect.addEventListener('change', toggleFields);
    toggleFields();
});
</script>
@endpush
@endsection
