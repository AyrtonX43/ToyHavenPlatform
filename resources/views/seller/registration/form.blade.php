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
                                    <i class="bi bi-shield-check text-success me-2"></i>Verified Trusted Toyshop Registration
                                @else
                                    <i class="bi bi-shop text-primary me-2"></i>Local Business Toyshop Registration
                                @endif
                            </h4>
                            <p class="text-muted mb-0 small">
                                @if(($type ?? request('type')) === 'verified')
                                    Register as a verified trusted toyshop with enhanced benefits
                                @else
                                    Fill out the form below to register your local toy business
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
                            <h6 class="alert-heading"><i class="bi bi-star-fill me-2"></i>Verified Trusted Toyshop Benefits:</h6>
                            <ul class="mb-0 small">
                                <li><i class="bi bi-check-circle-fill text-success me-1"></i>Verified badge on your shop profile</li>
                                <li><i class="bi bi-check-circle-fill text-success me-1"></i>Priority customer support</li>
                                <li><i class="bi bi-check-circle-fill text-success me-1"></i>Enhanced trust and credibility</li>
                                <li><i class="bi bi-check-circle-fill text-success me-1"></i>Featured placement in search results</li>
                                <li><i class="bi bi-check-circle-fill text-success me-1"></i>Access to advanced analytics</li>
                                <li><i class="bi bi-check-circle-fill text-success me-1"></i>Higher customer conversion rates</li>
                            </ul>
                        </div>
                    @else
                        <div class="alert alert-info mb-4">
                            <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Local Business Toyshop Registration:</h6>
                            <p class="mb-0 small">Perfect for small local toy businesses. You can upgrade to Verified Trusted Toyshop status later by providing additional business documents.</p>
                        </div>
                    @endif

                    <form action="{{ route('seller.register.store') }}" method="POST" enctype="multipart/form-data" accept-charset="UTF-8">
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
                            <label class="form-label">Business Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="5" style="text-align: justify;" required>{{ old('description') }}</textarea>
                            <small class="text-muted">Describe your business, products, and what makes you unique. Text will be justified for better readability.</small>
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
                            <label class="form-label">House/Apartment No., Building/Street, Residence <span class="text-danger">*</span></label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2" placeholder="e.g., Unit 123, ABC Building, 456 Main Street" required>{{ old('address', $prefilledData['address'] ?? '') }}</textarea>
                            <small class="text-muted">Enter your complete street address including house/apartment number, building name, and street</small>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Region <span class="text-danger">*</span></label>
                                <select name="region" id="region" class="form-select @error('region') is-invalid @enderror" required>
                                    <option value="">Select Region</option>
                                </select>
                                @error('region')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Province <span class="text-danger">*</span></label>
                                <select name="province" id="province" class="form-select @error('province') is-invalid @enderror" required disabled>
                                    <option value="">Select Province</option>
                                </select>
                                @error('province')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">City/Municipality <span class="text-danger">*</span></label>
                                <select name="city" id="city" class="form-select @error('city') is-invalid @enderror" required disabled>
                                    <option value="">Select City/Municipality</option>
                                </select>
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Barangay <span class="text-danger">*</span></label>
                                <select name="barangay" id="barangay" class="form-select @error('barangay') is-invalid @enderror" required disabled>
                                    <option value="">Select Barangay</option>
                                </select>
                                @error('barangay')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Postal Code <span class="text-danger">*</span></label>
                                <input type="text" name="postal_code" id="postal_code" class="form-control @error('postal_code') is-invalid @enderror" value="{{ old('postal_code', $prefilledData['postal_code'] ?? '') }}" placeholder="4 digits" maxlength="4" pattern="[0-9]{4}" required>
                                <small class="text-muted">4-digit postal code</small>
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <h5 class="mb-3 mt-4">Required Documents</h5>
                        <p class="text-muted small">Please upload the following documents for verification. You can preview and change your uploads before submitting.</p>

                        @if(($type ?? request('type')) === 'verified')
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-info-circle me-2"></i>For Verified Trusted Toyshop status, additional documents are required.
                            </div>

                            <!-- Business Permit -->
                            <div class="mb-4">
                                <label class="form-label">Business Permit <span class="text-danger">*</span></label>
                                <div class="document-upload-wrapper">
                                    <input type="file" name="business_permit" id="business_permit" class="form-control document-input @error('business_permit') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <div class="document-preview mt-2" id="business_permit_preview" style="display: none;">
                                        <div class="card">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-file-earmark-check text-success me-2" style="font-size: 2rem;"></i>
                                                        <div>
                                                            <div class="fw-semibold document-name"></div>
                                                            <small class="text-muted document-size"></small>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-danger document-remove" data-target="business_permit">
                                                        <i class="bi bi-trash me-1"></i>Change
                                                    </button>
                                                </div>
                                                <img class="document-image mt-2" style="max-width: 100%; max-height: 200px; display: none;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted d-block">Mayor's Permit or Business Permit from your local government unit.</small>
                                <small class="text-muted">PDF, JPG, PNG (Max: 5MB)</small>
                                @error('business_permit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- BIR Certificate -->
                            <div class="mb-4">
                                <label class="form-label">BIR Certificate of Registration <span class="text-danger">*</span></label>
                                <div class="document-upload-wrapper">
                                    <input type="file" name="bir_certificate" id="bir_certificate" class="form-control document-input @error('bir_certificate') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <div class="document-preview mt-2" id="bir_certificate_preview" style="display: none;">
                                        <div class="card">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-file-earmark-check text-success me-2" style="font-size: 2rem;"></i>
                                                        <div>
                                                            <div class="fw-semibold document-name"></div>
                                                            <small class="text-muted document-size"></small>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-danger document-remove" data-target="bir_certificate">
                                                        <i class="bi bi-trash me-1"></i>Change
                                                    </button>
                                                </div>
                                                <img class="document-image mt-2" style="max-width: 100%; max-height: 200px; display: none;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted d-block">BIR Form 2303 (Certificate of Registration). Upload the original PDF or a high-res scan.</small>
                                <small class="text-muted">PDF, JPG, PNG (Max: 5MB)</small>
                                @error('bir_certificate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Product Sample -->
                            <div class="mb-4">
                                <label class="form-label">Product Sample <span class="text-danger">*</span></label>
                                <div class="document-upload-wrapper">
                                    <input type="file" name="product_sample" id="product_sample" class="form-control document-input @error('product_sample') is-invalid @enderror" accept=".jpg,.jpeg,.png" required>
                                    <div class="document-preview mt-2" id="product_sample_preview" style="display: none;">
                                        <div class="card">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-file-earmark-check text-success me-2" style="font-size: 2rem;"></i>
                                                        <div>
                                                            <div class="fw-semibold document-name"></div>
                                                            <small class="text-muted document-size"></small>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-danger document-remove" data-target="product_sample">
                                                        <i class="bi bi-trash me-1"></i>Change
                                                    </button>
                                                </div>
                                                <img class="document-image mt-2" style="max-width: 100%; max-height: 200px; display: none;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted d-block">Clear photo of an actual product sample you will be selling (not a stock photo).</small>
                                <small class="text-muted">JPG, PNG (Max: 5MB)</small>
                                @error('product_sample')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <!-- Primary ID -->
                        <div class="mb-4">
                            <label class="form-label">Primary ID <span class="text-danger">*</span></label>
                            <div class="document-upload-wrapper">
                                <input type="file" name="id_document" id="id_document" class="form-control document-input @error('id_document') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                                <div class="document-preview mt-2" id="id_document_preview" style="display: none;">
                                    <div class="card">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-file-earmark-check text-success me-2" style="font-size: 2rem;"></i>
                                                    <div>
                                                        <div class="fw-semibold document-name"></div>
                                                        <small class="text-muted document-size"></small>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger document-remove" data-target="id_document">
                                                    <i class="bi bi-trash me-1"></i>Change
                                                </button>
                                            </div>
                                            <img class="document-image mt-2" style="max-width: 100%; max-height: 200px; display: none;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">Passport, Driver's License, UMID, or National ID. PDF, JPG, PNG (Max: 5MB)</small>
                            @error('id_document')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Facial Verification -->
                        <div class="mb-4">
                            <label class="form-label">Facial Verification (Selfie with ID) <span class="text-danger">*</span></label>
                            <div class="document-upload-wrapper">
                                <input type="file" name="facial_verification" id="facial_verification" class="form-control document-input @error('facial_verification') is-invalid @enderror" accept=".jpg,.jpeg,.png" required>
                                <div class="document-preview mt-2" id="facial_verification_preview" style="display: none;">
                                    <div class="card">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-file-earmark-check text-success me-2" style="font-size: 2rem;"></i>
                                                    <div>
                                                        <div class="fw-semibold document-name"></div>
                                                        <small class="text-muted document-size"></small>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger document-remove" data-target="facial_verification">
                                                    <i class="bi bi-trash me-1"></i>Change
                                                </button>
                                            </div>
                                            <img class="document-image mt-2" style="max-width: 100%; max-height: 200px; display: none;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted d-block">Clear selfie photo holding your Primary ID next to your face. Make sure both your face and ID details are clearly visible.</small>
                            <small class="text-muted">JPG, PNG (Max: 5MB)</small>
                            @error('facial_verification')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Bank Statement -->
                        <div class="mb-4">
                            <label class="form-label">Bank Statement <span class="text-danger">*</span></label>
                            <div class="document-upload-wrapper">
                                <input type="file" name="bank_document" id="bank_document" class="form-control document-input @error('bank_document') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                                <div class="document-preview mt-2" id="bank_document_preview" style="display: none;">
                                    <div class="card">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-file-earmark-check text-success me-2" style="font-size: 2rem;"></i>
                                                    <div>
                                                        <div class="fw-semibold document-name"></div>
                                                        <small class="text-muted document-size"></small>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger document-remove" data-target="bank_document">
                                                    <i class="bi bi-trash me-1"></i>Change
                                                </button>
                                            </div>
                                            <img class="document-image mt-2" style="max-width: 100%; max-height: 200px; display: none;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted d-block">Bank statement or Passbook photo showing your account details.</small>
                            <small class="text-warning d-block"><strong>Important:</strong> The name on the ID must match the bank account name exactly. (e.g., if ID says "Maria A. Cruz", Bank cannot be "Maria Cruz").</small>
                            <small class="text-muted">PDF, JPG, PNG (Max: 5MB)</small>
                            @error('bank_document')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <h5 class="mb-3 mt-4">Toy Categories You Sell</h5>
                        <div class="mb-4 {{ $errors->has('toy_category_ids') ? 'is-invalid' : '' }}">
                            <label class="form-label">Select 1-3 Categories <span class="text-danger">*</span></label>
                            
                            @if(isset($categories) && count($categories) > 0)
                                <div class="row g-3" id="toy-category-buttons">
                                    @foreach($categories as $cat)
                                        @php 
                                            $oldIds = old('toy_category_ids', []); 
                                            $isOld = is_array($oldIds) && in_array($cat->id, $oldIds);
                                            $icon = $cat->getDisplayIcon();
                                        @endphp
                                        <div class="col-md-6 col-lg-4">
                                            <input type="checkbox" class="btn-check category-checkbox" name="toy_category_ids[]" value="{{ $cat->id }}" id="toy_cat_{{ $cat->id }}" {{ $isOld ? 'checked' : '' }} autocomplete="off">
                                            <label class="btn btn-outline-primary text-start w-100 h-100 p-3 category-card" for="toy_cat_{{ $cat->id }}">
                                                <div class="d-flex align-items-start">
                                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; min-width: 50px;">
                                                        <i class="bi {{ $icon }} text-primary" style="font-size: 1.5rem;"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="fw-semibold mb-1">{{ $cat->name }}</div>
                                                        @if(!empty($cat->description))
                                                            <small class="text-muted d-block lh-sm">{{ Str::limit($cat->description, 80) }}</small>
                                                        @else
                                                            <small class="text-muted d-block lh-sm">Quality {{ strtolower($cat->name) }} for all ages</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <i class="bi bi-info-circle me-1"></i>Select between 1 to 3 toy categories that best represent your product range. 
                                    <span id="category-count" class="fw-semibold">0 selected</span>
                                </small>
                            @else
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>No categories available.</strong> Please contact the administrator to set up toy categories.
                                </div>
                            @endif
                            
                            @error('toy_category_ids')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <h5 class="mb-3 mt-4">Social Media Links (Optional)</h5>
                        <p class="text-muted small">Connect your social media accounts to build trust with customers</p>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-facebook text-primary me-1"></i>Facebook Page
                                </label>
                                <input type="url" name="facebook_url" class="form-control @error('facebook_url') is-invalid @enderror" value="{{ old('facebook_url') }}" placeholder="https://facebook.com/yourpage">
                                @error('facebook_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-instagram text-danger me-1"></i>Instagram
                                </label>
                                <input type="url" name="instagram_url" class="form-control @error('instagram_url') is-invalid @enderror" value="{{ old('instagram_url') }}" placeholder="https://instagram.com/youraccount">
                                @error('instagram_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-tiktok me-1"></i>TikTok
                                </label>
                                <input type="url" name="tiktok_url" class="form-control @error('tiktok_url') is-invalid @enderror" value="{{ old('tiktok_url') }}" placeholder="https://tiktok.com/@youraccount">
                                @error('tiktok_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-globe me-1"></i>Website
                                </label>
                                <input type="url" name="website_url" class="form-control @error('website_url') is-invalid @enderror" value="{{ old('website_url') }}" placeholder="https://yourwebsite.com">
                                @error('website_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="alert alert-info mt-4">
                            <i class="bi bi-info-circle"></i> Your documents will be reviewed by our admin team. You will be notified once your account is verified.
                        </div>

                        <button type="submit" class="btn btn-lg w-100 {{ ($type ?? request('type')) === 'verified' ? 'btn-success' : 'btn-primary' }}">
                            @if(($type ?? request('type')) === 'verified')
                                <i class="bi bi-shield-check me-2"></i>Submit Verified Trusted Toyshop Registration
                            @else
                                <i class="bi bi-shop me-2"></i>Submit Local Business Toyshop Registration
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
// Cache buster - force reload of this script
console.log('Seller Registration Form Script Loaded - Version 2.1');

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

    // Philippine Address Cascading Dropdowns using PSGC API
    const regionSelect = document.getElementById('region');
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const barangaySelect = document.getElementById('barangay');
    const API_BASE = 'https://psgc.cloud/api';

    // Helper function to normalize special characters
    function normalizeText(text) {
        if (!text) return text;
        
        // Create a mapping of special characters to their normalized versions
        const charMap = {
            'ñ': 'n', 'Ñ': 'N',
            'á': 'a', 'Á': 'A',
            'é': 'e', 'É': 'E',
            'í': 'i', 'Í': 'I',
            'ó': 'o', 'Ó': 'O',
            'ú': 'u', 'Ú': 'U',
            'ü': 'u', 'Ü': 'U',
            // Handle encoding issues
            'Ã±': 'n', 'Ã'': 'N',
            'Ã¡': 'a', 'Ã©': 'e', 'Ã­': 'i', 'Ã³': 'o', 'Ãº': 'u'
        };
        
        let normalized = text;
        for (const [special, normal] of Object.entries(charMap)) {
            normalized = normalized.split(special).join(normal);
        }
        
        return normalized;
    }

    // Load regions on page load
    fetch(`${API_BASE}/regions`)
        .then(response => response.json())
        .then(data => {
            console.log('Regions loaded:', data.length);
            data.forEach(region => {
                const originalName = region.name;
                const normalizedName = normalizeText(region.name);
                
                // Debug: Log if normalization changed anything
                if (originalName !== normalizedName) {
                    console.log('Normalized:', originalName, '→', normalizedName);
                }
                
                const option = document.createElement('option');
                option.value = normalizedName;
                option.textContent = normalizedName;
                option.dataset.code = region.code;
                regionSelect.appendChild(option);
            });
            
            // Pre-select if old value exists
            const oldRegion = "{{ old('region', $prefilledData['region'] ?? '') }}";
            if (oldRegion) {
                regionSelect.value = oldRegion;
                regionSelect.dispatchEvent(new Event('change'));
            }
        })
        .catch(error => console.error('Error loading regions:', error));

    // Load provinces when region changes
    regionSelect.addEventListener('change', function() {
        provinceSelect.innerHTML = '<option value="">Select Province</option>';
        citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        
        provinceSelect.disabled = true;
        citySelect.disabled = true;
        barangaySelect.disabled = true;

        if (!this.value) return;

        const selectedOption = this.options[this.selectedIndex];
        const regionCode = selectedOption.dataset.code;
        const regionName = this.value;

        // Check if NCR (National Capital Region) - it has no provinces, only cities
        if (regionName.includes('NCR') || regionName.includes('National Capital Region') || regionName.includes('Metro Manila')) {
            // For NCR, load cities directly
            provinceSelect.innerHTML = '<option value="Metro Manila">Metro Manila</option>';
            provinceSelect.value = 'Metro Manila';
            provinceSelect.disabled = true; // Disable since there's only one option
            
            // Load cities for NCR
            fetch(`${API_BASE}/regions/${regionCode}/cities-municipalities`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(city => {
                        const option = document.createElement('option');
                        option.value = normalizeText(city.name);
                        option.textContent = normalizeText(city.name);
                        option.dataset.code = city.code;
                        citySelect.appendChild(option);
                    });
                    citySelect.disabled = false;
                    
                    // Pre-select if old value exists
                    const oldCity = "{{ old('city', $prefilledData['city'] ?? '') }}";
                    if (oldCity) {
                        citySelect.value = oldCity;
                        citySelect.dispatchEvent(new Event('change'));
                    }
                })
                .catch(error => console.error('Error loading NCR cities:', error));
        } else {
            // For other regions, load provinces normally
            fetch(`${API_BASE}/regions/${regionCode}/provinces`)
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        // If no provinces, try loading cities directly
                        fetch(`${API_BASE}/regions/${regionCode}/cities-municipalities`)
                            .then(response => response.json())
                            .then(cityData => {
                                cityData.forEach(city => {
                                    const option = document.createElement('option');
                                    option.value = normalizeText(city.name);
                                    option.textContent = normalizeText(city.name);
                                    option.dataset.code = city.code;
                                    citySelect.appendChild(option);
                                });
                                citySelect.disabled = false;
                            })
                            .catch(error => console.error('Error loading cities:', error));
                    } else {
                        data.forEach(province => {
                            const option = document.createElement('option');
                            option.value = normalizeText(province.name);
                            option.textContent = normalizeText(province.name);
                            option.dataset.code = province.code;
                            provinceSelect.appendChild(option);
                        });
                        provinceSelect.disabled = false;
                        
                        // Pre-select if old value exists
                        const oldProvince = "{{ old('province', $prefilledData['province'] ?? '') }}";
                        if (oldProvince) {
                            provinceSelect.value = oldProvince;
                            provinceSelect.dispatchEvent(new Event('change'));
                        }
                    }
                })
                .catch(error => console.error('Error loading provinces:', error));
        }
    });

    // Load cities when province changes
    provinceSelect.addEventListener('change', function() {
        citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        
        citySelect.disabled = true;
        barangaySelect.disabled = true;

        if (!this.value) return;

        const selectedOption = this.options[this.selectedIndex];
        const provinceCode = selectedOption.dataset.code;

        fetch(`${API_BASE}/provinces/${provinceCode}/cities-municipalities`)
            .then(response => response.json())
            .then(data => {
                data.forEach(city => {
                    const option = document.createElement('option');
                    option.value = normalizeText(city.name);
                    option.textContent = normalizeText(city.name);
                    option.dataset.code = city.code;
                    citySelect.appendChild(option);
                });
                citySelect.disabled = false;
                
                // Pre-select if old value exists
                const oldCity = "{{ old('city', $prefilledData['city'] ?? '') }}";
                if (oldCity) {
                    citySelect.value = oldCity;
                    citySelect.dispatchEvent(new Event('change'));
                }
            })
            .catch(error => console.error('Error loading cities:', error));
    });

    // Load barangays when city changes
    citySelect.addEventListener('change', function() {
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        barangaySelect.disabled = true;

        if (!this.value) return;

        const selectedOption = this.options[this.selectedIndex];
        const cityCode = selectedOption.dataset.code;

        fetch(`${API_BASE}/cities-municipalities/${cityCode}/barangays`)
            .then(response => response.json())
            .then(data => {
                data.forEach(barangay => {
                    const option = document.createElement('option');
                    option.value = normalizeText(barangay.name);
                    option.textContent = normalizeText(barangay.name);
                    barangaySelect.appendChild(option);
                });
                barangaySelect.disabled = false;
                
                // Pre-select if old value exists
                const oldBarangay = "{{ old('barangay') }}";
                if (oldBarangay) {
                    barangaySelect.value = oldBarangay;
                }
            })
            .catch(error => console.error('Error loading barangays:', error));
    });

    // Postal code validation
    const postalCodeInput = document.getElementById('postal_code');
    if (postalCodeInput) {
        postalCodeInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 4);
        });
    }

    // Document upload preview and management
    const documentInputs = document.querySelectorAll('.document-input');
    documentInputs.forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;

            const previewId = this.id + '_preview';
            const preview = document.getElementById(previewId);
            
            if (!preview) return;

            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                this.value = '';
                return;
            }

            // Show preview
            preview.style.display = 'block';
            preview.querySelector('.document-name').textContent = file.name;
            preview.querySelector('.document-size').textContent = formatFileSize(file.size);

            // Show image preview if it's an image
            const imagePreview = preview.querySelector('.document-image');
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
            }
        });
    });

    // Document remove/change buttons
    document.querySelectorAll('.document-remove').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);
            const preview = document.getElementById(targetId + '_preview');
            
            if (input) {
                input.value = '';
            }
            if (preview) {
                preview.style.display = 'none';
                const imagePreview = preview.querySelector('.document-image');
                if (imagePreview) {
                    imagePreview.src = '';
                }
            }
        });
    });

    // Helper function to format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // Toy category selection (limit 1-3)
    const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
    const categoryCount = document.getElementById('category-count');
    
    function updateCategoryCount() {
        const checkedCount = document.querySelectorAll('.category-checkbox:checked').length;
        categoryCount.textContent = `${checkedCount} selected`;
        
        // Disable unchecked boxes if 3 are selected
        if (checkedCount >= 3) {
            categoryCheckboxes.forEach(checkbox => {
                if (!checkbox.checked) {
                    checkbox.disabled = true;
                    checkbox.parentElement.querySelector('label').classList.add('opacity-50');
                }
            });
        } else {
            categoryCheckboxes.forEach(checkbox => {
                checkbox.disabled = false;
                checkbox.parentElement.querySelector('label').classList.remove('opacity-50');
            });
        }
    }
    
    categoryCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateCategoryCount);
    });
    
    // Initialize count
    updateCategoryCount();

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const checkedCount = document.querySelectorAll('.category-checkbox:checked').length;
        if (checkedCount < 1 || checkedCount > 3) {
            e.preventDefault();
            alert('Please select between 1 to 3 toy categories.');
            document.getElementById('toy-category-buttons').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
    });
});
</script>

<style>
.category-card {
    transition: all 0.2s ease;
    border-width: 2px !important;
}

.category-checkbox:checked + .category-card {
    background-color: var(--bs-primary) !important;
    color: white !important;
    border-color: var(--bs-primary) !important;
}

.category-checkbox:checked + .category-card .text-muted {
    color: rgba(255, 255, 255, 0.8) !important;
}

.category-checkbox:checked + .category-card .bg-primary {
    background-color: rgba(255, 255, 255, 0.2) !important;
}

.category-checkbox:checked + .category-card .text-primary {
    color: white !important;
}

.category-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
</style>
@endpush
@endsection
