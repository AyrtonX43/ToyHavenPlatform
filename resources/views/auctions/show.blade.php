@extends('layouts.toyshop')

@section('title', $auction->title . ' - Auctions')

@push('styles')
<style>
    .auction-detail-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 2px solid #e2e8f0;
        overflow: hidden;
    }
    .main-image-container {
        position: relative;
        width: 100%;
        height: 500px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        overflow: hidden;
    }
    .main-image-container img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        transition: transform 0.3s ease;
    }
    .main-image-container:hover img {
        transform: scale(1.05);
    }
    .thumbnail-container {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding: 12px;
        background: #f8fafc;
    }
    .thumbnail-container::-webkit-scrollbar {
        height: 6px;
    }
    .thumbnail-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
    .thumbnail {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    .thumbnail:hover {
        border-color: #0891b2;
        transform: scale(1.05);
    }
    .thumbnail.active {
        border-color: #0891b2;
        box-shadow: 0 0 0 2px rgba(8, 145, 178, 0.2);
    }
    .current-price {
        font-size: 2rem;
        font-weight: 800;
        color: #0891b2;
    }
    .bid-list {
        max-height: 300px;
        overflow-y: auto;
    }
    .countdown-badge {
        background: linear-gradient(135deg, #0891b2, #06b6d4);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 12px;
        font-weight: 700;
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
    .media-overlay img {
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
    .info-badge {
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        background: #f1f5f9;
        border-radius: 8px;
        margin-right: 8px;
        margin-bottom: 8px;
    }
    .info-badge i {
        margin-right: 6px;
        color: #64748b;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auctions.index') }}">Auctions</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($auction->title, 40) }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Left Column: Images & Details -->
        <div class="col-lg-8">
            <!-- Image Gallery -->
            <div class="auction-detail-card mb-4">
                <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        @if($auction->is_members_only)
                            <span class="badge bg-warning text-dark me-2">Members Only</span>
                        @endif
                        <span class="badge bg-info">{{ ucfirst($auction->auction_type) }}</span>
                    </div>
                    <span class="countdown-badge">
                        <i class="bi bi-clock me-1"></i>
                        @if($auction->isEnded())
                            Ended {{ $auction->end_at?->format('M j, Y H:i') ?? 'N/A' }}
                        @else
                            Ends {{ $auction->end_at?->diffForHumans() ?? 'TBD' }}
                        @endif
                    </span>
                </div>
                
                <!-- Main Image -->
                <div class="main-image-container" id="mainImageContainer">
                    @php
                        $images = $auction->images->where('image_type', 'standard');
                        $primaryImage = $images->firstWhere('display_order', 0) ?? $images->first();
                    @endphp
                    @if($primaryImage)
                        <img src="{{ asset('storage/' . $primaryImage->path) }}" alt="{{ $auction->title }}" id="mainImage">
                    @else
                        <div class="d-flex align-items-center justify-content-center" style="height: 100%;">
                            <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                        </div>
                    @endif
                </div>

                <!-- Thumbnails -->
                @if($images->count() > 1)
                    <div class="thumbnail-container">
                        @foreach($images as $img)
                            <img src="{{ asset('storage/' . $img->path) }}" 
                                 class="thumbnail {{ $loop->first ? 'active' : '' }}" 
                                 data-full="{{ asset('storage/' . $img->path) }}"
                                 alt="Thumbnail {{ $loop->iteration }}">
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Product Details -->
            <div class="auction-detail-card p-4">
                <h1 class="h3 fw-bold mb-3">{{ $auction->title }}</h1>
                
                <!-- Product Info Badges -->
                <div class="mb-3">
                    <div class="info-badge">
                        <i class="bi bi-box"></i>
                        <span>{{ \App\Models\Auction::boxConditionLabel($auction->box_condition) }}</span>
                    </div>
                    @if($auction->categories->count())
                        @foreach($auction->categories as $cat)
                            <div class="info-badge">
                                <i class="bi bi-tag"></i>
                                <span>{{ $cat->name }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>

                @if($auction->description)
                    <div class="mb-4">
                        <h5 class="fw-bold mb-2">Description</h5>
                        <p class="text-justify" style="text-align: justify; text-justify: inter-word;">
                            {!! nl2br(e($auction->description)) !!}
                        </p>
                    </div>
                @endif

                @if($auction->provenance || $auction->authenticity_marks || $auction->known_defects)
                    <hr class="my-4">
                    <h5 class="fw-bold mb-3">Additional Information</h5>
                    
                    @if($auction->provenance)
                        <div class="mb-3">
                            <strong><i class="bi bi-journal-text me-2"></i>Provenance:</strong>
                            <p class="mb-0 ms-4">{{ $auction->provenance }}</p>
                        </div>
                    @endif

                    @if($auction->authenticity_marks)
                        <div class="mb-3">
                            <strong><i class="bi bi-shield-check me-2"></i>Authenticity Marks:</strong>
                            <p class="mb-0 ms-4">{{ $auction->authenticity_marks }}</p>
                        </div>
                    @endif

                    @if($auction->known_defects)
                        <div class="alert alert-warning mb-0">
                            <strong><i class="bi bi-exclamation-triangle me-2"></i>Known Defects:</strong>
                            <p class="mb-0">{{ $auction->known_defects }}</p>
                        </div>
                    @endif
                @endif

                <!-- Seller Info -->
                <hr class="my-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-person-circle fs-3 text-muted me-3"></i>
                    <div>
                        <small class="text-muted d-block">Seller</small>
                        <strong>{{ $auction->user->name }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Bidding -->
        <div class="col-lg-4">
            <div class="auction-detail-card p-4 sticky-top" style="top: 20px;">
                <div class="current-price mb-2">
                    ₱{{ number_format($auction->getCurrentPrice(), 2) }}
                </div>
                <p class="text-muted small mb-3">
                    {{ $auction->bids_count }} bid(s) · Min bid: ₱{{ number_format($auction->getMinNextBid(), 2) }}
                </p>

                @if($canBid)
                    <form action="{{ route('auctions.bids.store', $auction) }}" method="POST" class="mb-4">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Your Bid (₱)</label>
                            <input type="number" name="amount" class="form-control form-control-lg" step="0.01" min="{{ $auction->getMinNextBid() }}" value="{{ $auction->getMinNextBid() }}" required>
                            @error('amount')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                            <i class="bi bi-hammer me-1"></i>Place Bid
                        </button>
                    </form>
                @elseif(!auth()->user() || !auth()->user()->hasActiveMembership())
                    <a href="{{ route('membership.index', ['intent' => 'auction']) }}" class="btn btn-primary w-100 btn-lg mb-4" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                        <i class="bi bi-gem me-1"></i>Join Membership to Bid
                    </a>
                @elseif($auction->isEnded())
                    <div class="alert alert-secondary mb-4">
                        <i class="bi bi-info-circle me-2"></i>This auction has ended.
                    </div>
                    @if($auction->winner_id && $auction->winner_id === auth()->id())
                        <div class="alert alert-success">
                            <i class="bi bi-trophy me-2"></i><strong>You won this auction!</strong>
                        </div>
                    @endif
                @elseif($auction->user_id === auth()->id())
                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle me-2"></i>You cannot bid on your own auction.
                    </div>
                @endif

                <h6 class="fw-bold mt-4 mb-2">Bid History</h6>
                <div class="bid-list">
                    @forelse($auction->bids as $bid)
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span>
                                @if($bid->user_id === auth()->id())
                                    <strong>You</strong>
                                @else
                                    {{ $bid->user?->getAuctionAlias() ?? 'Anonymous' }}
                                @endif
                                @if($bid->is_winning)
                                    <span class="badge bg-success ms-1">Highest</span>
                                @endif
                            </span>
                            <span class="fw-semibold">₱{{ number_format($bid->amount, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-muted small">No bids yet. Be the first!</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image gallery functionality
    const mainImage = document.getElementById('mainImage');
    const mainImageContainer = document.getElementById('mainImageContainer');
    const thumbnails = document.querySelectorAll('.thumbnail');

    // Thumbnail click to change main image
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            const fullSrc = this.getAttribute('data-full');
            if (mainImage) {
                mainImage.src = fullSrc;
            }
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Fullscreen overlay
    const overlay = document.createElement('div');
    overlay.className = 'media-overlay';
    overlay.innerHTML = '<button class="overlay-close" title="Close">&times;</button><div id="overlayContent"></div>';
    document.body.appendChild(overlay);

    const overlayContent = document.getElementById('overlayContent');

    function openOverlay(src) {
        overlayContent.innerHTML = '<img src="' + src + '" alt="Full View">';
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeOverlay() {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
        overlayContent.innerHTML = '';
    }

    overlay.querySelector('.overlay-close').addEventListener('click', function(e) {
        e.stopPropagation();
        closeOverlay();
    });
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closeOverlay();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeOverlay();
    });

    // Click main image to view fullscreen
    if (mainImageContainer) {
        mainImageContainer.addEventListener('click', function() {
            if (mainImage && mainImage.src) {
                openOverlay(mainImage.src);
            }
        });
    }

    // Real-time bid updates
    @if($canBid)
    if (typeof Echo !== 'undefined') {
        Echo.channel('auction.{{ $auction->id }}')
            .listen('.AuctionBidPlaced', (e) => {
                if (e.amount) {
                    const priceEl = document.querySelector('.current-price');
                    if (priceEl) priceEl.textContent = '₱' + parseFloat(e.amount).toLocaleString('en-PH', {minimumFractionDigits: 2});
                }
                location.reload();
            });
    }
    @endif
});
</script>
@endpush
@endsection
