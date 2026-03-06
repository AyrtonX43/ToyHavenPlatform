@extends('layouts.toyshop')

@section('title', 'Checkout - ' . $plan->name)

@push('styles')
<style>
    .checkout-step { border-left: 4px solid #dee2e6; padding-left: 1.5rem; margin-bottom: 2rem; }
    .checkout-step.active { border-color: #0d9488; }
    .checkout-step.completed { border-color: #0d9488; opacity: 0.8; }
</style>
@endpush

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auctions.index') }}">Auctions</a></li>
            <li class="breadcrumb-item"><a href="{{ route('membership.index') }}">Membership</a></li>
            <li class="breadcrumb-item active">{{ $plan->name }} Checkout</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $plan->name }} - ₱{{ number_format($plan->price, 0) }}/mo</h5>
                    <span class="badge bg-primary">Step 1 → 2</span>
                </div>
                <div class="card-body">
                    <form action="{{ route('membership.subscribe') }}" method="POST" id="checkoutForm">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">

                        {{-- Step 1: Terms & Conditions --}}
                        <div class="checkout-step active" id="step1">
                            <h6 class="fw-bold mb-2">Step 1: Terms & Conditions</h6>
                            <div class="terms-content border rounded p-3 mb-3" style="max-height: 260px; overflow-y: auto; font-size: 0.9rem;">
                                @include('membership.terms-content')
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="terms_accepted" id="termsAccepted" value="1" required>
                                <label class="form-check-label fw-medium" for="termsAccepted">
                                    I have read and agree to the Terms & Conditions
                                </label>
                            </div>
                        </div>

                        {{-- Step 2: Payment Method (shown after terms accepted) --}}
                        <div class="checkout-step" id="step2" style="display: none;">
                            <h6 class="fw-bold mb-3">Step 2: Select Payment Method</h6>
                            <div class="mb-3">
                                <div class="form-check p-3 border rounded mb-2">
                                    <input class="form-check-input" type="radio" name="payment_method" id="qrph" value="qrph" checked required>
                                    <label class="form-check-label w-100" for="qrph">
                                        <strong>QR Ph (PayMongo)</strong>
                                        <br><small class="text-muted">Scan QR code with your e-wallet or banking app</small>
                                    </label>
                                </div>
                                <div class="form-check p-3 border rounded">
                                    <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal">
                                    <label class="form-check-label w-100" for="paypal">
                                        <strong>PayPal</strong>
                                        <br><small class="text-muted">Pay with your PayPal account</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                            <i class="bi bi-credit-card me-2"></i> Proceed to Payment
                        </button>
                    </form>
                </div>
            </div>
            <a href="{{ route('membership.index') }}" class="btn btn-link text-muted"><i class="bi bi-arrow-left me-1"></i> Back to membership plans</a>
        </div>
    </div>
</div>

<script>
document.getElementById('termsAccepted').addEventListener('change', function() {
    document.getElementById('step2').style.display = this.checked ? 'block' : 'none';
});
</script>
@endsection
