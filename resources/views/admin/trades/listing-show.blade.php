@extends('layouts.admin-new')
@section('title', 'Listing #' . $listing->id . ' - Admin')
@section('content')
@push('styles')
<style>
.admin-listing-img { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid #e2e8f0; }
.admin-listing-img:hover { border-color: #0891b2; }
#adminImageLightbox .modal-body img { max-width: 100%; max-height: 85vh; object-fit: contain; }
</style>
@endpush
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h4 mb-1">{{ $listing->title }}</h1>
            <p class="text-muted small mb-0">Listing #{{ $listing->id }} · {{ $listing->created_at->format('M j, Y H:i') }}</p>
        </div>
        <span class="badge bg-{{ $listing->status === 'pending_approval' ? 'warning' : ($listing->status === 'active' ? 'success' : 'secondary') }} fs-6">{{ $listing->getStatusLabel() }}</span>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="row">
        <div class="col-lg-8">
            {{-- Images --}}
            <div class="card mb-4">
                <div class="card-header fw-semibold">Images</div>
                <div class="card-body">
                    @php $thumbImg = $listing->getThumbnailImage(); @endphp
                    @if($listing->images->count() > 0)
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($listing->images as $img)
                        <img src="{{ asset('storage/' . $img->image_path) }}" alt="" class="admin-listing-img" data-src="{{ asset('storage/' . $img->image_path) }}" onclick="openAdminLightbox(this)">
                        @endforeach
                    </div>
                    <p class="small text-muted mt-2 mb-0">Click image to view full size</p>
                    @else
                    <p class="text-muted mb-0">No images</p>
                    @endif
                </div>
            </div>

            {{-- Details --}}
            <div class="card mb-4">
                <div class="card-header fw-semibold">Listing Details</div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" style="width:140px;">Title</td>
                            <td>{{ $listing->title }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Category</td>
                            <td>{{ $listing->categories->isNotEmpty() ? $listing->categories->pluck('name')->implode(', ') : ($listing->category->name ?? '-') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Brand</td>
                            <td>{{ $listing->brand ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Condition</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $listing->condition ?? 'N/A')) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Trade Type</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $listing->trade_type)) }}</td>
                        </tr>
                        @if($listing->cash_amount)
                        <tr>
                            <td class="text-muted">Cash Amount</td>
                            <td>₱{{ number_format($listing->cash_amount, 0) }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="text-muted">Description</td>
                            <td><div class="text-justify">{{ nl2br(e($listing->description)) }}</div></td>
                        </tr>
                        @if($listing->location)
                        <tr>
                            <td class="text-muted">Location</td>
                            <td>{{ $listing->location }}</td>
                        </tr>
                        @endif
                        @if($listing->meetup_radius_km)
                        <tr>
                            <td class="text-muted">Meetup Radius</td>
                            <td>{{ $listing->meetup_radius_km }} km</td>
                        </tr>
                        @endif
                        @if($listing->meet_up_references)
                        <tr>
                            <td class="text-muted">Meetup Notes</td>
                            <td>{{ $listing->meet_up_references }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- User --}}
            <div class="card mb-4">
                <div class="card-header fw-semibold">User</div>
                <div class="card-body">
                    <p class="mb-1"><strong>{{ $listing->user->name }}</strong></p>
                    <p class="small text-muted mb-0">{{ $listing->user->email ?? '' }}</p>
                </div>
            </div>

            {{-- Rejection reason (if rejected) --}}
            @if($listing->status === 'rejected' && $listing->rejection_reason)
            <div class="card mb-4 border-warning">
                <div class="card-header fw-semibold text-warning">Rejection Feedback</div>
                <div class="card-body">
                    <p class="mb-0">{{ $listing->rejection_reason }}</p>
                </div>
            </div>
            @endif

            {{-- Approve / Reject (pending only) --}}
            @if($listing->status === 'pending_approval')
            <div class="card mb-4">
                <div class="card-header fw-semibold">Actions</div>
                <div class="card-body">
                    <form action="{{ route('admin.trades.approve-listing', $listing->id) }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-success w-100"><i class="bi bi-check2-circle me-2"></i>Approve Listing</button>
                    </form>
                    <p class="small text-muted mb-2">Approving will send an email and in-app notification to the user.</p>
                    <hr>
                    <form action="{{ route('admin.trades.reject-listing', $listing->id) }}" method="POST" id="rejectForm">
                        @csrf
                        <label class="form-label fw-semibold">Rejection feedback <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control mb-2" rows="3" required placeholder="Provide feedback to the user (e.g. reason for rejection). This will be sent via email and notification."></textarea>
                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Reject this listing? Feedback will be sent to the user.');"><i class="bi bi-x-circle me-2"></i>Reject Listing</button>
                    </form>
                    <p class="small text-muted mt-2 mb-0">Rejecting will send an email and in-app notification with your feedback.</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

{{-- Image lightbox --}}
<div class="modal fade" id="adminImageLightbox" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title">Full image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img src="" alt="" id="adminLightboxImg" class="img-fluid">
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
function openAdminLightbox(el) {
    var img = document.getElementById('adminLightboxImg');
    if (img && el && el.dataset.src) {
        img.src = el.dataset.src;
        var modal = new bootstrap.Modal(document.getElementById('adminImageLightbox'));
        modal.show();
    }
}
</script>
@endpush
@endsection
