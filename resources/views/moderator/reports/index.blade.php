@extends('layouts.admin-new')

@section('title', 'Trade Reports - Moderator')
@section('page-title', 'Trade Reports')

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter Reports</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('moderator.reports.index') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Reporter name, reason...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="dismissed" {{ request('status') == 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Report Type</label>
                <select name="type" class="form-select">
                    <option value="">All</option>
                    <option value="fake" {{ request('type') == 'fake' ? 'selected' : '' }}>Fake</option>
                    <option value="inappropriate" {{ request('type') == 'inappropriate' ? 'selected' : '' }}>Inappropriate</option>
                    <option value="scam" {{ request('type') == 'scam' ? 'selected' : '' }}>Scam</option>
                    <option value="harassment" {{ request('type') == 'harassment' ? 'selected' : '' }}>Harassment</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Filter</button>
                <a href="{{ route('moderator.reports.index') }}" class="btn btn-outline-secondary ms-2">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-flag me-2"></i>Trade Reports ({{ $reports->total() }})</h5>
        <small class="text-muted">Reports on trades and trade listings only.</small>
    </div>
    <div class="card-body p-0">
        @if($reports->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Reported Item</th>
                        <th>Reporter</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr>
                        <td class="align-middle">#{{ $report->id }}</td>
                        <td class="align-middle"><span class="badge bg-info">{{ ucfirst($report->report_type) }}</span></td>
                        <td class="align-middle">
                            @if($report->reportable)
                                @if($report->reportable_type === 'App\Models\Trade')
                                    <a href="{{ route('moderator.trades.show', $report->reportable_id) }}">Trade #{{ $report->reportable_id }}</a>
                                @elseif($report->reportable_type === 'App\Models\TradeListing')
                                    <a href="{{ route('moderator.trade-listings.show', $report->reportable_id) }}">Listing #{{ $report->reportable_id }}</a>
                                @else
                                    {{ Str::limit($report->reportable->title ?? $report->reportable->name ?? '—', 30) }}
                                @endif
                            @else
                                <span class="text-muted">Item Deleted</span>
                            @endif
                        </td>
                        <td class="align-middle">{{ $report->reporter->name ?? 'N/A' }}</td>
                        <td class="align-middle">{{ Str::limit($report->reason, 30) }}</td>
                        <td class="align-middle">
                            <span class="badge bg-{{ $report->status === 'resolved' ? 'success' : ($report->status === 'dismissed' ? 'secondary' : ($report->status === 'reviewed' ? 'info' : 'warning')) }}">
                                {{ ucfirst($report->status) }}
                            </span>
                        </td>
                        <td class="align-middle"><small>{{ $report->created_at->format('M d, Y') }}</small></td>
                        <td class="align-middle text-end">
                            <a href="{{ route('moderator.reports.show', $report->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i> View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $reports->withQueryString()->links() }}</div>
        @else
        <div class="p-5 text-center text-muted">
            <i class="bi bi-inbox display-4 d-block mb-2"></i>
            No trade reports found.
        </div>
        @endif
    </div>
</div>
@endsection
