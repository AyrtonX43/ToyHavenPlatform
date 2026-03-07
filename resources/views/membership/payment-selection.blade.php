@extends('layouts.toyshop')

@section('title', 'Select Payment - ' . $plan->name)

@push('styles')
<style>
    .payment-selection-hero {
        background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0284c7 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 20px;
        box-shadow: 0 8px 30px rgba(2, 132, 199, 0.2);
    }
    .payment-method-card {
        border: 2px solid #e2e8f0;
        border-radius: 20px;
        transition: all 0.3s ease;
        height: 100%;
        background: #fff;
        cursor: pointer;
    }
    .payment-method-card:hover {
        border-color: #0284c7;
        box-shadow: 0 12px 40px rgba(2, 132, 199, 0.12);
        transform: translateY(-4px);
    }
    .payment-method-card.qrph:hover { border-color: #059669; box-shadow: 0 12px 40px rgba(5, 150, 105, 0.12); }
    .payment-method-card.paypal:hover { border-color: #0066cc; box-shadow: 0 12px 40px rgba(0, 102, 204, 0.12); }
    .plan-summary-box {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        border: 1px solid #e2e8f0;
    }
</style>
@endpush

@section('content')
<div class="payment-selection-hero">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 opacity-90">
                <li class="breadcrumb-item"><a href="{{ route('auctions.index') }}" class="text-white-50">Auctions</a></li>
                <li class="breadcrumb-item"><a href="{{ route('membership.checkout', $plan->slug) }}" class="text-white-50">Terms</a></li>
                <li class="breadcrumb-item active text-white">Payment</li>
            </ol>
        </nav>
        <h2 class="mt-3 mb-0 fw-bold"><i class="bi bi-credit-card-2-front me-2"></i>Select Payment Method</h2>
        <p class="mb-0 opacity-90">Choose how you'd like to pay for your membership</p>
    </div>
</div>

<div class="container py-4">
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show rounded-3 shadow-sm">
            <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="plan-summary-box mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Plan</small>
                        <h4 class="mb-0 fw-bold">{{ $plan->name }} — ₱{{ number_format($plan->price, 0) }}/mo</h4>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <form action="{{ route('membership.subscribe') }}" method="POST" class="h-100" target="_self">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <input type="hidden" name="terms_accepted" value="1">
                        <input type="hidden" name="payment_method" value="qrph">
                        <div class="payment-method-card card h-100 qrph border-0" onclick="this.closest('form').submit()">
                            <div class="card-body text-center py-5 px-4">
                                <div class="mb-3">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10" style="width: 80px; height: 80px;">
                                        <i class="bi bi-qr-code-scan text-success" style="font-size: 2.5rem;"></i>
                                    </div>
                                </div>
                                <h5 class="fw-bold mb-2">QR Ph</h5>
                                <p class="text-muted small mb-4">Scan with GCash, Maya, or any Philippine banking app. Instant confirmation.</p>
                                <button type="submit" class="btn btn-success px-4 py-2 rounded-3 fw-semibold">
                                    <i class="bi bi-qr-code me-1"></i> Pay with QR Ph
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-6">
                    <div class="payment-method-card card h-100 paypal border-0">
                        <div class="card-body text-center py-5 px-4">
                            <div class="mb-3">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10" style="width: 80px; height: 80px;">
                                    <i class="bi bi-paypal text-primary" style="font-size: 2.5rem;"></i>
                                </div>
                            </div>
                            <h5 class="fw-bold mb-2">PayPal</h5>
                            <p class="text-muted small mb-4">Pay securely with your PayPal account. A PayPal popup will open—complete payment there (no address required).</p>
                            @if(!empty($paypal_client_id))
                                <div id="paypal-button-container" class="d-flex justify-content-center"></div>
                            @else
                                <p class="text-warning small mb-0">PayPal is not configured.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('membership.checkout', $plan->slug) }}" class="btn btn-link text-muted text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i> Back to Terms & Conditions
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@if(!empty($paypal_client_id))
@push('scripts')
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypal_client_id }}&components=buttons&currency=PHP&intent=capture&disable-funding=card,credit"></script>
<script>
(function() {
    var planId = {{ $plan->id }};
    var createOrderUrl = @json(route('membership.paypal.create-order'));
    var captureOrderUrl = @json(route('membership.paypal.capture-order'));
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || @json(csrf_token());

    paypal.Buttons({
        createOrder: function() {
            return fetch(createOrderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    plan_id: planId,
                    _token: csrfToken
                })
            }).then(function(r) { return r.json(); }).then(function(data) {
                if (data.orderId) return data.orderId;
                throw new Error(data.error || 'Could not create order');
            });
        },
        onApprove: function(data) {
            return fetch(captureOrderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    order_id: data.orderID,
                    _token: csrfToken
                })
            }).then(function(r) { return r.json();             }).then(function(res) {
                if (res.success && res.redirect) {
                    var url = res.redirect + (res.message ? '?success=' + encodeURIComponent(res.message) : '');
                    window.location.href = url;
                } else {
                    throw new Error(res.error || 'Payment failed');
                }
            });
        },
        onCancel: function() {
            console.log('PayPal payment cancelled');
        },
        onError: function(err) {
            alert(err || 'PayPal error. Please try again or use QR Ph.');
        }
    }).render('#paypal-button-container');
})();
</script>
@endpush
@endif
