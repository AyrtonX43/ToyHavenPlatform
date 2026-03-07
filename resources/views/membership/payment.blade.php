@extends('layouts.toyshop')

@section('title', 'Pay - ' . $subscription->plan->name)

@push('styles')
<style>
    .payment-hero {
        background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0284c7 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 20px;
        box-shadow: 0 8px 30px rgba(2, 132, 199, 0.2);
    }
    .qr-display-box {
        background: #fff;
        border: 2px solid #e2e8f0;
        border-radius: 20px;
        padding: 2rem;
        display: inline-block;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    }
    .payment-card {
        border: 2px solid #e2e8f0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    }
</style>
@endpush

@section('content')
<div class="payment-hero">
    <div class="container text-center">
        <h2 class="mb-1 fw-bold"><i class="bi bi-qr-code-scan me-2"></i>Complete Payment</h2>
        <p class="mb-0 opacity-90">{{ $subscription->plan->name }} — ₱{{ number_format($subscription->plan->price, 0) }}/mo</p>
    </div>
</div>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="payment-card card border-0 mb-4">
                <div class="card-body text-center py-5 px-4">
                    @if(!empty($qr_image))
                        <p class="text-muted mb-3">Scan this QR code with GCash, Maya, or your banking app</p>
                        <div id="qr-expiry-box" class="qr-display-box mb-4">
                            <img src="{{ $qr_image }}" alt="QR Ph" class="img-fluid" style="max-width: 260px;">
                            <div id="qr-expiry-countdown" class="mt-3 text-muted small">
                                <i class="bi bi-clock me-1"></i> QR expires in <strong id="qr-expiry-time">30:00</strong>
                            </div>
                        </div>
                        <div id="qr-expired-message" class="alert alert-warning d-none mb-4">
                            <i class="bi bi-exclamation-triangle me-2"></i> This QR code has expired. Please get a new one to continue.
                            <a href="{{ url()->current() }}" class="btn btn-sm btn-warning mt-2 d-inline-block">
                                <i class="bi bi-arrow-clockwise me-1"></i> Get New QR Code
                            </a>
                        </div>
                        <div id="qr-polling" class="d-flex align-items-center justify-content-center gap-2 mb-3">
                            <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                            <span class="text-muted">Waiting for payment...</span>
                        </div>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-shield-check me-1"></i> Secure payment via PayMongo. Receipt will be emailed once confirmed.
                        </p>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Payment session expired. Please <a href="{{ route('membership.payment-selection', $subscription->plan->slug) }}" class="alert-link fw-semibold">try again</a>.
                        </div>
                    @endif
                </div>
            </div>

            <div class="text-center">
                <button type="button" class="btn btn-link text-danger text-decoration-none p-0" data-bs-toggle="modal" data-bs-target="#cancelModal">
                    <i class="bi bi-x-circle me-1"></i> Cancel and return to membership plans
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 border-0 shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Cancel Payment?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure? You will return to the membership plans page and can select a plan again later.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Keep Payment</button>
                <a href="{{ route('membership.cancel-pending', $subscription) }}" class="btn btn-danger rounded-3">
                    <i class="bi bi-x-circle me-1"></i> Yes, Cancel
                </a>
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

    // QR Ph expires in 30 minutes - countdown
    var expiresAt = Date.now() + 30 * 60 * 1000;
    var expiryEl = document.getElementById('qr-expiry-time');
    var expiryBox = document.getElementById('qr-expiry-box');
    var expiredMsg = document.getElementById('qr-expired-message');
    var pollingEl = document.getElementById('qr-polling');
    function tick() {
        var left = Math.max(0, Math.floor((expiresAt - Date.now()) / 1000));
        var m = Math.floor(left / 60);
        var s = left % 60;
        if (expiryEl) expiryEl.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
        if (left <= 0 && expiryBox && expiredMsg) {
            expiryBox.classList.add('d-none');
            expiredMsg.classList.remove('d-none');
            if (pollingEl) pollingEl.classList.add('d-none');
        }
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
@endpush
@endif
