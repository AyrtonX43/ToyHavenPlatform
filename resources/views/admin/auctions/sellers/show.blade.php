@extends('layouts.admin-new')

@section('title', 'Auction Seller: ' . $seller->getDisplayName() . ' - ToyHaven')
@section('page-title', 'Auction Seller Details')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.auction-sellers.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Auction Sellers
    </a>
</div>

{{-- Header --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">{{ $seller->getDisplayName() }}</h4>
                <p class="text-muted mb-0">Owner: {{ $seller->user->name }} ({{ $seller->user->email }})</p>
            </div>
            <div class="text-end">
                <span class="badge bg-{{ $seller->seller_type === 'business' ? 'success' : 'primary' }} fs-6">
                    <i class="bi bi-{{ $seller->seller_type === 'business' ? 'building' : 'person' }} me-1"></i>{{ ucfirst($seller->seller_type) }}
                </span>
                @if($seller->is_suspended)
                    <span class="badge bg-danger fs-6 ms-1">Suspended</span>
                @else
                    <span class="badge bg-success fs-6 ms-1">Active</span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-primary">{{ $auctionCount }}</h3>
                <small class="text-muted">Auction Listings</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="{{ $seller->isSynced() ? 'text-info' : 'text-secondary' }}">
                    <i class="bi bi-{{ $seller->isSynced() ? 'link-45deg' : 'dash-circle' }}"></i>
                </h3>
                <small class="text-muted">{{ $seller->isSynced() ? 'Synced with ToyShop' : 'Auction Only' }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-success">{{ $seller->verified_at ? $seller->verified_at->format('M d, Y') : 'N/A' }}</h3>
                <small class="text-muted">Verified Date</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-warning">{{ $seller->documents->count() }}</h3>
                <small class="text-muted">Documents</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        {{-- Seller Information --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Seller Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Display Name:</strong><br>
                        {{ $seller->getDisplayName() }}
                    </div>
                    <div class="col-md-6">
                        <strong>Seller Type:</strong><br>
                        <span class="badge bg-{{ $seller->seller_type === 'business' ? 'success' : 'primary' }}">{{ ucfirst($seller->seller_type) }}</span>
                    </div>
                </div>
                @if($seller->auction_business_name)
                <div class="row mb-3">
                    <div class="col-md-12">
                        <strong>Auction Business Name:</strong><br>
                        {{ $seller->auction_business_name }}
                    </div>
                </div>
                @endif
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Phone:</strong><br>
                        {{ $seller->phone ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Email:</strong><br>
                        {{ $seller->user->email }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <strong>Address:</strong><br>
                        {{ $seller->address ?? 'N/A' }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Verified At:</strong><br>
                        {{ $seller->verified_at ? $seller->verified_at->format('M d, Y h:i A') : 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Verified By:</strong><br>
                        {{ $seller->verifiedByUser->name ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- ToyShop Sync Info --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-link-45deg me-1"></i>ToyShop Sync Status
                </h5>
                @if($seller->isSynced())
                    <span class="badge bg-info fs-6">Synced</span>
                @else
                    <span class="badge bg-secondary fs-6">Auction Only</span>
                @endif
            </div>
            <div class="card-body">
                @if($seller->isSynced())
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        This auction seller is linked to a ToyShop business. Both accounts are managed under the same user.
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>ToyShop Business Name:</strong><br>
                            {{ $seller->seller->business_name ?? 'N/A' }}
                        </div>
                        <div class="col-md-6">
                            <strong>ToyShop Status:</strong><br>
                            @if($seller->seller)
                                <span class="badge bg-{{ $seller->seller->verification_status === 'approved' ? 'success' : ($seller->seller->verification_status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($seller->seller->verification_status) }}
                                </span>
                                @if(!$seller->seller->is_active)
                                    <span class="badge bg-secondary ms-1">Suspended</span>
                                @endif
                            @endif
                        </div>
                    </div>
                    @if($seller->seller)
                    <div class="mt-3">
                        <a href="{{ route('admin.sellers.show', $seller->seller->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-shop me-1"></i>View ToyShop Seller Profile
                        </a>
                    </div>
                    @endif
                @else
                    <div class="alert alert-secondary mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        This is an <strong>auction-only seller</strong>. They do not have a linked ToyShop business account.
                    </div>
                @endif
            </div>
        </div>

        {{-- Documents --}}
        @if($seller->documents->count() > 0)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Verification Documents</h5>
                <span class="badge bg-secondary">{{ $seller->documents->count() }} Document(s)</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($seller->documents as $doc)
                        <div class="col-md-4">
                            <div class="card h-100 border">
                                <div class="card-header d-flex justify-content-between align-items-center py-2">
                                    <span class="fw-semibold small">{{ \App\Models\AuctionSellerVerification::documentLabel($doc->document_type) }}</span>
                                    <span class="badge bg-{{ $doc->status === 'approved' ? 'success' : ($doc->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($doc->status) }}</span>
                                </div>
                                <div class="card-body text-center py-3">
                                    @if(in_array(pathinfo($doc->document_path, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png']))
                                        <img src="{{ asset('storage/' . $doc->document_path) }}" alt="{{ $doc->document_type }}" class="img-fluid rounded fullscreen-img" style="max-height: 150px; cursor: pointer;" title="Click to view full screen">
                                    @else
                                        <a href="{{ asset('storage/' . $doc->document_path) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>View PDF
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Selfie --}}
        @if($seller->selfie_path)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Selfie Verification</h5>
            </div>
            <div class="card-body text-center">
                <img src="{{ asset('storage/' . $seller->selfie_path) }}" alt="Selfie" class="img-fluid rounded fullscreen-img" style="max-height: 300px; cursor: pointer;" title="Click to view full screen">
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar Actions --}}
    <div class="col-md-4">
        {{-- Business Name Management --}}
        @if($seller->seller_type === 'business')
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-building me-1"></i>Auction Business Name</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.auction-sellers.update-name', $seller) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Business Name for Auctions</label>
                        <input type="text" name="auction_business_name" class="form-control"
                               value="{{ old('auction_business_name', $seller->auction_business_name ?? ($seller->seller->business_name ?? '')) }}"
                               placeholder="Enter auction business name" required>
                        <small class="text-muted d-block mt-1">
                            @if($seller->isSynced())
                                ToyShop name: <strong>{{ $seller->seller->business_name ?? 'N/A' }}</strong>
                            @else
                                This name will be displayed on auction listings.
                            @endif
                        </small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-pencil me-1"></i>Update Business Name
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Actions --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($seller->is_suspended)
                        <div class="alert alert-warning mb-3">
                            <strong>Status:</strong> Suspended<br>
                            @if($seller->suspended_at)
                                <small>Suspended on: {{ $seller->suspended_at->format('M d, Y h:i A') }}</small>
                            @endif
                            @if($seller->suspension_reason)
                                <br><br><strong>Reason:</strong><br>
                                <small>{{ $seller->suspension_reason }}</small>
                            @endif
                            @if($seller->suspendedByUser)
                                <br><small>Suspended by: {{ $seller->suspendedByUser->name }}</small>
                            @endif
                        </div>
                        <form action="{{ route('admin.auction-sellers.activate', $seller) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Activate this auction seller?')">
                                <i class="bi bi-play-circle me-1"></i>Activate Seller
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#suspendModal">
                            <i class="bi bi-pause-circle me-1"></i>Suspend Seller
                        </button>
                    @endif

                    <a href="{{ route('admin.auction-verifications.show', $seller) }}" class="btn btn-outline-info w-100">
                        <i class="bi bi-file-earmark-check me-1"></i>View Verification Details
                    </a>

                    @if($seller->isSynced() && $seller->seller)
                        <a href="{{ route('admin.sellers.show', $seller->seller->id) }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-shop me-1"></i>View ToyShop Profile
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Suspend Modal --}}
@if(!$seller->is_suspended)
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.auction-sellers.suspend', $seller) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Suspend Auction Seller</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Suspending this auction seller will prevent them from creating new auction listings. Existing active auctions will continue until they end.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Suspension Reason <span class="text-danger">*</span></label>
                        <textarea name="suspension_reason" class="form-control" rows="4" placeholder="Provide a reason for suspending this auction seller..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Suspend Seller</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Fullscreen Image Overlay --}}
@endsection

@push('styles')
<style>
    .img-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.95);
        z-index: 99999;
        align-items: center;
        justify-content: center;
        cursor: zoom-out;
    }
    .img-overlay.active { display: flex; }
    .img-overlay img {
        max-width: 92vw;
        max-height: 92vh;
        object-fit: contain;
        border-radius: 6px;
        box-shadow: 0 0 40px rgba(0,0,0,0.5);
        transition: transform 0.15s ease;
    }
    .img-overlay .overlay-close {
        position: fixed;
        top: 18px;
        right: 24px;
        z-index: 100000;
        background: rgba(255,255,255,0.15);
        border: none;
        color: #fff;
        font-size: 2rem;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(4px);
        transition: background 0.2s;
    }
    .img-overlay .overlay-close:hover { background: rgba(255,255,255,0.3); }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var overlay = document.createElement('div');
    overlay.className = 'img-overlay';
    overlay.innerHTML = '<button class="overlay-close" title="Close">&times;</button><img src="" alt="Full View">';
    document.body.appendChild(overlay);

    var overlayImg = overlay.querySelector('img');
    var scale = 1;

    document.querySelectorAll('.fullscreen-img').forEach(function (img) {
        img.addEventListener('click', function () {
            overlayImg.src = this.src;
            scale = 1;
            overlayImg.style.transform = 'scale(1)';
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    });

    function closeOverlay() {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
        overlayImg.src = '';
        scale = 1;
    }

    overlay.querySelector('.overlay-close').addEventListener('click', function (e) { e.stopPropagation(); closeOverlay(); });
    overlay.addEventListener('click', function (e) { if (e.target === overlay) closeOverlay(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && overlay.classList.contains('active')) closeOverlay(); });
    overlay.addEventListener('wheel', function (e) {
        e.preventDefault();
        scale += e.deltaY > 0 ? -0.15 : 0.15;
        scale = Math.max(0.3, Math.min(5, scale));
        overlayImg.style.transform = 'scale(' + scale + ')';
    }, { passive: false });
});
</script>
@endpush
