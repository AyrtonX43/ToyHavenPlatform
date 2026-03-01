@extends('layouts.toyshop')

@section('title', 'Checkout - ToyHaven')

@push('styles')
<style>
    .checkout-header {
        background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
        border-radius: 16px;
        padding: 1.75rem 2rem;
        margin-bottom: 2rem;
        color: white;
        box-shadow: 0 4px 20px rgba(8, 145, 178, 0.25);
    }
    .checkout-header h2 { color: white; margin: 0; font-weight: 700; }

    .checkout-step {
        background: white;
        border-radius: 14px;
        padding: 1.75rem 2rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        margin-bottom: 1.5rem;
        border: 1px solid #e2e8f0;
    }

    .step-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .step-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, #0891b2, #06b6d4);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
    }

    .step-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .form-label {
        font-weight: 600;
        color: #475569;
        margin-bottom: 0.5rem;
        font-size: 0.9375rem;
    }

    .form-control, .form-select {
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.65rem 1rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-control:focus, .form-select:focus {
        border-color: #0891b2;
        box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.15);
        outline: none;
    }

    .payment-option {
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-bottom: 0.75rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .payment-option:hover {
        border-color: #06b6d4;
        background: #f0fdfa;
    }

    .payment-option:has(input:checked) {
        border-color: #0891b2;
        background: #ecfeff;
    }

    .payment-option input[type="radio"]:checked + label {
        color: #0891b2;
    }

    .order-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .order-item:last-child { border-bottom: none; }

    .order-item-image {
        width: 72px;
        height: 72px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
    }

    .summary-card {
        background: white;
        border-radius: 16px;
        padding: 1.75rem 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        position: sticky;
        top: 100px;
        border: 1px solid #e2e8f0;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 0.6rem 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9375rem;
    }

    .summary-total {
        font-size: 1.35rem;
        font-weight: 700;
        color: #0891b2;
    }

    .btn-primary {
        background: linear-gradient(135deg, #0891b2, #06b6d4);
        border: none;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        border-radius: 10px;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #0e7490, #0891b2);
        border: none;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="checkout-header reveal">
        <h2 class="fw-bold mb-0"><i class="bi bi-credit-card me-2"></i>Checkout and User Details Info</h2>
    </div>

    <form action="{{ route('checkout.process') }}" method="POST">
        @csrf
        @foreach($selectedIds ?? $cartItems->pluck('id') as $cid)
            <input type="hidden" name="cart_item_ids[]" value="{{ $cid }}">
        @endforeach
        <div class="row">
            <div class="col-lg-8">
                <!-- 1. Order Products -->
                <div class="checkout-step reveal">
                    <div class="step-header">
                        <div class="step-icon">1</div>
                        <h3 class="step-title">Order Items</h3>
                    </div>
                    @foreach($itemsBySeller as $sellerId => $items)
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3 text-muted">
                                <i class="bi bi-shop me-2"></i>Seller: {{ $items->first()->product->seller->business_name }}
                            </h6>
                            @foreach($items as $item)
                                <div class="order-item">
                                    @if($item->product->images->first())
                                        <img src="{{ asset('storage/' . $item->product->images->first()->image_path) }}" 
                                             class="order-item-image" 
                                             alt="{{ $item->product->name }}">
                                    @else
                                        <div class="order-item-image bg-light d-flex align-items-center justify-content-center">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-semibold">{{ $item->product->name }}</h6>
                                        <small class="text-muted">Quantity: {{ $item->quantity }}</small>
                                    </div>
                                    <div class="text-end">
                                        <strong class="text-primary">₱{{ number_format($item->product->price * $item->quantity, 2) }}</strong>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>

                <!-- 2. Expected Delivery -->
                <div class="checkout-step reveal" style="animation-delay: 0.05s;">
                    <div class="step-header">
                        <div class="step-icon">2</div>
                        <h3 class="step-title">Expected Delivery</h3>
                    </div>
                    <div class="d-flex align-items-center gap-3 p-3 rounded" style="background: #f0fdfa;">
                        <i class="bi bi-truck fs-2 text-primary"></i>
                        <div>
                            <p class="mb-1 fw-semibold">{{ $minDeliveryDate->format('M d, Y') }} – {{ $maxDeliveryDate->format('M d, Y') }}</p>
                            <p class="mb-0 text-muted small">Estimated 3–5 business days after payment confirmation</p>
                        </div>
                    </div>
                </div>

                <!-- 3. Payment -->
                <div class="checkout-step reveal" style="animation-delay: 0.1s;">
                    <div class="step-header">
                        <div class="step-icon">3</div>
                        <h3 class="step-title">Payment</h3>
                    </div>
                    <input type="hidden" name="payment_method" value="card">
                    <div class="d-flex align-items-center gap-3 p-3 rounded" style="background: #f0fdfa;">
                        <i class="bi bi-shield-lock fs-2 text-primary"></i>
                        <div>
                            <p class="mb-1 fw-semibold">Secure Payment via PayMongo</p>
                            <p class="mb-0 text-muted small">After placing your order, you'll be taken to the payment page where you can pay with <strong>Credit/Debit Card</strong>, <strong>GCash</strong>, or <strong>Maya (PayMaya)</strong>.</p>
                        </div>
                    </div>
                </div>

                <!-- 4. Address Information -->
                <div class="checkout-step reveal" style="animation-delay: 0.15s;">
                    <div class="step-header">
                        <div class="step-icon">4</div>
                        <h3 class="step-title">Address Information</h3>
                    </div>
                    
                    @php
                        $userAddresses = auth()->user()->addresses ?? collect();
                        $defaultAddr = $defaultAddress ?? $userAddresses->where('is_default', true)->first() ?? $userAddresses->first();
                        $userPhone = auth()->user()->phone ?? '';
                        $raw = preg_replace('/\D/', '', $userPhone);
                        $phoneDigits = preg_match('/^63(\d{10})$/', $raw, $pm) ? $pm[1] : (strlen($raw) >= 10 ? substr($raw, -10) : '');
                        $prefillPhoneDisplay = old('shipping_phone') ? (preg_match('/^\+63(\d{10})$/', old('shipping_phone'), $om) ? $om[1] : '') : $phoneDigits;
                        $prefillPhone = old('shipping_phone') ?: ($phoneDigits ? '+63' . $phoneDigits : '');
                    @endphp

                    @if($userAddresses->count() > 0)
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Select Saved Address <span class="text-danger">*</span></label>
                            <select id="savedAddressSelect" class="form-select" onchange="loadSavedAddress()">
                                <option value="">-- Select an address --</option>
                                @foreach($userAddresses as $addr)
                                    <option value="{{ $addr->id }}" 
                                            data-address="{{ $addr->address }}"
                                            data-city="{{ $addr->city }}"
                                            data-province="{{ $addr->province }}"
                                            data-postal="{{ $addr->postal_code }}"
                                            {{ $addr->is_default || ($defaultAddr && $defaultAddr->id == $addr->id) ? 'selected' : '' }}>
                                        {{ $addr->label ?? 'Address ' . $loop->iteration }} 
                                        @if($addr->is_default) (Default) @endif
                                        - {{ $addr->city }}, {{ $addr->province }}
                                    </option>
                                @endforeach
                                <option value="new">+ Add New Address</option>
                            </select>
                            <small class="text-muted d-block mt-1">
                                <i class="bi bi-info-circle me-1"></i>You can manage your addresses in your 
                                <a href="{{ route('profile.edit') }}" target="_blank" class="text-decoration-none">profile settings</a>
                            </small>
                        </div>
                    @else
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            You don't have any saved addresses. Please enter your delivery address below.
                            You can save addresses in your <a href="{{ route('profile.edit') }}" target="_blank" class="text-decoration-none">profile settings</a>.
                        </div>
                    @endif

                    <div id="addressFormContainer" style="{{ $userAddresses->count() > 0 && $defaultAddr ? '' : '' }}">
                        @php
                            $prefillAddress = old('shipping_address') ?? ($defaultAddr?->address ?? '');
                            $prefillCity = old('shipping_city') ?? ($defaultAddr?->city ?? '');
                            $prefillProvince = old('shipping_province') ?? ($defaultAddr?->province ?? '');
                            $prefillPostal = old('shipping_postal_code') ?? ($defaultAddr?->postal_code ?? '');
                        @endphp
                        
                        <div class="mb-3">
                            <label class="form-label">Full Address <span class="text-danger">*</span></label>
                            <textarea name="shipping_address" id="shipping_address" class="form-control @error('shipping_address') is-invalid @enderror" rows="3" required placeholder="Enter your complete delivery address">{{ $prefillAddress }}</textarea>
                            @error('shipping_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" name="shipping_city" id="shipping_city" class="form-control @error('shipping_city') is-invalid @enderror" value="{{ $prefillCity }}" required placeholder="City">
                                @error('shipping_city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Province <span class="text-danger">*</span></label>
                                <input type="text" name="shipping_province" id="shipping_province" class="form-control @error('shipping_province') is-invalid @enderror" value="{{ $prefillProvince }}" required placeholder="Province">
                                @error('shipping_province')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Postal Code <span class="text-danger">*</span></label>
                                <input type="text" name="shipping_postal_code" id="shipping_postal_code" class="form-control @error('shipping_postal_code') is-invalid @enderror" value="{{ $prefillPostal }}" required placeholder="Postal Code">
                                @error('shipping_postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone-fill me-1"></i>+63</span>
                                    <input type="tel" id="shipping_phone_display" class="form-control @error('shipping_phone') is-invalid @enderror" value="{{ $prefillPhoneDisplay }}" placeholder="9123456789" maxlength="10" pattern="[0-9]{10}" inputmode="numeric" autocomplete="tel" title="10-digit Philippine mobile number">
                                </div>
                                <input type="hidden" name="shipping_phone" id="shipping_phone" value="{{ $prefillPhone }}">
                                @error('shipping_phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Philippines +63, 10 digits.</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Delivery Notes (Optional)</label>
                            <textarea name="shipping_notes" class="form-control" rows="2" placeholder="Any special delivery instructions...">{{ old('shipping_notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="summary-card reveal" style="animation-delay: 0.3s;">
                    <h5 class="fw-bold mb-4"><i class="bi bi-receipt me-2"></i>Order Summary</h5>
                    
                    <div class="summary-row">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-semibold">₱{{ number_format($subtotal, 2) }}</span>
                    </div>
                    @if(isset($membershipDiscount) && $membershipDiscount > 0)
                        <div class="summary-row text-success">
                            <span class="text-muted">Membership discount ({{ $membershipDiscountPercent }}%):</span>
                            <span class="fw-semibold">-₱{{ number_format($membershipDiscount, 2) }}</span>
                        </div>
                    @endif
                    <div class="summary-row">
                        <span class="text-muted">VAT ({{ $vatRate ?? 12 }}%):</span>
                        <span class="fw-semibold">₱{{ number_format($vatAmount ?? 0, 2) }}</span>
                    </div>
                    @if(isset($freeShippingMin) && $subtotalAfterDiscount >= $freeShippingMin)
                        <div class="summary-row text-success">
                            <span class="text-muted">Shipping:</span>
                            <span class="fw-semibold">Free (Member)</span>
                        </div>
                    @else
                        <div class="summary-row">
                            <span class="text-muted">Shipping:</span>
                            <span class="fw-semibold">₱{{ number_format($shippingFee, 2) }}</span>
                        </div>
                    @endif
                    <hr class="my-3">
                    <div class="summary-row">
                        <span class="fw-bold">Total:</span>
                        <span class="summary-total">₱{{ number_format($totalWithVat ?? ($subtotal + $shippingFee), 2) }}</span>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-lock me-2"></i>Place Order & Proceed to Payment
                        </button>
                        <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Cart
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function() {
    var display = document.getElementById('shipping_phone_display');
    var hidden = document.getElementById('shipping_phone');
    if (!display || !hidden) return;
    display.addEventListener('input', function() {
        var digits = this.value.replace(/\D/g, '').slice(0, 10);
        this.value = digits;
        hidden.value = digits.length === 10 ? '+63' + digits : '';
    });
    display.addEventListener('keypress', function(e) {
        if (e.key && !/\d/.test(e.key) && !e.ctrlKey && !e.metaKey && e.key.length === 1) e.preventDefault();
    });
    var form = display.closest('form');
    if (form) form.addEventListener('submit', function() {
        var digits = display.value.replace(/\D/g, '').slice(0, 10);
        if (digits.length === 10) hidden.value = '+63' + digits;
    });
    if (display.value) {
        var d = display.value.replace(/\D/g, '').slice(0, 10);
        display.value = d;
        if (d.length === 10) hidden.value = '+63' + d;
    }
})();

// Load saved address functionality
function loadSavedAddress() {
    const select = document.getElementById('savedAddressSelect');
    if (!select) return;
    
    const selectedOption = select.options[select.selectedIndex];
    const value = selectedOption.value;
    
    if (value === 'new') {
        // Clear all fields for new address
        document.getElementById('shipping_address').value = '';
        document.getElementById('shipping_city').value = '';
        document.getElementById('shipping_province').value = '';
        document.getElementById('shipping_postal_code').value = '';
        document.getElementById('shipping_address').focus();
        return;
    }
    
    if (value === '') {
        // No selection
        return;
    }
    
    // Load selected address data
    const address = selectedOption.getAttribute('data-address');
    const city = selectedOption.getAttribute('data-city');
    const province = selectedOption.getAttribute('data-province');
    const postal = selectedOption.getAttribute('data-postal');
    
    if (address) document.getElementById('shipping_address').value = address;
    if (city) document.getElementById('shipping_city').value = city;
    if (province) document.getElementById('shipping_province').value = province;
    if (postal) document.getElementById('shipping_postal_code').value = postal;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('savedAddressSelect');
    if (select && select.value && select.value !== '' && select.value !== 'new') {
        loadSavedAddress();
    }
});
</script>
@endpush
@endsection
