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
                    @if($syncedData)
                        <div class="alert alert-success d-flex align-items-start mb-4">
                            <i class="bi bi-arrow-repeat me-2 mt-1" style="font-size: 1.2rem;"></i>
                            <div>
                                <strong>ToyShop Seller Data Detected!</strong><br>
                                <span class="small">Your address, ID, bank statement, and business documents from your ToyShop seller registration have been synced below. You can still replace any file by uploading a new one.</span>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('auctions.verification.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="seller_type" value="{{ $type }}">

                        <h5 class="fw-bold mb-3 mt-2"><i class="bi bi-telephone me-1"></i>Contact Information</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                @php
                                    $defaultPhone = old('phone', $syncedData['phone'] ?? auth()->user()->phone ?? '');
                                    $phoneDisplay = substr($defaultPhone, 0, 3) === '+63' ? substr($defaultPhone, 3) : $defaultPhone;
                                @endphp
                                <div class="input-group">
                                    <span class="input-group-text">+63</span>
                                    <input type="text" name="phone_display" class="form-control" placeholder="9XXXXXXXXX" value="{{ old('phone_display', $phoneDisplay) }}" maxlength="10">
                                </div>
                                <input type="hidden" name="phone" id="phone_hidden" value="{{ old('phone', $defaultPhone) }}">
                                @if($syncedData && ($syncedData['phone'] ?? false))
                                    <div class="text-success small mt-1"><i class="bi bi-arrow-repeat me-1"></i>Synced from ToyShop registration</div>
                                @endif
                                @error('phone')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Full Address <span class="text-danger">*</span></label>
                                <textarea name="address" class="form-control" rows="2" required>{{ old('address', $syncedData['address'] ?? auth()->user()->address) }}</textarea>
                                @if($syncedData && ($syncedData['address'] ?? false))
                                    <div class="text-success small mt-1"><i class="bi bi-arrow-repeat me-1"></i>Synced from ToyShop registration — feel free to edit</div>
                                @endif
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
                            <input type="file" name="selfie" class="form-control" accept="image/jpeg,image/png" required>
                            @error('selfie')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
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
                                <label class="form-label fw-semibold">
                                    Government ID #1
                                    @if($syncedData && isset($syncedData['documents']['government_id_1']))
                                        <span class="text-muted">(Optional — synced)</span>
                                    @else
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                @if($syncedData && isset($syncedData['documents']['government_id_1']))
                                    <div class="synced-doc-preview mb-2 p-2 bg-success bg-opacity-10 border border-success rounded d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-file-earmark-check text-success me-2"></i>
                                            <span class="small text-success fw-semibold">{{ $syncedData['documents']['government_id_1']['label'] }}</span>
                                        </div>
                                        <a href="{{ asset('storage/' . $syncedData['documents']['government_id_1']['path']) }}" target="_blank" class="btn btn-sm btn-outline-success py-0 px-2">
                                            <i class="bi bi-eye me-1"></i>View
                                        </a>
                                    </div>
                                    <input type="file" name="government_id_1" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                    <div class="text-muted small mt-1"><i class="bi bi-info-circle me-1"></i>Upload a new file to replace the synced document</div>
                                @else
                                    <input type="file" name="government_id_1" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                @endif
                                @error('government_id_1')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            @if($type === 'individual')
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Government ID #2 <span class="text-danger">*</span></label>
                                    <input type="file" name="government_id_2" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                    @error('government_id_2')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Government ID #3 <span class="text-muted">(Optional)</span></label>
                                    <input type="file" name="government_id_3" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                    @error('government_id_3')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif
                        </div>

                        <hr class="my-4">

                        <h5 class="fw-bold mb-3"><i class="bi bi-bank me-1"></i>Bank Statement
                            @if($syncedData && isset($syncedData['documents']['bank_statement']))
                                <span class="text-muted fw-normal fs-6">(Optional — synced)</span>
                            @else
                                <span class="text-danger">*</span>
                            @endif
                        </h5>
                        <p class="text-muted small">Upload a recent bank statement showing your name and account details.</p>
                        <div class="mb-4">
                            @if($syncedData && isset($syncedData['documents']['bank_statement']))
                                <div class="synced-doc-preview mb-2 p-2 bg-success bg-opacity-10 border border-success rounded d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-file-earmark-check text-success me-2"></i>
                                        <span class="small text-success fw-semibold">{{ $syncedData['documents']['bank_statement']['label'] }}</span>
                                    </div>
                                    <a href="{{ asset('storage/' . $syncedData['documents']['bank_statement']['path']) }}" target="_blank" class="btn btn-sm btn-outline-success py-0 px-2">
                                        <i class="bi bi-eye me-1"></i>View
                                    </a>
                                </div>
                                <input type="file" name="bank_statement" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                <div class="text-muted small mt-1"><i class="bi bi-info-circle me-1"></i>Upload a new file to replace the synced document</div>
                            @else
                                <input type="file" name="bank_statement" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                            @endif
                            @error('bank_statement')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($type === 'business')
                            <hr class="my-4">

                            <h5 class="fw-bold mb-3"><i class="bi bi-building me-1"></i>Business Documents</h5>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Business Permit
                                        @if($syncedData && isset($syncedData['documents']['business_permit']))
                                            <span class="text-muted">(Optional — synced)</span>
                                        @else
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    @if($syncedData && isset($syncedData['documents']['business_permit']))
                                        <div class="synced-doc-preview mb-2 p-2 bg-success bg-opacity-10 border border-success rounded d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-file-earmark-check text-success me-2"></i>
                                                <span class="small text-success fw-semibold">{{ $syncedData['documents']['business_permit']['label'] }}</span>
                                            </div>
                                            <a href="{{ asset('storage/' . $syncedData['documents']['business_permit']['path']) }}" target="_blank" class="btn btn-sm btn-outline-success py-0 px-2">
                                                <i class="bi bi-eye me-1"></i>View
                                            </a>
                                        </div>
                                        <input type="file" name="business_permit" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                        <div class="text-muted small mt-1"><i class="bi bi-info-circle me-1"></i>Upload a new file to replace the synced document</div>
                                    @else
                                        <input type="file" name="business_permit" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                    @endif
                                    @error('business_permit')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">BIR Certificate of Registration <span class="text-danger">*</span></label>
                                    <input type="file" name="bir_certificate" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                    @error('bir_certificate')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Official Receipt Sample <span class="text-danger">*</span></label>
                                    <input type="file" name="official_receipt_sample" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                    @error('official_receipt_sample')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">DTI Registration <span class="text-muted">(if sole proprietor)</span></label>
                                    <input type="file" name="dti_registration" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                    @error('dti_registration')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">SEC Registration <span class="text-muted">(if corporation/partnership)</span></label>
                                    <input type="file" name="sec_registration" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                    @error('sec_registration')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
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
    });
</script>
@endpush
@endsection
