@extends('layouts.admin-new')

@section('title', 'Auction Details - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <h2 class="mb-0 fw-bold">{{ $auction->title }}</h2>
                                <span class="badge bg-{{ $auction->status === 'live' ? 'success' : ($auction->status === 'ended' ? 'secondary' : ($auction->status === 'pending_approval' ? 'warning' : 'danger')) }} fs-6">
                                    {{ ucwords(str_replace('_', ' ', $auction->status)) }}
                                </span>
                            </div>
                            <p class="text-muted mb-0">
                                <i class="bi bi-person-circle me-1"></i>{{ $auction->user->name }} 
                                <span class="mx-2">•</span>
                                <i class="bi bi-calendar3 me-1"></i>Created {{ $auction->created_at->format('M d, Y') }}
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            @if($auction->status === 'pending_approval')
                                <form action="{{ route('admin.auctions.approve', $auction) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-circle me-1"></i>Approve
                                    </button>
                                </form>
                                <form action="{{ route('admin.auctions.reject', $auction) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Reject this auction listing?')">
                                        <i class="bi bi-x-circle me-1"></i>Reject
                                    </button>
                                </form>
                            @elseif(!$auction->isEnded())
                                <form action="{{ route('admin.auctions.cancel', $auction) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this auction?');">
                                    @csrf
                                    <button type="submit" class="btn btn-warning">
                                        <i class="bi bi-ban me-1"></i>Cancel
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('admin.auctions.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="row g-4">
                <!-- Left Column: Media -->
                <div class="col-lg-5">
                    <!-- Product Images -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-images text-primary me-2"></i>Product Images</h5>
                        </div>
                        <div class="card-body">
                            @if($auction->images->where('image_type', 'standard')->count())
                                <div class="row g-2">
                                    @foreach($auction->images->where('image_type', 'standard') as $img)
                                        <div class="col-6">
                                            <div class="position-relative overflow-hidden rounded" style="height: 150px;">
                                                <img src="{{ asset('storage/' . $img->path) }}" class="img-fluid fullscreen-img" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" title="Click to view full screen">
                                                @if($img->display_order === 0)
                                                    <span class="position-absolute top-0 start-0 badge bg-primary m-2">Primary</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-image fs-1 d-block mb-2"></i>
                                    <p class="mb-0">No images uploaded</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Verification Video -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-camera-video text-danger me-2"></i>Verification Video</h5>
                        </div>
                        <div class="card-body">
                            @if($auction->verification_video_path)
                                <div class="ratio ratio-16x9 rounded overflow-hidden">
                                    <video src="{{ asset('storage/' . $auction->verification_video_path) }}" controls class="fullscreen-video" style="cursor: pointer; object-fit: cover;" title="Click to view full screen"></video>
                                </div>
                                <small class="text-muted d-block mt-2"><i class="bi bi-info-circle me-1"></i>Click video to view full screen</small>
                            @else
                                <div class="alert alert-warning mb-0">
                                    <i class="bi bi-exclamation-triangle me-2"></i><strong>Missing!</strong> No verification video uploaded.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 360° Photos (if any) -->
                    @if($auction->images->where('image_type', 'photo_360')->count())
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0 fw-bold"><i class="bi bi-arrow-repeat text-info me-2"></i>360° Photos</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    @foreach($auction->images->where('image_type', 'photo_360')->take(6) as $img)
                                        <div class="col-4">
                                            <div class="rounded overflow-hidden" style="height: 80px;">
                                                <img src="{{ asset('storage/' . $img->path) }}" class="img-fluid fullscreen-img" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;" title="Click to view full screen">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @if($auction->images->where('image_type', 'photo_360')->count() > 6)
                                    <div class="text-center mt-2">
                                        <small class="text-muted">+{{ $auction->images->where('image_type', 'photo_360')->count() - 6 }} more photos</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right Column: Details -->
                <div class="col-lg-7">
                    <!-- Description -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-file-text text-success me-2"></i>Description</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0 text-justify">{{ $auction->description }}</p>
                        </div>
                    </div>

                    <!-- Product Details -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-box-seam text-warning me-2"></i>Product Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-box text-muted me-2"></i>
                                        <div>
                                            <small class="text-muted d-block">Box Condition</small>
                                            <span class="badge bg-info">{{ \App\Models\Auction::boxConditionLabel($auction->box_condition) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-clock-history text-muted me-2"></i>
                                        <div>
                                            <small class="text-muted d-block">Auction Type</small>
                                            <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $auction->auction_type)) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-tags text-muted me-2 mt-1"></i>
                                        <div>
                                            <small class="text-muted d-block">Categories</small>
                                            @if($auction->categories->count())
                                                @foreach($auction->categories as $cat)
                                                    <span class="badge bg-primary me-1">{{ $cat->name }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">None</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if($auction->provenance)
                                    <div class="col-12">
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-journal-text text-muted me-2 mt-1"></i>
                                            <div>
                                                <small class="text-muted d-block">Provenance</small>
                                                <span>{{ $auction->provenance }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if($auction->authenticity_marks)
                                    <div class="col-12">
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-shield-check text-muted me-2 mt-1"></i>
                                            <div>
                                                <small class="text-muted d-block">Authenticity Marks</small>
                                                <span>{{ $auction->authenticity_marks }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if($auction->known_defects)
                                    <div class="col-12">
                                        <div class="alert alert-danger mb-0 py-2">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            <strong>Known Defects:</strong> {{ $auction->known_defects }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Auction Settings -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-gear text-secondary me-2"></i>Auction Settings</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-4">
                                    <div class="text-center p-3 bg-light rounded">
                                        <small class="text-muted d-block">Starting Bid</small>
                                        <h5 class="mb-0 text-primary">₱{{ number_format($auction->starting_bid, 2) }}</h5>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="text-center p-3 bg-light rounded">
                                        <small class="text-muted d-block">Current Price</small>
                                        <h5 class="mb-0 text-success">₱{{ number_format($auction->getCurrentPrice(), 2) }}</h5>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="text-center p-3 bg-light rounded">
                                        <small class="text-muted d-block">Bid Increment</small>
                                        <h5 class="mb-0">₱{{ number_format($auction->bid_increment, 2) }}</h5>
                                    </div>
                                </div>
                                @if($auction->reserve_price)
                                    <div class="col-md-6 col-lg-4">
                                        <div class="text-center p-3 bg-light rounded">
                                            <small class="text-muted d-block">Reserve Price</small>
                                            <h5 class="mb-0 text-warning">₱{{ number_format($auction->reserve_price, 2) }}</h5>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-6 col-lg-4">
                                    <div class="text-center p-3 bg-light rounded">
                                        <small class="text-muted d-block">Total Bids</small>
                                        <h5 class="mb-0">{{ $auction->bids_count }}</h5>
                                    </div>
                                </div>
                            </div>
                            <hr class="my-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <i class="bi bi-calendar-event text-muted me-2"></i>
                                    <small class="text-muted">Start Date</small>
                                    <div class="fw-semibold">{{ $auction->start_at?->format('M d, Y H:i') ?? 'TBD' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <i class="bi bi-calendar-check text-muted me-2"></i>
                                    <small class="text-muted">End Date</small>
                                    <div class="fw-semibold">{{ $auction->end_at?->format('M d, Y H:i') ?? 'TBD' }}</div>
                                </div>
                                @if($auction->duration_minutes)
                                    <div class="col-md-6">
                                        <i class="bi bi-stopwatch text-muted me-2"></i>
                                        <small class="text-muted">Duration</small>
                                        <div class="fw-semibold">{{ $auction->duration_minutes }} minutes</div>
                                    </div>
                                @endif
                                <div class="col-12">
                                    <i class="bi bi-people text-muted me-2"></i>
                                    <small class="text-muted">Allowed Bidders</small>
                                    <div class="mt-1">
                                        @if(in_array('all', $auction->allowed_bidder_plans ?? []))
                                            <span class="badge bg-success">All Members</span>
                                        @else
                                            @foreach($auction->allowed_bidder_plans ?? [] as $plan)
                                                <span class="badge bg-info me-1">{{ ucfirst($plan) }}</span>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seller Information -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-person-badge text-info me-2"></i>Seller Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-person-circle fs-3 text-muted me-3"></i>
                                <div>
                                    <div class="fw-semibold">{{ $auction->user->name }}</div>
                                    <small class="text-muted">{{ $auction->user->email }}</small>
                                </div>
                            </div>
                            @if($auction->seller)
                                <div class="d-flex align-items-center mt-2">
                                    <i class="bi bi-shop fs-3 text-muted me-3"></i>
                                    <div>
                                        <div class="fw-semibold">{{ $auction->seller->business_name ?? 'N/A' }}</div>
                                        <small class="text-muted">Business Seller</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Winner (if exists) -->
                    @if($auction->winner)
                        <div class="alert alert-success d-flex align-items-center">
                            <i class="bi bi-trophy-fill fs-3 me-3"></i>
                            <div>
                                <strong>Auction Winner</strong>
                                <div>{{ $auction->winner->name }} - ₱{{ number_format($auction->winning_amount ?? $auction->getCurrentPrice(), 2) }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Bid History -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-list-ol text-primary me-2"></i>Bid History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><i class="bi bi-person me-1"></i>Bidder</th>
                                    <th><i class="bi bi-currency-dollar me-1"></i>Amount</th>
                                    <th><i class="bi bi-clock me-1"></i>Date & Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($auction->bids as $bid)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-person-circle fs-5 text-muted me-2"></i>
                                                <span>{{ $bid->user->name ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td class="fw-semibold text-success">₱{{ number_format($bid->amount, 2) }}</td>
                                        <td>{{ $bid->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            @if($bid->is_winning)
                                                <span class="badge bg-success">Winning</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            No bids placed yet
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .text-justify {
        text-align: justify;
        text-justify: inter-word;
    }
    .card {
        transition: box-shadow 0.3s ease;
    }
    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
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
    .fullscreen-img:hover,
    .fullscreen-video:hover {
        opacity: 0.9;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>
@endpush
@endsection
