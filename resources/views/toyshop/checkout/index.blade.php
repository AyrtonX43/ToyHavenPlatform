@extends('layouts.toyshop')

@section('title', 'Checkout - ToyHaven')

@push('styles')
<style>
    .checkout-header {
        background: white;
        border-radius: 16px;
        padding: 1.5rem 2rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
    }
    
    .checkout-step {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
    }
    
    .step-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .step-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #ff6b6b;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
    }
    
    .step-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2d2a26;
        margin: 0;
    }
    
    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
    }
    
    .form-control, .form-select {
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #ff6b6b;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
    }
    
    .payment-option {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .payment-option:hover {
        border-color: #ff6b6b;
        background: #f8fafc;
    }
    
    .payment-option input[type="radio"]:checked + label {
        color: #ff6b6b;
    }
    
    .order-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .order-item:last-child {
        border-bottom: none;
    }
    
    .order-item-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 10px;
        border: 2px solid #e5e7eb;
    }
    
    .summary-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        position: sticky;
        top: 100px;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .summary-total {
        font-size: 1.5rem;
        font-weight: 700;
        color: #ff6b6b;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="checkout-header reveal">
        <h2 class="fw-bold mb-0"><i class="bi bi-credit-card me-2"></i>Checkout</h2>
    </div>

    <form action="{{ route('checkout.process') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <!-- Shipping Information -->
                <div class="checkout-step reveal">
                    <div class="step-header">
                        <div class="step-icon">1</div>
                        <h3 class="step-title">Shipping Information</h3>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Full Address <span class="text-danger">*</span></label>
                        <textarea name="shipping_address" class="form-control @error('shipping_address') is-invalid @enderror" rows="3" required placeholder="Enter your complete delivery address">{{ old('shipping_address') }}</textarea>
                        @error('shipping_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" name="shipping_city" class="form-control @error('shipping_city') is-invalid @enderror" value="{{ old('shipping_city') }}" required placeholder="City">
                            @error('shipping_city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Province <span class="text-danger">*</span></label>
                            <input type="text" name="shipping_province" class="form-control @error('shipping_province') is-invalid @enderror" value="{{ old('shipping_province') }}" required placeholder="Province">
                            @error('shipping_province')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Postal Code <span class="text-danger">*</span></label>
                            <input type="text" name="shipping_postal_code" class="form-control @error('shipping_postal_code') is-invalid @enderror" value="{{ old('shipping_postal_code') }}" required placeholder="Postal Code">
                            @error('shipping_postal_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone-fill me-1"></i>+63</span>
                                <input type="tel" id="shipping_phone_display" class="form-control @error('shipping_phone') is-invalid @enderror" value="{{ old('shipping_phone') ? (preg_match('/^\+63(\d{10})$/', old('shipping_phone'), $m) ? $m[1] : '') : '' }}" placeholder="9123456789" maxlength="10" pattern="[0-9]{10}" inputmode="numeric" autocomplete="tel" title="10-digit Philippine mobile number">
                            </div>
                            <input type="hidden" name="shipping_phone" id="shipping_phone" value="{{ old('shipping_phone') }}">
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

                <!-- Payment Method -->
                <div class="checkout-step reveal" style="animation-delay: 0.1s;">
                    <div class="step-header">
                        <div class="step-icon">2</div>
                        <h3 class="step-title">Payment Method</h3>
                    </div>
                    
                    <div class="payment-option">
                        <input class="form-check-input" type="radio" name="payment_method" id="payment_card" value="card" {{ old('payment_method', 'card') == 'card' ? 'checked' : '' }} required>
                        <label class="form-check-label ms-2" for="payment_card" style="cursor: pointer; font-weight: 600;">
                            <i class="bi bi-credit-card me-2"></i>Credit/Debit Card
                        </label>
                    </div>
                    <div class="payment-option">
                        <input class="form-check-input" type="radio" name="payment_method" id="payment_gcash" value="gcash" {{ old('payment_method') == 'gcash' ? 'checked' : '' }}>
                        <label class="form-check-label ms-2" for="payment_gcash" style="cursor: pointer; font-weight: 600;">
                            <i class="bi bi-phone me-2"></i>GCash
                        </label>
                    </div>
                    <div class="payment-option">
                        <input class="form-check-input" type="radio" name="payment_method" id="payment_paymaya" value="paymaya" {{ old('payment_method') == 'paymaya' ? 'checked' : '' }}>
                        <label class="form-check-label ms-2" for="payment_paymaya" style="cursor: pointer; font-weight: 600;">
                            <i class="bi bi-phone me-2"></i>PayMaya
                        </label>
                    </div>
                    @error('payment_method')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Order Items -->
                <div class="checkout-step reveal" style="animation-delay: 0.2s;">
                    <div class="step-header">
                        <div class="step-icon">3</div>
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
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="summary-card reveal" style="animation-delay: 0.3s;">
                    <h5 class="fw-bold mb-4"><i class="bi bi-receipt me-2"></i>Order Summary</h5>
                    
                    <div class="summary-row">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-semibold">₱{{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="text-muted">Shipping:</span>
                        <span class="fw-semibold">₱{{ number_format($shippingFee, 2) }}</span>
                    </div>
                    <hr class="my-3">
                    <div class="summary-row">
                        <span class="fw-bold">Total:</span>
                        <span class="summary-total">₱{{ number_format($subtotal + $shippingFee, 2) }}</span>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle me-2"></i>Place Order
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
</script>
@endpush
@endsection
