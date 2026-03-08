@extends('layouts.toyshop')

@section('title', 'Individual Auction Seller Registration - ToyHaven')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auction.index') }}">Auction</a></li>
            <li class="breadcrumb-item active">Individual Seller Registration</li>
        </ol>
    </nav>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><i class="bi bi-person-badge me-2"></i>Individual Auction Seller Registration</h4>
                            <p class="mb-0 small opacity-90">2 Government IDs, 1 Facial Selfie, Bank Statement</p>
                        </div>
                        <a href="{{ route('auction.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Requirements</h6>
                        <ul class="mb-0 small">
                            <li>2 Government-issued valid IDs (e.g., Passport, Driver's License, UMID, National ID)</li>
                            <li>1 Facial verification selfie (holding your ID next to your face)</li>
                            <li>Bank statement or passbook</li>
                        </ul>
                    </div>

                    <form action="{{ route('auction.seller-registration.individual.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <h5 class="mb-3">Required Documents</h5>

                        <div class="mb-4">
                            <label class="form-label">Government ID #1 <span class="text-danger">*</span></label>
                            <input type="file" name="government_id_1" class="form-control @error('government_id_1') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                            <small class="text-muted">PDF, JPG, PNG (Max: 5MB)</small>
                            @error('government_id_1')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Government ID #2 <span class="text-danger">*</span></label>
                            <input type="file" name="government_id_2" class="form-control @error('government_id_2') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                            <small class="text-muted">PDF, JPG, PNG (Max: 5MB)</small>
                            @error('government_id_2')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Facial Verification Selfie <span class="text-danger">*</span></label>
                            <input type="file" name="facial_verification" class="form-control @error('facial_verification') is-invalid @enderror" accept=".jpg,.jpeg,.png" required>
                            <small class="text-muted d-block">Selfie holding your ID next to your face. JPG, PNG (Max: 5MB)</small>
                            @error('facial_verification')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Bank Statement <span class="text-danger">*</span></label>
                            <input type="file" name="bank_statement" class="form-control @error('bank_statement') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                            <small class="text-muted">Bank statement or passbook. PDF, JPG, PNG (Max: 5MB)</small>
                            @error('bank_statement')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <hr class="my-4">
                        <div class="d-flex gap-2">
                            <a href="{{ route('auction.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-send me-2"></i>Submit for Review
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
