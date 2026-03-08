@extends('layouts.toyshop')

@section('title', 'Business Auction Seller Registration - ToyHaven')

@php
$prefillAddress = $prefilledData['address'] ?? '';
$prefillRegion = $prefilledData['region'] ?? '';
$prefillProvince = $prefilledData['province'] ?? '';
$prefillCity = $prefilledData['city'] ?? '';
$prefillBarangay = $prefilledData['barangay'] ?? '';
$prefillPostal = $prefilledData['postal_code'] ?? '';
$prefillRegion = $prefillRegion ? normalizePhilippineText($prefillRegion) : '';
$prefillProvince = $prefillProvince ? normalizePhilippineText($prefillProvince) : '';
$prefillCity = $prefillCity ? normalizePhilippineText($prefillCity) : '';
$prefillBarangay = $prefillBarangay ? normalizePhilippineText($prefillBarangay) : '';
@endphp

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auction.index') }}">Auction</a></li>
            <li class="breadcrumb-item active">Business Seller Registration</li>
        </ol>
    </nav>
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                <div class="card-header bg-success text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><i class="bi bi-shop me-2"></i>Business Auction Seller Registration</h4>
                            <p class="mb-0 small opacity-90">Same requirements as Verified Trusted Toyshop</p>
                        </div>
                        <a href="{{ route('auction.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Requirements (Verified Trusted Toyshop standard)</h6>
                        <ul class="mb-0 small">
                            <li>Primary ID, Facial Verification, Bank Statement</li>
                            <li>Business Permit, BIR Certificate, Product Sample</li>
                        </ul>
                    </div>

                    <form action="{{ route('auction.seller-registration.business.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <h5 class="mb-3">Business Information</h5>
                        <div class="mb-3">
                            <label class="form-label">Business Name <span class="text-danger">*</span></label>
                            <input type="text" name="business_name" class="form-control @error('business_name') is-invalid @enderror" value="{{ old('business_name') }}" required>
                            @error('business_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Business Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4" required>{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">+63</span>
                                    <input type="text" id="phone_display" class="form-control" value="{{ old('phone', $prefilledData['phone'] ?? '') }}" placeholder="9123456789" maxlength="10" pattern="[0-9]{10}">
                                </div>
                                <input type="hidden" id="phone" name="phone" value="{{ old('phone', $prefilledData['phone'] ? '+63'.$prefilledData['phone'] : '') }}">
                                @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $prefilledData['email'] ?? '') }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <h5 class="mb-3 mt-4">Business Address</h5>
                        @include('partials.philippine-address-fields', [
                            'prefillAddress' => $prefillAddress,
                            'prefillRegion' => $prefillRegion,
                            'prefillProvince' => $prefillProvince,
                            'prefillCity' => $prefillCity,
                            'prefillBarangay' => $prefillBarangay,
                            'prefillPostal' => $prefillPostal,
                        ])

                        <h5 class="mb-3 mt-4">Required Documents</h5>
                        @foreach([
                            ['name' => 'id_document', 'label' => 'Primary ID', 'accept' => '.pdf,.jpg,.jpeg,.png'],
                            ['name' => 'facial_verification', 'label' => 'Facial Verification (Selfie with ID)', 'accept' => '.jpg,.jpeg,.png'],
                            ['name' => 'bank_document', 'label' => 'Bank Statement', 'accept' => '.pdf,.jpg,.jpeg,.png'],
                            ['name' => 'business_permit', 'label' => 'Business Permit', 'accept' => '.pdf,.jpg,.jpeg,.png'],
                            ['name' => 'bir_certificate', 'label' => 'BIR Certificate', 'accept' => '.pdf,.jpg,.jpeg,.png'],
                            ['name' => 'product_sample', 'label' => 'Product Sample', 'accept' => '.jpg,.jpeg,.png'],
                        ] as $doc)
                            <div class="mb-3">
                                <label class="form-label">{{ $doc['label'] }} <span class="text-danger">*</span></label>
                                <input type="file" name="{{ $doc['name'] }}" class="form-control @error($doc['name']) is-invalid @enderror" accept="{{ $doc['accept'] }}" required>
                                <small class="text-muted">Max 5MB</small>
                                @error($doc['name'])<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        @endforeach

                        <hr class="my-4">
                        <div class="d-flex gap-2">
                            <a href="{{ route('auction.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-send me-2"></i>Submit for Review
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@include('partials.philippine-address-script', [
    'prefillRegion' => $prefillRegion,
    'prefillProvince' => $prefillProvince,
    'prefillCity' => $prefillCity,
    'prefillBarangay' => $prefillBarangay,
])
<script>
document.addEventListener('DOMContentLoaded', function() {
    const phoneDisplay = document.getElementById('phone_display');
    const phoneHidden = document.getElementById('phone');
    if (phoneDisplay && phoneHidden) {
        phoneDisplay.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10);
            if (this.value.length === 10) phoneHidden.value = '+63' + this.value;
            else phoneHidden.value = '';
        });
        if (phoneDisplay.value && phoneDisplay.value.length === 10) {
            phoneHidden.value = '+63' + phoneDisplay.value;
        }
    }
});
</script>
@endpush
@endsection
