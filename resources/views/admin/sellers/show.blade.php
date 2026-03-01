@extends('layouts.admin-new')

@section('title', 'Seller Details - ToyHaven')
@section('page-title', 'Seller Details: ' . $seller->business_name)

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-2 fw-bold">
                            @if($seller->is_verified_shop)
                                <i class="bi bi-shield-check text-success me-2"></i>
                            @else
                                <i class="bi bi-shop text-primary me-2"></i>
                            @endif
                            {{ $seller->business_name }}
                        </h3>
                        <p class="text-muted mb-0">
                            <i class="bi bi-person-circle me-1"></i>
                            <strong>Owner:</strong> {{ $seller->user->name }} 
                            <span class="text-muted">|</span>
                            <i class="bi bi-envelope me-1"></i>{{ $seller->user->email }}
                        </p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-{{ $seller->verification_status === 'approved' ? 'success' : ($seller->verification_status === 'rejected' ? 'danger' : 'warning') }} fs-5 px-3 py-2 mb-2">
                            <i class="bi bi-{{ $seller->verification_status === 'approved' ? 'check-circle' : ($seller->verification_status === 'rejected' ? 'x-circle' : 'clock') }} me-1"></i>
                            {{ ucfirst($seller->verification_status) }}
                        </span>
                        @if(!$seller->is_active)
                            <br><span class="badge bg-secondary fs-6 px-3 py-2">
                                <i class="bi bi-pause-circle me-1"></i>Suspended
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4 g-3">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center p-4">
                <div class="mb-2">
                    <i class="bi bi-box-seam text-primary" style="font-size: 2rem;"></i>
                </div>
                <h2 class="text-primary mb-1 fw-bold">{{ $stats['total_products'] }}</h2>
                <p class="text-muted mb-3 small">Total Products</p>
                <div class="d-flex justify-content-center gap-2">
                    <span class="badge bg-success-subtle text-success border border-success">
                        <i class="bi bi-check-circle me-1"></i>{{ $stats['active_products'] }} Active
                    </span>
                    <span class="badge bg-warning-subtle text-warning border border-warning">
                        <i class="bi bi-clock me-1"></i>{{ $stats['pending_products'] }} Pending
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center p-4">
                <div class="mb-2">
                    <i class="bi bi-cart-check text-success" style="font-size: 2rem;"></i>
                </div>
                <h2 class="text-success mb-1 fw-bold">{{ $stats['total_orders'] }}</h2>
                <p class="text-muted mb-0 small">Total Orders</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center p-4">
                <div class="mb-2">
                    <i class="bi bi-currency-dollar text-info" style="font-size: 2rem;"></i>
                </div>
                <h2 class="text-info mb-1 fw-bold">₱{{ number_format($stats['total_revenue'], 2) }}</h2>
                <p class="text-muted mb-0 small">Total Revenue</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center p-4">
                <div class="mb-2">
                    <i class="bi bi-star-fill text-warning" style="font-size: 2rem;"></i>
                </div>
                <h2 class="text-warning mb-1 fw-bold">{{ number_format($seller->rating, 1) }}</h2>
                <p class="text-muted mb-0 small">{{ $seller->total_reviews }} Reviews</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-building text-primary me-2"></i>Business Information
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small mb-1">
                                <i class="bi bi-shop me-1"></i>Business Name
                            </label>
                            <div class="fw-semibold">{{ $seller->business_name }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small mb-1">
                                <i class="bi bi-award me-1"></i>Registration Type
                            </label>
                            <div>
                                @if($seller->is_verified_shop)
                                    <span class="badge bg-info-subtle text-info border border-info px-3 py-2">
                                        <i class="bi bi-shield-check me-1"></i> Full Trusted Verification Shop
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary px-3 py-2">
                                        <i class="bi bi-shop me-1"></i> Basic Seller
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small mb-1">
                                <i class="bi bi-link-45deg me-1"></i>Business Slug
                            </label>
                            <div><code class="bg-light px-2 py-1 rounded">{{ $seller->business_slug }}</code></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small mb-1">
                                <i class="bi bi-check-circle me-1"></i>Verification Status
                            </label>
                            <div>
                                <span class="badge bg-{{ $seller->verification_status === 'approved' ? 'success' : ($seller->verification_status === 'rejected' ? 'danger' : 'warning') }}-subtle text-{{ $seller->verification_status === 'approved' ? 'success' : ($seller->verification_status === 'rejected' ? 'danger' : 'warning') }} border border-{{ $seller->verification_status === 'approved' ? 'success' : ($seller->verification_status === 'rejected' ? 'danger' : 'warning') }} px-3 py-2">
                                    <i class="bi bi-{{ $seller->verification_status === 'approved' ? 'check-circle-fill' : ($seller->verification_status === 'rejected' ? 'x-circle-fill' : 'clock-fill') }} me-1"></i>
                                    {{ ucfirst($seller->verification_status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small mb-1">
                                <i class="bi bi-envelope me-1"></i>Email
                            </label>
                            <div class="fw-semibold">{{ $seller->email ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small mb-1">
                                <i class="bi bi-telephone me-1"></i>Phone
                            </label>
                            <div class="fw-semibold">{{ $seller->phone ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="text-muted small mb-1">
                                <i class="bi bi-geo-alt me-1"></i>Business Address
                            </label>
                            <div class="fw-semibold">
                                {{ $seller->address ?? 'N/A' }}
                                @if($seller->barangay), {{ $seller->barangay }}@endif
                                @if($seller->city), {{ $seller->city }}@endif
                                @if($seller->province), {{ $seller->province }}@endif
                                @if($seller->postal_code) {{ $seller->postal_code }}@endif
                            </div>
                        </div>
                    </div>
                </div>
                @if($seller->description)
                <div class="row">
                    <div class="col-12">
                        <div class="border-top pt-3">
                            <label class="text-muted small mb-2">
                                <i class="bi bi-file-text me-1"></i>Business Description
                            </label>
                            <p class="mb-0 text-secondary" style="text-align: justify; line-height: 1.8;">{{ $seller->description }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($seller->documents->count() > 0)
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-file-earmark-check text-success me-2"></i>Verification Documents
                    </h5>
                    <span class="badge bg-primary px-3 py-2">
                        <i class="bi bi-files me-1"></i>{{ $seller->documents->count() }} Document(s)
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-info border-info mb-4">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-info-circle-fill me-3 mt-1" style="font-size: 1.5rem;"></i>
                        <div>
                            <strong class="d-block mb-1">Registration Type</strong>
                            @if($seller->is_verified_shop)
                                <span class="text-dark">Full Trusted Verification Shop</span>
                                <p class="mb-0 small mt-1">
                                    <i class="bi bi-check-circle me-1"></i>Requires: ID Document, Business Permit, BIR Certificate, Product Sample, Facial Verification, Bank Statement
                                </p>
                            @else
                                <span class="text-dark">Basic Seller</span>
                                <p class="mb-0 small mt-1">
                                    <i class="bi bi-check-circle me-1"></i>Requires: ID Document, Facial Verification, Bank Statement
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-bold">
                                    <i class="bi bi-file-earmark me-1"></i>Document Type
                                </th>
                                <th class="fw-bold">
                                    <i class="bi bi-check-circle me-1"></i>Status
                                </th>
                                <th class="fw-bold">
                                    <i class="bi bi-calendar me-1"></i>Uploaded Date
                                </th>
                                <th class="fw-bold">
                                    <i class="bi bi-eye me-1"></i>Actions
                                </th>
                                <th class="fw-bold text-center">
                                    <i class="bi bi-gear me-1"></i>Manage
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($seller->documents as $document)
                                <tr class="border-bottom">
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            @php
                                                $docIcon = match($document->document_type) {
                                                    'id' => 'bi-person-badge',
                                                    'business_permit' => 'bi-building',
                                                    'bir_certificate' => 'bi-file-earmark-text',
                                                    'bank_statement' => 'bi-bank',
                                                    'facial_verification' => 'bi-person-circle',
                                                    'product_sample' => 'bi-box-seam',
                                                    default => 'bi-file-earmark'
                                                };
                                                $docLabel = match($document->document_type) {
                                                    'id' => 'Primary ID',
                                                    'business_permit' => 'Business Permit',
                                                    'bir_certificate' => 'BIR Certificate',
                                                    'bank_statement' => 'Bank Statement',
                                                    'facial_verification' => 'Facial Verification',
                                                    'product_sample' => 'Product Sample',
                                                    default => ucfirst(str_replace('_', ' ', $document->document_type))
                                                };
                                                $docDesc = match($document->document_type) {
                                                    'id' => 'Government-issued ID',
                                                    'business_permit' => 'Mayor\'s or Business Permit',
                                                    'bir_certificate' => 'BIR Form 2303',
                                                    'bank_statement' => 'Bank Account Verification',
                                                    'facial_verification' => 'Selfie with ID',
                                                    'product_sample' => 'Product Photo',
                                                    default => ''
                                                };
                                            @endphp
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; min-width: 45px;">
                                                <i class="bi {{ $docIcon }} text-primary" style="font-size: 1.3rem;"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $docLabel }}</div>
                                                @if($docDesc)
                                                    <small class="text-muted">{{ $docDesc }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        @if($document->status === 'approved')
                                            <span class="badge bg-success-subtle text-success border border-success px-3 py-2">
                                                <i class="bi bi-check-circle-fill me-1"></i>Approved
                                            </span>
                                        @elseif($document->status === 'rejected')
                                            <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2">
                                                <i class="bi bi-x-circle-fill me-1"></i>Rejected
                                            </span>
                                            @if($document->rejection_reason)
                                                <div class="alert alert-danger mt-2 mb-0 py-2 px-3">
                                                    <small><strong>Reason:</strong> {{ Str::limit($document->rejection_reason, 80) }}</small>
                                                </div>
                                            @endif
                                        @else
                                            <span class="badge bg-warning-subtle text-warning border border-warning px-3 py-2">
                                                <i class="bi bi-clock-fill me-1"></i>Pending Review
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        <div class="fw-semibold">{{ $document->created_at->format('M d, Y') }}</div>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>{{ $document->created_at->format('h:i A') }}
                                        </small>
                                    </td>
                                    <td class="py-3">
                                        @if($document->document_path)
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewDocumentModal{{ $document->id }}">
                                                    <i class="bi bi-eye me-1"></i> View
                                                </button>
                                                <a href="{{ asset('storage/' . $document->document_path) }}" download class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-download me-1"></i> Download
                                                </a>
                                            </div>
                                        @else
                                            <span class="text-muted">
                                                <i class="bi bi-x-circle me-1"></i>Not available
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-center" style="min-width: 180px;">
                                        @if($document->status === 'pending')
                                            <div class="d-flex flex-column gap-2">
                                                <form action="{{ route('admin.sellers.documents.approve', ['sellerId' => $seller->id, 'documentId' => $document->id]) }}" method="POST" class="w-100">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success w-100 fw-semibold" onclick="return confirm('Approve this document?')">
                                                        <i class="bi bi-check-circle-fill me-1"></i> Approve
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-danger w-100 fw-semibold" data-bs-toggle="modal" data-bs-target="#rejectDocumentModal{{ $document->id }}">
                                                    <i class="bi bi-x-circle-fill me-1"></i> Reject
                                                </button>
                                            </div>
                                        @elseif($document->status === 'approved')
                                            <div class="d-flex flex-column align-items-center">
                                                <span class="badge bg-success-subtle text-success border border-success fs-6 py-2 px-4 mb-2">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Approved
                                                </span>
                                                <small class="text-muted">
                                                    <i class="bi bi-lock-fill me-1"></i>Verified
                                                </small>
                                            </div>
                                        @elseif($document->status === 'rejected')
                                            <div class="d-flex flex-column align-items-center">
                                                <span class="badge bg-danger-subtle text-danger border border-danger fs-6 py-2 px-4 mb-2">
                                                    <i class="bi bi-x-circle-fill me-1"></i> Rejected
                                                </span>
                                                <form action="{{ route('admin.sellers.documents.approve', ['sellerId' => $seller->id, 'documentId' => $document->id]) }}" method="POST" class="w-100">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success w-100" onclick="return confirm('Approve this document?')">
                                                        <i class="bi bi-arrow-clockwise me-1"></i> Re-approve
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
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
        
        <!-- View Document Modals (Outside the table) -->
        @foreach($seller->documents as $document)
            <div class="modal fade" id="viewDocumentModal{{ $document->id }}" tabindex="-1" aria-labelledby="viewDocumentModalLabel{{ $document->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-dark text-white py-2">
                            <h6 class="modal-title mb-0" id="viewDocumentModalLabel{{ $document->id }}">
                                <i class="bi bi-file-earmark-text me-2"></i>
                                @php
                                    $docLabel = match($document->document_type) {
                                        'id' => 'Primary ID',
                                        'business_permit' => 'Business Permit',
                                        'bir_certificate' => 'BIR Certificate',
                                        'bank_statement' => 'Bank Statement',
                                        'facial_verification' => 'Facial Verification',
                                        'product_sample' => 'Product Sample',
                                        default => ucfirst(str_replace('_', ' ', $document->document_type))
                                    };
                                @endphp
                                {{ $docLabel }} - {{ $seller->business_name }}
                            </h6>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body bg-dark text-center p-3" style="max-height: 65vh; overflow: auto;">
                            @php
                                $extension = pathinfo($document->document_path, PATHINFO_EXTENSION);
                                $isPdf = strtolower($extension) === 'pdf';
                            @endphp
                            @if($isPdf)
                                <iframe src="{{ asset('storage/' . $document->document_path) }}" style="width: 100%; height: 60vh; border: none; border-radius: 8px;"></iframe>
                            @else
                                <img src="{{ asset('storage/' . $document->document_path) }}" alt="Document" class="img-fluid" style="max-height: 60vh; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                            @endif
                        </div>
                        <div class="modal-footer bg-dark text-white py-2">
                            <a href="{{ asset('storage/' . $document->document_path) }}" download class="btn btn-sm btn-outline-light">
                                <i class="bi bi-download me-1"></i> Download
                            </a>
                            <a href="{{ asset('storage/' . $document->document_path) }}" target="_blank" class="btn btn-sm btn-outline-light">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Open in New Tab
                            </a>
                            <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">
                                <i class="bi bi-x-lg me-1"></i> Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        
        <!-- Reject Document Modals (Outside the table) -->
        @foreach($seller->documents as $document)
            <div class="modal fade" id="rejectDocumentModal{{ $document->id }}" tabindex="-1" aria-labelledby="rejectDocumentModalLabel{{ $document->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <form action="{{ route('admin.sellers.documents.reject', ['sellerId' => $seller->id, 'documentId' => $document->id]) }}" method="POST">
                            @csrf
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title" id="rejectDocumentModalLabel{{ $document->id }}">
                                    <i class="bi bi-x-circle me-2"></i>Reject Document - Invalid Document
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-danger border-danger">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <strong>Warning:</strong> Rejecting this document will mark it as invalid. The seller will be notified via email that their required verification document is invalid and needs to be resubmitted.
                                </div>
                                <div class="alert alert-info border-info">
                                    <i class="bi bi-info-circle-fill me-2"></i>
                                    <strong>Document Type:</strong> 
                                    @php
                                        $docLabel = match($document->document_type) {
                                            'id' => 'Primary ID',
                                            'business_permit' => 'Business Permit',
                                            'bir_certificate' => 'BIR Certificate',
                                            'bank_statement' => 'Bank Statement',
                                            'facial_verification' => 'Facial Verification',
                                            'product_sample' => 'Product Sample',
                                            default => ucfirst(str_replace('_', ' ', $document->document_type))
                                        };
                                    @endphp
                                    {{ $docLabel }}
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                                    <textarea name="reason" class="form-control" rows="5" placeholder="Please provide a detailed reason why this document is invalid (e.g., document is unclear, expired, doesn't match business information, etc.)..." required>{{ old('reason', $document->rejection_reason ?? '') }}</textarea>
                                    <small class="text-muted">
                                        <i class="bi bi-envelope me-1"></i>This reason will be sent to the seller via email notification and website notification.
                                    </small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x-lg me-1"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-x-circle-fill me-1"></i> Reject Document (Invalid)
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
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

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-box-seam text-primary me-2"></i>Recent Products
                    </h5>
                    <a href="{{ route('admin.products.index', ['seller' => $seller->id]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-right me-1"></i>View All
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3">Product</th>
                                <th class="py-3">Status</th>
                                <th class="py-3">Price</th>
                                <th class="py-3">Stock</th>
                                <th class="py-3">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($seller->products->take(5) as $product)
                                <tr>
                                    <td class="py-3">
                                        <a href="{{ route('admin.products.show', $product->id) }}" class="text-decoration-none fw-semibold">
                                            {{ Str::limit($product->name, 40) }}
                                        </a>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-{{ $product->status === 'active' ? 'success' : ($product->status === 'pending' ? 'warning' : 'secondary') }}-subtle text-{{ $product->status === 'active' ? 'success' : ($product->status === 'pending' ? 'warning' : 'secondary') }} border border-{{ $product->status === 'active' ? 'success' : ($product->status === 'pending' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($product->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 fw-semibold">₱{{ number_format($product->price, 2) }}</td>
                                    <td class="py-3">
                                        <span class="badge bg-light text-dark">{{ $product->stock_quantity }}</span>
                                    </td>
                                    <td class="py-3 text-muted small">{{ $product->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox me-2"></i>No products yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-cart-check text-success me-2"></i>Recent Orders
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3">Order #</th>
                                <th class="py-3">Customer</th>
                                <th class="py-3">Amount</th>
                                <th class="py-3">Status</th>
                                <th class="py-3">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($seller->orders->take(5) as $order)
                                <tr>
                                    <td class="py-3">
                                        <code class="bg-light px-2 py-1 rounded">{{ $order->order_number }}</code>
                                    </td>
                                    <td class="py-3">{{ $order->user->name }}</td>
                                    <td class="py-3 fw-semibold">₱{{ number_format($order->total, 2) }}</td>
                                    <td class="py-3">
                                        <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : 'warning' }}-subtle text-{{ $order->status === 'delivered' ? 'success' : 'warning' }} border border-{{ $order->status === 'delivered' ? 'success' : 'warning' }}">
                                            {{ $order->getStatusLabel() }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-muted small">{{ $order->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox me-2"></i>No orders yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-gradient text-white py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-lightning-charge me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body p-4">
                @php
                    $requiredDocsCount = $seller->is_verified_shop ? 6 : 3;
                    $approvedDocsCount = $seller->documents->where('status', 'approved')->count();
                    $pendingDocsCount = $seller->documents->where('status', 'pending')->count();
                    $rejectedDocsCount = $seller->documents->where('status', 'rejected')->count();
                @endphp
                @if($seller->verification_status === 'pending' && $seller->documents->count() > 0)
                    <div class="alert alert-info border-info mb-4">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                            <div class="flex-grow-1">
                                <strong class="d-block mb-2">Document Review Status</strong>
                                <div class="d-flex flex-column gap-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small">Approved:</span>
                                        <span class="badge bg-success px-3">{{ $approvedDocsCount }} / {{ $requiredDocsCount }}</span>
                                    </div>
                                    @if($pendingDocsCount > 0)
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="small">Pending:</span>
                                            <span class="badge bg-warning px-3">{{ $pendingDocsCount }}</span>
                                        </div>
                                    @endif
                                    @if($rejectedDocsCount > 0)
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="small">Rejected:</span>
                                            <span class="badge bg-danger px-3">{{ $rejectedDocsCount }}</span>
                                        </div>
                                    @endif
                                </div>
                                <hr class="my-2">
                                @if($approvedDocsCount < $requiredDocsCount)
                                    <div class="alert alert-warning mb-0 py-2 px-3 mt-2">
                                        <small>
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            <strong>Action Required:</strong> Approve all documents first
                                        </small>
                                    </div>
                                @else
                                    <div class="alert alert-success mb-0 py-2 px-3 mt-2">
                                        <small>
                                            <i class="bi bi-check-circle me-1"></i>
                                            <strong>Ready:</strong> All documents approved
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
                <div class="d-grid gap-3">
                    @if($seller->verification_status === 'pending')
                        <form action="{{ route('admin.sellers.approve', $seller->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 py-3 fw-bold" 
                                @if($seller->documents->count() > 0 && $approvedDocsCount < $requiredDocsCount)
                                    disabled
                                    title="All required documents must be approved first"
                                @endif>
                                <i class="bi bi-check-circle-fill me-2"></i> Approve Seller
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger w-100 py-3 fw-bold" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle-fill me-2"></i> Reject Seller
                        </button>
                    @endif

                    @if($seller->is_active)
                        <button type="button" class="btn btn-warning w-100 py-3 fw-bold" data-bs-toggle="modal" data-bs-target="#suspendModal">
                            <i class="bi bi-pause-circle-fill me-2"></i> Suspend Seller
                        </button>
                    @else
                        <div class="alert alert-danger border-danger mb-3">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-exclamation-octagon-fill me-2 mt-1"></i>
                                <div>
                                    <strong class="d-block mb-2">Account Suspended</strong>
                                    @if($seller->suspended_at)
                                        <small class="d-block mb-1">
                                            <i class="bi bi-calendar me-1"></i>
                                            {{ $seller->suspended_at instanceof \Carbon\Carbon ? $seller->suspended_at->format('M d, Y h:i A') : \Carbon\Carbon::parse($seller->suspended_at)->format('M d, Y h:i A') }}
                                        </small>
                                    @endif
                                    @if($seller->suspension_reason)
                                        <small class="d-block mt-2 p-2 bg-light rounded">
                                            <strong>Reason:</strong><br>
                                            {{ $seller->suspension_reason }}
                                        </small>
                                    @endif
                                    @if($seller->suspendedBy)
                                        <small class="d-block mt-2">
                                            <i class="bi bi-person me-1"></i>By: {{ $seller->suspendedBy->name }}
                                        </small>
                                    @endif
                                    @if($seller->relatedReport)
                                        <a href="{{ route('admin.reports.show', $seller->related_report_id) }}" class="btn btn-sm btn-outline-danger mt-2">
                                            <i class="bi bi-file-text me-1"></i>View Related Report
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <form action="{{ route('admin.sellers.activate', $seller->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 py-3 fw-bold">
                                <i class="bi bi-play-circle-fill me-2"></i> Activate Seller
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        @if($seller->reviews->count() > 0)
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-star-fill text-warning me-2"></i>Recent Reviews
                </h5>
            </div>
            <div class="card-body p-4">
                @foreach($seller->reviews->take(3) as $review)
                    <div class="mb-3 pb-3 @if(!$loop->last) border-bottom @endif">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                    <i class="bi bi-person-fill text-primary"></i>
                                </div>
                                <strong>{{ $review->user->name }}</strong>
                            </div>
                            <div class="d-flex gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $review->overall_rating ? '-fill' : '' }} text-warning"></i>
                                @endfor
                            </div>
                        </div>
                        <p class="mb-2 small text-secondary" style="line-height: 1.6;">{{ Str::limit($review->review_text, 120) }}</p>
                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i>{{ $review->created_at->diffForHumans() }}
                        </small>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('admin.sellers.reject', $seller->id) }}" method="POST" id="rejectForm">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="rejectModalLabel">
                        <i class="bi bi-x-octagon me-2"></i>Reject Seller Application
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger border-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Important:</strong> The seller will be notified via email and website notification about this rejection.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Rejection Reason <span class="text-danger">*</span></label>
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
                        <label class="form-label fw-semibold">Additional Feedback / Custom Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" id="rejection_reason" class="form-control" rows="5" placeholder="Please provide detailed feedback or reason for rejection..." required></textarea>
                        <small class="text-muted">
                            <i class="bi bi-envelope me-1"></i>This feedback will be sent to the seller via email and website notification.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-octagon-fill me-1"></i> Reject Seller
                    </button>
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
<div class="modal fade" id="suspendModal" tabindex="-1" aria-labelledby="suspendModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.sellers.suspend', $seller->id) }}" method="POST" id="suspendForm">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="suspendModalLabel">
                        <i class="bi bi-pause-circle me-2"></i>Suspend Business Account
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning border-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Warning:</strong> Suspending this seller will also ban their user account. They will not be able to access their account, create new listings, or receive new orders. Existing orders will continue to be processed. The seller will be notified via email and website notification.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Suspension Reason <span class="text-danger">*</span></label>
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
                        <label class="form-label fw-semibold">Additional Feedback / Custom Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" id="suspension_reason" class="form-control" rows="5" placeholder="Please provide detailed feedback or reason for suspension..." required></textarea>
                        <small class="text-muted">
                            <i class="bi bi-envelope me-1"></i>This feedback will be sent to the seller via email and website notification.
                        </small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Related Report (Optional)</label>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-warning text-dark fw-semibold">
                        <i class="bi bi-pause-circle-fill me-1"></i> Suspend Seller
                    </button>
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

<style>
/* Enhanced card styling */
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
}

/* Stat cards hover effect */
.col-md-3 .card:hover {
    border-color: rgba(var(--bs-primary-rgb), 0.3) !important;
}

/* Table row hover */
.table-hover tbody tr:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.05);
    cursor: pointer;
}

/* Badge improvements */
.badge {
    font-weight: 500;
    letter-spacing: 0.3px;
}

/* Button hover effects */
.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

/* Document icon styling */
.bg-light.rounded-circle {
    transition: all 0.2s ease;
}

tr:hover .bg-light.rounded-circle {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
    transform: scale(1.05);
}

/* Alert styling improvements */
.alert {
    border-left-width: 4px;
}

/* Modal improvements */
.modal-xl .modal-body {
    background: #1a1a1a;
}

.modal-xl img {
    cursor: zoom-in;
}

/* Fix modal positioning and sizing */
.modal-dialog-centered {
    display: flex;
    align-items: center;
    min-height: calc(100% - 3.5rem);
}

.modal-dialog-scrollable {
    max-height: calc(100vh - 3.5rem);
}

.modal-dialog-scrollable .modal-content {
    max-height: calc(100vh - 3.5rem);
    overflow: hidden;
}

.modal-dialog-scrollable .modal-body {
    overflow-y: auto;
}

/* Ensure modals don't overflow - set reasonable max sizes */
.modal-xl {
    max-width: 90%;
    margin: 1.75rem auto;
}

@media (min-width: 1200px) {
    .modal-xl {
        max-width: 1140px;
    }
}

.modal-lg {
    max-width: 90%;
    margin: 2rem auto;
}

@media (min-width: 992px) {
    .modal-lg {
        max-width: 800px;
    }
}

/* Limit modal content height to prevent overflow */
.modal-content {
    max-height: calc(100vh - 4rem);
}

.modal-body {
    max-height: calc(100vh - 12rem);
    overflow-y: auto;
}

/* Smooth transitions */
* {
    transition: background-color 0.2s ease, border-color 0.2s ease;
}

/* Card header gradient */
.bg-gradient {
    position: relative;
    overflow: hidden;
}

.bg-gradient::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
    pointer-events: none;
}
</style>
@endsection
