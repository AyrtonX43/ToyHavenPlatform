@extends('layouts.toyshop')

@section('title', 'Complete Subscription Payment - ToyHaven')

@push('styles')
<style>
    .payment-order-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 2px solid #e2e8f0;
        overflow: hidden;
    }
    .payment-plan-header {
        background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
        color: white;
        padding: 1.5rem 2rem;
    }
    .payment-plan-header h2 {
        margin: 0;
        font-weight: 700;
        font-size: 1.5rem;
    }
    .payment-plan-header .plan-badge {
        display: inline-block;
        background: rgba(255,255,255,0.25);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-top: 0.5rem;
    }
    .benefits-section {
        padding: 1.5rem 2rem;
    }
    .benefits-section h4 {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .benefits-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .benefits-list li {
        padding: 0.6rem 0;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        font-size: 0.95rem;
        color: #475569;
        border-bottom: 1px solid #f1f5f9;
    }
    .benefits-list li:last-child {
        border-bottom: none;
    }
    .benefits-list li i {
        color: #10b981;
        font-size: 1.1rem;
        flex-shrink: 0;
        margin-top: 0.1rem;
    }
    .order-summary-box {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        margin-top: 1.5rem;
    }
    .order-summary-box .price-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 1.1rem;
    }
    .order-summary-box .price-row .total {
        font-weight: 800;
        color: #0891b2;
        font-size: 1.35rem;
    }
    .payment-actions {
        padding: 1.5rem 2rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }
    .payment-method-option {
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-bottom: 0.75rem;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
    }
    .payment-method-option:hover { border-color: #06b6d4; background: #f0fdfa; }
    .payment-method-option.selected { border-color: #0891b2; background: #ecfeff; box-shadow: 0 0 0 3px rgba(8,145,178,0.1); }
    .payment-method-option input[type="radio"] { display: none; }
    .payment-method-option .pm-radio {
        width: 20px; height: 20px;
        border: 2px solid #cbd5e1;
        border-radius: 50%;
        flex-shrink: 0;
        margin-right: 1rem;
        display: flex; align-items: center; justify-content: center;
        transition: all 0.2s;
    }
    .payment-method-option.selected .pm-radio {
        border-color: #0891b2;
    }
    .payment-method-option.selected .pm-radio::after {
        content: '';
        width: 10px; height: 10px;
        background: #0891b2;
        border-radius: 50%;
    }
    .cancel-payment-modal .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    }
    .cancel-payment-modal .modal-header {
        border-bottom: 1px solid #f1f5f9;
        padding: 1.5rem 2rem 1rem;
    }
    .cancel-payment-modal .modal-body {
        padding: 1.5rem 2rem;
    }
    .cancel-payment-modal .modal-footer {
        border-top: 1px solid #f1f5f9;
        padding: 1rem 2rem 1.5rem;
    }
    .secure-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.8rem;
        color: #64748b;
        background: #f1f5f9;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        margin-top: 0.75rem;
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
                <div class="payment-plan-header">
                    <h2><i class="bi bi-gem me-2"></i>{{ $subscription->plan->name }} Membership</h2>
                    <span class="plan-badge">{{ $subscription->plan->interval === 'monthly' ? 'Monthly billing' : 'Annual billing' }}</span>
                </div>

                <div class="benefits-section">
                    <h4><i class="bi bi-receipt text-primary"></i> What you're purchasing</h4>
                    <p class="mb-0 text-muted">
                        {{ $subscription->plan->description }}
                    </p>
                    <p class="mt-2 mb-0 small text-muted">
                        You're subscribing to <strong>{{ $subscription->plan->name }}</strong> at
                        <strong>₱{{ number_format($subscription->plan->price, 0) }}/{{ $subscription->plan->interval === 'monthly' ? 'month' : 'year' }}</strong>.
                        {{ $subscription->plan->interval === 'monthly' ? 'Billed monthly. Cancel anytime.' : 'Billed annually. Cancel anytime.' }}
                    </p>
                </div>

                <div class="benefits-section pt-0">
                    <h4><i class="bi bi-stars text-warning"></i> Benefits you'll get</h4>
                    <ul class="benefits-list">
                        @foreach($subscription->plan->features ?? [] as $feature)
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                        @if(($subscription->plan->features ?? []) === [])
                            <li><i class="bi bi-check-circle-fill"></i> Access to auctions and bidding</li>
                            <li><i class="bi bi-check-circle-fill"></i> Member badge on your profile</li>
                        @endif
                    </ul>
                </div>

                <div class="benefits-section pt-0">
                    <div class="order-summary-box">
                        <div class="price-row mb-1">
                            <span>{{ $subscription->plan->name }} ({{ $subscription->plan->interval === 'monthly' ? 'per month' : 'per year' }})</span>
                            <span class="total">₱{{ number_format($subscription->plan->price, 0) }}</span>
                        </div>
                        <p class="mb-0 small text-muted mt-2">
                            {{ $subscription->plan->interval === 'monthly' ? 'First payment today. Then charged monthly.' : 'First payment today. Then charged yearly.' }}
                        </p>
                    </div>
                </div>

                <div class="payment-actions">
                    @if(($publicKey ?? false) && ($paymentIntentId ?? false))
                        @php
                            $methods = $availableMethods ?? ['card'];
                        @endphp
                        <div class="mb-4">
                            <label class="form-label fw-semibold mb-3">Select Payment Method</label>

                            @if(in_array('card', $methods))
                            <div class="payment-method-option selected" data-method="card">
                                <input type="radio" name="pay_method" value="card" id="pm_card" checked>
                                <span class="pm-radio"></span>
                                <i class="bi bi-credit-card-2-front fs-4 me-3 text-primary"></i>
                                <div>
                                    <strong>Credit / Debit Card</strong>
                                    <small class="d-block text-muted">Visa, Mastercard</small>
                                </div>
                            </div>
                            @endif

                            @if(in_array('gcash', $methods))
                            <div class="payment-method-option {{ !in_array('card', $methods) ? 'selected' : '' }}" data-method="gcash">
                                <input type="radio" name="pay_method" value="gcash" id="pm_gcash" {{ !in_array('card', $methods) ? 'checked' : '' }}>
                                <span class="pm-radio"></span>
                                <i class="bi bi-phone fs-4 me-3 text-success"></i>
                                <div>
                                    <strong>GCash</strong>
                                    <small class="d-block text-muted">Pay with GCash e-wallet</small>
                                </div>
                            </div>
                            @endif

                            @if(in_array('paymaya', $methods))
                            <div class="payment-method-option {{ !in_array('card', $methods) && !in_array('gcash', $methods) ? 'selected' : '' }}" data-method="paymaya">
                                <input type="radio" name="pay_method" value="paymaya" id="pm_paymaya" {{ !in_array('card', $methods) && !in_array('gcash', $methods) ? 'checked' : '' }}>
                                <span class="pm-radio"></span>
                                <i class="bi bi-wallet2 fs-4 me-3 text-primary"></i>
                                <div>
                                    <strong>Maya (PayMaya)</strong>
                                    <small class="d-block text-muted">Pay with Maya e-wallet</small>
                                </div>
                            </div>
                            @endif
                        </div>

                        @if(in_array('card', $methods))
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
                        @endif

                        <div id="ewallet-notice" class="alert alert-light border {{ in_array('card', $methods) ? 'd-none' : '' }} mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            You will be redirected to complete payment securely via the selected e-wallet app.
                        </div>

                        <div id="pay-error" class="alert alert-danger d-none"></div>
                        <div id="pay-loading" class="d-none text-center py-3">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 mb-0">Processing your payment securely...</p>
                        </div>

                        <button type="button" id="pay-btn" class="btn btn-primary btn-lg w-100" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                            <i class="bi bi-lock-fill me-2"></i>Pay ₱{{ number_format($subscription->plan->price, 0) }}
                        </button>

                        <div class="text-center">
                            <span class="secure-badge">
                                <i class="bi bi-shield-lock-fill"></i> Secured by PayMongo
                            </span>
                        </div>

                        <div class="mt-3 text-center">
                            <button type="button" class="btn btn-link text-muted small text-decoration-none" id="cancel-payment-btn">
                                <i class="bi bi-x-circle me-1"></i>Cancel &amp; choose a different plan
                            </button>
                        </div>
                    @else
                        <p class="text-muted small mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Could not initialize payment. Please go back and try again, or contact support.
                        </p>
                        <a href="{{ route('membership.index') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                            <i class="bi bi-arrow-left me-1"></i>Back to Plans
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade cancel-payment-modal" id="cancelPaymentModal" tabindex="-1" aria-labelledby="cancelPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="cancelPaymentModalLabel">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>Cancel Payment?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Are you sure you want to cancel this payment?</p>
                <p class="text-muted small mb-0">
                    Your <strong>{{ $subscription->plan->name }}</strong> subscription will not be activated
                    and you will be taken back to the plan selection page where you can choose a different plan.
                </p>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-arrow-left me-1"></i>Continue Payment
                </button>
                <form action="{{ route('membership.cancel-pending', $subscription->id) }}" method="POST" id="cancel-pending-form">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i>Yes, Cancel &amp; Choose Another Plan
                    </button>
                </form>
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
    var subscriptionId = @json($subscription->id);
    var clientKey = @json($clientKey ?? null);
    var returnUrl = new URL('/membership/payment-return', window.location.origin);
    returnUrl.searchParams.set('subscription_id', subscriptionId);

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
        var cardForm = document.getElementById('card-form');
        var ewalletNotice = document.getElementById('ewallet-notice');
        if (cardForm) cardForm.classList.toggle('d-none', method !== 'card');
        if (ewalletNotice) ewalletNotice.classList.toggle('d-none', method === 'card');
    }

    document.querySelectorAll('.payment-method-option').forEach(function(el) {
        el.addEventListener('click', function() {
            document.querySelectorAll('.payment-method-option').forEach(function(o) {
                o.classList.remove('selected');
            });
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
            var formatted = val.replace(/(.{4})/g, '$1 ').trim();
            this.value = formatted;
        });
    }

    var cancelBtn = document.getElementById('cancel-payment-btn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            var modal = new bootstrap.Modal(document.getElementById('cancelPaymentModal'));
            modal.show();
        });
    }

    async function ensureClientKey() {
        if (clientKey) return;
        var res = await fetch('https://api.paymongo.com/v1/payment_intents/' + paymentIntentId, {
            headers: { 'Authorization': 'Basic ' + btoa(publicKey + ':') }
        });
        var data = await res.json();
        clientKey = data.data?.attributes?.client_key;
        if (!clientKey) {
            throw new Error('Could not retrieve payment session. Please refresh the page and try again.');
        }
    }

    async function createPaymentMethod() {
        var method = document.querySelector('input[name="pay_method"]:checked').value;
        var attrs = {
            type: method,
            billing: {
                name: @json(auth()->user()->name ?? 'Customer'),
                email: @json(auth()->user()->email ?? ''),
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
            attrs.details = { card_number: cardNumber, exp_month: expMonth, exp_year: expYear, cvc: cvc };
        }

        var res = await fetch('https://api.paymongo.com/v1/payment_methods', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Basic ' + btoa(publicKey + ':')
            },
            body: JSON.stringify({ data: { attributes: attrs } })
        });

        var data = await res.json();
        if (!data.data?.id) {
            throw new Error(data.errors?.[0]?.detail || 'Failed to create payment method. Please check your details.');
        }
        return data.data.id;
    }

    async function attachPaymentMethod(paymentMethodId) {
        var method = document.querySelector('input[name="pay_method"]:checked').value;
        var body = {
            data: {
                attributes: {
                    client_key: clientKey,
                    payment_method: paymentMethodId,
                }
            }
        };

        if (method === 'gcash' || method === 'paymaya') {
            returnUrl.searchParams.set('payment_intent_id', paymentIntentId);
            body.data.attributes.return_url = returnUrl.toString();
        }

        var res = await fetch('https://api.paymongo.com/v1/payment_intents/' + paymentIntentId + '/attach', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Basic ' + btoa(publicKey + ':')
            },
            body: JSON.stringify(body)
        });

        var data = await res.json();
        var pi = data.data;
        if (!pi) {
            throw new Error(data.errors?.[0]?.detail || 'Failed to process payment. Please try again.');
        }

        var status = pi.attributes?.status;
        var nextAction = pi.attributes?.next_action;

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
            throw new Error(pi.attributes?.last_payment_error?.message || 'Payment failed. Please try again with a different payment method.');
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
            await ensureClientKey();
            var pmId = await createPaymentMethod();
            await attachPaymentMethod(pmId);
        } catch (e) {
            setError(e.message || 'Payment failed. Please try again.');
            setLoading(false);
        }
    });
})();
</script>
@endif
@endpush
