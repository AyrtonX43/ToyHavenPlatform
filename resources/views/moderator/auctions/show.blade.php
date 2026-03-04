@extends('layouts.admin-new')

@section('title', 'Auction Details - Moderator')
@section('page-title', 'Auction Details')

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
                            @if($auction->status === 'pending_approval' && auth()->user()->hasAuctionPermission('auctions_moderate'))
                                <form action="{{ route('moderator.auctions.approve', $auction) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-circle me-1"></i>Approve
                                    </button>
                                </form>
                                <form action="{{ route('moderator.auctions.reject', $auction) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Reject this auction listing?')">
                                        <i class="bi bi-x-circle me-1"></i>Reject
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('moderator.auctions.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content (same as admin show) -->
            <div class="row g-4">
                <div class="col-lg-5">
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
                                                <img src="{{ asset('storage/' . $img->path) }}" class="img-fluid fullscreen-img" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;" title="Click to view full screen">
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
                    @if($auction->verification_video_path)
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0 fw-bold"><i class="bi bi-camera-video text-danger me-2"></i>Verification Video</h5>
                            </div>
                            <div class="card-body">
                                <div class="ratio ratio-16x9 rounded overflow-hidden">
                                    <video src="{{ asset('storage/' . $auction->verification_video_path) }}" controls class="fullscreen-video" style="object-fit: cover;"></video>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-file-text text-success me-2"></i>Description</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0 text-justify">{{ $auction->description ?? 'No description.' }}</p>
                        </div>
                    </div>
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
                                <div class="col-md-6">
                                    <small class="text-muted">End Date</small>
                                    <div class="fw-semibold">{{ $auction->end_at?->format('M d, Y H:i') ?? 'TBD' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-person-badge text-info me-2"></i>Seller</h5>
                        </div>
                        <div class="card-body">
                            <div class="fw-semibold">{{ $auction->user->name }}</div>
                            <small class="text-muted">{{ $auction->user->email }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-list-ol text-primary me-2"></i>Bid History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Bidder</th>
                                    <th>Amount</th>
                                    <th>Date & Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($auction->bids as $bid)
                                    <tr>
                                        <td>{{ $bid->user->name ?? 'N/A' }}</td>
                                        <td class="fw-semibold text-success">₱{{ number_format($bid->amount, 2) }}</td>
                                        <td>{{ $bid->created_at->format('M d, Y H:i') }}</td>
                                        <td>@if($bid->is_winning)<span class="badge bg-success">Winning</span>@endif</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No bids placed yet</td>
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
    .text-justify { text-align: justify; text-justify: inter-word; }
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
    .media-overlay img, .media-overlay video {
        max-width: 92vw; max-height: 92vh;
        object-fit: contain;
        border-radius: 6px;
        box-shadow: 0 0 40px rgba(0,0,0,0.5);
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
        img.addEventListener('click', function() { openOverlay('<img src="' + this.src + '" alt="Full View">'); });
    });
    document.querySelectorAll('.fullscreen-video').forEach(function(vid) {
        vid.addEventListener('click', function(e) {
            e.preventDefault();
            openOverlay('<video src="' + this.src + '" controls autoplay style="max-width:92vw;max-height:92vh;border-radius:6px;"></video>');
        });
    });
});
</script>
@endpush
@endsection
