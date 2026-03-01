@extends('layouts.admin-new')

@section('title', 'Report Management - ToyHaven')
@section('page-title', 'Report Management')

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filter Reports</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Reporter name, reason...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="dismissed" {{ request('status') == 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Report Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="fake" {{ request('type') == 'fake' ? 'selected' : '' }}>Fake</option>
                    <option value="inappropriate" {{ request('type') == 'inappropriate' ? 'selected' : '' }}>Inappropriate</option>
                    <option value="scam" {{ request('type') == 'scam' ? 'selected' : '' }}>Scam</option>
                    <option value="harassment" {{ request('type') == 'harassment' ? 'selected' : '' }}>Harassment</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Reportable Type</label>
                <select name="reportable_type" class="form-select">
                    <option value="">All</option>
                    <option value="App\Models\Product" {{ request('reportable_type') == 'App\Models\Product' ? 'selected' : '' }}>Product</option>
                    <option value="App\Models\Seller" {{ request('reportable_type') == 'App\Models\Seller' ? 'selected' : '' }}>Seller</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All Reports ({{ $reports->total() }})</h5>
    </div>
    <div class="card-body">
        @if($reports->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Reported Item</th>
                            <th>Reporter</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $report)
                            <tr>
                                <td>#{{ $report->id }}</td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst($report->report_type) }}</span>
                                </td>
                                <td>
                                    @if($report->reportable)
                                        @if($report->reportable_type === 'App\Models\Product')
                                            <a href="{{ route('admin.products.show', $report->reportable_id) }}">{{ Str::limit($report->reportable->name, 30) }}</a>
                                        @elseif($report->reportable_type === 'App\Models\Seller')
                                            <a href="{{ route('admin.sellers.show', $report->reportable_id) }}">{{ Str::limit($report->reportable->business_name, 30) }}</a>
                                        @endif
                                    @else
                                        <span class="text-muted">Item Deleted</span>
                                    @endif
                                </td>
                                <td>{{ $report->reporter->name ?? 'N/A' }}</td>
                                <td>{{ Str::limit($report->reason, 30) }}</td>
                                <td>
                                    <span class="badge bg-{{ $report->status === 'resolved' ? 'success' : ($report->status === 'dismissed' ? 'secondary' : ($report->status === 'reviewed' ? 'info' : 'warning')) }}">
                                        {{ ucfirst($report->status) }}
                                    </span>
                                </td>
                                <td>{{ $report->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.reports.show', $report->id) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $reports->links() }}</div>
        @else
            <div class="alert alert-info text-center">
                <p class="mb-0">No reports found.</p>
            </div>
        @endif
    </div>
</div>
@endsection
