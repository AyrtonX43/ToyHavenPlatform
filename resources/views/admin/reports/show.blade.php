@extends('layouts.admin')

@section('title', 'Report Details - ToyHaven')
@section('page-title', 'Report Details #' . $report->id)

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">Report #{{ $report->id }}</h4>
                        <p class="text-muted mb-0">Reported by: {{ $report->reporter->name ?? 'N/A' }} on {{ $report->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    <div>
                        <span class="badge bg-{{ $report->status === 'resolved' ? 'success' : ($report->status === 'dismissed' ? 'secondary' : ($report->status === 'reviewed' ? 'info' : 'warning')) }} fs-6">
                            {{ ucfirst($report->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Report Information</h5>
            </div>
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
                    <hr>
                    <h6>Reported Item:</h6>
                    @if($report->reportable_type === 'App\Models\Product')
                        <div class="card bg-light">
                            <div class="card-body">
                                <strong>Product:</strong> {{ $report->reportable->name }}<br>
                                <strong>Seller:</strong> {{ $report->reportable->seller->business_name ?? 'N/A' }}<br>
                                <strong>Price:</strong> ₱{{ number_format($report->reportable->price, 2) }}<br>
                                <a href="{{ route('admin.products.show', $report->reportable_id) }}" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="bi bi-box-seam"></i> View Product
                                </a>
                            </div>
                        </div>
                    @elseif($report->reportable_type === 'App\Models\Seller')
                        <div class="card bg-light">
                            <div class="card-body">
                                <strong>Business Name:</strong> {{ $report->reportable->business_name }}<br>
                                <strong>Owner:</strong> {{ $report->reportable->user->name ?? 'N/A' }}<br>
                                <strong>Status:</strong> 
                                <span class="badge bg-{{ $report->reportable->verification_status === 'approved' ? 'success' : 'warning' }}">
                                    {{ ucfirst($report->reportable->verification_status) }}
                                </span><br>
                                <a href="{{ route('admin.sellers.show', $report->reportable_id) }}" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="bi bi-shop"></i> View Seller
                                </a>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="alert alert-warning">
                        The reported item has been deleted.
                    </div>
                @endif

                @if($report->evidence && count($report->evidence) > 0)
                    <hr>
                    <h6>Evidence:</h6>
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
            <div class="card-header">
                <h5 class="mb-0">Admin Notes</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $report->admin_notes }}</p>
                @if($report->reviewedBy)
                    <small class="text-muted">Reviewed by {{ $report->reviewedBy->name }} on {{ $report->reviewed_at->format('M d, Y h:i A') }}</small>
                @endif
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Reporter Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Name:</strong> {{ $report->reporter->name ?? 'N/A' }}<br>
                        <strong>Email:</strong> {{ $report->reporter->email ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Reported On:</strong> {{ $report->created_at->format('M d, Y h:i A') }}<br>
                        <strong>User ID:</strong> #{{ $report->reporter_id }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <p><strong>Current Status:</strong><br>
                    <span class="badge bg-{{ $report->status === 'resolved' ? 'success' : ($report->status === 'dismissed' ? 'secondary' : ($report->status === 'reviewed' ? 'info' : 'warning')) }} fs-6">
                        {{ ucfirst($report->status) }}
                    </span>
                </p>

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
                    <hr>
                    <h6 class="mb-3">Quick Actions</h6>
                    @if($report->reportable_type === 'App\Models\User' && $report->reportable)
                        @if(!$report->reportable->is_banned)
                            <button type="button" class="btn btn-danger w-100 mb-2" data-bs-toggle="modal" data-bs-target="#banFromReportModal">
                                <i class="bi bi-x-circle me-1"></i> Ban User
                            </button>
                        @else
                            <div class="alert alert-info mb-2">
                                <small>User is already banned</small>
                            </div>
                        @endif
                    @elseif($report->reportable_type === 'App\Models\Seller' && $report->reportable)
                        @if($report->reportable->is_active)
                            <button type="button" class="btn btn-warning w-100 mb-2" data-bs-toggle="modal" data-bs-target="#suspendFromReportModal">
                                <i class="bi bi-pause-circle me-1"></i> Suspend Seller
                            </button>
                        @else
                            <div class="alert alert-info mb-2">
                                <small>Seller is already suspended</small>
                            </div>
                        @endif
                    @endif
                @else
                    <div class="alert alert-info">
                        This report has been {{ $report->status }}.
                    </div>
                @endif

                <hr>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary w-100">Back to Reports</a>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.reports.review', $report->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Mark as Reviewed</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Admin Notes (Optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="Add any notes about this review..."></textarea>
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

<!-- Resolve Modal -->
<div class="modal fade" id="resolveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.reports.resolve', $report->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Resolve Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Admin Notes (Optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="Add notes about how this was resolved..."></textarea>
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

<!-- Dismiss Modal -->
<div class="modal fade" id="dismissModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.reports.dismiss', $report->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Dismiss Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to dismiss this report?</p>
                    <div class="mb-3">
                        <label class="form-label">Admin Notes (Optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="Add notes about why this is being dismissed..."></textarea>
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

@if($report->reportable_type === 'App\Models\User' && $report->reportable && !$report->reportable->is_banned)
<!-- Ban User from Report Modal -->
<div class="modal fade" id="banFromReportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.users.ban', $report->reportable_id) }}" method="POST">
                @csrf
                <input type="hidden" name="report_id" value="{{ $report->id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Ban User from Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This action will permanently ban this user from the platform due to Report #{{ $report->id }}.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ban Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="4" placeholder="Please provide a detailed reason for banning this user..." required>{{ 'Report #' . $report->id . ': ' . $report->reason . '. ' . ($report->description ?? '') }}</textarea>
                        <small class="text-muted">This reason will be sent to the user via email.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Ban User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if($report->reportable_type === 'App\Models\Seller' && $report->reportable && $report->reportable->is_active)
<!-- Suspend Seller from Report Modal -->
<div class="modal fade" id="suspendFromReportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.sellers.suspend', $report->reportable_id) }}" method="POST">
                @csrf
                <input type="hidden" name="report_id" value="{{ $report->id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Suspend Seller from Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This action will suspend this business account and ban the user due to Report #{{ $report->id }}.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Suspension Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="4" placeholder="Please provide a detailed reason for suspending this business account..." required>{{ 'Report #' . $report->id . ': ' . $report->reason . '. ' . ($report->description ?? '') }}</textarea>
                        <small class="text-muted">This reason will be sent to the seller via email.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Suspend Seller</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
