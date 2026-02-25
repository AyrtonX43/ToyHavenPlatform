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
    .payment-method-option { border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 0.75rem; cursor: pointer; transition: all 0.2s; }
    .payment-method-option:hover { border-color: #06b6d4; background: #f0fdfa; }
    .payment-method-option.selected { border-color: #0891b2; background: #ecfeff; }
    .payment-method-option input { display: none; }
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
                {{-- Plan header --}}
                <div class="payment-plan-header">
                    <h2><i class="bi bi-gem me-2"></i>{{ $subscription->plan->name }} Membership</h2>
                    <span class="plan-badge">{{ $subscription->plan->interval === 'monthly' ? 'Monthly billing' : 'Annual billing' }}</span>
                </div>

                {{-- Purchase description --}}
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

                {{-- Benefits --}}
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

                {{-- Order summary --}}
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

                {{-- Payment section --}}
                <div class="payment-actions">
                    @if(($publicKey ?? false) && ($paymentIntentId ?? false))
                        {{-- Payment method selection --}}
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

                        {{-- Card form --}}
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

                        {{-- E-wallet notice --}}
                        <div id="ewallet-notice" class="alert alert-light border d-none mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            You will be redirected to complete payment in the app.
                        </div>

                        <div id="pay-error" class="alert alert-danger d-none"></div>
                        <div id="pay-loading" class="d-none text-center py-3">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 mb-0">Processing payment...</p>
                        </div>

                        <button type="button" id="pay-btn" class="btn btn-primary btn-lg w-100" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                            <i class="bi bi-lock-fill me-2"></i>Pay ₱{{ number_format($subscription->plan->price, 0) }}
                        </button>

                        <div class="mt-3 text-center">
                            <a href="{{ route('membership.manage') }}" class="text-muted small">
                                <i class="bi bi-arrow-left me-1"></i>Back to Membership
                            </a>
                        </div>
                    @else
                        <p class="text-muted small mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Could not initialize payment. Please go back and try again, or contact support.
                        </p>
                        <a href="{{ route('membership.manage') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                            <i class="bi bi-arrow-left me-1"></i>Back to Membership
                        </a>
                    @endif
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
    const publicKey = @json($publicKey);
    const paymentIntentId = @json($paymentIntentId);
    const subscriptionId = @json($subscription->id);
    const returnUrl = new URL('{{ route("membership.payment-return") }}', window.location.origin);
    returnUrl.searchParams.set('subscription_id', subscriptionId);

    let clientKey = null;

    function setError(msg) {
        const el = document.getElementById('pay-error');
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
            document.querySelectorAll('.payment-method-option').forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            this.querySelector('input').checked = true;
            togglePaymentUi(this.dataset.method);
        });
    });

    // Fetch the client_key for this payment intent
    async function fetchClientKey() {
        const res = await fetch('https://api.paymongo.com/v1/payment_intents/' + paymentIntentId + '?client_key=', {
            headers: { 'Authorization': 'Basic ' + btoa(publicKey + ':') }
        });
        const data = await res.json();
        clientKey = data.data?.attributes?.client_key;
    }

    async function createPaymentMethod() {
        const method = document.querySelector('input[name="pay_method"]:checked').value;
        const attrs = {
            type: method,
            billing: {
                name: @json(auth()->user()->name ?? 'Customer'),
                email: @json(auth()->user()->email ?? ''),
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
            attrs.details = { card_number: cardNumber, exp_month: expMonth, exp_year: expYear, cvc: cvc };
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
        if (!data.data?.id) {
            throw new Error(data.errors?.[0]?.detail || 'Failed to create payment method');
        }
        return data.data.id;
    }

    async function attachPaymentMethod(paymentMethodId) {
        const method = document.querySelector('input[name="pay_method"]:checked').value;
        const body = {
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
            throw new Error(data.errors?.[0]?.detail || 'Failed to process payment');
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
            throw new Error(pi.attributes?.last_payment_error?.message || 'Payment failed. Please try again.');
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
            if (!clientKey) {
                await fetchClientKey();
            }
            const pmId = await createPaymentMethod();
            await attachPaymentMethod(pmId);
        } catch (e) {
            setError(e.message || 'Payment failed. Please try again.');
            setLoading(false);
        }
    });

    fetchClientKey().catch(function() {});
})();
</script>
@endif
@endpush
