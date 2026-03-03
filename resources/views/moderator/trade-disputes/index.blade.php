@extends('layouts.admin-new')

@section('title', 'Trade Disputes - Moderator')
@section('page-title', 'Trade Disputes')

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter Disputes</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('moderator.trade-disputes.index') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="investigating" {{ request('status') == 'investigating' ? 'selected' : '' }}>Investigating</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Filter</button>
                <a href="{{ route('moderator.trade-disputes.index') }}" class="btn btn-outline-secondary ms-2">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Trade Disputes ({{ $disputes->total() }})</h5>
        <small class="text-muted">Assign and resolve trade disputes. Admin can override any resolution.</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Trade</th>
                        <th>Reporter</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($disputes as $dispute)
                    <tr>
                        <td class="align-middle">{{ $dispute->id }}</td>
                        <td class="align-middle">
                            <a href="{{ route('moderator.trades.show', $dispute->trade_id) }}">Trade #{{ $dispute->trade_id }}</a>
                            <br><small class="text-muted">{{ Str::limit($dispute->trade->tradeListing->title ?? '—', 40) }}</small>
                        </td>
                        <td class="align-middle">{{ $dispute->reporter->name ?? '—' }}</td>
                        <td class="align-middle">{{ $dispute->getTypeLabel() }}</td>
                        <td class="align-middle">
                            @php
                                $statusClass = match($dispute->status) {
                                    'open' => 'bg-warning text-dark',
                                    'investigating' => 'bg-info',
                                    'resolved', 'closed' => 'bg-success',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $dispute->getStatusLabel() }}</span>
                        </td>
                        <td class="align-middle">{{ $dispute->assignedTo->name ?? '—' }}</td>
                        <td class="align-middle"><small>{{ $dispute->created_at->format('M d, Y H:i') }}</small></td>
                        <td class="align-middle text-end">
                            <a href="{{ route('moderator.trade-disputes.show', $dispute) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                            No trade disputes found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $disputes->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
