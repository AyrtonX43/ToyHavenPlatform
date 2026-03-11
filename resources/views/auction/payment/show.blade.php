@extends('layouts.toyshop')

@section('title', 'Pay for ' . $payment->auction->title . ' - ToyHaven')

@push('styles')
<link href="{{ asset('css/auction.css') }}" rel="stylesheet">
<style>
    .payment-method-option { cursor: pointer; border: 2px solid #e2e8f0; border-radius: .75rem; padding: 1rem; transition: all .2s; }
    .payment-method-option:hover { border-color: #94a3b8; }
    .payment-method-option.active { border-color: #0284c7; background: #f0f9ff; }
    .payment-method-option input[type="radio"] { display: none; }
    .deadline-countdown { font-size: .85rem; font-weight: 600; }
    .deadline-urgent { color: #ef4444; }
</style>
@endpush

@section('content')
<div class="container py-4" x-data="auctionPayment()">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('auction.index') }}">Auction</a></li>
            <li class="breadcrumb-item"><a href="{{ route('auction.show', $payment->auction) }}">{{ Str::limit($payment->auction->title, 30) }}</a></li>
            <li class="breadcrumb-item active">Payment</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="auction-payment-card">
                <div class="auction-payment-header">
                    <h4 class="mb-0 fw-bold"><i class="bi bi-trophy-fill me-2"></i>You Won: {{ Str::limit($payment->auction->title, 60) }}</h4>
                </div>
                <div class="card-body p-4 p-lg-5">

                    {{-- Order Summary --}}
                    <div class="row mb-4">
                        <div class="col-sm-4">
                            @php $img = $payment->auction->images->firstWhere('is_primary', true) ?? $payment->auction->images->first(); @endphp
                            @if($img)
                                <img src="{{ asset('storage/' . $img->image_path) }}" alt="{{ $payment->auction->title }}" class="img-fluid rounded-3" style="max-height:160px;object-fit:contain;">
                            @endif
                        </div>
                        <div class="col-sm-8">
                            <h5 class="fw-semibold">{{ $payment->auction->title }}</h5>
                            <table class="table table-sm table-borderless mb-0 mt-2">
                                <tr>
                                    <td class="text-muted ps-0">Winning bid</td>
                                    <td class="text-end fw-semibold pe-0">₱{{ number_format($payment->amount, 2) }}</td>
                                </tr>
                                <tr class="border-top">
                                    <td class="text-muted ps-0 fw-bold">Total due</td>
                                    <td class="text-end pe-0"><span class="fs-4 fw-bold" style="color:#0d9488;">₱{{ number_format($payment->amount, 2) }}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Deadline --}}
                    <div class="d-flex align-items-center gap-2 mb-4 p-3 rounded-3 {{ $payment->isOverdue() ? 'bg-danger bg-opacity-10' : 'bg-light' }}">
                        <i class="bi bi-clock fs-5 {{ $payment->isOverdue() ? 'text-danger' : 'text-primary' }}"></i>
                        <div>
                            <span class="small text-muted">Payment deadline</span>
                            <p class="mb-0 fw-semibold {{ $payment->isOverdue() ? 'text-danger' : '' }}">
                                {{ $payment->payment_deadline?->format('M d, Y g:i A') }}
                                @if($payment->isOverdue())
                                    <span class="badge bg-danger ms-1">Overdue</span>
                                @endif
                            </p>
                        </div>
                        @if(!$payment->isOverdue() && $payment->payment_deadline)
                            <span class="ms-auto deadline-countdown" x-data="deadlineTimer('{{ $payment->payment_deadline->toIso8601String() }}')" x-text="text" :class="{ 'deadline-urgent': urgent }"></span>
                        @endif
                    </div>

                    {{-- Escrow Notice --}}
                    <div class="p-3 rounded-3 mb-4" style="background:#eff6ff;border:1px solid #bfdbfe;">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-shield-check text-primary fs-5 flex-shrink-0 mt-1"></i>
                            <div>
                                <p class="mb-1 fw-semibold small text-primary">Buyer Protection - Escrow Payment</p>
                                <p class="mb-0 text-muted small">Your payment is held securely by ToyHaven until you confirm receipt of the item. The seller will ship the item, and once you confirm delivery, the funds are released to the seller after a 3-day holding period.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Method Selection --}}
                    <h6 class="fw-semibold mb-3">Choose payment method</h6>
                    <div class="row g-3 mb-4">
                        @if(!empty($paypalClientId))
                            <div class="col-sm-6">
                                <label class="payment-method-option d-flex align-items-center gap-3 w-100 mb-0" :class="{ 'active': method === 'paypal' }" @click="method = 'paypal'">
                                    <input type="radio" name="payment_method" value="paypal" x-model="method">
                                    <div>
                                        <i class="bi bi-paypal fs-4 text-primary"></i>
                                        <span class="fw-semibold d-block">PayPal</span>
                                        <span class="text-muted small">Pay with PayPal account</span>
                                    </div>
                                </label>
                            </div>
                        @endif
                        @if($paymongoEnabled ?? false)
                            <div class="col-sm-6">
                                <label class="payment-method-option d-flex align-items-center gap-3 w-100 mb-0" :class="{ 'active': method === 'qrph' }" @click="method = 'qrph'">
                                    <input type="radio" name="payment_method" value="qrph" x-model="method">
                                    <div>
                                        <i class="bi bi-qr-code fs-4 text-success"></i>
                                        <span class="fw-semibold d-block">GCash / Maya</span>
                                        <span class="text-muted small">Scan QR code to pay</span>
                                    </div>
                                </label>
                            </div>
                            <div class="col-sm-6">
                                <label class="payment-method-option d-flex align-items-center gap-3 w-100 mb-0" :class="{ 'active': method === 'card' }" @click="method = 'card'">
                                    <input type="radio" name="payment_method" value="card" x-model="method">
                                    <div>
                                        <i class="bi bi-credit-card fs-4 text-warning"></i>
                                        <span class="fw-semibold d-block">Credit / Debit Card</span>
                                        <span class="text-muted small">Visa, Mastercard via PayMongo</span>
                                    </div>
                                </label>
                            </div>
                        @endif
                    </div>

                    {{-- Error / Success --}}
                    <div x-show="errorMsg" x-transition class="alert alert-danger py-2 mb-3" x-cloak>
                        <span x-text="errorMsg"></span>
                    </div>
                    <div x-show="successMsg" x-transition class="alert alert-success py-2 mb-3" x-cloak>
                        <span x-text="successMsg"></span>
                    </div>

                    {{-- PayPal Button --}}
                    <div x-show="method === 'paypal'" x-cloak>
                        <div id="paypal-button-container" class="mb-3"></div>
                    </div>

                    {{-- PayMongo QR/Card --}}
                    <div x-show="method === 'qrph' || method === 'card'" x-cloak>
                        <button @click="startPayMongo()" :disabled="paymongoLoading" class="btn btn-primary btn-lg w-100 rounded-pill">
                            <span x-show="!paymongoLoading"><i class="bi bi-shield-lock me-1"></i>Pay ₱{{ number_format($payment->amount, 2) }}</span>
                            <span x-show="paymongoLoading" x-cloak><span class="spinner-border spinner-border-sm me-1"></span> Processing...</span>
                        </button>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('auction.index') }}" class="btn btn-outline-secondary rounded-pill">Back to Auctions</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(!empty($paypalClientId))
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&components=buttons&currency=PHP&intent=capture&disable-funding=card,credit"></script>
@endif

<script>
function deadlineTimer(endIso) {
    return {
        text: '',
        urgent: false,
        interval: null,
        init() { this.tick(); this.interval = setInterval(() => this.tick(), 1000); },
        tick() {
            const diff = new Date(endIso).getTime() - Date.now();
            if (diff <= 0) { this.text = 'Expired'; this.urgent = true; clearInterval(this.interval); return; }
            this.urgent = diff <= 3600000;
            const h = Math.floor(diff / 3600000);
            const m = Math.floor((diff % 3600000) / 60000);
            const s = Math.floor((diff % 60000) / 1000);
            this.text = h + 'h ' + m + 'm ' + s + 's left';
        },
        destroy() { if (this.interval) clearInterval(this.interval); }
    };
}

function auctionPayment() {
    return {
        method: @json(!empty($paypalClientId) ? 'paypal' : ($paymongoEnabled ? 'qrph' : '')),
        errorMsg: '',
        successMsg: '',
        paymongoLoading: false,
        paypalRendered: false,

        init() {
            this.$watch('method', (val) => {
                if (val === 'paypal' && !this.paypalRendered) {
                    this.$nextTick(() => this.renderPayPal());
                }
            });
            if (this.method === 'paypal') {
                this.$nextTick(() => this.renderPayPal());
            }
        },

        renderPayPal() {
            if (this.paypalRendered || typeof paypal === 'undefined') return;
            this.paypalRendered = true;

            const paymentId = {{ $payment->id }};
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const self = this;

            paypal.Buttons({
                createOrder() {
                    return fetch(@json(route('auction.payment.paypal.create-order')), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify({ payment_id: paymentId }),
                    }).then(r => r.json()).then(data => {
                        if (data.orderId) return data.orderId;
                        throw new Error(data.error || 'Could not create order');
                    });
                },
                onApprove(data) {
                    return fetch(@json(route('auction.payment.paypal.capture-order')), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify({ order_id: data.orderID }),
                    }).then(r => r.json()).then(res => {
                        if (res.success && res.redirect) { window.location.href = res.redirect; }
                        else { self.errorMsg = res.error || 'Payment failed.'; }
                    }).catch(() => { self.errorMsg = 'Payment failed. Please try again.'; });
                },
                onError() { self.errorMsg = 'PayPal encountered an error.'; },
            }).render('#paypal-button-container');
        },

        async startPayMongo() {
            this.paymongoLoading = true;
            this.errorMsg = '';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            try {
                const intentRes = await fetch(@json(route('auction.payment.paymongo.create-intent')), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ payment_id: {{ $payment->id }}, payment_type: this.method }),
                });
                const intentData = await intentRes.json();
                if (!intentRes.ok) { this.errorMsg = intentData.error || 'Failed to create payment.'; return; }

                const pmRes = await this.createPayMongoMethod();
                if (!pmRes) return;

                const processRes = await fetch(@json(route('auction.payment.paymongo.process')), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({
                        payment_id: {{ $payment->id }},
                        payment_intent_id: intentData.payment_intent_id,
                        payment_method_id: pmRes,
                    }),
                });
                const processData = await processRes.json();

                if (processData.success && processData.redirect) {
                    window.location.href = processData.redirect;
                } else if (processData.requires_action && processData.redirect_url) {
                    window.location.href = processData.redirect_url;
                } else {
                    this.errorMsg = processData.error || 'Payment failed.';
                }
            } catch (err) {
                this.errorMsg = 'Network error. Please try again.';
            } finally {
                this.paymongoLoading = false;
            }
        },

        async createPayMongoMethod() {
            const publicKey = @json($paymongoPublicKey ?? '');
            const user = @json(['name' => $payment->winner->name ?? '', 'email' => $payment->winner->email ?? '']);

            try {
                const res = await fetch('https://api.paymongo.com/v1/payment_methods', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Basic ' + btoa(publicKey + ':'),
                    },
                    body: JSON.stringify({
                        data: {
                            attributes: {
                                type: this.method === 'card' ? 'card' : 'qrph',
                                billing: { name: user.name, email: user.email },
                            },
                        },
                    }),
                });
                const data = await res.json();
                if (data.data?.id) return data.data.id;
                this.errorMsg = data.errors?.[0]?.detail || 'Failed to create payment method.';
                return null;
            } catch (err) {
                this.errorMsg = 'Failed to initialize payment.';
                return null;
            }
        },
    };
}
</script>
@endpush
