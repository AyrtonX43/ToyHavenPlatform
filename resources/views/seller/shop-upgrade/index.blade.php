@extends('layouts.seller-new')

@section('title', 'Upgrade to Trusted Shop - ToyHaven')

@section('page-title', 'Upgrade to Trusted Shop')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Upgrade to Trusted Shop</h4>
        <p class="text-muted mb-0">Get verified as a trusted shop and unlock premium features</p>
    </div>
    @if($seller->is_verified_shop)
        <span class="badge bg-success fs-6 px-3 py-2">
            <i class="bi bi-shield-check me-1"></i>Verified Trusted Shop
        </span>
    @endif
</div>

@if($seller->is_verified_shop)
    <!-- Already Verified -->
    <div class="card border-success">
        <div class="card-body text-center py-5">
            <i class="bi bi-shield-check text-success" style="font-size: 4rem;"></i>
            <h4 class="mt-3 mb-2">Your Shop is Verified!</h4>
            <p class="text-muted">Congratulations! Your shop has been verified as a trusted shop.</p>
            <div class="mt-4">
                <h5>Benefits You're Enjoying:</h5>
                <div class="row mt-3">
                    <div class="col-md-4 mb-3">
                        <i class="bi bi-star-fill text-warning" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">Trusted Badge</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <i class="bi bi-graph-up text-primary" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">Priority Listing</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <i class="bi bi-shield-check text-success" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">Enhanced Credibility</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <!-- Requirements Check -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Upgrade Requirements</h5>
        </div>
        <div class="card-body">
            @foreach($requirements as $key => $requirement)
                @if($key !== 'all_met')
                    <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                        <div class="flex-shrink-0 me-3">
                            @if($requirement['met'])
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 1.5rem;"></i>
                            @else
                                <i class="bi bi-x-circle-fill text-danger" style="font-size: 1.5rem;"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                {{ ucfirst(str_replace('_', ' ', $key)) }}
                                @if($requirement['required'])
                                    <span class="badge bg-danger ms-2">Required</span>
                                @else
                                    <span class="badge bg-secondary ms-2">Optional</span>
                                @endif
                            </h6>
                            <p class="mb-1">{{ $requirement['message'] }}</p>
                            @if(isset($requirement['current']))
                                <small class="text-muted">{{ $requirement['current'] }}</small>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Upload Documents -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-file-earmark-arrow-up me-2"></i>Upload Required Documents</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('seller.shop-upgrade.upload-document') }}" method="POST" enctype="multipart/form-data" class="mb-3">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Document Type <span class="text-danger">*</span></label>
                        <select name="document_type" class="form-select" required>
                            <option value="">Select document type</option>
                            <option value="business_registration">Business Registration (BIR 2303 + DTI/SEC)</option>
                            <option value="brand_rights">Brand Rights (Trademark or LOA)</option>
                            <option value="bank_account">Bank Document</option>
                            <option value="id">Primary ID</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Document File <span class="text-danger">*</span></label>
                        <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="text-muted">Max 5MB (PDF, JPG, PNG)</small>
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-upload me-1"></i> Upload
                        </button>
                    </div>
                </div>
            </form>

            <!-- Uploaded Documents -->
            @if($seller->documents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Document Type</th>
                                <th>Status</th>
                                <th>Uploaded</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($seller->documents as $document)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</td>
                                    <td>
                                        @if($document->status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($document->status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                            @if($document->rejection_reason)
                                                <small class="d-block text-danger">{{ $document->rejection_reason }}</small>
                                            @endif
                                        @else
                                            <span class="badge bg-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td>{{ $document->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ Storage::url($document->document_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Submit Upgrade Request -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-send me-2"></i>Submit Upgrade Request</h5>
        </div>
        <div class="card-body">
            @if($requirements['all_met'])
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Congratulations!</strong> You meet all the required criteria for shop upgrade.
                </div>
                <form action="{{ route('seller.shop-upgrade.submit') }}" method="POST">
                    @csrf
                    <p class="mb-3">Click the button below to submit your upgrade request. Our admin team will review your application.</p>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-send me-2"></i>Submit Upgrade Request
                    </button>
                </form>
            @else
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Requirements Not Met</strong> Please complete all required criteria above before submitting your upgrade request.
                </div>
                <button type="button" class="btn btn-success btn-lg" disabled>
                    <i class="bi bi-send me-2"></i>Submit Upgrade Request
                </button>
            @endif
        </div>
    </div>
@endif
@endsection
