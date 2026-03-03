@extends('layouts.admin-new')

@section('title', 'Report #' . $report->id . ' - Moderator')
@section('page-title', 'Report #' . $report->id)

@section('content')
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('moderator.dashboard') }}">Moderator</a></li>
        <li class="breadcrumb-item"><a href="{{ route('moderator.reports.index') }}">Trade Reports</a></li>
        <li class="breadcrumb-item active">Report #{{ $report->id }}</li>
    </ol>
</nav>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">Report #{{ $report->id }}</h4>
                        <p class="text-muted mb-0">Reported by: {{ $report->reporter->name ?? 'N/A' }} on {{ $report->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    <span class="badge bg-{{ $report->status === 'resolved' ? 'success' : ($report->status === 'dismissed' ? 'secondary' : ($report->status === 'reviewed' ? 'info' : 'warning')) }} fs-6">
                        {{ ucfirst($report->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Report Information</h5></div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Report Type:</strong><br>
                        <span class="badge bg-info">{{ ucfirst($report->report_type) }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Reason:</strong><br>
                        {{ $report->reason }}
                    </div>
                </div>
                @if($report->description)
                <div class="mb-3">
                    <strong>Description:</strong><br>
                    <p class="border p-3 rounded bg-light mb-0">{{ $report->description }}</p>
                </div>
                @endif
                @if($report->reportable)
                <hr><h6>Reported Item:</h6>
                @if($report->reportable_type === 'App\Models\Trade')
                <div class="card bg-light">
                    <div class="card-body">
                        <strong>Trade #{{ $report->reportable_id }}</strong><br>
                        <a href="{{ route('moderator.trades.show', $report->reportable_id) }}" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="bi bi-arrow-left-right me-1"></i> View Trade
                        </a>
                    </div>
                </div>
                @elseif($report->reportable_type === 'App\Models\TradeListing')
                <div class="card bg-light">
                    <div class="card-body">
                        <strong>Listing:</strong> {{ $report->reportable->title ?? 'Listing #' . $report->reportable_id }}<br>
                        <a href="{{ route('moderator.trade-listings.show', $report->reportable_id) }}" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="bi bi-card-list me-1"></i> View Listing
                        </a>
                    </div>
                </div>
                @endif
                @else
                <div class="alert alert-warning">The reported item has been deleted.</div>
                @endif
                @if($report->evidence && count($report->evidence) > 0)
                <hr><h6>Evidence:</h6>
                <div class="row">
                    @foreach($report->evidence as $evidence)
                    <div class="col-md-3 mb-2">
                        <a href="{{ asset('storage/' . $evidence) }}" target="_blank">
                            <img src="{{ asset('storage/' . $evidence) }}" class="img-thumbnail" style="max-height: 150px;">
                        </a>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @if($report->admin_notes)
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Notes</h5></div>
            <div class="card-body">
                <p class="mb-0">{{ $report->admin_notes }}</p>
                @if($report->reviewedBy)
                <small class="text-muted">By {{ $report->reviewedBy->name }} on {{ $report->reviewed_at->format('M d, Y h:i A') }}</small>
                @endif
            </div>
        </div>
        @endif
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Actions</h5></div>
            <div class="card-body">
                @if($report->status === 'pending')
                <button type="button" class="btn btn-info w-100 mb-2" data-bs-toggle="modal" data-bs-target="#reviewModal">
                    <i class="bi bi-check-circle me-1"></i> Mark as Reviewed
                </button>
                <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#resolveModal">
                    <i class="bi bi-check2-circle me-1"></i> Resolve Report
                </button>
                <button type="button" class="btn btn-secondary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#dismissModal">
                    <i class="bi bi-x-circle me-1"></i> Dismiss Report
                </button>
                @else
                <div class="alert alert-info">This report has been {{ $report->status }}.</div>
                @endif
                <hr>
                <a href="{{ route('moderator.reports.index') }}" class="btn btn-outline-secondary w-100">Back to Reports</a>
            </div>
        </div>
    </div>
</div>

@if($report->status === 'pending')
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('moderator.reports.review', $report->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Mark as Reviewed</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="Add notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Mark as Reviewed</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="resolveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('moderator.reports.resolve', $report->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Resolve Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="Add notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Resolve Report</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="dismissModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('moderator.reports.dismiss', $report->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Dismiss Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to dismiss this report?</p>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-secondary">Dismiss Report</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
