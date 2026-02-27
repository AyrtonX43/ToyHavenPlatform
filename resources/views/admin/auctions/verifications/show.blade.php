@extends('layouts.admin')

@section('title', 'Review Verification #' . $verification->id . ' - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="{{ route('admin.auction-verifications.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Verifications
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <div class="d-flex justify-content-between align-items-center mb-6">
                    <h1 class="text-2xl font-bold mb-0">Verification #{{ $verification->id }}</h1>
                    <span class="badge bg-{{ $verification->status === 'approved' ? 'success' : ($verification->status === 'pending' ? 'warning' : 'danger') }} fs-5">
                        {{ ucfirst(str_replace('_', ' ', $verification->status)) }}
                    </span>
                </div>

                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header fw-bold">User Information</div>
                            <div class="card-body">
                                <p><strong>Name:</strong> {{ $verification->user->name }}</p>
                                <p><strong>Email:</strong> {{ $verification->user->email }}</p>
                                <p><strong>Phone:</strong> {{ $verification->phone }}</p>
                                <p><strong>Address:</strong> {{ $verification->address }}</p>
                                <p><strong>Seller Type:</strong> <span class="badge bg-{{ $verification->seller_type === 'business' ? 'success' : 'primary' }}">{{ ucfirst($verification->seller_type) }}</span></p>
                                <p class="mb-0"><strong>Submitted:</strong> {{ $verification->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header fw-bold">Selfie Photo</div>
                            <div class="card-body text-center">
                                @if($verification->selfie_path)
                                    <img src="{{ asset('storage/' . $verification->selfie_path) }}" alt="Selfie" class="img-fluid rounded fullscreen-img" style="max-height: 300px; cursor: pointer;" title="Click to view full screen">
                                @else
                                    <p class="text-muted">No selfie uploaded</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <h4 class="fw-bold mb-3">Submitted Documents</h4>
                <div class="row g-3 mb-5">
                    @foreach($verification->documents as $doc)
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold small">{{ \App\Models\AuctionSellerVerification::documentLabel($doc->document_type) }}</span>
                                    <span class="badge bg-{{ $doc->status === 'approved' ? 'success' : ($doc->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($doc->status) }}</span>
                                </div>
                                <div class="card-body text-center">
                                    @if(in_array(pathinfo($doc->document_path, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png']))
                                        <img src="{{ asset('storage/' . $doc->document_path) }}" alt="{{ $doc->document_type }}" class="img-fluid rounded fullscreen-img" style="max-height: 200px; cursor: pointer;" title="Click to view full screen">
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

                @if($verification->status === 'pending')
                    <hr>
                    <h4 class="fw-bold mb-3">Actions</h4>
                    <div class="d-flex gap-3 flex-wrap">
                        <form action="{{ route('admin.auction-verifications.approve', $verification) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Approve this verification?')">
                                <i class="bi bi-check-circle me-1"></i>Approve
                            </button>
                        </form>

                        <button type="button" class="btn btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle me-1"></i>Reject
                        </button>

                        <button type="button" class="btn btn-warning btn-lg" data-bs-toggle="modal" data-bs-target="#resubmitModal">
                            <i class="bi bi-arrow-repeat me-1"></i>Request Resubmission
                        </button>
                    </div>

                    <div class="modal fade" id="rejectModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('admin.auction-verifications.reject', $verification) }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Reject Verification</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label class="form-label fw-semibold">Rejection Reason</label>
                                        <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Reject</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="resubmitModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('admin.auction-verifications.resubmission', $verification) }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Request Resubmission</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label class="form-label fw-semibold">Feedback / What needs to be fixed</label>
                                        <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-warning">Request Resubmission</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif

                @if($verification->rejection_reason && $verification->status !== 'pending')
                    <div class="alert alert-{{ $verification->status === 'rejected' ? 'danger' : 'warning' }} mt-4">
                        <strong>{{ $verification->status === 'rejected' ? 'Rejection' : 'Resubmission' }} Reason:</strong>
                        {{ $verification->rejection_reason }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
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
    .img-overlay.active {
        display: flex;
    }
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
        line-height: 1;
        backdrop-filter: blur(4px);
        transition: background 0.2s;
    }
    .img-overlay .overlay-close:hover {
        background: rgba(255,255,255,0.3);
    }
    .img-overlay .overlay-hint {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        color: rgba(255,255,255,0.5);
        font-size: 0.85rem;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var overlay = document.createElement('div');
    overlay.className = 'img-overlay';
    overlay.innerHTML = '<button class="overlay-close" title="Close">&times;</button><img src="" alt="Full View"><span class="overlay-hint">Scroll to zoom &middot; Click or press Esc to close</span>';
    document.body.appendChild(overlay);

    var overlayImg = overlay.querySelector('img');
    var scale = 1;

    document.querySelectorAll('.fullscreen-img').forEach(function (img) {
        img.addEventListener('click', function () {
            overlayImg.src = this.src;
            overlayImg.alt = this.alt;
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

    overlay.querySelector('.overlay-close').addEventListener('click', function (e) {
        e.stopPropagation();
        closeOverlay();
    });

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeOverlay();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay.classList.contains('active')) closeOverlay();
    });

    overlay.addEventListener('wheel', function (e) {
        e.preventDefault();
        scale += e.deltaY > 0 ? -0.15 : 0.15;
        scale = Math.max(0.3, Math.min(5, scale));
        overlayImg.style.transform = 'scale(' + scale + ')';
    }, { passive: false });
});
</script>
@endpush
