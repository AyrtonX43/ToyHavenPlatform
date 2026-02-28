@extends('layouts.toyshop')

@section('title', 'Auction Seller Verification - ' . ucfirst($type) . ' Seller')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auctions.index') }}">Auctions</a></li>
            <li class="breadcrumb-item"><a href="{{ route('auctions.verification.index') }}">Seller Verification</a></li>
            <li class="breadcrumb-item active">{{ ucfirst($type) }} Seller</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white p-4 border-bottom">
                    <h3 class="mb-1 fw-bold">
                        @if($type === 'business')
                            <i class="bi bi-building text-success me-2"></i>Business Seller Verification
                        @else
                            <i class="bi bi-person text-primary me-2"></i>Individual Seller Verification
                        @endif
                    </h3>
                    <p class="text-muted mb-0">Complete all required fields to submit your verification</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('auctions.verification.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="seller_type" value="{{ $type }}">

                        @if($type === 'business')
                        <h5 class="fw-bold mb-3 mt-2"><i class="bi bi-building me-1"></i>Business Information</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Auction Business Name <span class="text-danger">*</span></label>
                                <input type="text" name="auction_business_name" class="form-control"
                                       value="{{ old('auction_business_name', auth()->user()->seller->business_name ?? '') }}"
                                       placeholder="Enter your business name for auction listings" required>
                                <small class="text-muted">This name will be displayed on your auction listings. You can change it later.</small>
                                @error('auction_business_name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">
                        @endif

                        <h5 class="fw-bold mb-3 mt-2"><i class="bi bi-telephone me-1"></i>Contact Information</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">+63</span>
                                    <input type="text" name="phone_display" class="form-control" placeholder="9XXXXXXXXX" value="{{ old('phone_display', substr(old('phone', auth()->user()->phone ?? ''), 3)) }}" maxlength="10">
                                </div>
                                <input type="hidden" name="phone" id="phone_hidden" value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Full Address <span class="text-danger">*</span></label>
                                <textarea name="address" class="form-control" rows="2" required>{{ old('address', auth()->user()->address) }}</textarea>
                                @error('address')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="fw-bold mb-3"><i class="bi bi-camera me-1"></i>Selfie Verification <span class="text-danger">*</span></h5>
                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle me-1"></i>
                            Upload a clear photo of yourself with a <strong>plain background</strong> and <strong>proper lighting</strong>. No sunglasses, hats, or face coverings.
                        </div>
                        <div class="mb-4">
                            <input type="file" name="selfie" id="selfie" class="form-control file-input-with-preview" accept="image/jpeg,image/png" required data-preview-target="selfie-preview">
                            @error('selfie')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div id="selfie-preview" class="image-preview-container mt-3" style="display: none;">
                                <div class="position-relative d-inline-block">
                                    <img src="" alt="Selfie Preview" class="img-thumbnail" style="max-width: 300px; max-height: 300px; object-fit: contain;">
                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 delete-preview" data-input-id="selfie">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="fw-bold mb-3"><i class="bi bi-file-earmark-text me-1"></i>Government ID(s) <span class="text-danger">*</span></h5>
                        @if($type === 'individual')
                            <p class="text-muted small">Upload at least 2 valid government IDs (3rd is optional). Accepted: Passport, Driver's License, PhilID, SSS, Postal ID, Voter's ID, etc.</p>
                        @else
                            <p class="text-muted small">Upload at least 1 valid government ID.</p>
                        @endif

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Government ID #1 <span class="text-danger">*</span></label>
                                <input type="file" name="government_id_1" id="government_id_1" class="form-control file-input-with-preview" accept=".pdf,.jpg,.jpeg,.png" required data-preview-target="gov-id-1-preview">
                                @error('government_id_1')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <div id="gov-id-1-preview" class="image-preview-container mt-3" style="display: none;">
                                    <div class="position-relative d-inline-block">
                                        <img src="" alt="Government ID #1 Preview" class="img-thumbnail" style="max-width: 250px; max-height: 250px; object-fit: contain;">
                                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 delete-preview" data-input-id="government_id_1">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @if($type === 'individual')
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Government ID #2 <span class="text-danger">*</span></label>
                                    <input type="file" name="government_id_2" id="government_id_2" class="form-control file-input-with-preview" accept=".pdf,.jpg,.jpeg,.png" required data-preview-target="gov-id-2-preview">
                                    @error('government_id_2')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    <div id="gov-id-2-preview" class="image-preview-container mt-3" style="display: none;">
                                        <div class="position-relative d-inline-block">
                                            <img src="" alt="Government ID #2 Preview" class="img-thumbnail" style="max-width: 250px; max-height: 250px; object-fit: contain;">
                                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 delete-preview" data-input-id="government_id_2">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Government ID #3 <span class="text-muted">(Optional)</span></label>
                                    <input type="file" name="government_id_3" id="government_id_3" class="form-control file-input-with-preview" accept=".pdf,.jpg,.jpeg,.png" data-preview-target="gov-id-3-preview">
                                    @error('government_id_3')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    <div id="gov-id-3-preview" class="image-preview-container mt-3" style="display: none;">
                                        <div class="position-relative d-inline-block">
                                            <img src="" alt="Government ID #3 Preview" class="img-thumbnail" style="max-width: 250px; max-height: 250px; object-fit: contain;">
                                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 delete-preview" data-input-id="government_id_3">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <hr class="my-4">

                        <h5 class="fw-bold mb-3"><i class="bi bi-bank me-1"></i>Bank Statement <span class="text-danger">*</span></h5>
                        <p class="text-muted small">Upload a recent bank statement showing your name and account details.</p>
                        <div class="mb-4">
                            <input type="file" name="bank_statement" id="bank_statement" class="form-control file-input-with-preview" accept=".pdf,.jpg,.jpeg,.png" required data-preview-target="bank-statement-preview">
                            @error('bank_statement')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div id="bank-statement-preview" class="image-preview-container mt-3" style="display: none;">
                                <div class="position-relative d-inline-block">
                                    <img src="" alt="Bank Statement Preview" class="img-thumbnail" style="max-width: 250px; max-height: 250px; object-fit: contain;">
                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 delete-preview" data-input-id="bank_statement">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>

                        @if($type === 'business')
                            <hr class="my-4">

                            <h5 class="fw-bold mb-3"><i class="bi bi-building me-1"></i>Business Documents</h5>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Business Permit <span class="text-danger">*</span></label>
                                    <input type="file" name="business_permit" id="business_permit" class="form-control file-input-with-preview" accept=".pdf,.jpg,.jpeg,.png" required data-preview-target="business-permit-preview">
                                    @error('business_permit')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    <div id="business-permit-preview" class="image-preview-container mt-3" style="display: none;">
                                        <div class="position-relative d-inline-block">
                                            <img src="" alt="Business Permit Preview" class="img-thumbnail" style="max-width: 250px; max-height: 250px; object-fit: contain;">
                                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 delete-preview" data-input-id="business_permit">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">BIR Certificate of Registration <span class="text-danger">*</span></label>
                                    <input type="file" name="bir_certificate" id="bir_certificate" class="form-control file-input-with-preview" accept=".pdf,.jpg,.jpeg,.png" required data-preview-target="bir-certificate-preview">
                                    @error('bir_certificate')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    <div id="bir-certificate-preview" class="image-preview-container mt-3" style="display: none;">
                                        <div class="position-relative d-inline-block">
                                            <img src="" alt="BIR Certificate Preview" class="img-thumbnail" style="max-width: 250px; max-height: 250px; object-fit: contain;">
                                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 delete-preview" data-input-id="bir_certificate">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Official Receipt Sample <span class="text-danger">*</span></label>
                                    <input type="file" name="official_receipt_sample" id="official_receipt_sample" class="form-control file-input-with-preview" accept=".pdf,.jpg,.jpeg,.png" required data-preview-target="official-receipt-preview">
                                    @error('official_receipt_sample')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    <div id="official-receipt-preview" class="image-preview-container mt-3" style="display: none;">
                                        <div class="position-relative d-inline-block">
                                            <img src="" alt="Official Receipt Preview" class="img-thumbnail" style="max-width: 250px; max-height: 250px; object-fit: contain;">
                                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 delete-preview" data-input-id="official_receipt_sample">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">DTI Registration <span class="text-muted">(if sole proprietor)</span></label>
                                    <input type="file" name="dti_registration" id="dti_registration" class="form-control file-input-with-preview" accept=".pdf,.jpg,.jpeg,.png" data-preview-target="dti-registration-preview">
                                    @error('dti_registration')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    <div id="dti-registration-preview" class="image-preview-container mt-3" style="display: none;">
                                        <div class="position-relative d-inline-block">
                                            <img src="" alt="DTI Registration Preview" class="img-thumbnail" style="max-width: 250px; max-height: 250px; object-fit: contain;">
                                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 delete-preview" data-input-id="dti_registration">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">SEC Registration <span class="text-muted">(if corporation/partnership)</span></label>
                                    <input type="file" name="sec_registration" id="sec_registration" class="form-control file-input-with-preview" accept=".pdf,.jpg,.jpeg,.png" data-preview-target="sec-registration-preview">
                                    @error('sec_registration')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    <div id="sec-registration-preview" class="image-preview-container mt-3" style="display: none;">
                                        <div class="position-relative d-inline-block">
                                            <img src="" alt="SEC Registration Preview" class="img-thumbnail" style="max-width: 250px; max-height: 250px; object-fit: contain;">
                                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 delete-preview" data-input-id="sec_registration">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <hr class="my-4">

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('auctions.verification.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Back
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-send me-1"></i>Submit Verification
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const phoneDisplay = document.querySelector('[name="phone_display"]');
        const phoneHidden = document.getElementById('phone_hidden');
        function syncPhone() {
            const val = phoneDisplay.value.replace(/\D/g, '');
            phoneHidden.value = val ? '+63' + val : '';
        }
        phoneDisplay.addEventListener('input', syncPhone);
        syncPhone();

        // Image preview functionality
        const fileInputs = document.querySelectorAll('.file-input-with-preview');
        
        fileInputs.forEach(input => {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                const previewTargetId = this.getAttribute('data-preview-target');
                const previewContainer = document.getElementById(previewTargetId);
                
                if (!file) {
                    if (previewContainer) {
                        previewContainer.style.display = 'none';
                    }
                    return;
                }
                
                if (!previewContainer) return;
                
                const fileType = file.type;
                const img = previewContainer.querySelector('img');
                
                // Check if it's an image file
                if (fileType.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        img.src = e.target.result;
                        previewContainer.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else if (fileType === 'application/pdf') {
                    // Show PDF icon for PDF files
                    img.src = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiB2aWV3Qm94PSIwIDAgMjQgMjQiIGZpbGw9Im5vbmUiIHN0cm9rZT0iIzY2NiIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiPjxwYXRoIGQ9Ik0xNCAySDZhMiAyIDAgMCAwLTIgMnYxNmEyIDIgMCAwIDAgMiAyaDEyYTIgMiAwIDAgMCAyLTJWOHoiPjwvcGF0aD48cG9seWxpbmUgcG9pbnRzPSIxNCAyIDE0IDggMjAgOCI+PC9wb2x5bGluZT48L3N2Zz4=';
                    img.alt = 'PDF Document';
                    previewContainer.style.display = 'block';
                    
                    // Add PDF label
                    const pdfLabel = previewContainer.querySelector('.pdf-label');
                    if (!pdfLabel) {
                        const label = document.createElement('div');
                        label.className = 'pdf-label text-center mt-2 text-muted small';
                        label.textContent = file.name;
                        previewContainer.querySelector('.position-relative').appendChild(label);
                    }
                }
            });
        });
        
        // Delete preview functionality
        const deleteButtons = document.querySelectorAll('.delete-preview');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const inputId = this.getAttribute('data-input-id');
                const fileInput = document.getElementById(inputId);
                const previewTargetId = fileInput.getAttribute('data-preview-target');
                const previewContainer = document.getElementById(previewTargetId);
                
                // Clear the file input
                fileInput.value = '';
                
                // Hide the preview
                if (previewContainer) {
                    previewContainer.style.display = 'none';
                    const img = previewContainer.querySelector('img');
                    if (img) {
                        img.src = '';
                    }
                    
                    // Remove PDF label if exists
                    const pdfLabel = previewContainer.querySelector('.pdf-label');
                    if (pdfLabel) {
                        pdfLabel.remove();
                    }
                }
            });
        });
    });
</script>
@endpush
@endsection
