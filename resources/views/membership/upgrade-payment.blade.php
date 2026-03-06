@extends('layouts.toyshop')

@section('title', 'Pay for Upgrade - ToyHaven')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Complete upgrade to {{ $newPlan->name }}</h2>
    <p class="h4 text-primary mb-4">₱{{ number_format($amount, 2) }}</p>

    <div id="pay-error" class="alert alert-danger d-none"></div>
    <div id="pay-loading" class="text-center py-3 d-none">
        <div class="spinner-border text-primary"></div>
        <p class="mt-2 mb-0">Preparing payment...</p>
    </div>
    <div id="qr-section" class="d-none">
        <div class="text-center mb-3">
            <img id="qr-image" src="" alt="QR Code" style="max-width: 280px;">
        </div>
        <p class="text-center text-muted">Scan with GCash, Maya, or your bank app.</p>
        <div id="qr-polling" class="text-center text-muted small">Waiting for payment...</div>
    </div>
    <div id="pay-btn-wrap">
        <button type="button" id="pay-btn" class="btn btn-primary btn-lg">
            <i class="bi bi-qr-code-scan me-2"></i>Pay with QR Ph
        </button>
    </div>

    <p class="mt-4 mb-0">
        <a href="{{ route('membership.manage') }}">Cancel and back to membership</a>
    </p>
</div>

@push('scripts')
<script>
(function() {
    var payBtn = document.getElementById('pay-btn');
    var payLoading = document.getElementById('pay-loading');
    var qrSection = document.getElementById('qr-section');
    var qrImage = document.getElementById('qr-image');
    var payError = document.getElementById('pay-error');
    var payBtnWrap = document.getElementById('pay-btn-wrap');
    var intentId = @json($intentId);
    var processUrl = @json(route('membership.process-upgrade-payment', $subscription));
    var checkUrl = @json(route('membership.check-upgrade-payment')) + '?payment_intent_id=' + encodeURIComponent(intentId);
    var manageUrl = @json(route('membership.manage'));
    var pollTimer = null;

    function setError(msg) {
        payError.textContent = msg;
        payError.classList.remove('d-none');
    }
    function clearError() { payError.classList.add('d-none'); }

    function startPolling() {
        pollTimer = setInterval(async function() {
            try {
                var res = await fetch(checkUrl, { headers: { 'Accept': 'application/json' } });
                var data = await res.json();
                if (data.status === 'succeeded' && data.redirect_url) {
                    clearInterval(pollTimer);
                    window.location.href = data.redirect_url;
                }
            } catch (e) {}
        }, 4000);
    }

    payBtn.addEventListener('click', async function() {
        clearError();
        payBtn.disabled = true;
        payLoading.classList.remove('d-none');
        try {
            var res = await fetch(processUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json' }
            });
            var data = await res.json();
            payLoading.classList.add('d-none');
            if (data.redirect_url) {
                window.location.href = data.redirect_url;
                return;
            }
            if (data.qr_image) {
                qrImage.src = data.qr_image;
                qrSection.classList.remove('d-none');
                payBtnWrap.classList.add('d-none');
                startPolling();
                return;
            }
            if (data.error) {
                setError(data.error);
                payBtn.disabled = false;
                return;
            }
            setError('Payment could not be started. Please try again.');
            payBtn.disabled = false;
        } catch (e) {
            payLoading.classList.add('d-none');
            setError('Network error. Please try again.');
            payBtn.disabled = false;
        }
    });
})();
</script>
@endpush
@endsection
