@extends('layouts.toyshop')

@section('title', 'Pay with PayPal - ' . $subscription->plan->name)

@push('styles')
<style>
    .payment-hero {
        background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0284c7 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 20px;
        box-shadow: 0 8px 30px rgba(2, 132, 199, 0.2);
    }
    .payment-card {
        border: 2px solid #e2e8f0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    }
    #paypal-button-container {
        min-height: 200px;
    }
</style>
@endpush

@section('content')
<div class="payment-hero">
    <div class="container text-center">
        <h2 class="mb-1 fw-bold"><i class="bi bi-paypal me-2"></i>Pay with PayPal</h2>
        <p class="mb-0 opacity-90">{{ $subscription->plan->name }} — ₱{{ number_format($subscription->plan->price, 0) }}/mo</p>
    </div>
</div>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="payment-card card border-0 mb-4">
                <div class="card-body text-center py-5 px-4">
                    <p class="text-muted mb-4">Complete your payment securely with PayPal. No redirect — pay directly on this page.</p>
                    <div id="paypal-button-container"></div>
                    <div id="paypal-error" class="alert alert-danger mt-3 d-none"></div>
                    <div id="paypal-loading" class="text-muted small mt-3 d-none">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div> Processing...
                    </div>
                    <p class="text-muted small mt-4 mb-0">
                        <i class="bi bi-shield-check me-1"></i> Secure payment via PayPal. Receipt will be emailed once confirmed.
                    </p>
                </div>
            </div>

            <div class="text-center">
                <a href="{{ route('membership.cancel-pending', $subscription) }}?return_to=selection" class="btn btn-link text-danger text-decoration-none p-0">
                    <i class="bi bi-x-circle me-1"></i> Cancel and return to payment selection
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://www.paypal.com/sdk/js?client-id={{ $paypal_client_id }}&currency={{ config('paypal.currency', 'PHP') }}&intent=capture"></script>
<script>
(function() {
    var createOrderUrl = @json(route('membership.create-paypal-order', $subscription));
    var captureOrderUrl = @json(route('membership.capture-paypal-order', $subscription));
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var cancelUrl = @json(route('membership.cancel-pending', $subscription)) + '?return_to=selection';
    var selectionUrl = @json(route('membership.payment-selection', $subscription->plan->slug));

    function showError(msg) {
        var el = document.getElementById('paypal-error');
        el.textContent = msg;
        el.classList.remove('d-none');
    }

    function showLoading(show) {
        document.getElementById('paypal-loading').classList.toggle('d-none', !show);
    }

    paypal.Buttons({
        createOrder: function() {
            return fetch(createOrderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({})
            }).then(function(r) {
                return r.json();
            }).then(function(data) {
                if (data.orderId) return data.orderId;
                throw new Error(data.error || 'Could not create order');
            });
        },
        onApprove: function(data) {
            showLoading(true);
            document.getElementById('paypal-error').classList.add('d-none');
            return fetch(captureOrderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ order_id: data.orderID })
            }).then(function(r) {
                return r.json();
            }).then(function(res) {
                showLoading(false);
                if (res.success && res.redirect) {
                    window.location.href = res.redirect + (res.message ? '?success=' + encodeURIComponent(res.message) : '');
                } else {
                    showError(res.error || 'Payment could not be completed.');
                    if (res.redirect) {
                        setTimeout(function() { window.location.href = res.redirect; }, 2000);
                    }
                }
            }).catch(function(err) {
                showLoading(false);
                showError(err.message || 'Something went wrong.');
                setTimeout(function() { window.location.href = selectionUrl; }, 2000);
            });
        },
        onCancel: function() {
            window.location.href = cancelUrl;
        },
        onError: function(err) {
            showError(err.message || 'PayPal error. Please try again or use QR Ph.');
            setTimeout(function() { window.location.href = selectionUrl; }, 3000);
        }
    }).render('#paypal-button-container');
})();
</script>
@endsection
