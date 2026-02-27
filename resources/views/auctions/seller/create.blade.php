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
                                <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <div class="text-danger small">{{ $message }}</div> @enderror
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
                                <input type="file" name="images[]" class="form-control" accept="image/jpeg,image/png,image/webp" multiple required>
                                <div class="form-text">Upload 1-20 photos. First photo will be the primary image.</div>
                                @error('images') <div class="text-danger small">{{ $message }}</div> @enderror
                                @error('images.*') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">360° Photos <span class="text-muted small">(optional, up to 36 photos)</span></label>
                                <input type="file" name="photo_360[]" class="form-control" accept="image/jpeg,image/png,image/webp" multiple>
                                <div class="form-text">Upload multiple angle photos for a 360° view of your product.</div>
                                @error('photo_360') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Verification Video <span class="text-muted small">(10 sec, holding the item)</span></label>
                                <input type="file" name="verification_video" class="form-control" accept="video/mp4,video/webm">
                                <div class="form-text">Record a short video (max 10 seconds, max 50MB) showing yourself holding the product.</div>
                                @error('verification_video') <div class="text-danger small">{{ $message }}</div> @enderror
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
                            <div class="col-md-4" id="endAtGroup">
                                <label class="form-label fw-semibold">End Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="end_at" class="form-control" value="{{ old('end_at') }}">
                                @error('end_at') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4" id="startAtGroup">
                                <label class="form-label fw-semibold">Start Date & Time <span class="text-muted small">(optional)</span></label>
                                <input type="datetime-local" name="start_at" class="form-control" value="{{ old('start_at') }}">
                                @error('start_at') <div class="text-danger small">{{ $message }}</div> @enderror
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('auctionType');
    const endAtGroup = document.getElementById('endAtGroup');
    const durationGroup = document.getElementById('durationGroup');

    function toggleFields() {
        if (typeSelect.value === 'live_event') {
            durationGroup.style.display = '';
            endAtGroup.querySelector('input').removeAttribute('required');
        } else {
            durationGroup.style.display = 'none';
            endAtGroup.querySelector('input').setAttribute('required', 'required');
        }
    }

    typeSelect.addEventListener('change', toggleFields);
    toggleFields();
});
</script>
@endpush
@endsection
