@extends('layouts.toyshop')

@section('title', 'Payment - ToyHaven')

@push('styles')
<style>
    .payment-order-card {
        background: #fff; border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 2px solid #e2e8f0; overflow: hidden;
    }
    .payment-order-header {
        background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
        color: white; padding: 1.5rem 2rem;
    }
    .payment-order-header h2 { margin: 0; font-weight: 700; font-size: 1.5rem; }
    .payment-order-header .order-badge {
        display: inline-block; background: rgba(255,255,255,0.25);
        padding: 0.25rem 0.75rem; border-radius: 20px;
        font-size: 0.85rem; font-weight: 600; margin-top: 0.5rem;
    }
    .order-details-section { padding: 1.5rem 2rem; }
    .order-details-section h4 {
        font-size: 1rem; font-weight: 700; color: #1e293b;
        margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;
    }
    .order-summary-box { background: #f8fafc; border-radius: 12px; padding: 1.25rem 1.5rem; }
    .order-summary-box .price-row { display: flex; justify-content: space-between; align-items: center; font-size: 1.1rem; }
    .order-summary-box .price-row .total { font-weight: 800; color: #0891b2; font-size: 1.35rem; }
    .payment-actions { padding: 1.5rem 2rem; background: #f8fafc; border-top: 1px solid #e2e8f0; }
    .payment-method-option {
        border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 1rem 1.25rem;
        margin-bottom: 0.75rem; cursor: pointer; transition: all 0.2s;
        display: flex; align-items: center;
    }
    .payment-method-option:hover { border-color: #06b6d4; background: #f0fdfa; }
    .payment-method-option.selected { border-color: #0891b2; background: #ecfeff; box-shadow: 0 0 0 3px rgba(8,145,178,0.1); }
    .payment-method-option input[type="radio"] { display: none; }
    .payment-method-option .pm-radio {
        width: 20px; height: 20px; border: 2px solid #cbd5e1; border-radius: 50%;
        flex-shrink: 0; margin-right: 1rem;
        display: flex; align-items: center; justify-content: center; transition: all 0.2s;
    }
    .payment-method-option.selected .pm-radio { border-color: #0891b2; }
    .payment-method-option.selected .pm-radio::after {
        content: ''; width: 10px; height: 10px; background: #0891b2; border-radius: 50%;
    }
    .secure-badge {
        display: inline-flex; align-items: center; gap: 0.4rem;
        font-size: 0.8rem; color: #64748b; background: #f1f5f9;
        padding: 0.35rem 0.75rem; border-radius: 20px; margin-top: 0.75rem;
    }
    .secure-badge i { color: #10b981; }
</style>
@endpush

@section('content')
<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="payment-order-card mb-4">
                <div class="payment-order-header">
                    <h2><i class="bi bi-credit-card-2-front me-2"></i>Complete Payment</h2>
                    <span class="order-badge">Order #{{ $order->order_number }}</span>
                </div>

                <div class="order-details-section">
                    <h4><i class="bi bi-receipt text-primary"></i> Order Summary</h4>
                    <div class="order-summary-box">
                        <div class="price-row mb-1">
                            <span>Order Total</span>
                            <span class="total">₱{{ number_format($order->total, 2) }}</span>
                        </div>
                        <p class="mb-0 small text-muted mt-2">
                            Secure payment powered by PayMongo. Your card details are never stored on our servers.
                        </p>
                    </div>
                </div>

                <div class="payment-actions">
                    @if(($publicKey ?? false) && ($paymentIntentId ?? false) && ($clientKey ?? false))
                        <div class="mb-4">
                            <label class="form-label fw-semibold mb-3">Select Payment Method</label>

                            <div class="payment-method-option selected" data-method="card">
                                <input type="radio" name="pay_method" value="card" id="pm_card" checked>
                                <span class="pm-radio"></span>
                                <i class="bi bi-credit-card-2-front fs-4 me-3 text-primary"></i>
                                <div>
                                    <strong>Credit / Debit Card</strong>
                                    <small class="d-block text-muted">Visa, Mastercard</small>
                                </div>
                            </div>

                            <div class="payment-method-option" data-method="gcash">
                                <input type="radio" name="pay_method" value="gcash" id="pm_gcash">
                                <span class="pm-radio"></span>
                                <i class="bi bi-phone fs-4 me-3 text-success"></i>
                                <div>
                                    <strong>GCash</strong>
                                    <small class="d-block text-muted">Pay with GCash e-wallet</small>
                                </div>
                            </div>

                            <div class="payment-method-option" data-method="paymaya">
                                <input type="radio" name="pay_method" value="paymaya" id="pm_paymaya">
                                <span class="pm-radio"></span>
                                <i class="bi bi-wallet2 fs-4 me-3 text-primary"></i>
                                <div>
                                    <strong>Maya (PayMaya)</strong>
                                    <small class="d-block text-muted">Pay with Maya e-wallet</small>
                                </div>
                            </div>
                        </div>

                        <div id="card-form" class="mb-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Card Number</label>
                                    <input type="text" id="card_number" class="form-control" placeholder="0000 0000 0000 0000" maxlength="19" autocomplete="cc-number" inputmode="numeric">
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Exp Month</label>
                                    <input type="number" id="exp_month" class="form-control" placeholder="MM" min="1" max="12" autocomplete="cc-exp-month" inputmode="numeric">
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Exp Year</label>
                                    <input type="number" id="exp_year" class="form-control" placeholder="YYYY" min="{{ date('Y') }}" autocomplete="cc-exp-year" inputmode="numeric">
                                </div>
                                <div class="col-4">
                                    <label class="form-label">CVC</label>
                                    <input type="password" id="cvc" class="form-control" placeholder="•••" maxlength="4" autocomplete="cc-csc" inputmode="numeric">
                                </div>
                            </div>
                        </div>

                        <div id="ewallet-notice" class="alert alert-light border d-none mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            You will be redirected to complete payment securely via the selected e-wallet app.
                        </div>

                        <div id="pay-error" class="alert alert-danger d-none"></div>
                        <div id="pay-loading" class="d-none text-center py-3">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 mb-0">Processing your payment securely...</p>
                        </div>

                        <button type="button" id="pay-btn" class="btn btn-primary btn-lg w-100" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                            <i class="bi bi-lock-fill me-2"></i>Pay ₱{{ number_format($order->total, 2) }}
                        </button>

                        <div class="text-center">
                            <span class="secure-badge"><i class="bi bi-shield-lock-fill"></i> Secured by PayMongo</span>
                        </div>

                        <div class="mt-3 text-center">
                            <a href="{{ route('orders.show', $order->id) }}" class="text-muted small">
                                <i class="bi bi-arrow-left me-1"></i>View Order Details
                            </a>
                        </div>
                    @else
                        <div class="alert alert-warning mb-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Could not initialize payment. Please try again or contact support.
                        </div>
                        <a href="{{ route('checkout.payment', $order->order_number) }}" class="btn btn-primary w-100 mb-3" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                            <i class="bi bi-arrow-clockwise me-2"></i>Retry Payment
                        </a>
                        <div class="mt-2 text-center">
                            <a href="{{ route('orders.show', $order->id) }}" class="text-muted small">
                                <i class="bi bi-arrow-left me-1"></i>View Order Details
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(($publicKey ?? false) && ($paymentIntentId ?? false) && ($clientKey ?? false))
<script>
(function() {
    var publicKey = @json($publicKey);
    var paymentIntentId = @json($paymentIntentId);
    var clientKey = @json($clientKey);
    var orderNumber = @json($order->order_number);
    var returnUrl = new URL('/checkout/return', window.location.origin);
    returnUrl.searchParams.set('order_number', orderNumber);
    returnUrl.searchParams.set('payment_intent_id', paymentIntentId);

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
    function getSelectedMethod() {
        var checked = document.querySelector('input[name="pay_method"]:checked');
        return checked ? checked.value : 'card';
    }

    function togglePaymentUi(method) {
        var cardForm = document.getElementById('card-form');
        var ewalletNotice = document.getElementById('ewallet-notice');
        if (cardForm) cardForm.classList.toggle('d-none', method !== 'card');
        if (ewalletNotice) ewalletNotice.classList.toggle('d-none', method === 'card');
    }

    document.querySelectorAll('.payment-method-option').forEach(function(el) {
        el.addEventListener('click', function() {
            document.querySelectorAll('.payment-method-option').forEach(function(o) { o.classList.remove('selected'); });
            this.classList.add('selected');
            this.querySelector('input[type="radio"]').checked = true;
            togglePaymentUi(this.dataset.method);
            clearError();
        });
    });

    var cardInput = document.getElementById('card_number');
    if (cardInput) {
        cardInput.addEventListener('input', function() {
            var val = this.value.replace(/\D/g, '').substring(0, 16);
            this.value = val.replace(/(.{4})/g, '$1 ').trim();
        });
    }

    function handlePaymentStatus(pi) {
        var status = pi.attributes?.status;
        var nextAction = pi.attributes?.next_action;

        if (status === 'succeeded') {
            window.location.href = returnUrl.toString();
            return true;
        }

        if (status === 'awaiting_next_action' && nextAction) {
            var redirectUrl = nextAction.redirect?.url || nextAction.url;
            if (redirectUrl) {
                window.location.href = redirectUrl;
                return true;
            }
        }

        if (status === 'processing') {
            window.location.href = returnUrl.toString();
            return true;
        }

        if (status === 'awaiting_payment_method') {
            throw new Error(pi.attributes?.last_payment_error?.message || 'Payment failed. Please try again.');
        }

        return false;
    }

    document.getElementById('pay-btn').addEventListener('click', async function() {
        clearError();
        setLoading(true);
        try {
            var method = getSelectedMethod();
            var pmAttrs = {
                type: method,
                billing: {
                    name: @json(auth()->user()->name ?? 'Customer'),
                    email: @json(auth()->user()->email ?? '')
                }
            };

            if (method === 'card') {
                var cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
                var expMonth = parseInt(document.getElementById('exp_month').value, 10);
                var expYear = parseInt(document.getElementById('exp_year').value, 10);
                var cvc = document.getElementById('cvc').value;
                if (!cardNumber || !expMonth || !expYear || !cvc) {
                    throw new Error('Please fill in all card details.');
                }
                if (cardNumber.length < 13 || cardNumber.length > 19) {
                    throw new Error('Please enter a valid card number.');
                }
                if (expMonth < 1 || expMonth > 12) {
                    throw new Error('Please enter a valid expiration month (1-12).');
                }
                if (cvc.length < 3) {
                    throw new Error('Please enter a valid CVC.');
                }
                pmAttrs.details = { card_number: cardNumber, exp_month: expMonth, exp_year: expYear, cvc: cvc };
            }

            var pmRes = await fetch('https://api.paymongo.com/v1/payment_methods', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Basic ' + btoa(publicKey + ':') },
                body: JSON.stringify({ data: { attributes: pmAttrs } })
            });
            var pmData = await pmRes.json();
            if (!pmData.data?.id) {
                throw new Error(pmData.errors?.[0]?.detail || 'Failed to create payment method. Please check your details.');
            }

            var attachBody = {
                data: {
                    attributes: {
                        payment_method: pmData.data.id,
                        client_key: clientKey,
                        return_url: returnUrl.toString()
                    }
                }
            };

            var attachRes = await fetch('https://api.paymongo.com/v1/payment_intents/' + paymentIntentId + '/attach', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Basic ' + btoa(publicKey + ':') },
                body: JSON.stringify(attachBody)
            });
            var attachData = await attachRes.json();
            var pi = attachData.data;
            if (!pi) {
                throw new Error(attachData.errors?.[0]?.detail || 'Failed to process payment. Please try again.');
            }

            if (!handlePaymentStatus(pi)) {
                throw new Error('Payment could not be completed. Status: ' + (pi.attributes?.status || 'unknown') + '. Please try again.');
            }
        } catch (e) {
            setError(e.message || 'Payment failed. Please try again.');
            setLoading(false);
        }
    });
})();
</script>
@endif
@endpush
