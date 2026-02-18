@extends('layouts.admin')

@section('title', 'Trade Listings - Admin')
@section('page-title', 'Trade Listings')

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter Listings</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.trades.listings') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending Review</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending_trade" {{ request('status') == 'pending_trade' ? 'selected' : '' }}>Trade in Progress</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search by title or user...">
            </div>
            <div class="col-md-5 d-flex align-items-end gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Filter</button>
                <a href="{{ route('admin.trades.listings') }}" class="btn btn-outline-secondary">Clear</a>
                <a href="{{ route('admin.trades.listings', ['status' => 'pending_approval']) }}" class="btn btn-warning btn-sm"><i class="bi bi-hourglass-split me-1"></i> Pending</a>
                <a href="{{ route('admin.trades.listings', ['status' => 'active']) }}" class="btn btn-success btn-sm"><i class="bi bi-check2-square me-1"></i> Approved</a>
                <a href="{{ route('admin.trades.listings', ['status' => 'rejected']) }}" class="btn btn-danger btn-sm"><i class="bi bi-x-circle me-1"></i> Rejected</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>Trade Listings ({{ $listings->total() }})</h5>
        <small class="text-muted">Review and approve or reject new listings. Only approved (active) listings appear on the marketplace.</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px;">Image</th>
                        <th>Title</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($listings as $listing)
                    <tr>
                        <td class="align-middle">
                            @php
                                $firstImage = $listing->images->first();
                            @endphp
                            @if($firstImage)
                                <img src="{{ asset('storage/' . $firstImage->image_path) }}" alt="" class="rounded" style="width: 48px; height: 48px; object-fit: cover;">
                            @else
                                <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td class="align-middle">
                            <strong>{{ Str::limit($listing->title, 50) }}</strong>
                            @if($listing->category)
                                <br><small class="text-muted">{{ $listing->category->name }}</small>
                            @endif
                        </td>
                        <td class="align-middle">
                            <a href="{{ route('admin.users.show', $listing->user_id) }}">{{ $listing->user->name ?? '—' }}</a>
                            <br><small class="text-muted">{{ $listing->user->email ?? '' }}</small>
                        </td>
                        <td class="align-middle">
                            <span class="badge bg-info">{{ str_replace('_', ' ', ucfirst($listing->trade_type)) }}</span>
                        </td>
                        <td class="align-middle">
                            @php
                                $statusClass = match($listing->status) {
                                    'pending_approval' => 'bg-warning text-dark',
                                    'active' => 'bg-success',
                                    'rejected' => 'bg-danger',
                                    'cancelled', 'expired' => 'bg-secondary',
                                    default => 'bg-primary',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $listing->getStatusLabel() }}</span>
                        </td>
                        <td class="align-middle">
                            <small>{{ $listing->created_at->format('M d, Y H:i') }}</small>
                        </td>
                        <td class="align-middle text-end">
                            <div class="d-flex justify-content-end gap-1 flex-wrap">
                                <a href="{{ route('admin.trades.listings.show', $listing->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($listing->status === 'pending_approval')
                                    <form action="{{ route('admin.trades.approve-listing', $listing->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger" title="Reject" data-bs-toggle="modal" data-bs-target="#rejectModal" data-reject-url="{{ route('admin.trades.reject-listing', $listing->id) }}" data-listing-title="{{ e($listing->title) }}">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                            No listings found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $listings->withQueryString()->links() }}</div>
    </div>
</div>

<!-- Reject Modal -->
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

@push('scripts')
<script>
(function() {
    var modal = document.getElementById('rejectModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function(event) {
            var btn = event.relatedTarget;
            if (btn) {
                document.getElementById('rejectForm').action = btn.getAttribute('data-reject-url') || '';
                document.getElementById('rejectListingTitle').textContent = btn.getAttribute('data-listing-title') || 'Listing';
                document.getElementById('rejection_reason').value = '';
            }
        });
    }
})();
</script>
@endpush
@endsection
