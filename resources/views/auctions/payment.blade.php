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
                    <input class="form-check-input" type="radio" name="payment_type" value="qrph" id="pm_qrph" checked>
                    <label class="form-check-label" for="pm_qrph">QR Ph</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment_type" value="paypal_demo" id="pm_paypal">
                    <label class="form-check-label" for="pm_paypal">PayPal (Demo)</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Pay Now</button>
        </form>
        <div id="qr-display" class="d-none mt-3"></div>
    @else
        <div class="alert alert-danger">Payment deadline has passed.</div>
    @endif
</div>

@push('scripts')
@if($payment->payment_status === 'pending' && !$payment->isPastDeadline())
<script>
document.getElementById('pay-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    formData.append('payment_type', document.querySelector('input[name="payment_type"]:checked').value);
    var res = await fetch('{{ route("auctions.payment.process", $payment) }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: formData
    });
    var data = await res.json();
    if (data.redirect_url) window.location.href = data.redirect_url;
    if (data.qr_image) {
        document.getElementById('qr-display').classList.remove('d-none');
        document.getElementById('qr-display').innerHTML = '<img src="' + data.qr_image + '" alt="QR" style="max-width: 280px;">';
    }
    if (data.error) alert(data.error);
});
</script>
@endif
@endpush
@endsection
