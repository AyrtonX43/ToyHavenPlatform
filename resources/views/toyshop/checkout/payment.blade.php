@extends('layouts.toyshop')

@section('title', 'Payment - ToyHaven')

@push('styles')
<style>
    .payment-header { background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%); color: white; border-radius: 14px; padding: 1.5rem 2rem; margin-bottom: 1.5rem; }
    .payment-method-option { border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 0.75rem; cursor: pointer; transition: all 0.2s; }
    .payment-method-option:hover { border-color: #06b6d4; background: #f0fdfa; }
    .payment-method-option.selected { border-color: #0891b2; background: #ecfeff; }
    .payment-method-option input { display: none; }
    .form-control:focus { border-color: #0891b2; box-shadow: 0 0 0 3px rgba(8,145,178,0.15); }
    .btn-primary { background: linear-gradient(135deg, #0891b2, #06b6d4); border: none; }
    .btn-primary:hover { background: linear-gradient(135deg, #0e7490, #0891b2); border: none; }
</style>
@endpush

@section('content')
<div class="container py-4">
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0" style="border-radius: 14px; overflow: hidden;">
                <div class="card-header payment-header border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-credit-card-2-front me-2"></i>Complete Payment</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <h6 class="mb-1">Order Number: {{ $order->order_number }}</h6>
                        <p class="mb-0">Total Amount: <strong>₱{{ number_format($order->total, 2) }}</strong></p>
                    </div>

                    @if(!($publicKey ?? false) || !($paymentIntentId ?? false))
                        <div class="alert alert-warning">
                            <p class="mb-0">Could not initialize payment. Please try refreshing the page or contact support.</p>
                        </div>
                        <a href="{{ route('checkout.payment', $order->order_number) }}" class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-arrow-clockwise me-2"></i>Retry Payment
                        </a>
                    @else
                        <!-- Payment method selection -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Select Payment Method</label>
                            <div class="payment-method-option selected" data-method="card">
                                <input type="radio" name="pay_method" value="card" id="pm_card" checked>
                                <label for="pm_card" class="mb-0 d-flex align-items-center" style="cursor: pointer;">
                                    <i class="bi bi-credit-card-2-front fs-4 me-3 text-primary"></i>
                                    <div>
                                        <strong>Credit / Debit Card</strong>
                                        <small class="d-block text-muted">Visa, Mastercard</small>
                                    </div>
                                </label>
                            </div>
                            <div class="payment-method-option" data-method="gcash">
                                <input type="radio" name="pay_method" value="gcash" id="pm_gcash">
                                <label for="pm_gcash" class="mb-0 d-flex align-items-center" style="cursor: pointer;">
                                    <i class="bi bi-phone fs-4 me-3 text-success"></i>
                                    <div>
                                        <strong>GCash</strong>
                                        <small class="d-block text-muted">Pay with GCash e-wallet</small>
                                    </div>
                                </label>
                            </div>
                            <div class="payment-method-option" data-method="paymaya">
                                <input type="radio" name="pay_method" value="paymaya" id="pm_paymaya">
                                <label for="pm_paymaya" class="mb-0 d-flex align-items-center" style="cursor: pointer;">
                                    <i class="bi bi-wallet2 fs-4 me-3 text-primary"></i>
                                    <div>
                                        <strong>Maya (PayMaya)</strong>
                                        <small class="d-block text-muted">Pay with Maya e-wallet</small>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Card form -->
                        <div id="card-form" class="mb-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Card Number</label>
                                    <input type="text" id="card_number" class="form-control" placeholder="4343 4343 4343 4345" maxlength="19" autocomplete="cc-number">
                                    <small class="text-muted">Test card: 4343 4343 4343 4345</small>
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Exp Month</label>
                                    <input type="number" id="exp_month" class="form-control" placeholder="12" min="1" max="12" autocomplete="cc-exp-month">
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Exp Year</label>
                                    <input type="number" id="exp_year" class="form-control" placeholder="2028" min="{{ date('Y') }}" autocomplete="cc-exp-year">
                                </div>
                                <div class="col-4">
                                    <label class="form-label">CVC</label>
                                    <input type="text" id="cvc" class="form-control" placeholder="123" maxlength="4" autocomplete="cc-csc">
                                </div>
                            </div>
                        </div>

                        <!-- E-wallet notice -->
                        <div id="ewallet-notice" class="alert alert-light border d-none mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            You will be redirected to complete payment in the app.
                        </div>

                        <div id="pay-error" class="alert alert-danger d-none"></div>
                        <div id="pay-loading" class="d-none text-center py-3">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 mb-0">Processing payment...</p>
                        </div>

                        <button type="button" id="pay-btn" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-lock-fill me-2"></i>Pay ₱{{ number_format($order->total, 2) }}
                        </button>
                    @endif

                    <hr class="my-4">
                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-outline-secondary w-100">View Order Details</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(($publicKey ?? false) && ($paymentIntentId ?? false))
<script>
(function() {
    var publicKey = @json($publicKey);
    var paymentIntentId = @json($paymentIntentId);
    var clientKey = @json($clientKey ?? '');
    var orderNumber = @json($order->order_number);
    var returnUrl = new URL('/checkout/return', window.location.origin);
    returnUrl.searchParams.set('order_number', orderNumber);

    function setError(msg) {
        var el = document.getElementById('pay-error');
        el.textContent = msg;
        el.classList.remove('d-none');
    }
    function clearError() { document.getElementById('pay-error').classList.add('d-none'); }
    function setLoading(show) {
        document.getElementById('pay-btn').disabled = show;
        document.getElementById('pay-loading').classList.toggle('d-none', !show);
    }

    function togglePaymentUi(method) {
        document.getElementById('card-form').classList.toggle('d-none', method !== 'card');
        document.getElementById('ewallet-notice').classList.toggle('d-none', method !== 'gcash' && method !== 'paymaya');
    }

    document.querySelectorAll('.payment-method-option').forEach(function(el) {
        el.addEventListener('click', function() {
            document.querySelectorAll('.payment-method-option').forEach(function(o) { o.classList.remove('selected'); });
            this.classList.add('selected');
            this.querySelector('input').checked = true;
            togglePaymentUi(this.dataset.method);
        });
    });

    function fetchClientKey() {
        if (clientKey) return Promise.resolve();
        return fetch('https://api.paymongo.com/v1/payment_intents/' + paymentIntentId + '?client_key=', {
            headers: { 'Authorization': 'Basic ' + btoa(publicKey + ':') }
        }).then(function(r) { return r.json(); }).then(function(d) {
            clientKey = d.data && d.data.attributes && d.data.attributes.client_key || '';
        });
    }

    function createPaymentMethod() {
        var method = document.querySelector('input[name="pay_method"]:checked').value;
        var attrs = {
            type: method,
            billing: {
                name: @json(auth()->user()->name ?? 'Customer'),
                email: @json(auth()->user()->email ?? ''),
                phone: @json($order->shipping_phone ?? '')
            }
        };

        if (method === 'card') {
            var cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
            var expMonth = parseInt(document.getElementById('exp_month').value, 10);
            var expYear = parseInt(document.getElementById('exp_year').value, 10);
            var cvc = document.getElementById('cvc').value;
            if (!cardNumber || !expMonth || !expYear || !cvc) {
                return Promise.reject(new Error('Please fill in all card details.'));
            }
            attrs.details = { card_number: cardNumber, exp_month: expMonth, exp_year: expYear, cvc: cvc };
        }

        return fetch('https://api.paymongo.com/v1/payment_methods', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Basic ' + btoa(publicKey + ':')
            },
            body: JSON.stringify({ data: { attributes: attrs } })
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (!data.data || !data.data.id) {
                throw new Error((data.errors && data.errors[0] && data.errors[0].detail) || 'Failed to create payment method');
            }
            return data.data.id;
        });
    }

    function attachPaymentMethod(paymentMethodId) {
        var method = document.querySelector('input[name="pay_method"]:checked').value;
        var body = {
            data: {
                attributes: {
                    client_key: clientKey,
                    payment_method: paymentMethodId
                }
            }
        };

        if (method === 'gcash' || method === 'paymaya') {
            returnUrl.searchParams.set('payment_intent_id', paymentIntentId);
            body.data.attributes.return_url = returnUrl.toString();
        }

        return fetch('https://api.paymongo.com/v1/payment_intents/' + paymentIntentId + '/attach', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Basic ' + btoa(publicKey + ':')
            },
            body: JSON.stringify(body)
        }).then(function(r) { return r.json(); }).then(function(data) {
            var pi = data.data;
            if (!pi) {
                throw new Error((data.errors && data.errors[0] && data.errors[0].detail) || 'Failed to process payment');
            }

            var status = pi.attributes && pi.attributes.status;
            var nextAction = pi.attributes && pi.attributes.next_action;

            if (status === 'succeeded') {
                returnUrl.searchParams.set('payment_intent_id', paymentIntentId);
                window.location.href = returnUrl.toString();
                return;
            }

            if (status === 'awaiting_next_action' && nextAction && nextAction.redirect && nextAction.redirect.url) {
                window.location.href = nextAction.redirect.url;
                return;
            }

            if (status === 'awaiting_payment_method') {
                throw new Error((pi.attributes && pi.attributes.last_payment_error && pi.attributes.last_payment_error.message) || 'Payment failed. Please try again.');
            }

            if (status === 'processing') {
                returnUrl.searchParams.set('payment_intent_id', paymentIntentId);
                window.location.href = returnUrl.toString();
                return;
            }

            throw new Error('Unexpected payment status: ' + status);
        });
    }

    document.getElementById('pay-btn').addEventListener('click', function() {
        clearError();
        setLoading(true);

        fetchClientKey()
            .then(function() { return createPaymentMethod(); })
            .then(function(pmId) { return attachPaymentMethod(pmId); })
            .catch(function(e) {
                setError(e.message || 'Payment failed. Please try again.');
                setLoading(false);
            });
    });

    fetchClientKey().catch(function() {});
})();
</script>
@endif
@endpush
