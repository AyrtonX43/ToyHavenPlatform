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
{{-- Fullscreen Image Viewer Modal --}}
<div class="modal fade" id="fullscreenImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen" style="margin: 0; max-width: 100%;">
        <div class="modal-content" style="background: rgba(0, 0, 0, 0.92); border: none; border-radius: 0;">
            <div class="modal-header border-0 position-absolute w-100" style="z-index: 10; top: 0;">
                <button type="button" class="btn-close btn-close-white ms-auto me-2 mt-2" data-bs-dismiss="modal" aria-label="Close" style="font-size: 1.25rem; opacity: 0.8; filter: drop-shadow(0 0 2px rgba(0,0,0,0.5));"></button>
            </div>
            <div class="modal-body d-flex align-items-center justify-content-center p-0" style="overflow: auto;">
                <img id="fullscreenImage" src="" alt="Full Screen View" style="max-width: 95vw; max-height: 95vh; object-fit: contain; transition: transform 0.2s ease;" class="rounded shadow">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('fullscreenImageModal');
    const fullscreenImg = document.getElementById('fullscreenImage');
    let scale = 1;

    document.querySelectorAll('.fullscreen-img').forEach(function (img) {
        img.addEventListener('click', function () {
            fullscreenImg.src = this.src;
            fullscreenImg.alt = this.alt;
            scale = 1;
            fullscreenImg.style.transform = 'scale(1)';
            new bootstrap.Modal(modal).show();
        });
    });

    modal.addEventListener('wheel', function (e) {
        e.preventDefault();
        scale += e.deltaY > 0 ? -0.1 : 0.1;
        scale = Math.max(0.3, Math.min(5, scale));
        fullscreenImg.style.transform = 'scale(' + scale + ')';
    }, { passive: false });

    modal.addEventListener('hidden.bs.modal', function () {
        fullscreenImg.src = '';
        scale = 1;
    });
});
</script>
@endpush
