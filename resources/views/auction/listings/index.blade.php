@extends('layouts.toyshop')

@section('title', 'My Auction Listings - ToyHaven')

@push('styles')
<link href="{{ asset('css/auction.css') }}" rel="stylesheet">
<style>
    .listing-card { border-radius: 14px; border: 1px solid #e2e8f0; transition: transform .2s, box-shadow .2s; overflow: hidden; }
    .listing-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.08); }
    .listing-card-img { height: 200px; object-fit: cover; width: 100%; background: #f8fafc; }
    .listing-card-body { padding: 1.25rem; }
    .listing-stat { display: flex; align-items: center; gap: 6px; font-size: .82rem; }
    .listing-stat i { font-size: .9rem; }
    .status-pill { font-size: .7rem; font-weight: 600; letter-spacing: .3px; }
    .listing-actions .btn { font-size: .8rem; padding: .35rem .75rem; }
    .live-dot-sm { width: 6px; height: 6px; border-radius: 50%; background: #ef4444; display: inline-block; animation: livePulse 1.5s infinite; }
    @keyframes livePulse { 0%,100%{opacity:1} 50%{opacity:.4} }
</style>
@endpush

@section('content')
<div class="container py-4 pb-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auction.index') }}">Auction</a></li>
            <li class="breadcrumb-item"><a href="{{ route('auction.seller.dashboard') }}">Seller Dashboard</a></li>
            <li class="breadcrumb-item active">My Listings</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-list-ul me-2"></i>My Auction Listings</h4>
        <a href="{{ route('auction.listings.create') }}" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-plus-lg me-1"></i>Add Listing
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($listings->count() > 0)
        <div class="row g-4">
            @foreach($listings as $l)
                @php
                    $primaryImg = $l->images->firstWhere('is_primary', true) ?? $l->images->first();
                    $badge = match($l->status) {
                        'draft' => 'secondary',
                        'pending_approval' => 'warning',
                        'active' => 'success',
                        'ended' => 'info',
                        'cancelled' => 'danger',
                        default => 'secondary',
                    };
                    $statusLabel = match($l->status) {
                        'pending_approval' => 'Pending Approval',
                        default => ucfirst($l->status),
                    };
                    $catNames = $l->category ? $l->category->name : ($l->categories()->pluck('name')->join(', ') ?: 'Uncategorized');
                @endphp
                <div class="col-md-6 col-xl-4">
                    <div class="card listing-card h-100">
                        @if($primaryImg)
                            <div class="position-relative">
                                <img src="{{ asset('storage/' . $primaryImg->image_path) }}" alt="{{ Str::limit($l->title, 50) }}" class="listing-card-img">
                                <span class="position-absolute top-0 end-0 m-2">
                                    <span class="badge bg-{{ $badge }} status-pill rounded-pill px-2 py-1">
                                        @if($l->isActive())<span class="live-dot-sm me-1"></span>@endif
                                        {{ $statusLabel }}
                                    </span>
                                </span>
                                @if($l->images->count() > 1)
                                    <span class="position-absolute bottom-0 end-0 m-2 badge bg-dark bg-opacity-75 rounded-pill" style="font-size:.7rem;">
                                        <i class="bi bi-images me-1"></i>{{ $l->images->count() }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <div class="listing-card-img d-flex align-items-center justify-content-center bg-light">
                                <i class="bi bi-image text-muted" style="font-size:3rem;"></i>
                            </div>
                        @endif

                        <div class="listing-card-body">
                            <h6 class="fw-bold mb-1">{{ Str::limit($l->title, 55) }}</h6>
                            <p class="text-muted small mb-2">{{ $catNames }}</p>

                            @if($l->rejection_reason)
                                <div class="alert alert-warning py-1 px-2 mb-2" style="font-size:.78rem;">
                                    <i class="bi bi-exclamation-triangle me-1"></i>{{ Str::limit($l->rejection_reason, 80) }}
                                </div>
                            @endif

                            <div class="d-flex flex-wrap gap-3 mb-3">
                                <div class="listing-stat">
                                    <i class="bi bi-tag text-primary"></i>
                                    <span><strong>₱{{ number_format($l->starting_bid, 2) }}</strong></span>
                                </div>
                                <div class="listing-stat">
                                    <i class="bi bi-arrow-up-right text-success"></i>
                                    <span>+₱{{ number_format($l->bid_increment, 2) }}</span>
                                </div>
                                @if($l->isActive() || $l->isEnded())
                                    <div class="listing-stat">
                                        <i class="bi bi-hammer text-info"></i>
                                        <span>{{ $l->bids_count ?? 0 }} bid{{ ($l->bids_count ?? 0) !== 1 ? 's' : '' }}</span>
                                    </div>
                                @endif
                            </div>

                            @if($l->winning_amount && ($l->isActive() || $l->isEnded()))
                                <p class="mb-2 small">
                                    <strong>{{ $l->isEnded() ? 'Final price:' : 'Current bid:' }}</strong>
                                    <span class="text-success fw-bold">₱{{ number_format($l->winning_amount, 2) }}</span>
                                </p>
                            @endif

                            @if($l->isActive() && $l->end_at)
                                <p class="mb-2 small text-muted" x-data="listingTimer('{{ $l->end_at->toIso8601String() }}')" x-text="text" :class="{ 'text-danger fw-semibold': urgent }"></p>
                            @elseif($l->isEnded())
                                <p class="mb-2 small text-muted"><i class="bi bi-flag me-1"></i>Ended {{ $l->end_at?->diffForHumans() }}</p>

                                @if($l->auction_outcome === 'sold' && $l->winner)
                                    <p class="mb-1 small"><i class="bi bi-trophy text-success me-1"></i>Winner: <strong>{{ $l->winner->name }}</strong></p>
                                    @if($l->payment)
                                        @if($l->payment->status === 'pending')
                                            <span class="badge bg-warning text-dark" style="font-size:.7rem;">
                                                <i class="bi bi-clock me-1"></i>Awaiting Payment
                                                @if($l->payment->isOverdue()) &mdash; OVERDUE @endif
                                            </span>
                                        @elseif($l->payment->status === 'held')
                                            <span class="badge bg-info" style="font-size:.7rem;"><i class="bi bi-lock me-1"></i>Escrow</span>
                                            @if(!in_array($l->payment->delivery_status, ['shipped', 'delivered', 'confirmed']))
                                                <span class="badge bg-danger" style="font-size:.7rem;"><i class="bi bi-truck me-1"></i>Ship Now</span>
                                            @elseif($l->payment->delivery_status === 'shipped')
                                                <span class="badge bg-info" style="font-size:.7rem;"><i class="bi bi-truck me-1"></i>Shipped</span>
                                            @else
                                                <span class="badge bg-success" style="font-size:.7rem;"><i class="bi bi-check me-1"></i>Delivered</span>
                                            @endif
                                        @elseif($l->payment->status === 'released')
                                            <span class="badge bg-success" style="font-size:.7rem;"><i class="bi bi-check-circle me-1"></i>Complete</span>
                                        @elseif($l->payment->status === 'paid')
                                            <span class="badge bg-primary" style="font-size:.7rem;"><i class="bi bi-credit-card me-1"></i>Paid</span>
                                        @elseif($l->payment->status === 'refunded')
                                            <span class="badge bg-danger" style="font-size:.7rem;"><i class="bi bi-x-circle me-1"></i>Failed</span>
                                        @endif
                                    @endif
                                @elseif($l->auction_outcome === 'reserve_not_met')
                                    <p class="mb-1 small text-warning"><i class="bi bi-exclamation-triangle me-1"></i>Reserve not met</p>
                                @elseif($l->auction_outcome === 'no_bids')
                                    <p class="mb-1 small text-muted"><i class="bi bi-dash-circle me-1"></i>No bids placed</p>
                                @endif
                            @endif

                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <span class="badge bg-light text-dark border" style="font-size:.72rem;">
                                    {{ \App\Models\Auction::CONDITIONS[$l->condition ?? 'good'] ?? 'Good' }}
                                </span>
                                @if($l->reserve_price)
                                    <span class="badge bg-light text-dark border" style="font-size:.72rem;">
                                        <i class="bi bi-shield-lock me-1"></i>Reserve set
                                    </span>
                                @endif
                                <span class="badge bg-light text-dark border" style="font-size:.72rem;">
                                    <i class="bi bi-clock me-1"></i>{{ $l->duration_hours ?? 0 }}h duration
                                </span>
                            </div>

                            @if($l->description)
                                <p class="text-muted small mb-3" style="line-height:1.4;">{{ Str::limit($l->description, 100) }}</p>
                            @endif

                            <hr class="my-2">

                            <div class="listing-actions d-flex flex-wrap gap-2">
                                @if($l->isDraft())
                                    <a href="{{ route('auction.listings.edit', $l) }}" class="btn btn-outline-primary rounded-pill">
                                        <i class="bi bi-pencil me-1"></i>Edit
                                    </a>
                                    <form action="{{ route('auction.listings.submit-for-approval', $l) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success rounded-pill">
                                            <i class="bi bi-send me-1"></i>Submit
                                        </button>
                                    </form>
                                    <form action="{{ route('auction.listings.destroy', $l) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this draft listing?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger rounded-pill">
                                            <i class="bi bi-trash me-1"></i>Delete
                                        </button>
                                    </form>
                                @elseif($l->isActive())
                                    <a href="{{ route('auction.show', $l) }}" class="btn btn-primary rounded-pill">
                                        <i class="bi bi-eye me-1"></i>View Live
                                    </a>
                                    <a href="{{ route('auction.listings.edit', $l) }}" class="btn btn-outline-secondary rounded-pill">
                                        <i class="bi bi-pencil me-1"></i>Edit
                                    </a>
                                @elseif($l->isEnded())
                                    <a href="{{ route('auction.show', $l) }}" class="btn btn-outline-primary rounded-pill">
                                        <i class="bi bi-eye me-1"></i>View Details
                                    </a>
                                @elseif($l->isPendingApproval())
                                    <a href="{{ route('auction.show', $l) }}" class="btn btn-outline-primary rounded-pill">
                                        <i class="bi bi-eye me-1"></i>Preview
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $listings->links() }}</div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                <h5 class="text-muted mb-2">No auction listings yet</h5>
                <p class="text-muted mb-3">Create your first listing to start selling on the auction platform.</p>
                <a href="{{ route('auction.listings.create') }}" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-plus-lg me-1"></i>Create Your First Listing
                </a>
            </div>
        </div>
    @endif

    <a href="{{ route('auction.seller.dashboard') }}" class="btn btn-outline-secondary rounded-pill mt-4">
        <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
    </a>
</div>
@endsection

@push('scripts')
<script>
function listingTimer(endAtIso) {
    return {
        text: '',
        urgent: false,
        interval: null,
        init() {
            this.update();
            this.interval = setInterval(() => this.update(), 1000);
        },
        update() {
            const diff = new Date(endAtIso).getTime() - Date.now();
            if (diff <= 0) {
                this.text = 'Auction ended';
                this.urgent = false;
                if (this.interval) clearInterval(this.interval);
                return;
            }
            this.urgent = diff <= 300000;
            const d = Math.floor(diff / 86400000);
            const h = Math.floor((diff % 86400000) / 3600000);
            const m = Math.floor((diff % 3600000) / 60000);
            const s = Math.floor((diff % 60000) / 1000);
            let parts = [];
            if (d > 0) parts.push(d + 'd');
            if (h > 0) parts.push(h + 'h');
            parts.push(m + 'm ' + s + 's');
            this.text = 'Ends in ' + parts.join(' ');
        },
        destroy() { if (this.interval) clearInterval(this.interval); }
    };
}
</script>
@endpush
