@extends('layouts.admin-new')

@section('title', 'Auction Seller Verification #' . $verification->id)

@section('content')
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.auction-seller-verifications.index') }}">Auction Seller Verifications</a></li>
            <li class="breadcrumb-item active">#{{ $verification->id }}</li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-{{ $verification->type === 'business' ? 'shop' : 'person' }} me-2"></i>
                        {{ ucfirst($verification->type) }} Auction Seller Verification
                    </h5>
                    <span class="badge bg-{{ $verification->verification_status === 'approved' ? 'success' : ($verification->verification_status === 'rejected' ? 'danger' : 'warning') }} fs-6">
                        {{ ucfirst($verification->verification_status) }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <h6 class="text-muted mb-3">Applicant</h6>
                    <p class="mb-1"><strong>{{ $verification->user?->name }}</strong></p>
                    <p class="mb-0 text-muted small">{{ $verification->user?->email }}</p>
                    <p class="mb-0 text-muted small">Submitted: {{ $verification->created_at->format('M d, Y H:i') }}</p>

                    @if($verification->type === 'business' && $verification->business_info)
                        <hr>
                        <h6 class="text-muted mb-3">Business Information</h6>
                        <table class="table table-sm">
                            <tr><td class="text-muted" style="width:140px">Business Name</td><td>{{ $verification->business_info['business_name'] ?? '-' }}</td></tr>
                            <tr><td class="text-muted">Description</td><td>{{ Str::limit($verification->business_info['description'] ?? '-', 200) }}</td></tr>
                            <tr><td class="text-muted">Phone</td><td>{{ $verification->business_info['phone'] ?? '-' }}</td></tr>
                            <tr><td class="text-muted">Email</td><td>{{ $verification->business_info['email'] ?? '-' }}</td></tr>
                            <tr><td class="text-muted">Address</td><td>{{ $verification->business_info['address'] ?? '-' }}, {{ $verification->business_info['barangay'] ?? '' }}, {{ $verification->business_info['city'] ?? '' }}, {{ $verification->business_info['province'] ?? '' }}</td></tr>
                        </table>
                    @endif

                    @if($verification->rejection_reason)
                        <hr>
                        <h6 class="text-muted mb-2">Rejection Feedback</h6>
                        <div class="alert alert-light border">{{ $verification->rejection_reason }}</div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark me-2"></i>Documents</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        @foreach($verification->documents as $doc)
                            @php
                                $label = match($doc->document_type) {
                                    'government_id_1' => 'Government ID #1',
                                    'government_id_2' => 'Government ID #2',
                                    'facial_verification' => 'Facial Verification',
                                    'bank_statement' => 'Bank Statement',
                                    'id' => 'Primary ID',
                                    'business_permit' => 'Business Permit',
                                    'bir_certificate' => 'BIR Certificate',
                                    'product_sample' => 'Product Sample',
                                    default => ucfirst(str_replace('_', ' ', $doc->document_type)),
                                };
                            @endphp
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <strong>{{ $label }}</strong>
                                    <a href="{{ asset('storage/' . $doc->document_path) }}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    @if(pathinfo($doc->document_path, PATHINFO_EXTENSION) === 'pdf')
                                        <embed src="{{ asset('storage/' . $doc->document_path) }}#toolbar=0" type="application/pdf" width="100%" height="200" class="mt-2 rounded" />
                                    @else
                                        <img src="{{ asset('storage/' . $doc->document_path) }}" alt="{{ $label }}" class="img-fluid mt-2 rounded" style="max-height: 200px; object-fit: contain;" />
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if($verification->verification_status === 'pending')
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold">Actions</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.auction-seller-verifications.approve', $verification) }}" method="POST" class="mb-3">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-circle me-2"></i>Approve
                            </button>
                        </form>

                        <form action="{{ route('admin.auction-seller-verifications.reject', $verification) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Feedback / Rejection Reason <span class="text-danger">*</span></label>
                                <textarea name="feedback" class="form-control" rows="4" required placeholder="Explain what needs to be corrected or why the application is rejected...">{{ old('feedback') }}</textarea>
                                @error('feedback')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-x-circle me-2"></i>Reject with Feedback
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
