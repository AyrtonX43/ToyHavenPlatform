@extends('layouts.toyshop')

@section('title', 'Pay for ' . $payment->auction->title . ' - ToyHaven')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auction.index') }}">Auction</a></li>
            <li class="breadcrumb-item"><a href="{{ route('auction.show', $payment->auction) }}">{{ Str::limit($payment->auction->title, 30) }}</a></li>
            <li class="breadcrumb-item active">Payment</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white py-3">
                    <h4 class="mb-0"><i class="bi bi-trophy me-2"></i>You Won: {{ $payment->auction->title }}</h4>
                </div>
                <div class="card-body p-4">
                    <p class="mb-2"><strong>Amount to pay:</strong></p>
                    <p class="fs-2 text-primary mb-3">₱{{ number_format($payment->amount, 2) }}</p>
                    <p class="mb-4 text-muted">
                        <i class="bi bi-clock me-1"></i>
                        Payment deadline: {{ $payment->payment_deadline?->format('M d, Y H:i') }}
                        @if($payment->isOverdue())
                            <span class="text-danger">(Overdue)</span>
                        @endif
                    </p>

                    @if(!empty($paypalClientId))
                        <div class="mb-4">
                            <p class="mb-2"><strong>Pay with PayPal:</strong></p>
                            <div id="paypal-button-container"></div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <strong>Payment options coming soon.</strong> For now, please contact support to complete your payment.
                        </div>
                    @endif

                    <a href="{{ route('auction.index') }}" class="btn btn-outline-secondary">Back to Auctions</a>
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
