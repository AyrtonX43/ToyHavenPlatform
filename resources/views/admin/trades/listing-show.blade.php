@extends('layouts.admin-new')

@section('title', $listing->title . ' - Trade Listing')
@section('page-title', 'Trade Listing')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<style>
.listing-view-image-wrap {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 320px;
}
.listing-view-image-wrap img {
    max-width: 100%;
    max-height: 600px;
    width: auto;
    height: auto;
    object-fit: contain;
}
.listing-thumb {
    width: 64px;
    height: 64px;
    object-fit: contain;
    border: 2px solid #dee2e6;
    border-radius: 6px;
    cursor: pointer;
    padding: 4px;
    background: #fff;
}
.listing-thumb:hover, .listing-thumb.active {
    border-color: #0d6efd;
    box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
}
.info-label { color: #6c757d; font-size: 0.8125rem; font-weight: 600; }
.info-value { color: #212529; }
</style>
@endpush

@section('content')
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.trades.listings') }}">Trade Listings</a></li>
        <li class="breadcrumb-item active">{{ Str::limit($listing->title, 40) }}</li>
    </ol>
</nav>

<div class="row g-4">
    <!-- Images -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                @php
                    $listingImages = $listing->images;
                    $item = $listing->getItem();
                    $productImages = $item ? $item->images : collect();
                    $useListingImages = $listingImages->isNotEmpty();
                    $allImages = $useListingImages ? $listingImages : ($productImages->isNotEmpty() ? $productImages : collect());
                    $fallbackSrc = $listing->image_path ? asset('storage/' . $listing->image_path) : null;
                    $mainSrc = $useListingImages
                        ? asset('storage/' . $listingImages->first()->image_path)
                        : ($fallbackSrc ?? ($productImages->count() > 0 ? asset('storage/' . $productImages->first()->image_path) : null));
                    $mainPath = $useListingImages ? $listingImages->first()->image_path : ($listing->image_path ?? ($productImages->first()->image_path ?? null));
                @endphp
                @if($mainSrc)
                <div class="listing-view-image-wrap">
                    <img src="{{ $mainSrc }}" alt="{{ $listing->title }}" id="mainImage" class="img-fluid" style="cursor: pointer;" data-full-src="{{ $mainSrc }}" title="Click to view full size">
                </div>
                @if($allImages->count() > 1)
                <div class="d-flex gap-2 p-3 overflow-auto flex-wrap border-top" id="thumbRow">
                    @foreach($allImages as $idx => $img)
                    @php
                        $imgPath = $img->image_path ?? null;
                        $imgSrc = $imgPath ? asset('storage/' . $imgPath) : null;
                    @endphp
                    @if($imgSrc)
                    <img src="{{ $imgSrc }}" alt="" class="listing-thumb {{ $loop->first ? 'active' : '' }}" data-src="{{ $imgSrc }}" role="button" tabindex="0">
                    @endif
                    @endforeach
                </div>
                @endif
                <div class="px-3 pb-3">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnViewFullImage">
                        <i class="bi bi-arrows-fullscreen me-1"></i> View Full Image
                    </button>
                </div>
                @else
                <div class="listing-view-image-wrap">
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-image display-4 d-block mb-2"></i>
                        <span>No image available</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Details -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">{{ $listing->title }}</h5>
                @php
                    $statusClass = match($listing->status) {
                        'pending_approval' => 'bg-warning text-dark',
                        'active' => 'bg-success',
                        'rejected' => 'bg-danger',
                        'cancelled', 'expired' => 'bg-secondary',
                        default => 'bg-primary',
                    };
                @endphp
                <span class="badge {{ $statusClass }} fs-6">{{ $listing->getStatusLabel() }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="info-label mb-1">Type</div>
                        <div class="info-value">{{ str_replace('_', ' ', ucfirst($listing->trade_type)) }}</div>
                    </div>
                    @if($listing->category)
                    <div class="col-sm-6">
                        <div class="info-label mb-1">Category</div>
                        <div class="info-value">{{ $listing->category->name }}</div>
                    </div>
                    @endif
                    <div class="col-12">
                        <div class="info-label mb-1">User</div>
                        <div class="info-value">
                            <a href="{{ route('admin.users.show', $listing->user_id) }}">{{ $listing->user->name ?? '—' }}</a>
                            <small class="text-muted ms-1">({{ $listing->user->email ?? '' }})</small>
                        </div>
                    </div>
                    @php $item = $listing->getItem(); @endphp
                    @if($item)
                    <div class="col-sm-6">
                        <div class="info-label mb-1">Item</div>
                        <div class="info-value">
                            {{ $item->name }}
                            @if($item instanceof \App\Models\Product)
                            <span class="text-success fw-semibold">— ₱{{ number_format($item->price, 2) }}</span>
                            @elseif($item instanceof \App\Models\UserProduct && $item->estimated_value)
                            <span class="text-success fw-semibold">— ₱{{ number_format($item->estimated_value, 2) }}</span>
                            @endif
                        </div>
                    </div>
                    @endif
                    @if($listing->cash_difference)
                    <div class="col-sm-6">
                        <div class="info-label mb-1">Cash Difference</div>
                        <div class="info-value fw-semibold">₱{{ number_format($listing->cash_difference, 2) }}</div>
                    </div>
                    @endif
                    @if($listing->condition)
                    <div class="col-sm-6">
                        <div class="info-label mb-1">Condition</div>
                        <div class="info-value">{{ ucfirst($listing->condition) }}</div>
                    </div>
                    @endif
                    <div class="col-12">
                        <div class="d-flex gap-4 text-muted small">
                            <span><i class="bi bi-eye me-1"></i>{{ $listing->views_count }} views</span>
                            <span><i class="bi bi-hand-thumbs-up me-1"></i>{{ $listing->offers_count }} offers</span>
                            <span><i class="bi bi-calendar3 me-1"></i>{{ $listing->created_at->format('M d, Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <hr>

                <h6 class="fw-bold mb-2">Description</h6>
                <p class="text-muted mb-0">{{ $listing->description ?: 'No description provided.' }}</p>

                @if(is_array($listing->desired_items) && count($listing->desired_items) > 0)
                <h6 class="fw-bold mt-4 mb-2">Desired Items</h6>
                <ul class="mb-0 text-muted">
                    @foreach($listing->desired_items as $d)
                    <li>{{ is_string($d) ? $d : $d }}</li>
                    @endforeach
                </ul>
                @endif

                @if($listing->location || $listing->meet_up_references)
                <hr>
                <h6 class="fw-bold mb-2">Meet-up Details</h6>
                @if($listing->location)
                <div class="mb-2">
                    <div class="info-label mb-1">Location</div>
                    <div class="info-value"><i class="bi bi-geo-alt me-1 text-muted"></i>{{ $listing->location }}</div>
                </div>
                @endif
                @if($listing->meet_up_references)
                <div>
                    <div class="info-label mb-1">Preferred Meet-up / References</div>
                    <div class="info-value text-muted">{{ $listing->meet_up_references }}</div>
                </div>
                @endif
                @endif

                @if($listing->status === 'pending_approval')
                <hr>
                <div class="d-flex gap-2 pt-2">
                    <form action="{{ route('admin.trades.approve-listing', $listing->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i> Approve Listing
                        </button>
                    </form>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal" data-reject-url="{{ route('admin.trades.reject-listing', $listing->id) }}" data-listing-title="{{ e($listing->title) }}">
                        <i class="bi bi-x-circle me-1"></i> Reject
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($listing->location)
<div class="card shadow-sm border-0 mt-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold"><i class="bi bi-map me-2"></i>Meet-up Location Map</h5>
    </div>
    <div class="card-body p-0">
        <div id="admin-listing-map" style="height: 280px; width: 100%;"></div>
        <div class="p-3 border-top">
            <small class="text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $listing->location }}</small>
        </div>
    </div>
</div>
@endif

@if($listing->activeOffers && $listing->activeOffers->count() > 0)
<div class="card shadow-sm border-0 mt-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold"><i class="bi bi-hand-thumbs-up me-2"></i>Active Offers ({{ $listing->activeOffers->count() }})</h5>
    </div>
    <div class="card-body">
        <div class="list-group list-group-flush">
            @foreach($listing->activeOffers as $offer)
            <div class="list-group-item px-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        @php
                            $offererName = $offer->offerer ? $offer->offerer->name : ($offer->offererSeller ? $offer->offererSeller->business_name : 'Unknown');
                        @endphp
                        <strong>{{ $offererName }}</strong>
                        @if($offer->getOfferedItem())
                        <div class="text-muted small">{{ $offer->getOfferedItem()->name }}</div>
                        @endif
                        @if($offer->cash_amount)
                        <div class="text-success small">+ ₱{{ number_format($offer->cash_amount, 2) }}</div>
                        @endif
                        @if($offer->message)
                        <p class="small text-muted mb-0 mt-1">{{ $offer->message }}</p>
                        @endif
                    </div>
                    <small class="text-muted">{{ $offer->created_at->diffForHumans() }}</small>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Full Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body p-0 text-center">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                <img src="" alt="Full size" id="imageModalImg" class="img-fluid" style="max-height: 90vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

@if($listing->status === 'pending_approval')
<!-- Reject Modal (only rendered when pending) -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="rejectForm" action="">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Listing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">Rejecting: <strong id="rejectListingTitle"></strong></p>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Reason (optional)</label>
                        <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="3" placeholder="Optionally provide a reason to notify the user..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle me-1"></i> Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endif

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
(function() {
    var mainImg = document.getElementById('mainImage');
    var thumbRow = document.getElementById('thumbRow');
    var btnViewFull = document.getElementById('btnViewFullImage');

    function setMainImage(src, el) {
        if (mainImg && src) {
            mainImg.src = src;
            mainImg.setAttribute('data-full-src', src);
        }
        if (thumbRow) {
            thumbRow.querySelectorAll('.listing-thumb').forEach(function(t) { t.classList.remove('active'); });
        }
        if (el) el.classList.add('active');
    }

    function openImageModal(src) {
        var s = src || (mainImg && (mainImg.getAttribute('data-full-src') || mainImg.src));
        var img = document.getElementById('imageModalImg');
        if (img && s) img.src = s;
        var m = document.getElementById('imageModal');
        if (m) new bootstrap.Modal(m).show();
    }

    if (thumbRow) {
        thumbRow.addEventListener('click', function(e) {
            var t = e.target.closest('.listing-thumb');
            if (t && t.dataset.src) {
                setMainImage(t.dataset.src, t);
            }
        });
        thumbRow.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                var t = e.target.closest('.listing-thumb');
                if (t && t.dataset.src) {
                    e.preventDefault();
                    setMainImage(t.dataset.src, t);
                }
            }
        });
    }

    if (mainImg) {
        mainImg.addEventListener('click', function() {
            openImageModal(mainImg.getAttribute('data-full-src') || mainImg.src);
        });
    }
    if (btnViewFull) {
        btnViewFull.addEventListener('click', function() {
            openImageModal(mainImg ? (mainImg.getAttribute('data-full-src') || mainImg.src) : null);
        });
    }

    var rejectModal = document.getElementById('rejectModal');
    if (rejectModal) {
        rejectModal.addEventListener('show.bs.modal', function(event) {
            var btn = event.relatedTarget;
            if (btn) {
                document.getElementById('rejectForm').action = btn.getAttribute('data-reject-url') || '';
                document.getElementById('rejectListingTitle').textContent = btn.getAttribute('data-listing-title') || 'Listing';
                document.getElementById('rejection_reason').value = '';
            }
        });
    }

    var mapEl = document.getElementById('admin-listing-map');
    var locationText = @json($listing->location ?? '');
    if (mapEl && locationText) {
        var defaultCenter = [14.5995, 120.9842];
        var map = L.map('admin-listing-map').setView(defaultCenter, 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);
        fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(locationText) + '&limit=1', {
            headers: { 'Accept': 'application/json', 'User-Agent': 'ToyHavenPlatform/1.0' }
        }).then(function(r) { return r.json(); })
        .then(function(data) {
            if (data && data[0]) {
                var lat = parseFloat(data[0].lat);
                var lon = parseFloat(data[0].lon);
                map.setView([lat, lon], 14);
                L.marker([lat, lon]).addTo(map);
            } else {
                L.marker(defaultCenter).addTo(map);
            }
        }).catch(function() {
            L.marker(defaultCenter).addTo(map);
        });
    }
})();
</script>
@endpush
@endsection
