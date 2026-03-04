@extends('layouts.admin-new')

@section('title', 'Auctions - Moderator')
@section('page-title', 'Auctions')

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('moderator.auctions.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                    <option value="live" {{ request('status') == 'live' ? 'selected' : '' }}>Live</option>
                    <option value="ended" {{ request('status') == 'ended' ? 'selected' : '' }}>Ended</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by title...">
            </div>
            <div class="col-md-5 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Filter</button>
                <a href="{{ route('moderator.auctions.index') }}" class="btn btn-outline-secondary">Clear</a>
                @if(auth()->user()->hasAuctionPermission('auctions_moderate'))
                    <a href="{{ route('moderator.auctions.index', ['status' => 'pending_approval']) }}" class="btn btn-warning btn-sm"><i class="bi bi-hourglass-split me-1"></i> Pending</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-hammer me-2"></i>Auctions ({{ $auctions->total() }})</h5>
        <small class="text-muted">View and moderate auction listings.</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Current Price</th>
                        <th>Bids</th>
                        <th>Status</th>
                        <th>Ends</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auctions as $auction)
                        <tr>
                            <td>{{ $auction->id }}</td>
                            <td>{{ Str::limit($auction->title, 40) }}</td>
                            <td>₱{{ number_format($auction->getCurrentPrice(), 2) }}</td>
                            <td>{{ $auction->bids_count ?? 0 }}</td>
                            <td>
                                <span class="badge bg-{{ $auction->status === 'live' ? 'success' : ($auction->status === 'ended' ? 'secondary' : ($auction->status === 'pending_approval' ? 'warning' : 'dark')) }}">
                                    {{ $auction->status }}
                                </span>
                            </td>
                            <td>{{ $auction->end_at?->format('M d, Y H:i') ?? 'TBD' }}</td>
                            <td>
                                <a href="{{ route('moderator.auctions.show', $auction) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No auctions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $auctions->links() }}</div>
    </div>
</div>
@endsection
