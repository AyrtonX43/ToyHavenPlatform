@extends('layouts.toyshop')

@section('title', 'Seller Registration - ToyHaven')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                @if(($type ?? request('type')) === 'verified')
                                    <i class="bi bi-shield-check text-success me-2"></i>Full Verified Trusted Shop Registration
                                @else
                                    Seller Registration Form
                                @endif
                            </h4>
                            <p class="text-muted mb-0 small">
                                @if(($type ?? request('type')) === 'verified')
                                    Register as a verified trusted shop with enhanced benefits
                                @else
                                    Fill out the form below to register your business
                                @endif
                            </p>
                        </div>
                        <a href="{{ route('seller.register') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(($type ?? request('type')) === 'verified')
                        <div class="alert alert-success mb-4">
                            <h6 class="alert-heading"><i class="bi bi-star-fill me-2"></i>Full Verified Trusted Shop Benefits:</h6>
                            <ul class="mb-0 small">
                                <li>Verified badge on your shop profile</li>
                                <li>Priority customer support</li>
                                <li>Enhanced trust and credibility</li>
                                <li>Featured placement in search results</li>
                                <li>Access to advanced analytics</li>
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('seller.register.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="registration_type" value="{{ $type ?? request('type', 'basic') }}">

                        <h5 class="mb-3">Business Information</h5>
                        <div class="mb-3">
                            <label class="form-label">Business Name <span class="text-danger">*</span></label>
                            <input type="text" name="business_name" class="form-control @error('business_name') is-invalid @enderror" value="{{ old('business_name') }}" required>
                            @error('business_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Business Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone-fill me-1"></i>+63</span>
                                    @php
                                        $oldPhone = old('phone');
                                        $phoneDisplayValue = '';
                                        if ($oldPhone) {
                                            // If old value has +63, remove it for display
                                            $phoneDisplayValue = strpos($oldPhone, '+63') === 0 ? substr($oldPhone, 3) : $oldPhone;
                                        } else {
                                            $phoneDisplayValue = $prefilledData['phone'] ?? '';
                                        }
                                        $phoneHiddenValue = $oldPhone ?: ($prefilledData['phone'] ? '+63' . $prefilledData['phone'] : '');
                                    @endphp
                                    <input type="text" id="phone_display" class="form-control @error('phone') is-invalid @enderror" value="{{ $phoneDisplayValue }}" placeholder="9123456789" maxlength="10" pattern="[0-9]{10}" autocomplete="tel">
                                </div>
                                <input type="hidden" id="phone" name="phone" value="{{ $phoneHiddenValue }}">
                                @if(!empty($prefilledData['phone']) && !$oldPhone)
                                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Pre-filled from your profile. You can edit if needed.</small>
                                @endif
                                @error('phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $prefilledData['email'] ?? '') }}" required>
                                @if(!empty($prefilledData['email']))
                                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Pre-filled from your profile. You can edit if needed.</small>
                                @endif
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <h5 class="mb-3 mt-4">Business Address</h5>
                        @if(!empty($prefilledData['address']) || !empty($prefilledData['city']))
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-info-circle me-2"></i>Address fields are pre-filled from your default address in profile settings. You can edit if needed.
                            </div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Full Address <span class="text-danger">*</span></label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2" required>{{ old('address', $prefilledData['address'] ?? '') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $prefilledData['city'] ?? '') }}" required>
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Province <span class="text-danger">*</span></label>
                                <input type="text" name="province" class="form-control @error('province') is-invalid @enderror" value="{{ old('province', $prefilledData['province'] ?? '') }}" required>
                                @error('province')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Postal Code <span class="text-danger">*</span></label>
                                <input type="text" name="postal_code" class="form-control @error('postal_code') is-invalid @enderror" value="{{ old('postal_code', $prefilledData['postal_code'] ?? '') }}" required>
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <h5 class="mb-3 mt-4">Required Documents</h5>
                        <p class="text-muted small">Please upload the following documents for verification:</p>

                        @if(($type ?? request('type')) === 'verified')
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-info-circle me-2"></i>For full verified trusted shop status, additional documents are required.
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Business Registration <span class="text-danger">*</span></label>
                                <input type="file" name="business_registration" class="form-control @error('business_registration') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                                <small class="text-muted d-block">BIR Form 2303 (COR) + DTI/SEC Permit. Upload the original PDF or a high-res scan. Do not upload a screenshot.</small>
                                <small class="text-muted">PDF, JPG, PNG (Max: 5MB)</small>
                                @error('business_registration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Brand Rights <span class="text-danger">*</span></label>
                                <input type="file" name="brand_rights" class="form-control @error('brand_rights') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                                <small class="text-muted d-block">Trademark Certificate OR Letter of Authorization (LOA). If you are a distributor, the LOA must be on the manufacturer's letterhead, signed, and dated within the last 1 year.</small>
                                <small class="text-muted">PDF, JPG, PNG (Max: 5MB)</small>
                                @error('brand_rights')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Primary ID <span class="text-danger">*</span></label>
                            <input type="file" name="id_document" class="form-control @error('id_document') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                            <small class="text-muted">Passport, Driver's License, UMID, or National ID. PDF, JPG, PNG (Max: 5MB)</small>
                            @error('id_document')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Bank Document <span class="text-danger">*</span></label>
                            <input type="file" name="bank_document" class="form-control @error('bank_document') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                            <small class="text-muted d-block">Bank statement or Passbook photo.</small>
                            <small class="text-warning d-block"><strong>Crucial:</strong> The name on the ID must match the bank account name exactly. (e.g., if ID says "Maria A. Cruz", Bank cannot be "Maria Cruz").</small>
                            <small class="text-muted">PDF, JPG, PNG (Max: 5MB)</small>
                            @error('bank_document')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 {{ $errors->has('toy_category_ids') ? 'is-invalid' : '' }}">
                            <label class="form-label">Toy Category You Sell <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-2" id="toy-category-buttons">
                                @foreach($categories ?? [] as $cat)
                                    @php $oldIds = old('toy_category_ids', []); $isOld = is_array($oldIds) && in_array($cat->id, $oldIds); @endphp
                                    <input type="checkbox" class="btn-check" name="toy_category_ids[]" value="{{ $cat->id }}" id="toy_cat_{{ $cat->id }}" {{ $isOld ? 'checked' : '' }} autocomplete="off">
                                    <label class="btn btn-outline-primary text-start d-block mb-0 py-2 px-3" for="toy_cat_{{ $cat->id }}" style="min-width: 200px; max-width: 280px;">
                                        <span class="fw-semibold d-block">{{ $cat->name }}</span>
                                        @if(!empty($cat->description))
                                            <small class="text-muted d-block mt-1 lh-sm">{{ $cat->description }}</small>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                            <small class="text-muted d-block mt-2">Select one or more toy categories you sell. Click to toggle.</small>
                            @error('toy_category_ids')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Your documents will be reviewed by our admin team. You will be notified once your account is verified.
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            @if(($type ?? request('type')) === 'verified')
                                <i class="bi bi-shield-check me-2"></i>Submit Verified Shop Registration
                            @else
                                Submit Registration
                            @endif
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Phone number formatting
    const phoneDisplay = document.getElementById('phone_display');
    const phoneHidden = document.getElementById('phone');
    
    if (phoneDisplay && phoneHidden) {
        // Initialize hidden field if display has value
        if (phoneDisplay.value) {
            const phoneValue = phoneDisplay.value.replace(/[^0-9]/g, '');
            if (phoneValue.length === 10) {
                phoneHidden.value = '+63' + phoneValue;
            }
        }
        
        // Format phone input to only allow numbers
        phoneDisplay.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Update hidden field with full phone number (+63 prefix)
            if (this.value.length === 10) {
                phoneHidden.value = '+63' + this.value;
            } else {
                phoneHidden.value = '';
            }
        });
        
        // On form submit, ensure full phone is set
        document.querySelector('form').addEventListener('submit', function(e) {
            const phoneValue = phoneDisplay.value.replace(/[^0-9]/g, '');
            if (phoneValue.length === 10) {
                phoneHidden.value = '+63' + phoneValue;
            } else if (phoneValue.length > 0) {
                e.preventDefault();
                alert('Please enter a valid 10-digit phone number.');
                phoneDisplay.focus();
                return false;
            } else {
                e.preventDefault();
                alert('Phone number is required.');
                phoneDisplay.focus();
                return false;
            }
        });
    }
});
</script>
@endpush
@endsection
