@extends('layouts.admin-new')

@section('title', 'Auction Listings')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Auction Listings</h1>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-3">
        <a href="{{ route('admin.auction-listings.index', ['status' => 'pending_approval']) }}" class="btn btn-sm {{ request('status') === 'pending_approval' ? 'btn-warning' : 'btn-outline-warning' }}">Pending Approval</a>
        <a href="{{ route('admin.auction-listings.index', ['status' => 'active']) }}" class="btn btn-sm {{ request('status') === 'active' ? 'btn-success' : 'btn-outline-success' }}">Active</a>
        <a href="{{ route('admin.auction-listings.index', ['status' => 'draft']) }}" class="btn btn-sm {{ request('status') === 'draft' ? 'btn-secondary' : 'btn-outline-secondary' }}">Draft</a>
        <a href="{{ route('admin.auction-listings.index', ['status' => 'ended']) }}" class="btn btn-sm {{ request('status') === 'ended' ? 'btn-info' : 'btn-outline-info' }}">Ended</a>
        <a href="{{ route('admin.auction-listings.index') }}" class="btn btn-sm btn-outline-dark">All</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Seller</th>
                        <th>Starting Bid</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($listings as $l)
                        <tr>
                            <td>#{{ $l->id }}</td>
                            <td><strong>{{ Str::limit($l->title, 40) }}</strong></td>
                            <td>
                                {{ $l->user?->name }}<br>
                                <small class="text-muted">{{ $l->user?->email }}</small>
                            </td>
                            <td>₱{{ number_format($l->starting_bid, 2) }}</td>
                            <td>
                                @php
                                    $badge = match($l->status) {
                                        'draft' => 'secondary',
                                        'pending_approval' => 'warning',
                                        'active' => 'success',
                                        'ended' => 'info',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $badge }}">{{ str_replace('_', ' ', ucfirst($l->status)) }}</span>
                            </td>
                            <td>{{ $l->updated_at->format('M d, Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.auction-listings.show', $l) }}" class="btn btn-sm btn-outline-primary">View</a>
                                @if($l->status === 'pending_approval')
                                    <form action="{{ route('admin.auction-listings.approve', $l) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $l->id }}">Reject</button>
                                @endif
                            </td>
                        </tr>
                        @if($l->status === 'pending_approval')
                        <div class="modal fade" id="rejectModal{{ $l->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.auction-listings.reject', $l) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Reject Listing: {{ Str::limit($l->title, 30) }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <label class="form-label">Feedback (required)</label>
                                            <textarea name="feedback" class="form-control" rows="4" required placeholder="Explain what needs to be corrected..."></textarea>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">Reject Listing</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No auction listings found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $listings->withQueryString()->links() }}</div>
</div>
@endsection
