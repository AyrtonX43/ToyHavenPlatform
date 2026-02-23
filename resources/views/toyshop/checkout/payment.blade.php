@extends('layouts.toyshop')

@section('title', 'Payment - ToyHaven')

@push('styles')
<style>
    .payment-method-option { border: 2px solid #e2e8f0; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 0.75rem; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; }
    .payment-method-option:hover { border-color: #06b6d4; background: #f0fdfa; }
    .payment-method-option.selected { border-color: #0891b2; background: #ecfeff; box-shadow: 0 0 0 2px rgba(8, 145, 178, 0.2); }
    .payment-method-option input { margin-right: 0.75rem; accent-color: #0891b2; }
    .card-form-section { background: #f8fafc; border-radius: 12px; padding: 1.5rem; border: 1px solid #e2e8f0; }
    .card-form-section .form-control { background: #fff; }
    .pay-amount { font-size: 1.25rem; color: #0891b2; }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
                <div class="card-header bg-white py-4" style="border-bottom: 1px solid #e2e8f0;">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-credit-card-2-front me-2 text-primary"></i>Complete Payment</h5>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-light border mb-4 d-flex align-items-center" style="background: #f0fdfa; border-color: #99f6e4 !important;">
                        <i class="bi bi-receipt-cutoff fs-4 me-3 text-primary"></i>
                        <div>
                            <strong>Order {{ $order->order_number }}</strong>
                            <span class="pay-amount fw-bold ms-2">₱{{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>

                    @if(!$publicKey)
                        <div class="alert alert-warning">
                            <p class="mb-0">PayMongo is not configured. Please add PAYMONGO_PUBLIC_KEY and PAYMONGO_SECRET_KEY to your .env file.</p>
                            <form action="{{ route('checkout.callback') }}" method="POST" class="mt-3">
                                @csrf
                                <input type="hidden" name="order_number" value="{{ $order->order_number }}">
                                <button type="submit" class="btn btn-outline-primary">Complete Payment (Test Mode)</button>
                            </form>
                        </div>
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
                                        <small class="d-block text-muted">Pay with GCash</small>
                                    </div>
                                </label>
                            </div>
                            <div class="payment-method-option" data-method="paymaya">
                                <input type="radio" name="pay_method" value="paymaya" id="pm_paymaya">
                                <label for="pm_paymaya" class="mb-0 d-flex align-items-center" style="cursor: pointer;">
                                    <i class="bi bi-wallet2 fs-4 me-3 text-primary"></i>
                                    <div>
                                        <strong>PayMaya</strong>
                                        <small class="d-block text-muted">Pay with PayMaya</small>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Card form (shown for card) - visible by default -->
                        <div id="card-form" class="card-form-section mb-4">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-credit-card-2-back me-2"></i>Card Details</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Card Number <span class="text-danger">*</span></label>
                                    <input type="text" id="card_number" class="form-control form-control-lg" placeholder="4242 4242 4242 4242" maxlength="19" autocomplete="cc-number" inputmode="numeric">
                                    <small class="text-muted">Test card: 4242 4242 4242 4242</small>
                                </div>
                                <div class="col-4">
                                    <label class="form-label fw-semibold">Exp Month <span class="text-danger">*</span></label>
                                    <input type="number" id="exp_month" class="form-control" placeholder="12" min="1" max="12" autocomplete="cc-exp-month">
                                </div>
                                <div class="col-4">
                                    <label class="form-label fw-semibold">Exp Year <span class="text-danger">*</span></label>
                                    <input type="number" id="exp_year" class="form-control" placeholder="{{ date('Y') + 2 }}" min="{{ date('Y') }}" max="{{ date('Y') + 10 }}" autocomplete="cc-exp-year">
                                </div>
                                <div class="col-4">
                                    <label class="form-label fw-semibold">CVC <span class="text-danger">*</span></label>
                                    <input type="text" id="cvc" class="form-control" placeholder="123" maxlength="4" autocomplete="cc-csc" inputmode="numeric">
                                </div>
                            </div>
                        </div>

                        <!-- E-wallet notice (shown for gcash/paymaya) -->
                        <div id="ewallet-notice" class="alert alert-light border d-none mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            You will be redirected to complete payment in the app.
                        </div>

                        <div id="pay-error" class="alert alert-danger d-none"></div>
                        <div id="pay-loading" class="d-none text-center py-3">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 mb-0">Processing payment...</p>
                        </div>

                        <button type="button" id="pay-btn" class="btn btn-primary btn-lg w-100 py-3" style="background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%); border: none; font-weight: 600; border-radius: 12px;">
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
@if($publicKey ?? false)
<script>
(function() {
    const orderNumber = @json($order->order_number);
    const publicKey = @json($publicKey);
    const returnUrl = new URL('{{ route("checkout.return") }}', window.location.origin);
    returnUrl.searchParams.set('order_number', orderNumber);

    let clientKey = null;
    let paymentIntentId = null;

    function setError(msg) {
        const el = document.getElementById('pay-error');
        el.textContent = msg;
        el.classList.remove('d-none');
    }
    function clearError() {
        document.getElementById('pay-error').classList.add('d-none');
    }
    function setLoading(show) {
        document.getElementById('pay-btn').disabled = show;
        document.getElementById('pay-loading').classList.toggle('d-none', !show);
    }

    // Payment method selection
    document.querySelectorAll('.payment-method-option').forEach(function(el) {
        el.addEventListener('click', function() {
            document.querySelectorAll('.payment-method-option').forEach(function(o) { o.classList.remove('selected'); });
            this.classList.add('selected');
            this.querySelector('input').checked = true;
            const method = this.dataset.method;
            document.getElementById('card-form').classList.toggle('d-none', method !== 'card');
            document.getElementById('ewallet-notice').classList.toggle('d-none', method === 'card');
        });
    });
    document.getElementById('ewallet-notice').classList.add('d-none');

    async function createPaymentIntent() {
        const res = await fetch('{{ route("checkout.create-payment-intent") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ order_number: orderNumber })
        });
        const data = await res.json();
        if (data.client_key && data.id) {
            clientKey = data.client_key;
            paymentIntentId = data.id;
            return true;
        }
        throw new Error(data.error || 'Failed to initialize payment');
    }

    async function createPaymentMethod() {
        const method = document.querySelector('input[name="pay_method"]:checked').value;

        const attrs = {
            type: method,
            billing: {
                name: @json(auth()->user()->name ?? 'Customer'),
                email: @json(auth()->user()->email ?? ''),
                phone: @json($order->shipping_phone ?? ''),
            }
        };

        if (method === 'card') {
            const cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
            const expMonth = parseInt(document.getElementById('exp_month').value, 10);
            const expYear = parseInt(document.getElementById('exp_year').value, 10);
            const cvc = document.getElementById('cvc').value;
            if (!cardNumber || !expMonth || !expYear || !cvc) {
                throw new Error('Please fill in all card details.');
            }
            attrs.details = {
                card_number: cardNumber,
                exp_month: expMonth,
                exp_year: expYear,
                cvc: cvc
            };
        }

        const res = await fetch('https://api.paymongo.com/v1/payment_methods', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Basic ' + btoa(publicKey + ':')
            },
            body: JSON.stringify({ data: { attributes: attrs } })
        });

        const data = await res.json();
        const pm = data.data;
        if (!pm || !pm.id) {
            const err = data.errors?.[0]?.detail || data.message || 'Failed to create payment method';
            throw new Error(err);
        }
        return pm.id;
    }

    async function attachPaymentMethod(paymentMethodId) {
        const method = document.querySelector('input[name="pay_method"]:checked').value;
        returnUrl.searchParams.set('payment_intent_id', paymentIntentId);
        const attachPayload = {
            order_number: orderNumber,
            payment_intent_id: paymentIntentId,
            payment_method_id: paymentMethodId
        };
        if (method === 'gcash' || method === 'paymaya') {
            attachPayload.return_url = returnUrl.toString();
        }

        const res = await fetch('{{ route("checkout.attach-payment-method") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(attachPayload)
        });

        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.error || data.message || 'Failed to process payment');
        }

        const status = data.status;
        const redirectUrl = data.redirect_url;

        if (status === 'succeeded') {
            window.location.href = returnUrl.toString();
            return;
        }

        if (status === 'awaiting_next_action' && redirectUrl) {
            window.location.href = redirectUrl;
            return;
        }

        if (status === 'awaiting_payment_method') {
            throw new Error('Payment failed. Please check your card details and try again.');
        }

        if (status === 'processing') {
            window.location.href = returnUrl.toString();
            return;
        }

        throw new Error('Unexpected payment status. Please try again.');
    }

    document.getElementById('pay-btn').addEventListener('click', async function() {
        clearError();
        setLoading(true);
        try {
            if (!clientKey || !paymentIntentId) {
                await createPaymentIntent();
            }
            const pmId = await createPaymentMethod();
            await attachPaymentMethod(pmId);
        } catch (e) {
            setError(e.message || 'Payment failed. Please try again.');
            setLoading(false);
        }
    });

    // Initialize payment intent on load
    createPaymentIntent().catch(function() {
        setError('Could not initialize payment. Please refresh the page.');
    });
})();
</script>
@endif
@endpush
