@extends('layouts.admin')

@section('title', 'Auction - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="text-2xl font-bold mb-0">{{ $auction->title }}</h1>
                    <div>
                        @if($auction->status === 'pending_approval')
                            <form action="{{ route('admin.auctions.approve', $auction) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">Approve</button>
                            </form>
                            <form action="{{ route('admin.auctions.reject', $auction) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger">Reject</button>
                            </form>
                        @elseif(!$auction->isEnded())
                            <form action="{{ route('admin.auctions.cancel', $auction) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this auction?');">
                                @csrf
                                <button type="submit" class="btn btn-warning">Cancel</button>
                            </form>
                        @endif
                        <a href="{{ route('admin.auctions.edit', $auction) }}" class="btn btn-outline-primary">Edit</a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <h5 class="fw-bold mb-3">Description</h5>
                        <p class="mb-4">{{ $auction->description }}</p>

                        <h5 class="fw-bold mb-3">Product Details</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <strong>Box Condition:</strong> 
                                <span class="badge bg-info">{{ \App\Models\Auction::boxConditionLabel($auction->box_condition) }}</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Auction Type:</strong> 
                                <span class="badge bg-secondary">{{ ucfirst($auction->auction_type) }}</span>
                            </div>
                            @if($auction->provenance)
                                <div class="col-12">
                                    <strong>Provenance:</strong> {{ $auction->provenance }}
                                </div>
                            @endif
                            @if($auction->authenticity_marks)
                                <div class="col-12">
                                    <strong>Authenticity Marks:</strong> {{ $auction->authenticity_marks }}
                                </div>
                            @endif
                            @if($auction->known_defects)
                                <div class="col-12">
                                    <strong>Known Defects:</strong> <span class="text-danger">{{ $auction->known_defects }}</span>
                                </div>
                            @endif
                            <div class="col-12">
                                <strong>Categories:</strong> 
                                @if($auction->categories->count())
                                    @foreach($auction->categories as $cat)
                                        <span class="badge bg-primary me-1">{{ $cat->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </div>
                        </div>

                        <h5 class="fw-bold mb-3">Auction Settings</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <strong>Status:</strong>
                                <span class="badge bg-{{ $auction->status === 'live' ? 'success' : ($auction->status === 'ended' ? 'secondary' : ($auction->status === 'pending_approval' ? 'warning' : 'danger')) }}">{{ ucwords(str_replace('_', ' ', $auction->status)) }}</span>
                            </div>
                            <div class="col-md-3"><strong>Starting Bid:</strong> ₱{{ number_format($auction->starting_bid, 2) }}</div>
                            <div class="col-md-3"><strong>Current Price:</strong> ₱{{ number_format($auction->getCurrentPrice(), 2) }}</div>
                            <div class="col-md-3"><strong>Bids:</strong> {{ $auction->bids_count }}</div>
                            <div class="col-md-3"><strong>Bid Increment:</strong> ₱{{ number_format($auction->bid_increment, 2) }}</div>
                            @if($auction->reserve_price)
                                <div class="col-md-3"><strong>Reserve Price:</strong> ₱{{ number_format($auction->reserve_price, 2) }}</div>
                            @endif
                            <div class="col-md-3"><strong>Starts:</strong> {{ $auction->start_at?->format('M d, Y H:i') ?? 'TBD' }}</div>
                            <div class="col-md-3"><strong>Ends:</strong> {{ $auction->end_at?->format('M d, Y H:i') ?? 'TBD' }}</div>
                            @if($auction->duration_minutes)
                                <div class="col-md-3"><strong>Duration:</strong> {{ $auction->duration_minutes }} minutes</div>
                            @endif
                            <div class="col-12">
                                <strong>Allowed Bidders:</strong> 
                                @if(in_array('all', $auction->allowed_bidder_plans ?? []))
                                    <span class="badge bg-success">All Members</span>
                                @else
                                    @foreach($auction->allowed_bidder_plans ?? [] as $plan)
                                        <span class="badge bg-info me-1">{{ ucfirst($plan) }}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <h5 class="fw-bold mb-3">Seller Information</h5>
                        <div class="mb-4">
                            <p class="mb-1"><strong>User:</strong> {{ $auction->user->name }} ({{ $auction->user->email }})</p>
                            @if($auction->seller)
                                <p class="mb-0"><strong>Seller:</strong> {{ $auction->seller->business_name ?? 'N/A' }}</p>
                            @endif
                        </div>

                        @if($auction->winner)
                            <div class="alert alert-success">
                                <strong>Winner:</strong> {{ $auction->winner->name }} (₱{{ number_format($auction->winning_amount ?? $auction->getCurrentPrice(), 2) }})
                            </div>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <h5 class="fw-bold mb-3">Product Images</h5>
                        @if($auction->images->where('image_type', 'standard')->count())
                            <div class="mb-3">
                                <div class="row g-2">
                                    @foreach($auction->images->where('image_type', 'standard') as $img)
                                        <div class="col-6">
                                            <img src="{{ asset('storage/' . $img->path) }}" class="img-fluid rounded fullscreen-img" style="width: 100%; height: 120px; object-fit: cover; cursor: pointer;" title="Click to view full screen">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="text-muted">No images uploaded</p>
                        @endif

                        @if($auction->images->where('image_type', 'photo_360')->count())
                            <h5 class="fw-bold mb-3 mt-4">360° Photos</h5>
                            <div class="mb-3">
                                <div class="row g-2">
                                    @foreach($auction->images->where('image_type', 'photo_360')->take(4) as $img)
                                        <div class="col-6">
                                            <img src="{{ asset('storage/' . $img->path) }}" class="img-fluid rounded fullscreen-img" style="width: 100%; height: 80px; object-fit: cover; cursor: pointer;" title="Click to view full screen">
                                        </div>
                                    @endforeach
                                </div>
                                @if($auction->images->where('image_type', 'photo_360')->count() > 4)
                                    <small class="text-muted">+{{ $auction->images->where('image_type', 'photo_360')->count() - 4 }} more</small>
                                @endif
                            </div>
                        @endif

                        @if($auction->verification_video_path)
                            <h5 class="fw-bold mb-3 mt-4">Verification Video</h5>
                            <div class="mb-3">
                                <video src="{{ asset('storage/' . $auction->verification_video_path) }}" controls class="w-100 rounded fullscreen-video" style="max-height: 250px; cursor: pointer;" title="Click to view full screen"></video>
                            </div>
                        @else
                            <div class="alert alert-warning mt-4">
                                <i class="bi bi-exclamation-triangle me-2"></i>No verification video uploaded
                            </div>
                        @endif
                    </div>
                </div>

                <h5 class="mt-5 fw-bold">Bid History</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>User</th><th>Amount</th><th>Date</th></tr></thead>
                        <tbody>
                            @forelse($auction->bids as $bid)
                                <tr>
                                    <td>{{ $bid->user->name ?? 'N/A' }}</td>
                                    <td>₱{{ number_format($bid->amount, 2) }}</td>
                                    <td>{{ $bid->created_at->format('M d, H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted">No bids yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
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
