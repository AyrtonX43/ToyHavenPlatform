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

                {{-- Benefits elaboration --}}
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
                    @if($paymentIntentId && $publicKey && $clientKey)
                        <p class="text-muted small mb-3">
                            <i class="bi bi-credit-card me-1"></i>
                            Complete your payment below. Payment integration with PayMongo will process your subscription securely.
                        </p>

                        {{-- Payment method selection --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Select Payment Method</label>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <div class="payment-method-option-member selected" data-method="card" style="border: 2px solid #e2e8f0; border-radius: 12px; padding: 0.75rem 1rem; cursor: pointer;">
                                    <input type="radio" name="pay_method" value="card" id="pm_card" checked style="display: none;">
                                    <label for="pm_card" class="mb-0 d-flex align-items-center" style="cursor: pointer;">
                                        <i class="bi bi-credit-card-2-front fs-4 me-2 text-primary"></i>
                                        <span><strong>Card</strong></span>
                                    </label>
                                </div>
                                <div class="payment-method-option-member" data-method="gcash" style="border: 2px solid #e2e8f0; border-radius: 12px; padding: 0.75rem 1rem; cursor: pointer;">
                                    <input type="radio" name="pay_method" value="gcash" id="pm_gcash" style="display: none;">
                                    <label for="pm_gcash" class="mb-0 d-flex align-items-center" style="cursor: pointer;">
                                        <i class="bi bi-phone fs-4 me-2 text-success"></i>
                                        <span><strong>GCash</strong></span>
                                    </label>
                                </div>
                                <div class="payment-method-option-member" data-method="paymaya" style="border: 2px solid #e2e8f0; border-radius: 12px; padding: 0.75rem 1rem; cursor: pointer;">
                                    <input type="radio" name="pay_method" value="paymaya" id="pm_paymaya" style="display: none;">
                                    <label for="pm_paymaya" class="mb-0 d-flex align-items-center" style="cursor: pointer;">
                                        <i class="bi bi-wallet2 fs-4 me-2 text-primary"></i>
                                        <span><strong>PayMaya</strong></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Card form --}}
                        <div id="member-card-form" class="mb-3">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label">Card Number</label>
                                    <input type="text" id="member_card_number" class="form-control" placeholder="4242 4242 4242 4242" maxlength="19" autocomplete="cc-number">
                                    <small class="text-muted">Test: 4242 4242 4242 4242</small>
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Exp Month</label>
                                    <input type="number" id="member_exp_month" class="form-control" placeholder="12" min="1" max="12">
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Exp Year</label>
                                    <input type="number" id="member_exp_year" class="form-control" placeholder="2028" min="{{ date('Y') }}">
                                </div>
                                <div class="col-4">
                                    <label class="form-label">CVC</label>
                                    <input type="text" id="member_cvc" class="form-control" placeholder="123" maxlength="4">
                                </div>
                            </div>
                        </div>

                        <div id="member-ewallet-notice" class="alert alert-light border d-none mb-3">
                            <i class="bi bi-info-circle me-2"></i>You will be redirected to complete payment in the app.
                        </div>
                        <div id="member-qr-section" class="d-none mb-3 p-3 rounded text-center" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <h6 class="fw-bold mb-2"><i class="bi bi-qr-code me-2"></i>Scan with GCash or PayMaya</h6>
                            <img id="member-qr-img" src="" alt="QR" class="img-fluid rounded" style="max-width: 200px;">
                        </div>

                        <div id="member-pay-error" class="alert alert-danger d-none mb-2"></div>
                        <div id="member-pay-loading" class="d-none text-center py-2">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                            <span class="ms-2">Processing...</span>
                        </div>

                        <button type="button" id="member-pay-btn" class="btn btn-primary w-100" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                            <i class="bi bi-lock-fill me-1"></i>Pay ₱{{ number_format($subscription->plan->price, 0) }}
                        </button>
                        <a href="{{ route('membership.manage') }}" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="bi bi-arrow-left me-1"></i>Back to Membership
                        </a>
                    @elseif($paymentIntentId && $publicKey)
                        <div class="alert alert-warning mb-2">
                            Could not load payment details. Please try again or contact support.
                        </div>
                        <a href="{{ route('membership.manage') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Back to Membership
                        </a>
                    @else
                        <p class="text-muted small mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Your subscription has been created. Please complete your payment in the membership dashboard or contact support.
                        </p>
                        <a href="{{ route('membership.manage') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                            <i class="bi bi-gear me-1"></i>Go to Membership
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@if($paymentIntentId && $publicKey && ($clientKey ?? null))
@push('scripts')
<script>
(function() {
    const subscriptionId = @json($subscription->id);
    const paymentIntentId = @json($paymentIntentId);
    const clientKey = @json($clientKey);
    const publicKey = @json($publicKey);
    const returnUrl = new URL('{{ route("membership.payment.return") }}', window.location.origin);
    returnUrl.searchParams.set('subscription_id', subscriptionId);
    returnUrl.searchParams.set('payment_intent_id', paymentIntentId);

    const cardForm = document.getElementById('member-card-form');
    const ewalletNotice = document.getElementById('member-ewallet-notice');
    const qrSection = document.getElementById('member-qr-section');
    const qrImg = document.getElementById('member-qr-img');
    const payError = document.getElementById('member-pay-error');
    const payLoading = document.getElementById('member-pay-loading');
    const payBtn = document.getElementById('member-pay-btn');

    function setError(msg) {
        payError.textContent = msg;
        payError.classList.remove('d-none');
    }
    function clearError() {
        payError.classList.add('d-none');
    }
    function setLoading(show) {
        payBtn.disabled = show;
        payLoading.classList.toggle('d-none', !show);
    }

    let qrPollInterval = null;
    function startQrPolling() {
        if (qrPollInterval) clearInterval(qrPollInterval);
        qrPollInterval = setInterval(async function() {
            try {
                const r = await fetch('https://api.paymongo.com/v1/payment_intents/' + paymentIntentId + '?client_key=' + encodeURIComponent(clientKey), {
                    headers: { 'Authorization': 'Basic ' + btoa(publicKey + ':') }
                });
                const d = await r.json();
                const status = d.data?.attributes?.status;
                if (status === 'succeeded') {
                    clearInterval(qrPollInterval);
                    qrPollInterval = null;
                    window.location.href = returnUrl.toString();
                }
            } catch (e) {}
        }, 3000);
    }

    document.querySelectorAll('.payment-method-option-member').forEach(function(el) {
        el.addEventListener('click', function() {
            document.querySelectorAll('.payment-method-option-member').forEach(function(o) {
                o.classList.remove('selected');
                o.style.borderColor = '#e2e8f0';
            });
            this.classList.add('selected');
            this.style.borderColor = '#0891b2';
            this.querySelector('input').checked = true;
            const method = this.dataset.method;
            cardForm.classList.toggle('d-none', method !== 'card');
            ewalletNotice.classList.toggle('d-none', method !== 'gcash' && method !== 'paymaya');
            qrSection.classList.add('d-none');
        });
    });

    async function createPaymentMethod() {
        const method = document.querySelector('input[name="pay_method"]:checked').value;
        const payMethodType = (method === 'gcash' || method === 'paymaya') ? 'qrph' : method;
        const attrs = {
            type: payMethodType,
            billing: {
                name: @json(auth()->user()->name ?? 'Customer'),
                email: @json(auth()->user()->email ?? ''),
                phone: @json(auth()->user()->phone ?? ''),
            }
        };
        if (method === 'card') {
            const cardNumber = document.getElementById('member_card_number').value.replace(/\s/g, '');
            const expMonth = parseInt(document.getElementById('member_exp_month').value, 10);
            const expYear = parseInt(document.getElementById('member_exp_year').value, 10);
            const cvc = document.getElementById('member_cvc').value;
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
            window.location.href = returnUrl.toString();
            return;
        }
        if (status === 'awaiting_next_action' && nextAction) {
            if (nextAction.type === 'consume_qr' && nextAction.code?.image_url && (method === 'gcash' || method === 'paymaya')) {
                qrImg.src = nextAction.code.image_url;
                qrSection.classList.remove('d-none');
                ewalletNotice.classList.add('d-none');
                setLoading(false);
                startQrPolling();
                return;
            }
            if (nextAction.redirect?.url) {
                window.location.href = nextAction.redirect.url;
                return;
            }
        }
        if (status === 'awaiting_payment_method') {
            const err = pi.attributes?.last_payment_error?.message || 'Payment failed. Please try again.';
            throw new Error(err);
        }
        if (status === 'processing') {
            window.location.href = returnUrl.toString();
            return;
        }
        throw new Error('Unexpected status: ' + status);
    }

    payBtn.addEventListener('click', async function() {
        clearError();
        setLoading(true);
        qrSection.classList.add('d-none');
        try {
            const pmId = await createPaymentMethod();
            await attachPaymentMethod(pmId);
        } catch (e) {
            setError(e.message || 'Payment failed. Please try again.');
            setLoading(false);
        }
    });
})();
</script>
@endpush
@endif
