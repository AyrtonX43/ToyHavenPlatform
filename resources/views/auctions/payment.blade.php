@extends('layouts.toyshop')

@section('title', 'Auction Payment - ' . $auction->title)

@push('styles')
<style>
    .payment-card { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 2px solid #e2e8f0; }
    .payment-amount { font-size: 2.5rem; font-weight: 800; color: #0891b2; }
    .deadline-badge { background: #fef2f2; color: #dc2626; padding: 0.5rem 1rem; border-radius: 10px; font-weight: 600; }
    .escrow-step { padding: 1rem; border-radius: 12px; background: #f8fafc; }
    .escrow-step.active { background: #ecfdf5; border: 1px solid #10b981; }
    .escrow-step.pending { opacity: 0.6; }
</style>
@endpush

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auctions.index') }}">Auctions</a></li>
            <li class="breadcrumb-item"><a href="{{ route('auctions.show', $auction) }}">{{ Str::limit($auction->title, 30) }}</a></li>
            <li class="breadcrumb-item active">Payment</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="payment-card p-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    @if($imgUrl = $auction->getPrimaryImageUrl())
                        <img src="{{ $imgUrl }}" alt="" class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                    @endif
                    <div>
                        <h4 class="fw-bold mb-1">{{ $auction->title }}</h4>
                        <span class="text-muted">Won auction</span>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="text-muted small">Winning Bid</div>
                        <div class="fw-bold fs-5">₱{{ number_format($payment->bid_amount, 2) }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Buyer's Premium</div>
                        <div class="fw-bold fs-5">₱{{ number_format($payment->buyer_premium, 2) }}</div>
                    </div>
                </div>

                <div class="bg-light rounded p-3 mb-4 text-center">
                    <div class="text-muted small">Total Amount Due</div>
                    <div class="payment-amount">₱{{ number_format($payment->total_amount, 2) }}</div>
                </div>

                @if($payment->payment_status === 'pending')
                    @if($payment->payment_deadline)
                        <div class="deadline-badge text-center mb-4">
                            <i class="bi bi-clock me-1"></i>
                            Pay before: {{ $payment->payment_deadline->format('M d, Y h:i A') }}
                            ({{ $payment->payment_deadline->diffForHumans() }})
                        </div>
                    @endif
                @endif

                {{-- Escrow Status Tracker --}}
                <h5 class="fw-bold mb-3">Order Progress</h5>
                <div class="d-flex flex-column gap-2">
                    <div class="escrow-step {{ $payment->payment_status === 'paid' ? 'active' : ($payment->payment_status === 'pending' ? 'active' : 'pending') }}">
                        <i class="bi bi-{{ $payment->payment_status === 'paid' ? 'check-circle-fill text-success' : 'circle' }} me-2"></i>
                        <strong>Payment</strong> - {{ $payment->payment_status === 'paid' ? 'Completed ' . $payment->paid_at?->format('M d') : 'Awaiting payment' }}
                    </div>
                    <div class="escrow-step {{ in_array($payment->delivery_status, ['shipped', 'delivered', 'confirmed']) ? 'active' : 'pending' }}">
                        <i class="bi bi-{{ in_array($payment->delivery_status, ['shipped', 'delivered', 'confirmed']) ? 'check-circle-fill text-success' : 'circle' }} me-2"></i>
                        <strong>Shipped</strong> - {{ $payment->shipped_at ? 'Shipped ' . $payment->shipped_at->format('M d') : 'Waiting for seller to ship' }}
                        @if($payment->tracking_number)
                            <br><small class="text-muted">Tracking: {{ $payment->tracking_number }}</small>
                        @endif
                    </div>
                    <div class="escrow-step {{ $payment->delivery_status === 'confirmed' ? 'active' : 'pending' }}">
                        <i class="bi bi-{{ $payment->delivery_status === 'confirmed' ? 'check-circle-fill text-success' : 'circle' }} me-2"></i>
                        <strong>Received</strong> - {{ $payment->confirmed_at ? 'Confirmed ' . $payment->confirmed_at->format('M d') : 'Confirm when you receive the item' }}
                    </div>
                    <div class="escrow-step {{ $payment->escrow_status === 'released' ? 'active' : 'pending' }}">
                        <i class="bi bi-{{ $payment->escrow_status === 'released' ? 'check-circle-fill text-success' : 'circle' }} me-2"></i>
                        <strong>Escrow Released</strong> - {{ $payment->released_at ? 'Released ' . $payment->released_at->format('M d') : 'Payment held in escrow until confirmed' }}
                    </div>
                </div>

                @if($payment->payment_status === 'paid' && $payment->escrow_status === 'held' && $payment->delivery_status !== 'confirmed')
                    <div class="mt-4 text-center">
                        <form action="{{ route('auctions.payment.confirm-received', $payment) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Confirm that you have received the item? This will release the payment to the seller.')">
                                <i class="bi bi-box-seam me-1"></i>I Received My Item
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-5">
            @if($payment->payment_status === 'pending' && !$payment->isPastDeadline())
                <div class="payment-card p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-credit-card me-1"></i>Complete Payment</h5>

                    <div class="alert alert-warning small">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        You must pay within 24 hours of winning. Failure to pay will result in a ban.
                    </div>

                    <form id="paymentForm">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Payment Method</label>
                            <select id="paymentType" class="form-select">
                                <option value="card">Credit/Debit Card</option>
                                <option value="qrph">QR Ph (GCash, Maya, etc.)</option>
                            </select>
                        </div>

                        <div id="cardFields">
                            <div class="mb-3">
                                <label class="form-label">Card Number</label>
                                <input type="text" id="cardNumber" class="form-control" placeholder="4343 4343 4343 4343">
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-4">
                                    <label class="form-label">Month</label>
                                    <input type="number" id="expMonth" class="form-control" placeholder="MM" min="1" max="12">
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Year</label>
                                    <input type="number" id="expYear" class="form-control" placeholder="YYYY">
                                </div>
                                <div class="col-4">
                                    <label class="form-label">CVC</label>
                                    <input type="text" id="cvc" class="form-control" placeholder="123">
                                </div>
                            </div>
                        </div>

                        <div id="qrContainer" style="display:none;" class="text-center mb-3">
                            <p class="text-muted">Click Pay to generate your QR code</p>
                        </div>

                        <button type="submit" id="payBtn" class="btn btn-primary btn-lg w-100" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                            <i class="bi bi-lock me-1"></i>Pay ₱{{ number_format($payment->total_amount, 2) }}
                        </button>

                        <div id="paymentError" class="alert alert-danger mt-3" style="display:none;"></div>
                    </form>
                </div>
            @elseif($payment->payment_status === 'paid')
                <div class="payment-card p-4 text-center">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    <h4 class="fw-bold mt-3">Payment Complete</h4>
                    <p class="text-muted">Your payment is held in escrow. The seller will ship your item soon.</p>
                </div>
            @else
                <div class="payment-card p-4 text-center">
                    <i class="bi bi-x-circle text-danger" style="font-size: 4rem;"></i>
                    <h4 class="fw-bold mt-3">Payment Deadline Passed</h4>
                    <p class="text-muted">The payment window has expired.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@if($payment->payment_status === 'pending')
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('paymentForm');
    const payBtn = document.getElementById('payBtn');
    const errorDiv = document.getElementById('paymentError');
    const typeSelect = document.getElementById('paymentType');
    const cardFields = document.getElementById('cardFields');
    const qrContainer = document.getElementById('qrContainer');

    typeSelect.addEventListener('change', function() {
        cardFields.style.display = this.value === 'card' ? '' : 'none';
        qrContainer.style.display = this.value === 'qrph' ? '' : 'none';
    });

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        payBtn.disabled = true;
        payBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';
        errorDiv.style.display = 'none';

        const body = { payment_type: typeSelect.value };
        if (typeSelect.value === 'card') {
            body.card_number = document.getElementById('cardNumber').value.replace(/\s/g, '');
            body.exp_month = parseInt(document.getElementById('expMonth').value);
            body.exp_year = parseInt(document.getElementById('expYear').value);
            body.cvc = document.getElementById('cvc').value;
        }

        try {
            const res = await fetch('{{ route("auctions.payment.process", $payment) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(body),
            });

            const data = await res.json();

            if (!res.ok) {
                errorDiv.textContent = data.error || 'Payment failed.';
                errorDiv.style.display = '';
                payBtn.disabled = false;
                payBtn.innerHTML = '<i class="bi bi-lock me-1"></i>Pay ₱{{ number_format($payment->total_amount, 2) }}';
                return;
            }

            if (data.status === 'succeeded' && data.redirect_url) {
                window.location.href = data.redirect_url;
            } else if (data.status === 'awaiting_next_action') {
                if (data.qr_image) {
                    qrContainer.innerHTML = '<img src="' + data.qr_image + '" class="img-fluid" style="max-width: 250px;"><p class="mt-2 text-muted small">Scan with GCash, Maya, or any QR Ph app</p>';
                    qrContainer.style.display = '';
                    cardFields.style.display = 'none';
                    payBtn.textContent = 'Waiting for payment...';
                    pollPayment();
                } else if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                }
            }
        } catch (err) {
            errorDiv.textContent = 'Network error. Please try again.';
            errorDiv.style.display = '';
            payBtn.disabled = false;
            payBtn.innerHTML = '<i class="bi bi-lock me-1"></i>Pay ₱{{ number_format($payment->total_amount, 2) }}';
        }
    });

    function pollPayment() {
        const interval = setInterval(async function() {
            try {
                const res = await fetch('{{ route("auctions.payment.check", $payment) }}', {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (data.status === 'succeeded') {
                    clearInterval(interval);
                    window.location.reload();
                }
            } catch (e) {}
        }, 5000);
    }
});
</script>
@endpush
@endif
@endsection
