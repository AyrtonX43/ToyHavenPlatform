@extends('layouts.admin-new')

@section('title', 'Trades - Moderator')
@section('page-title', 'Trade Management')

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter Trades</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('moderator.trades.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending_shipping" {{ request('status') == 'pending_shipping' ? 'selected' : '' }}>Pending Shipping</option>
                    <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="disputed" {{ request('status') == 'disputed' ? 'selected' : '' }}>Disputed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search by listing title...">
            </div>
            <div class="col-md-5 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Filter</button>
                <a href="{{ route('moderator.trades.index') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>Trades ({{ $trades->total() }})</h5>
        <small class="text-muted">View and monitor active trades. Disputed trades can be resolved from the trade detail view.</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Listing</th>
                        <th>Parties</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trades as $trade)
                    <tr>
                        <td class="align-middle">{{ $trade->id }}</td>
                        <td class="align-middle">{{ Str::limit($trade->tradeListing->title ?? '—', 50) }}</td>
                        <td class="align-middle">
                            {{ $trade->initiator->name ?? '—' }} ↔ {{ $trade->participant->name ?? '—' }}
                        </td>
                        <td class="align-middle">
                            @php
                                $statusClass = match($trade->status) {
                                    'completed' => 'bg-success',
                                    'disputed' => 'bg-danger',
                                    'cancelled' => 'bg-secondary',
                                    default => 'bg-warning text-dark',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $trade->getStatusLabel() }}</span>
                        </td>
                        <td class="align-middle"><small>{{ $trade->created_at->format('M d, Y') }}</small></td>
                        <td class="align-middle text-end">
                            <a href="{{ route('moderator.trades.show', $trade->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                            No trades found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $trades->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
