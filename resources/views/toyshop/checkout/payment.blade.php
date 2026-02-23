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

                        <!-- Card form (shown for card) -->
                        <div id="card-form" class="mb-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Card Number</label>
                                    <input type="text" id="card_number" class="form-control" placeholder="4242 4242 4242 4242" maxlength="19" autocomplete="cc-number">
                                    <small class="text-muted">Test: 4242 4242 4242 4242</small>
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
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="save_card" name="save_card" value="1">
                                        <label class="form-check-label" for="save_card">Save this card for future purchases (optional)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- E-wallet: PayMongo redirect OR Seller QR (shown for gcash/paymaya) -->
                        <div id="ewallet-notice" class="alert alert-light border d-none mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            You will be redirected to complete payment in the app.
                        </div>
                        @if(($seller ?? null) && (($seller->gcash_qr_code ?? null) || ($seller->paymaya_qr_code ?? null)))
                        <div id="seller-qr-section" class="d-none mb-4 p-4 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <h6 class="fw-bold mb-3"><i class="bi bi-qr-code me-2"></i>Scan to Pay (Seller QR)</h6>
                            @if($seller->gcash_qr_code ?? null)
                            <div id="qr-gcash-wrap" class="text-center d-none">
                                <img src="{{ asset('storage/' . $seller->gcash_qr_code) }}" alt="GCash QR" class="img-fluid rounded" style="max-width: 200px;">
                                <p class="mt-2 small text-muted">Scan with GCash app · Amount: ₱{{ number_format($order->total, 2) }}</p>
                            </div>
                            @endif
                            @if($seller->paymaya_qr_code ?? null)
                            <div id="qr-paymaya-wrap" class="text-center d-none">
                                <img src="{{ asset('storage/' . $seller->paymaya_qr_code) }}" alt="PayMaya QR" class="img-fluid rounded" style="max-width: 200px;">
                                <p class="mt-2 small text-muted">Scan with PayMaya app · Amount: ₱{{ number_format($order->total, 2) }}</p>
                            </div>
                            @endif
                        </div>
                        @endif

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

    const sellerQrSection = document.getElementById('seller-qr-section');
    const qrGcashWrap = document.getElementById('qr-gcash-wrap');
    const qrPaymayaWrap = document.getElementById('qr-paymaya-wrap');

    function togglePaymentUi(method) {
        document.getElementById('card-form').classList.toggle('d-none', method !== 'card');
        var showSellerQr = sellerQrSection && (method === 'gcash' || method === 'paymaya');
        if (sellerQrSection) {
            sellerQrSection.classList.toggle('d-none', !showSellerQr);
            if (qrGcashWrap) qrGcashWrap.classList.toggle('d-none', method !== 'gcash');
            if (qrPaymayaWrap) qrPaymayaWrap.classList.toggle('d-none', method !== 'paymaya');
        }
        document.getElementById('ewallet-notice').classList.toggle('d-none', method === 'card' || showSellerQr);
    }

    document.querySelectorAll('.payment-method-option').forEach(function(el) {
        el.addEventListener('click', function() {
            document.querySelectorAll('.payment-method-option').forEach(function(o) { o.classList.remove('selected'); });
            this.classList.add('selected');
            this.querySelector('input').checked = true;
            togglePaymentUi(this.dataset.method);
        });
    });
    document.getElementById('ewallet-notice').classList.add('d-none');
    if (sellerQrSection) sellerQrSection.classList.add('d-none');

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
        const body = {
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

        const res = await fetch('https://api.paymongo.com/v1/payment_intents/' + paymentIntentId + '/attach', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Basic ' + btoa(publicKey + ':')
            },
            body: JSON.stringify(body)
        });

        const data = await res.json();
        const pi = data.data;
        if (!pi) {
            const err = data.errors?.[0]?.detail || data.message || 'Failed to process payment';
            throw new Error(err);
        }

        const status = pi.attributes?.status;
        const nextAction = pi.attributes?.next_action;

        if (status === 'succeeded') {
            returnUrl.searchParams.set('payment_intent_id', paymentIntentId);
            window.location.href = returnUrl.toString();
            return;
        }

        if (status === 'awaiting_next_action' && nextAction?.redirect?.url) {
            window.location.href = nextAction.redirect.url;
            return;
        }

        if (status === 'awaiting_payment_method') {
            const err = pi.attributes?.last_payment_error?.message || 'Payment failed. Please check your details.';
            throw new Error(err);
        }

        if (status === 'processing') {
            returnUrl.searchParams.set('payment_intent_id', paymentIntentId);
            window.location.href = returnUrl.toString();
            return;
        }

        throw new Error('Unexpected payment status: ' + status);
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
