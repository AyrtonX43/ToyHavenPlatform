@extends('layouts.toyshop')

@section('title', 'Pay - ' . $subscription->plan->name)

@section('content')
<div class="container py-5">
    <h2 class="mb-4">Complete Payment</h2>
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-success text-white py-3">
                    <h5 class="mb-0"><i class="bi bi-qr-code-scan me-2"></i>{{ $subscription->plan->name }} - ₱{{ number_format($subscription->plan->price, 0) }}/mo</h5>
                </div>
                <div class="card-body text-center">
                    @if(!empty($qr_image))
                        <p class="mb-3">Scan this QR code with GCash, Maya, or your banking app</p>
                        <div class="p-4 bg-white rounded-3 border d-inline-block mb-4">
                            <img src="{{ $qr_image }}" alt="QR Ph" class="img-fluid" style="max-width: 280px;">
                        </div>
                        <div id="qr-polling" class="mb-3">
                            <div class="spinner-border spinner-border-sm text-success me-2" role="status"></div>
                            <span class="text-muted">Waiting for payment...</span>
                        </div>
                        <p class="text-muted small mb-0">Payment is secure via PayMongo. Your receipt will be emailed once confirmed.</p>
                    @else
                        <div class="alert alert-warning mb-0">Payment session expired. Please <a href="{{ route('membership.payment-selection', $subscription->plan->slug) }}">try again</a>.</div>
                    @endif
                </div>
            </div>
            <div class="text-center">
                <button type="button" class="btn btn-link text-danger p-0" data-bs-toggle="modal" data-bs-target="#cancelModal">
                    <i class="bi bi-x-circle me-1"></i>Cancel and return to membership plans
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to cancel? You will return to the membership plans page.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Payment</button>
                <a href="{{ route('membership.cancel-pending', $subscription) }}" class="btn btn-danger">Yes, Cancel</a>
            </div>
        </div>
    </div>
</div>
@endsection

@if(!empty($qr_image) && !empty($payment_intent_id))
@push('scripts')
<script>
(function() {
    var checkUrl = @json(route('membership.check-payment', $subscription));
    var paymentIntentId = @json($payment_intent_id);
    var polling = setInterval(function() {
        fetch(checkUrl + '?payment_intent_id=' + encodeURIComponent(paymentIntentId), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(function(data) {
                if (data.paid && data.redirect) {
                    clearInterval(polling);
                    window.location.href = data.redirect;
                }
            });
    }, 4000);
})();
</script>
@endpush
@endif
