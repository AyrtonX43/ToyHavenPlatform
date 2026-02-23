@extends('layouts.seller')

@section('title', 'Business Page Settings - ToyHaven')

@section('page-title', 'Business Page Settings')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Business Page Settings</h4>
        <p class="text-muted mb-0">Customize your business page appearance and information</p>
    </div>
    <div>
        <a href="{{ route('seller.business-page.preview') }}" class="btn btn-outline-primary" target="_blank">
            <i class="bi bi-eye me-1"></i> Preview Page
        </a>
        <a href="{{ route('seller.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>
</div>

@if(isset($pendingRevisions) && $pendingRevisions->isNotEmpty())
    <div class="alert alert-info alert-dismissible fade show mb-4">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Pending approval:</strong> You have {{ $pendingRevisions->count() }} business page change(s) waiting for admin approval.
        Your public page will update after they are approved.
        <ul class="mb-0 mt-2">
            @foreach($pendingRevisions as $rev)
                <li>
                    @if($rev->type === 'general') General settings
                    @elseif($rev->type === 'contact') Contact information
                    @else Social links
                    @endif
                    (submitted {{ $rev->created_at->diffForHumans() }})
                </li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Tabs Navigation -->
<ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ (session('tab') ?? 'general') === 'general' ? 'active' : '' }}" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
            <i class="bi bi-gear me-1"></i> General Settings
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ (session('tab') ?? '') === 'contact' ? 'active' : '' }}" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">
            <i class="bi bi-telephone-email me-1"></i> Contact Information
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ (session('tab') ?? '') === 'social' ? 'active' : '' }}" id="social-tab" data-bs-toggle="tab" data-bs-target="#social" type="button" role="tab">
            <i class="bi bi-share me-1"></i> Social Media Links
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ (session('tab') ?? '') === 'payment-qr' ? 'active' : '' }}" id="payment-qr-tab" data-bs-toggle="tab" data-bs-target="#payment-qr" type="button" role="tab">
            <i class="bi bi-qr-code me-1"></i> Payment QR Codes
        </button>
    </li>
</ul>

    <!-- Tab Content -->
    <div class="tab-content" id="settingsTabsContent">
        <!-- General Settings Tab -->
        <div class="tab-pane fade {{ (session('tab') ?? 'general') === 'general' ? 'show active' : '' }}" id="general" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">General Page Settings</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('seller.business-page.settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="page_name" class="form-label">Page Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('page_name') is-invalid @enderror" 
                                       id="page_name" name="page_name" 
                                       value="{{ old('page_name', $pageSettings->page_name ?? $seller->business_name) }}" 
                                       placeholder="Enter your business page name">
                                @error('page_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">This will be displayed as your business page title</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="layout_type" class="form-label">Layout Type</label>
                                <select class="form-select @error('layout_type') is-invalid @enderror" 
                                        id="layout_type" name="layout_type">
                                    <option value="grid" {{ old('layout_type', $pageSettings->layout_type ?? 'grid') === 'grid' ? 'selected' : '' }}>Grid Layout</option>
                                    <option value="list" {{ old('layout_type', $pageSettings->layout_type ?? 'grid') === 'list' ? 'selected' : '' }}>List Layout</option>
                                    <option value="featured" {{ old('layout_type', $pageSettings->layout_type ?? 'grid') === 'featured' ? 'selected' : '' }}>Featured Layout</option>
                                </select>
                                @error('layout_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="business_description" class="form-label">Business Description</label>
                            <textarea class="form-control @error('business_description') is-invalid @enderror" 
                                      id="business_description" name="business_description" 
                                      rows="5" 
                                      placeholder="Tell customers about your business...">{{ old('business_description', $pageSettings->business_description ?? $seller->description) }}</textarea>
                            @error('business_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maximum 5000 characters</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="profile_picture" class="form-label">Business profile picture</label>
                                <input type="file" class="form-control @error('profile_picture') is-invalid @enderror" 
                                       id="profile_picture" name="profile_picture" accept="image/*">
                                @error('profile_picture')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($pageSettings->logo_path ?? null)
                                    <div class="mt-2">
                                        <img src="{{ Storage::url($pageSettings->logo_path) }}" alt="Current profile picture" class="img-thumbnail rounded-circle" style="max-width: 120px; max-height: 120px; object-fit: cover;">
                                        <small class="d-block text-muted">Current profile picture</small>
                                    </div>
                                @elseif($seller->logo ?? null)
                                    <div class="mt-2">
                                        <img src="{{ Storage::url($seller->logo) }}" alt="Current (from registration)" class="img-thumbnail rounded-circle" style="max-width: 120px; max-height: 120px; object-fit: cover;">
                                        <small class="d-block text-muted">Using registration logo</small>
                                    </div>
                                @endif
                                <small class="text-muted">Shows on your business page header. Recommended: square, 300×300px, max 2MB (JPEG, PNG, JPG, GIF)</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="banner" class="form-label">Banner Image</label>
                                <input type="file" class="form-control @error('banner') is-invalid @enderror" 
                                       id="banner" name="banner" accept="image/*">
                                @error('banner')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($pageSettings->banner_path ?? null)
                                    <div class="mt-2">
                                        <img src="{{ Storage::url($pageSettings->banner_path) }}" alt="Current Banner" class="img-thumbnail" style="max-width: 100%; max-height: 150px;">
                                        <small class="d-block text-muted">Current banner</small>
                                    </div>
                                @endif
                                <small class="text-muted">Recommended: 1200×400px, max 5MB (JPEG, PNG, JPG, GIF)</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="primary_color" class="form-label">Primary Color</label>
                                <input type="color" class="form-control form-control-color @error('primary_color') is-invalid @enderror" 
                                       id="primary_color" name="primary_color" 
                                       value="{{ old('primary_color', $pageSettings->primary_color ?? '#007bff') }}">
                                @error('primary_color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="secondary_color" class="form-label">Secondary Color</label>
                                <input type="color" class="form-control form-control-color @error('secondary_color') is-invalid @enderror" 
                                       id="secondary_color" name="secondary_color" 
                                       value="{{ old('secondary_color', $pageSettings->secondary_color ?? '#6c757d') }}">
                                @error('secondary_color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3">SEO Settings</h5>

                        <div class="mb-3">
                            <label for="meta_title" class="form-label">Meta Title</label>
                            <input type="text" class="form-control @error('meta_title') is-invalid @enderror" 
                                   id="meta_title" name="meta_title" 
                                   value="{{ old('meta_title', $pageSettings->meta_title ?? '') }}" 
                                   placeholder="SEO meta title (50-60 characters recommended)">
                            @error('meta_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">This appears in search engine results</small>
                        </div>

                        <div class="mb-3">
                            <label for="meta_description" class="form-label">Meta Description</label>
                            <textarea class="form-control @error('meta_description') is-invalid @enderror" 
                                      id="meta_description" name="meta_description" 
                                      rows="3" 
                                      placeholder="SEO meta description (150-160 characters recommended)">{{ old('meta_description', $pageSettings->meta_description ?? '') }}</textarea>
                            @error('meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="keywords" class="form-label">Keywords</label>
                            <input type="text" class="form-control @error('keywords') is-invalid @enderror" 
                                   id="keywords" name="keywords" 
                                   value="{{ old('keywords', is_array($pageSettings->keywords ?? null) ? implode(', ', $pageSettings->keywords) : '') }}" 
                                   placeholder="keyword1, keyword2, keyword3">
                            @error('keywords')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Separate keywords with commas</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_published" name="is_published" 
                                       value="1" {{ old('is_published', $pageSettings->is_published ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_published">
                                    Publish Business Page
                                </label>
                            </div>
                            <small class="text-muted">When published, your business page will be visible to customers</small>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('seller.dashboard') }}" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-x-lg me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Contact Information Tab -->
        <div class="tab-pane fade {{ (session('tab') ?? '') === 'contact' ? 'show active' : '' }}" id="contact" role="tabpanel">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0">Business Contact Information</h5>
                        <p class="text-muted small mb-0 mt-1">Update your business email and phone. You must verify new contact details after changing them.</p>
                    </div>
                    @auth
                        @php
                            $profileEmail = auth()->user()->email ?? '';
                            $profilePhone = auth()->user()->phone ?? '';
                            $profilePhoneDigits = $profilePhone ? preg_replace('/^\+63/', '', $profilePhone) : '';
                        @endphp
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="contact-sync-from-profile"
                                data-profile-email="{{ $profileEmail }}"
                                data-profile-phone="{{ $profilePhone }}"
                                data-profile-phone-digits="{{ $profilePhoneDigits }}"
                                title="Copy email and phone from your account profile">
                            <i class="bi bi-arrow-repeat me-1"></i> Sync from my account
                        </button>
                    @endauth
                </div>
                <div class="card-body">
                    @if(session('success') && session('tab') === 'contact')
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('seller.business-page.contact.update') }}" method="POST" id="contactForm">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="business_email" class="form-label">Business Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="business_email" name="email" 
                                           value="{{ old('email', $seller->email) }}" 
                                           placeholder="business@example.com">
                                    @if($seller->hasVerifiedBusinessEmail())
                                        <span class="input-group-text bg-success text-white"><i class="bi bi-check-circle me-1"></i>Verified</span>
                                    @elseif($seller->email)
                                        <span class="input-group-text bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Unverified</span>
                                    @endif
                                </div>
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Changing this will require verification via email.</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="business_phone_display" class="form-label">Business Phone <small class="text-muted">(e.g. +63 912 345 6789)</small></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone-fill"></i>+63</span>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                           id="business_phone_display" 
                                           value="{{ old('phone', $seller->phone ? preg_replace('/^\+63/', '', $seller->phone) : '') }}" 
                                           placeholder="912 345 6789" maxlength="10" pattern="[0-9]{10}" title="10-digit Philippine mobile number">
                                    <input type="hidden" name="phone" id="business_phone_full" value="{{ old('phone', $seller->phone) }}">
                                    @if($seller->hasVerifiedBusinessPhone())
                                        <span class="input-group-text bg-success text-white"><i class="bi bi-check-circle me-1"></i>Verified</span>
                                    @elseif($seller->phone)
                                        <span class="input-group-text bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Unverified</span>
                                    @endif
                                </div>
                                @error('phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Save Contact Information
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Social Media Links Tab -->
        <div class="tab-pane fade" id="social" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Social Media Links</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('seller.business-page.social-links.update') }}" method="POST" id="socialLinksForm">
                        @csrf
                        
                        <div id="socialLinksContainer">
                            @if($socialLinks->count() > 0)
                                @foreach($socialLinks as $index => $link)
                                    <div class="social-link-item mb-3 p-3 border rounded">
                                        <div class="row">
                                            <div class="col-md-3 mb-2">
                                                <label class="form-label">Platform</label>
                                                <select class="form-select" name="social_links[{{ $index }}][platform]" required>
                                                    <option value="">Select Platform</option>
                                                    <option value="facebook" {{ $link->platform === 'facebook' ? 'selected' : '' }}>Facebook</option>
                                                    <option value="instagram" {{ $link->platform === 'instagram' ? 'selected' : '' }}>Instagram</option>
                                                    <option value="twitter" {{ $link->platform === 'twitter' ? 'selected' : '' }}>Twitter</option>
                                                    <option value="youtube" {{ $link->platform === 'youtube' ? 'selected' : '' }}>YouTube</option>
                                                    <option value="tiktok" {{ $link->platform === 'tiktok' ? 'selected' : '' }}>TikTok</option>
                                                    <option value="linkedin" {{ $link->platform === 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
                                                    <option value="pinterest" {{ $link->platform === 'pinterest' ? 'selected' : '' }}>Pinterest</option>
                                                    <option value="other" {{ $link->platform === 'other' ? 'selected' : '' }}>Other</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">URL</label>
                                                <input type="url" class="form-control" name="social_links[{{ $index }}][url]" 
                                                       value="{{ $link->url }}" placeholder="https://..." required>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <label class="form-label">Display Name (Optional)</label>
                                                <input type="text" class="form-control" name="social_links[{{ $index }}][display_name]" 
                                                       value="{{ $link->display_name }}" placeholder="Custom name">
                                            </div>
                                            <div class="col-md-2 mb-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-danger btn-sm remove-link">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="social_links[{{ $index }}][is_active]" 
                                                   value="1" {{ $link->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label">Active</label>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="social-link-item mb-3 p-3 border rounded">
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label">Platform</label>
                                            <select class="form-select" name="social_links[0][platform]" required>
                                                <option value="">Select Platform</option>
                                                <option value="facebook">Facebook</option>
                                                <option value="instagram">Instagram</option>
                                                <option value="twitter">Twitter</option>
                                                <option value="youtube">YouTube</option>
                                                <option value="tiktok">TikTok</option>
                                                <option value="linkedin">LinkedIn</option>
                                                <option value="pinterest">Pinterest</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label class="form-label">URL</label>
                                            <input type="url" class="form-control" name="social_links[0][url]" 
                                                   placeholder="https://..." required>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label">Display Name (Optional)</label>
                                            <input type="text" class="form-control" name="social_links[0][display_name]" 
                                                   placeholder="Custom name">
                                        </div>
                                        <div class="col-md-2 mb-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger btn-sm remove-link">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="social_links[0][is_active]" 
                                               value="1" checked>
                                        <label class="form-check-label">Active</label>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <button type="button" class="btn btn-outline-primary mb-3" id="addSocialLink">
                            <i class="bi bi-plus-circle me-1"></i> Add Another Link
                        </button>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('seller.dashboard') }}" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-x-lg me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Save Social Links
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Payment QR Codes Tab -->
        <div class="tab-pane fade {{ (session('tab') ?? '') === 'payment-qr' ? 'show active' : '' }}" id="payment-qr" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-qr-code me-2"></i>Payment QR Codes</h5>
                    <p class="text-muted small mb-0 mt-1">Upload your GCash and PayMaya QR codes. They will be shown to customers during checkout when they choose e-wallet payment.</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('seller.business-page.payment-qr.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label">GCash QR Code</label>
                                <input type="file" class="form-control @error('gcash_qr_code') is-invalid @enderror" 
                                       name="gcash_qr_code" accept="image/*">
                                @error('gcash_qr_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($seller->gcash_qr_code ?? null)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $seller->gcash_qr_code) }}" alt="GCash QR" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                        <small class="d-block text-muted">Current GCash QR</small>
                                    </div>
                                @endif
                                <small class="text-muted">PNG, JPG, max 2MB</small>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">PayMaya QR Code</label>
                                <input type="file" class="form-control @error('paymaya_qr_code') is-invalid @enderror" 
                                       name="paymaya_qr_code" accept="image/*">
                                @error('paymaya_qr_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($seller->paymaya_qr_code ?? null)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $seller->paymaya_qr_code) }}" alt="PayMaya QR" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                        <small class="d-block text-muted">Current PayMaya QR</small>
                                    </div>
                                @endif
                                <small class="text-muted">PNG, JPG, max 2MB</small>
                            </div>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Save QR Codes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
    // Business contact: sync full phone for form
    (function() {
        const phoneDisplay = document.getElementById('business_phone_display');
        const phoneFull = document.getElementById('business_phone_full');
        if (phoneDisplay && phoneFull) {
            phoneDisplay.addEventListener('input', function() {
                const v = this.value.replace(/\D/g, '').slice(0, 10);
                this.value = v;
                phoneFull.value = v.length === 10 ? '+63' + v : '';
            });
        }
    })();

    // Sync from my account: copy profile email and phone into business contact fields
    (function() {
        const btn = document.getElementById('contact-sync-from-profile');
        const emailInput = document.getElementById('business_email');
        const phoneDisplay = document.getElementById('business_phone_display');
        const phoneFull = document.getElementById('business_phone_full');
        if (btn && (emailInput || phoneDisplay)) {
            btn.addEventListener('click', function() {
                const email = this.getAttribute('data-profile-email') || '';
                const phoneDigits = this.getAttribute('data-profile-phone-digits') || '';
                const phone = this.getAttribute('data-profile-phone') || '';
                if (emailInput) { emailInput.value = email; }
                if (phoneDisplay) { phoneDisplay.value = phoneDigits; }
                if (phoneFull) { phoneFull.value = phone || (phoneDigits.length === 10 ? '+63' + phoneDigits : ''); }
            });
        }
    })();

    // Social Links Management
    let socialLinkIndex = {{ $socialLinks->count() > 0 ? $socialLinks->count() : 1 }};
    
    document.getElementById('addSocialLink').addEventListener('click', function() {
        const container = document.getElementById('socialLinksContainer');
        const newItem = document.createElement('div');
        newItem.className = 'social-link-item mb-3 p-3 border rounded';
        newItem.innerHTML = `
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label">Platform</label>
                    <select class="form-select" name="social_links[${socialLinkIndex}][platform]" required>
                        <option value="">Select Platform</option>
                        <option value="facebook">Facebook</option>
                        <option value="instagram">Instagram</option>
                        <option value="twitter">Twitter</option>
                        <option value="youtube">YouTube</option>
                        <option value="tiktok">TikTok</option>
                        <option value="linkedin">LinkedIn</option>
                        <option value="pinterest">Pinterest</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label">URL</label>
                    <input type="url" class="form-control" name="social_links[${socialLinkIndex}][url]" 
                           placeholder="https://..." required>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Display Name (Optional)</label>
                    <input type="text" class="form-control" name="social_links[${socialLinkIndex}][display_name]" 
                           placeholder="Custom name">
                </div>
                <div class="col-md-2 mb-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm remove-link">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="social_links[${socialLinkIndex}][is_active]" 
                       value="1" checked>
                <label class="form-check-label">Active</label>
            </div>
        `;
        container.appendChild(newItem);
        socialLinkIndex++;
        
        // Attach remove event
        newItem.querySelector('.remove-link').addEventListener('click', function() {
            newItem.remove();
        });
    });

    // Remove social link
    document.querySelectorAll('.remove-link').forEach(btn => {
        btn.addEventListener('click', function() {
            if (document.querySelectorAll('.social-link-item').length > 1) {
                this.closest('.social-link-item').remove();
            } else {
                alert('You must have at least one social link entry.');
            }
        });
    });

</script>
@endpush
@endsection
