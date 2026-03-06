@extends('layouts.toyshop')

@section('title', 'Pay for Won Auction - ToyHaven')

@section('content')
<div class="container py-4">
    <h2>Complete Payment</h2>
    <p>{{ $auction->title }}</p>
    <p class="h4 text-primary">₱{{ number_format($payment->total_amount, 0) }}</p>

    @if($payment->payment_status === 'paid')
        <div class="alert alert-success">Payment completed. <a href="{{ route('auctions.payment.success', $payment) }}">View receipt</a></div>
    @elseif(!$payment->isPastDeadline())
        <form id="pay-form">
            @csrf
            <div class="mb-3">
                <label class="form-label">Payment Method</label>
                <div class="form-check">
                    <input class="form-check-input pm-radio" type="radio" name="payment_type" value="qrph" id="pm_qrph" checked>
                    <label class="form-check-label" for="pm_qrph">QR Ph</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input pm-radio" type="radio" name="payment_type" value="paypal_demo" id="pm_paypal">
                    <label class="form-check-label" for="pm_paypal">PayPal (Demo)</label>
                    <small class="d-block text-muted">Simulated checkout – for testing only</small>
                </div>
            </div>

            <div id="paypal-demo-form" class="card mb-3 d-none">
                <div class="card-header bg-light">
                    <strong>PayPal Demo Checkout</strong>
                    <span class="badge bg-warning text-dark ms-2">Demo Only</span>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Fill out the details below to simulate a PayPal payment. No real payment will be processed.</p>
                    <div class="mb-2">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="paypal_demo_name" class="form-control" placeholder="John Doe" value="{{ auth()->user()?->name }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="paypal_demo_email" class="form-control" placeholder="john@example.com" value="{{ auth()->user()?->email }}" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">PayPal Email <span class="text-danger">*</span></label>
                        <input type="email" name="paypal_demo_payer_email" class="form-control" placeholder="paypal@example.com" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" id="pay-btn">Pay Now</button>
        </form>
        <div id="qr-display" class="d-none mt-3"></div>
    @else
        <div class="alert alert-danger">Payment deadline has passed.</div>
    @endif
</div>

@push('scripts')
@if($payment->payment_status === 'pending' && !$payment->isPastDeadline())
<script>
(function() {
    var pmQrph = document.getElementById('pm_qrph');
    var pmPaypal = document.getElementById('pm_paypal');
    var paypalForm = document.getElementById('paypal-demo-form');
    var payForm = document.getElementById('pay-form');

    function togglePaypalForm() {
        if (pmPaypal.checked) {
            paypalForm.classList.remove('d-none');
            paypalForm.querySelectorAll('input').forEach(function(i) { i.required = true; });
        } else {
            paypalForm.classList.add('d-none');
            paypalForm.querySelectorAll('input').forEach(function(i) { i.required = false; });
        }
    }
    pmQrph.addEventListener('change', togglePaypalForm);
    pmPaypal.addEventListener('change', togglePaypalForm);
    togglePaypalForm();

    payForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('payment_type', document.querySelector('input[name="payment_type"]:checked').value);
        document.getElementById('pay-btn').disabled = true;
        var res = await fetch('{{ route("auctions.payment.process", $payment) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: formData
        });
        var data = await res.json();
        document.getElementById('pay-btn').disabled = false;
        if (data.redirect_url) { window.location.href = data.redirect_url; return; }
        if (data.qr_image) {
            document.getElementById('qr-display').classList.remove('d-none');
            document.getElementById('qr-display').innerHTML = '<img src="' + data.qr_image + '" alt="QR" style="max-width: 280px;">';
        }
        if (data.error) alert(data.error);
    });
})();
</script>
@endif
@endpush
@endsection
