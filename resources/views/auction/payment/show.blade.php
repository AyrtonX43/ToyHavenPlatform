@extends('layouts.toyshop')

@section('title', 'Pay for ' . $payment->auction->title . ' - ToyHaven')

@push('styles')
<link href="{{ asset('css/auction.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('auction.index') }}">Auction</a></li>
            <li class="breadcrumb-item"><a href="{{ route('auction.show', $payment->auction) }}">{{ Str::limit($payment->auction->title, 30) }}</a></li>
            <li class="breadcrumb-item active">Payment</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="auction-payment-card">
                <div class="auction-payment-header">
                    <h4 class="mb-0 fw-bold"><i class="bi bi-trophy-fill me-2"></i>You Won: {{ Str::limit($payment->auction->title, 60) }}</h4>
                </div>
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                        <div>
                            <p class="text-muted small mb-1">Amount to pay</p>
                            <p class="fs-2 fw-bold mb-0" style="color:#0d9488;">₱{{ number_format($payment->amount, 2) }}</p>
                        </div>
                        <div class="text-end">
                            <p class="text-muted small mb-1"><i class="bi bi-clock me-1"></i>Payment deadline</p>
                            <p class="mb-0 fw-semibold {{ $payment->isOverdue() ? 'text-danger' : '' }}">
                                {{ $payment->payment_deadline?->format('M d, Y H:i') }}
                                @if($payment->isOverdue())
                                    <span class="badge bg-danger ms-1">Overdue</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if(!empty($paypalClientId))
                        <div class="mb-4 py-3 px-3 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0;">
                            <p class="mb-3 fw-semibold">Pay securely with PayPal</p>
                            <div id="paypal-button-container"></div>
                        </div>
                    @else
                        <div class="alert alert-info border-0 rounded-3">
                            <strong>Payment options coming soon.</strong> For now, please contact support to complete your payment.
                        </div>
                    @endif

                    <a href="{{ route('auction.index') }}" class="btn btn-outline-secondary rounded-pill">Back to Auctions</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@if(!empty($paypalClientId))
@push('scripts')
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&components=buttons&currency=PHP&intent=capture&disable-funding=card,credit"></script>
<script>
(function() {
    var paymentId = {{ $payment->id }};
    var createOrderUrl = @json(route('auction.payment.paypal.create-order'));
    var captureOrderUrl = @json(route('auction.payment.paypal.capture-order'));
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || @json(csrf_token());
    var payUrl = @json(route('auction.payment.show', $payment));

    paypal.Buttons({
        createOrder: function() {
            return fetch(createOrderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    payment_id: paymentId,
                    _token: csrfToken
                })
            }).then(function(r) { return r.json(); }).then(function(data) {
                if (data.orderId) return data.orderId;
                throw new Error(data.error || 'Could not create order');
            });
        },
        onApprove: function(data) {
            return fetch(captureOrderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    order_id: data.orderID,
                    _token: csrfToken
                })
            }).then(function(r) { return r.json(); }).then(function(res) {
                if (res.success && res.redirect) {
                    window.location.href = res.redirect;
                } else {
                    alert(res.error || 'Payment failed. Please try again.');
                    window.location.href = payUrl + '?payment_failed=1';
                }
            }).catch(function() {
                alert('Payment failed. Please try again.');
                window.location.href = payUrl + '?payment_failed=1';
            });
        },
        onCancel: function() {},
        onError: function() {
            alert('Payment failed. Please try again.');
            window.location.href = payUrl + '?payment_failed=1';
        }
    }).render('#paypal-button-container');
})();
</script>
@endpush
@endif
