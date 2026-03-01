@extends('layouts.admin-new')

@section('title', 'Seller Details - ToyHaven')
@section('page-title', 'Seller Details: ' . $seller->business_name)

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">{{ $seller->business_name }}</h4>
                        <p class="text-muted mb-0">Owner: {{ $seller->user->name }} ({{ $seller->user->email }})</p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-{{ $seller->verification_status === 'approved' ? 'success' : ($seller->verification_status === 'rejected' ? 'danger' : 'warning') }} fs-6">
                            {{ ucfirst($seller->verification_status) }}
                        </span>
                        @if(!$seller->is_active)
                            <span class="badge bg-secondary fs-6">Suspended</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-primary">{{ $stats['total_products'] }}</h3>
                <small class="text-muted">Total Products</small>
                <div class="mt-2">
                    <span class="badge bg-success">{{ $stats['active_products'] }} Active</span>
                    <span class="badge bg-warning">{{ $stats['pending_products'] }} Pending</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-success">{{ $stats['total_orders'] }}</h3>
                <small class="text-muted">Total Orders</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-info">₱{{ number_format($stats['total_revenue'], 2) }}</h3>
                <small class="text-muted">Total Revenue</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-warning">
                    <i class="bi bi-star-fill"></i> {{ number_format($seller->rating, 1) }}
                </h3>
                <small class="text-muted">{{ $seller->total_reviews }} Reviews</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Business Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Business Name:</strong><br>
                        {{ $seller->business_name }}
                    </div>
                    <div class="col-md-6">
                        <strong>Registration Type:</strong><br>
                        @if($seller->is_verified_shop)
                            <span class="badge bg-info fs-6">
                                <i class="bi bi-shield-check me-1"></i> Full Trusted Verification Shop
                            </span>
                        @else
                            <span class="badge bg-secondary fs-6">
                                <i class="bi bi-shop me-1"></i> Basic Seller
                            </span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Business Slug:</strong><br>
                        <code>{{ $seller->business_slug }}</code>
                    </div>
                    <div class="col-md-6">
                        <strong>Verification Status:</strong><br>
                        <span class="badge bg-{{ $seller->verification_status === 'approved' ? 'success' : ($seller->verification_status === 'rejected' ? 'danger' : 'warning') }} fs-6">
                            {{ ucfirst($seller->verification_status) }}
                        </span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Email:</strong><br>
                        {{ $seller->email ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Phone:</strong><br>
                        {{ $seller->phone ?? 'N/A' }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <strong>Address:</strong><br>
                        {{ $seller->address ?? 'N/A' }}, {{ $seller->city ?? '' }}, {{ $seller->province ?? '' }} {{ $seller->postal_code ?? '' }}
                    </div>
                </div>
                @if($seller->description)
                <div class="row">
                    <div class="col-12">
                        <strong>Description:</strong><br>
                        <p class="mb-0">{{ $seller->description }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($seller->documents->count() > 0)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Verification Documents</h5>
                <span class="badge bg-secondary">{{ $seller->documents->count() }} Document(s)</span>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Registration Type:</strong> 
                    @if($seller->is_verified_shop)
                        Full Trusted Verification Shop (Requires: ID Document, Business Permit, Bank Account Document)
                    @else
                        Basic Seller (Requires: ID Document only)
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Document Type</th>
                                <th>Status</th>
                                <th>Uploaded Date</th>
                                <th>View/Download</th>
                                <th>Manage Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($seller->documents as $document)
                                <tr>
                                    <td>
                                        <strong>{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</strong>
                                        @if($document->document_type === 'id')
                                            <br><small class="text-muted">Government-issued ID</small>
                                        @elseif($document->document_type === 'business_permit')
                                            <br><small class="text-muted">Business Registration Permit</small>
                                        @elseif($document->document_type === 'bank_account')
                                            <br><small class="text-muted">Bank Account Verification</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($document->status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($document->status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                            @if($document->rejection_reason)
                                                <br><small class="text-danger d-block mt-1">{{ Str::limit($document->rejection_reason, 50) }}</small>
                                            @endif
                                        @else
                                            <span class="badge bg-warning">Pending Review</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $document->created_at->format('M d, Y') }}<br>
                                        <small class="text-muted">{{ $document->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        @if($document->document_path)
                                            <a href="{{ asset('storage/' . $document->document_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i> View
                                            </a>
                                            <a href="{{ asset('storage/' . $document->document_path) }}" download class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-download me-1"></i> Download
                                            </a>
                                        @else
                                            <span class="text-muted">Not available</span>
                                        @endif
                                    </td>
                                    <td style="min-width: 150px;">
                                        @if($document->status === 'pending')
                                            <div class="d-flex flex-column gap-1">
                                                <form action="{{ route('admin.sellers.documents.approve', ['sellerId' => $seller->id, 'documentId' => $document->id]) }}" method="POST" class="w-100">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success w-100" onclick="return confirm('Approve this document?')">
                                                        <i class="bi bi-check-circle me-1"></i> Approve
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectDocumentModal{{ $document->id }}">
                                                    <i class="bi bi-x-circle me-1"></i> Reject
                                                </button>
                                            </div>
                                        @elseif($document->status === 'approved')
                                            <div class="text-center">
                                                <span class="badge bg-success fs-6 py-2 px-3">
                                                    <i class="bi bi-check-circle me-1"></i> Approved
                                                </span>
                                            </div>
                                        @elseif($document->status === 'rejected')
                                            <div class="text-center mb-2">
                                                <span class="badge bg-danger fs-6 py-2 px-3">
                                                    <i class="bi bi-x-circle me-1"></i> Rejected
                                                </span>
                                            </div>
                                            <form action="{{ route('admin.sellers.documents.approve', ['sellerId' => $seller->id, 'documentId' => $document->id]) }}" method="POST" class="w-100">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success w-100" onclick="return confirm('Approve this document?')">
                                                    <i class="bi bi-check-circle me-1"></i> Approve
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <!-- Reject Document Modal -->
                                        <div class="modal fade" id="rejectDocumentModal{{ $document->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('admin.sellers.documents.reject', ['sellerId' => $seller->id, 'documentId' => $document->id]) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Reject Document - Invalid Document</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="alert alert-danger">
                                                                <i class="bi bi-exclamation-triangle me-2"></i>
                                                                <strong>Warning:</strong> Rejecting this document will mark it as invalid. The seller will be notified that their required verification document is invalid and needs to be resubmitted.
                                                            </div>
                                                            <div class="alert alert-info">
                                                                <i class="bi bi-info-circle me-2"></i>
                                                                <strong>Document Type:</strong> {{ ucfirst(str_replace('_', ' ', $document->document_type)) }}
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                                                <textarea name="reason" class="form-control" rows="4" placeholder="Please provide a detailed reason why this document is invalid (e.g., document is unclear, expired, doesn't match business information, etc.)..." required>{{ old('reason', $document->rejection_reason ?? '') }}</textarea>
                                                                <small class="text-muted">This reason will be sent to the seller via email notification. They will be informed that their required document is invalid and needs to be resubmitted.</small>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">
                                                                <i class="bi bi-x-circle me-1"></i> Reject Document (Invalid)
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($seller->is_verified_shop && $seller->documents->count() < 4)
                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This seller registered as Full Trusted Verification Shop but is missing some required documents.
                        Expected: Business Registration (BIR 2303 + DTI/SEC), Brand Rights, Primary ID, Bank Document.
                    </div>
                @endif
            </div>
        </div>
        @else
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Verification Documents</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    No verification documents have been uploaded yet.
                </div>
            </div>
        </div>
        @endif

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Products</h5>
                <a href="{{ route('admin.products.index', ['seller' => $seller->id]) }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Status</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($seller->products->take(5) as $product)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.products.show', $product->id) }}">{{ $product->name }}</a>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $product->status === 'active' ? 'success' : ($product->status === 'pending' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($product->status) }}
                                        </span>
                                    </td>
                                    <td>₱{{ number_format($product->price, 2) }}</td>
                                    <td>{{ $product->stock_quantity }}</td>
                                    <td>{{ $product->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No products yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Orders</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($seller->orders->take(5) as $order)
                                <tr>
                                    <td>{{ $order->order_number }}</td>
                                    <td>{{ $order->user->name }}</td>
                                    <td>₱{{ number_format($order->total, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : 'warning' }}">
                                            {{ $order->getStatusLabel() }}
                                        </span>
                                    </td>
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No orders yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
                @php
                    $requiredDocsCount = $seller->is_verified_shop ? 4 : 2; // Verified: business_registration, brand_rights, id, bank_account; Basic: id, bank_account
                    $approvedDocsCount = $seller->documents->where('status', 'approved')->count();
                    $pendingDocsCount = $seller->documents->where('status', 'pending')->count();
                    $rejectedDocsCount = $seller->documents->where('status', 'rejected')->count();
                @endphp
                @if($seller->verification_status === 'pending' && $seller->documents->count() > 0)
                    <div class="alert alert-info mb-3">
                        <strong><i class="bi bi-info-circle me-1"></i>Document Status:</strong><br>
                        <small>
                            Approved: <span class="badge bg-success">{{ $approvedDocsCount }}</span> / {{ $requiredDocsCount }} required<br>
                            @if($pendingDocsCount > 0)
                                Pending: <span class="badge bg-warning">{{ $pendingDocsCount }}</span><br>
                            @endif
                            @if($rejectedDocsCount > 0)
                                Rejected: <span class="badge bg-danger">{{ $rejectedDocsCount }}</span><br>
                            @endif
                            @if($approvedDocsCount < $requiredDocsCount)
                                <span class="text-danger d-block mt-2"><i class="bi bi-exclamation-triangle me-1"></i>All required documents must be approved before seller approval.</span>
                            @else
                                <span class="text-success d-block mt-2"><i class="bi bi-check-circle me-1"></i>All required documents are approved. You can approve the seller.</span>
                            @endif
                        </small>
                    </div>
                @endif
                <div class="d-grid gap-2">
                    @if($seller->verification_status === 'pending')
                        <form action="{{ route('admin.sellers.approve', $seller->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100" 
                                @if($seller->documents->count() > 0 && $approvedDocsCount < $requiredDocsCount)
                                    disabled
                                    title="All required documents must be approved first"
                                @endif>
                                <i class="bi bi-check-circle me-1"></i> Approve Seller
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle me-1"></i> Reject Seller
                        </button>
                    @endif

                    @if($seller->is_active)
                        <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#suspendModal">
                            <i class="bi bi-pause-circle me-1"></i> Suspend Seller
                        </button>
                    @else
                        <div class="alert alert-warning mb-3">
                            <strong>Status:</strong> Suspended<br>
                            @if($seller->suspended_at)
                                <small>Suspended on: {{ $seller->suspended_at instanceof \Carbon\Carbon ? $seller->suspended_at->format('M d, Y h:i A') : \Carbon\Carbon::parse($seller->suspended_at)->format('M d, Y h:i A') }}</small>
                            @endif
                            @if($seller->suspension_reason)
                                <br><br><strong>Reason:</strong><br>
                                <small>{{ $seller->suspension_reason }}</small>
                            @endif
                            @if($seller->suspendedBy)
                                <br><small>Suspended by: {{ $seller->suspendedBy->name }}</small>
                            @endif
                            @if($seller->relatedReport)
                                <br><small><a href="{{ route('admin.reports.show', $seller->related_report_id) }}">View Related Report</a></small>
                            @endif
                        </div>
                        <form action="{{ route('admin.sellers.activate', $seller->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-play-circle me-1"></i> Activate Seller
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        @if($seller->reviews->count() > 0)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Reviews</h5>
            </div>
            <div class="card-body">
                @foreach($seller->reviews->take(3) as $review)
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between mb-1">
                            <strong>{{ $review->user->name }}</strong>
                            <div>
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $review->overall_rating ? '-fill' : '' }} text-warning"></i>
                                @endfor
                            </div>
                        </div>
                        <p class="mb-0 small">{{ Str::limit($review->review_text, 100) }}</p>
                        <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.sellers.reject', $seller->id) }}" method="POST" id="rejectForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Seller Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Note:</strong> The seller will be notified via email about this rejection.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Rejection Reason <span class="text-danger">*</span></label>
                        <select name="rejection_type" id="rejection_type" class="form-select" required>
                            <option value="">-- Select a reason --</option>
                            <option value="incomplete_documents">Incomplete or Missing Documents</option>
                            <option value="invalid_documents">Invalid or Unclear Documents</option>
                            <option value="business_info_mismatch">Business Information Mismatch</option>
                            <option value="suspicious_activity">Suspicious Activity Detected</option>
                            <option value="policy_violation">Policy Violation</option>
                            <option value="duplicate_account">Duplicate Account</option>
                            <option value="other">Other (Please specify below)</option>
                        </select>
                        <small class="text-muted">Choose a predefined reason or select "Other" to provide custom feedback.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Additional Feedback / Custom Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" id="rejection_reason" class="form-control" rows="4" placeholder="Please provide detailed feedback or reason for rejection..." required></textarea>
                        <small class="text-muted">This feedback will be sent to the seller via email.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Seller</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rejectionType = document.getElementById('rejection_type');
    const rejectionReason = document.getElementById('rejection_reason');
    
    const rejectionMessages = {
        'incomplete_documents': 'Your application has been rejected due to incomplete or missing required documents. Please ensure all required verification documents are uploaded and clearly visible.',
        'invalid_documents': 'Your application has been rejected because the submitted documents are invalid, unclear, or do not meet our verification requirements. Please submit clear, valid documents.',
        'business_info_mismatch': 'Your application has been rejected due to inconsistencies between the provided business information and the submitted documents. Please ensure all information matches your official business documents.',
        'suspicious_activity': 'Your application has been rejected due to suspicious activity detected during the verification process. Please contact support if you believe this is an error.',
        'policy_violation': 'Your application has been rejected due to a violation of our platform policies. Please review our terms and conditions before reapplying.',
        'duplicate_account': 'Your application has been rejected because a duplicate account already exists for this business. Please use your existing account or contact support.',
        'other': ''
    };
    
    rejectionType.addEventListener('change', function() {
        if (this.value && this.value !== 'other') {
            rejectionReason.value = rejectionMessages[this.value];
        } else if (this.value === 'other') {
            rejectionReason.value = '';
            rejectionReason.placeholder = 'Please provide a detailed reason for rejection...';
        }
    });
});
</script>

<!-- Suspend Seller Modal -->
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.sellers.suspend', $seller->id) }}" method="POST" id="suspendForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Suspend Business Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Suspending this seller will also ban their user account. They will not be able to access their account, create new listings, or receive new orders. Existing orders will continue to be processed. The seller will be notified via email.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Suspension Reason <span class="text-danger">*</span></label>
                        <select name="suspension_type" id="suspension_type" class="form-select" required>
                            <option value="">-- Select a reason --</option>
                            <option value="policy_violation">Policy Violation</option>
                            <option value="fraudulent_activity">Fraudulent Activity</option>
                            <option value="poor_product_quality">Poor Product Quality</option>
                            <option value="customer_complaints">Multiple Customer Complaints</option>
                            <option value="non_compliance">Non-Compliance with Platform Rules</option>
                            <option value="payment_issues">Payment or Transaction Issues</option>
                            <option value="inappropriate_content">Inappropriate Content or Behavior</option>
                            <option value="safety_concerns">Safety Concerns</option>
                            <option value="other">Other (Please specify below)</option>
                        </select>
                        <small class="text-muted">Choose a predefined reason or select "Other" to provide custom feedback.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Additional Feedback / Custom Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" id="suspension_reason" class="form-control" rows="4" placeholder="Please provide detailed feedback or reason for suspension..." required></textarea>
                        <small class="text-muted">This feedback will be sent to the seller via email.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Related Report (Optional)</label>
                        <select name="report_id" class="form-select">
                            <option value="">No related report</option>
                            @foreach(\App\Models\Report::where('reportable_type', 'App\Models\Seller')->where('reportable_id', $seller->id)->orderBy('created_at', 'desc')->get() as $report)
                                <option value="{{ $report->id }}">Report #{{ $report->id }} - {{ $report->report_type }} ({{ $report->created_at->format('M d, Y') }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Link this suspension to a specific report if applicable.</small>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const suspensionType = document.getElementById('suspension_type');
    const suspensionReason = document.getElementById('suspension_reason');
    
    const suspensionMessages = {
        'policy_violation': 'Your business account has been suspended due to a violation of our platform policies. Please review our terms and conditions and contact support if you have questions.',
        'fraudulent_activity': 'Your business account has been suspended due to suspected fraudulent activity. This is a serious matter and requires immediate attention. Please contact support for further information.',
        'poor_product_quality': 'Your business account has been suspended due to consistent reports of poor product quality or failure to meet product standards. Please review your product listings and quality control processes.',
        'customer_complaints': 'Your business account has been suspended due to multiple customer complaints regarding your products or services. We take customer satisfaction seriously and need to address these issues.',
        'non_compliance': 'Your business account has been suspended due to non-compliance with platform rules and regulations. Please review our seller guidelines and ensure full compliance.',
        'payment_issues': 'Your business account has been suspended due to payment or transaction-related issues. Please contact our finance team to resolve these matters.',
        'inappropriate_content': 'Your business account has been suspended due to inappropriate content or behavior that violates our community guidelines. Please review and remove any inappropriate content.',
        'safety_concerns': 'Your business account has been suspended due to safety concerns regarding your products or business practices. This requires immediate attention to ensure customer safety.',
        'other': ''
    };
    
    suspensionType.addEventListener('change', function() {
        if (this.value && this.value !== 'other') {
            suspensionReason.value = suspensionMessages[this.value];
        } else if (this.value === 'other') {
            suspensionReason.value = '';
            suspensionReason.placeholder = 'Please provide a detailed reason for suspension...';
        }
    });
});
</script>
@endsection
