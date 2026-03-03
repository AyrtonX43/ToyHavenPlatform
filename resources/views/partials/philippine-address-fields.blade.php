{{--
    Philippine Address Form Fields (Region, Province, City/Municipality, Barangay)
    Based on PSGC (Philippine Standard Geographic Code) API - same as business registration.
    Usage: @include('partials.philippine-address-fields', [
        'prefix' => '',  // or 'shipping_' for checkout
        'prefillRegion' => $addr?->region ?? '',
        'prefillProvince' => $addr?->province ?? '',
        'prefillCity' => $addr?->city ?? '',
        'prefillBarangay' => $addr?->barangay ?? '',
        'streetLabel' => 'House/Apartment No., Building/Street, Residence',
        'cols' => ['region' => 4, 'province' => 4, 'city' => 4],
    ])
--}}
@php
    $prefix = $prefix ?? '';
    $prefillAddress = $prefillAddress ?? '';
    $prefillRegion = $prefillRegion ?? '';
    $prefillProvince = $prefillProvince ?? '';
    $prefillCity = $prefillCity ?? '';
    $prefillBarangay = $prefillBarangay ?? '';
    $prefillPostal = $prefillPostal ?? '';
    $streetLabel = $streetLabel ?? 'House/Apartment No., Building/Street, Residence';
    $streetPlaceholder = $streetPlaceholder ?? 'e.g., Unit 123, ABC Building, 456 Main Street';
    $cols = $cols ?? [];
    $regionCol = $cols['region'] ?? 4;
    $provinceCol = $cols['province'] ?? 4;
    $cityCol = $cols['city'] ?? 4;
    // Normalize for JS
    $prefillRegion = $prefillRegion ? normalizePhilippineText($prefillRegion) : '';
    $prefillProvince = $prefillProvince ? normalizePhilippineText($prefillProvince) : '';
    $prefillCity = $prefillCity ? normalizePhilippineText($prefillCity) : '';
    $prefillBarangay = $prefillBarangay ? normalizePhilippineText($prefillBarangay) : '';
@endphp
<div class="mb-3">
    <label class="form-label">{{ $streetLabel }} <span class="text-danger">*</span></label>
    <textarea name="{{ $prefix }}address" id="{{ $prefix }}address" class="form-control @error($prefix.'address') is-invalid @enderror" rows="2" required placeholder="{{ $streetPlaceholder }}">{{ old($prefix.'address', $prefillAddress ?? '') }}</textarea>
    <small class="text-muted">Enter your complete street address including house/apartment number, building name, and street</small>
    @error($prefix.'address')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
<div class="row">
    <div class="col-md-{{ $regionCol }} mb-3">
        <label class="form-label">Region <span class="text-danger">*</span></label>
        <select name="{{ $prefix }}region" id="{{ $prefix }}region" class="form-select @error($prefix.'region') is-invalid @enderror" required>
            <option value="">Select Region</option>
        </select>
        @error($prefix.'region')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-{{ $provinceCol }} mb-3">
        <label class="form-label">Province <span class="text-danger">*</span></label>
        <select name="{{ $prefix }}province" id="{{ $prefix }}province" class="form-select @error($prefix.'province') is-invalid @enderror" required disabled>
            <option value="">Select Province</option>
        </select>
        @error($prefix.'province')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-{{ $cityCol }} mb-3">
        <label class="form-label">City/Municipality <span class="text-danger">*</span></label>
        <select name="{{ $prefix }}city" id="{{ $prefix }}city" class="form-select @error($prefix.'city') is-invalid @enderror" required disabled>
            <option value="">Select City/Municipality</option>
        </select>
        @error($prefix.'city')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row">
    <div class="col-md-8 mb-3">
        <label class="form-label">Barangay <span class="text-danger">*</span></label>
        <select name="{{ $prefix }}barangay" id="{{ $prefix }}barangay" class="form-select @error($prefix.'barangay') is-invalid @enderror" required disabled>
            <option value="">Select Barangay</option>
        </select>
        @error($prefix.'barangay')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Postal Code <span class="text-danger">*</span></label>
        <input type="text" name="{{ $prefix }}postal_code" id="{{ $prefix }}postal_code" class="form-control @error($prefix.'postal_code') is-invalid @enderror" value="{{ old($prefix.'postal_code', $prefillPostal ?? '') }}" placeholder="4 digits" maxlength="4" pattern="[0-9]{4}" required>
        <small class="text-muted">4-digit postal code</small>
        @error($prefix.'postal_code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
