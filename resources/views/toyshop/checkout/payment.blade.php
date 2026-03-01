@extends('layouts.toyshop')

@section('title', 'Payment - ToyHaven')

@push('styles')
<style>
    .payment-order-card {
        background: #fff; border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 2px solid #e2e8f0; overflow: hidden;
    }
    .payment-order-header {
        background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
        color: white; padding: 1.5rem 2rem;
    }
    .payment-order-header h2 { margin: 0; font-weight: 700; font-size: 1.5rem; }
    .payment-order-header .order-badge {
        display: inline-block; background: rgba(255,255,255,0.25);
        padding: 0.25rem 0.75rem; border-radius: 20px;
        font-size: 0.85rem; font-weight: 600; margin-top: 0.5rem;
    }
    .order-details-section { padding: 1.5rem 2rem; }
    .order-details-section h4 {
        font-size: 1rem; font-weight: 700; color: #1e293b;
        margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;
    }
    .order-summary-box { background: #f8fafc; border-radius: 12px; padding: 1.25rem 1.5rem; }
    .order-summary-box .price-row { display: flex; justify-content: space-between; align-items: center; font-size: 1.1rem; }
    .order-summary-box .price-row .total { font-weight: 800; color: #0891b2; font-size: 1.35rem; }
    .payment-actions { padding: 1.5rem 2rem; background: #f8fafc; border-top: 1px solid #e2e8f0; }
    .payment-method-option {
        border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 1rem 1.25rem;
        margin-bottom: 0.75rem; cursor: pointer; transition: all 0.2s;
        display: flex; align-items: center;
    }
    .payment-method-option:hover { border-color: #06b6d4; background: #f0fdfa; }
    .payment-method-option.selected { border-color: #0891b2; background: #ecfeff; box-shadow: 0 0 0 3px rgba(8,145,178,0.1); }
    .payment-method-option input[type="radio"] { display: none; }
    .payment-method-option .pm-radio {
        width: 20px; height: 20px; border: 2px solid #cbd5e1; border-radius: 50%;
        flex-shrink: 0; margin-right: 1rem;
        display: flex; align-items: center; justify-content: center; transition: all 0.2s;
    }
    .payment-method-option.selected .pm-radio { border-color: #0891b2; }
    .payment-method-option.selected .pm-radio::after {
        content: ''; width: 10px; height: 10px; background: #0891b2; border-radius: 50%;
    }
    .secure-badge {
        display: inline-flex; align-items: center; gap: 0.4rem;
        font-size: 0.8rem; color: #64748b; background: #f1f5f9;
        padding: 0.35rem 0.75rem; border-radius: 20px; margin-top: 0.75rem;
    }
    .secure-badge i { color: #10b981; }
</style>
@endpush

@section('content')
<div class="container py-4">
    @if(config('services.paymongo.mode') === 'test')
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>TEST MODE:</strong> No real payments will be processed. Use test payment methods only.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="payment-order-card mb-4">
                <div class="payment-order-header">
                    <h2><i class="bi bi-credit-card-2-front me-2"></i>Complete Payment</h2>
                    <span class="order-badge">Order #{{ $order->order_number }}</span>
                </div>

                <div class="order-details-section">
                    <h4><i class="bi bi-receipt text-primary"></i> Order Summary</h4>
                    <div class="order-summary-box">
                        <div class="price-row mb-1">
                            <span>Order Total</span>
                            <span class="total">₱{{ number_format($order->total, 2) }}</span>
                        </div>
                        <p class="mb-0 small text-muted mt-2">
                            Secure payment powered by PayMongo. Your card details are never stored on our servers.
                        </p>
                    </div>
                </div>

                <div class="payment-actions">
                    @if(($publicKey ?? false))
                        <div class="mb-4">
                            <label class="form-label fw-semibold mb-3">Select Payment Method</label>

                            <div class="payment-method-option selected" data-method="qrph">
                                <input type="radio" name="pay_method" value="qrph" id="pm_qrph" checked>
                                <span class="pm-radio"></span>
                                <i class="bi bi-qr-code-scan fs-4 me-3 text-success"></i>
                                <div>
                                    <strong>QR Ph</strong>
                                    <small class="d-block text-muted">Scan with GCash, Maya, banks &amp; e-wallets</small>
                                </div>
                            </div>

                            <div class="payment-method-option" data-method="card">
                                <input type="radio" name="pay_method" value="card" id="pm_card">
                                <span class="pm-radio"></span>
                                <i class="bi bi-credit-card-2-front fs-4 me-3 text-primary"></i>
                                <div>
                                    <strong>Credit / Debit Card</strong>
                                    <small class="d-block text-muted">Visa, Mastercard</small>
                                </div>
                            </div>
                        </div>

                        <div id="card-form" class="mb-4 d-none">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Card Number</label>
                                    <input type="text" id="card_number" class="form-control" placeholder="1234 5678 9012 3456" maxlength="23" autocomplete="cc-number" inputmode="numeric">
                                </div>
                                <div class="col-5">
                                    <label class="form-label">Expiry Date (MM/YY)</label>
                                    <input type="text" id="card_expiry" class="form-control" placeholder="MM/YY" maxlength="5" autocomplete="cc-exp" inputmode="numeric" style="max-width: 120px;">
                                </div>
                                <div class="col-3">
                                    <label class="form-label">CVC</label>
                                    <input type="password" id="cvc" class="form-control" placeholder="•••" maxlength="4" autocomplete="cc-csc" inputmode="numeric">
                                </div>
                            </div>
                        </div>

                        <div id="qrph-notice" class="alert alert-light border mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            A QR code will be generated. Scan it with your GCash, Maya, or banking app to complete payment.
                        </div>

                        <div id="qr-display" class="d-none text-center mb-4">
                            <div class="p-4 bg-white rounded-3 border d-inline-block">
                                <img id="qr-image" src="" alt="QR Ph Code" style="max-width: 280px; width: 100%;">
                            </div>
                            <p class="mt-3 mb-1 fw-semibold">Scan this QR code to pay</p>
                            <p class="text-muted small mb-2">Open your GCash, Maya, or banking app and scan the code above.</p>
                            <div id="qr-timer" class="text-muted small mb-2"><i class="bi bi-clock me-1"></i>QR code expires in <span id="qr-countdown">30:00</span></div>
                            <div id="qr-polling" class="text-center mb-3">
                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                <span class="text-muted small">Waiting for payment...</span>
                            </div>
                            <button type="button" id="change-method-btn" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i>Change Payment Method
                            </button>
                        </div>

                        <div id="pay-error" class="alert alert-danger d-none"></div>
                        <div id="pay-loading" class="d-none text-center py-3">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 mb-0">Processing your payment securely...</p>
                        </div>

                        <button type="button" id="pay-btn" class="btn btn-primary btn-lg w-100" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                            <i class="bi bi-lock-fill me-2"></i>Pay ₱{{ number_format($order->total, 2) }}
                        </button>

                        <div class="text-center">
                            <span class="secure-badge"><i class="bi bi-shield-lock-fill"></i> Secured by PayMongo</span>
                        </div>

                        <div class="mt-3 text-center">
                            <button type="button" id="cancel-payment-btn" class="btn btn-outline-danger" onclick="cancelPayment()">
                                <i class="bi bi-x-circle me-1"></i>Cancel Payment
                            </button>
                        </div>
                    @else
                        <div class="alert alert-warning mb-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Could not initialize payment. Please try again or contact support.
                        </div>
                        <a href="{{ route('checkout.payment', $order->order_number) }}" class="btn btn-primary w-100 mb-3" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                            <i class="bi bi-arrow-clockwise me-2"></i>Retry Payment
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Payment Confirmation Modal -->
<div class="modal fade" id="cancelPaymentModal" tabindex="-1" aria-labelledby="cancelPaymentModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="cancelPaymentModalLabel">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>Cancel Payment?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2 fw-semibold">Are you sure you want to cancel this payment?</p>
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> Cancelling will delete this order and return items to your cart.
                </div>
                <p class="text-muted small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    You will need to checkout again if you want to purchase these items.
                </p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-arrow-left me-1"></i>Continue Payment
                </button>
                <form action="{{ route('checkout.cancel-payment', $order->order_number) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i>Yes, Cancel & Return to Cart
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Payment Success Confirmation Modal -->
<div class="modal fade" id="paymentSuccessModal" tabindex="-1" aria-labelledby="paymentSuccessModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 bg-success text-white">
                <h5 class="modal-title fw-bold" id="paymentSuccessModalLabel">
                    <i class="bi bi-check-circle-fill me-2"></i>Payment Successful!
                </h5>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                </div>
                <h5 class="fw-bold mb-2">Thank you for your payment!</h5>
                <p class="text-muted mb-3">
                    Your order <strong>#{{ $order->order_number }}</strong> has been confirmed and paid successfully.
                </p>
                <p class="text-muted small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    You will be redirected to your order details shortly...
                </p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <a href="{{ route('orders.show', $order->id) }}" class="btn btn-primary">
                    <i class="bi bi-eye me-1"></i>View Order Details
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(($publicKey ?? false))
<script>
// Cancel payment function
function cancelPayment() {
    var modal = new bootstrap.Modal(document.getElementById('cancelPaymentModal'));
    modal.show();
}

// Show payment success modal
function showPaymentSuccess() {
    var modal = new bootstrap.Modal(document.getElementById('paymentSuccessModal'));
    modal.show();
}

// Track if payment is completed
var paymentCompleted = false;

// ALWAYS prevent navigation away from payment page unless payment is completed
window.addEventListener('beforeunload', function(e) {
    if (!paymentCompleted) {
        e.preventDefault();
        e.returnValue = 'Your payment is not complete. If you leave now, your order will be cancelled and items returned to cart. Are you sure?';
        return e.returnValue;
    }
});

// Intercept all link clicks on the page
document.addEventListener('DOMContentLoaded', function() {
    // Get all links except the cancel button form
    var links = document.querySelectorAll('a:not([data-allow-navigation])');
    links.forEach(function(link) {
        link.addEventListener('click', function(e) {
            if (!paymentCompleted && !link.closest('#cancelPaymentModal') && !link.closest('#paymentSuccessModal')) {
                e.preventDefault();
                if (confirm('Your payment is not complete. If you leave now, your order will be cancelled and items returned to cart. Do you want to leave?')) {
                    // User confirmed, show cancel modal
                    cancelPayment();
                }
            }
        });
    });
});

(function() {
    var publicKey = @json($publicKey);
    var csrfToken = @json(csrf_token());
    var processUrl = @json(route('checkout.process-payment', $order->order_number));
    var checkUrl = @json(route('checkout.check-payment', $order->order_number));
    var returnBaseUrl = '/checkout/return?order_number={{ $order->order_number }}';
    var pollingTimer = null;
    var countdownTimer = null;

    function setError(msg) {
        var el = document.getElementById('pay-error');
        el.textContent = msg;
        el.classList.remove('d-none');
    }
    function clearError() { document.getElementById('pay-error').classList.add('d-none'); }
    function setLoading(show) {
        document.getElementById('pay-btn').disabled = show;
        document.getElementById('pay-loading').classList.toggle('d-none', !show);
        var cancelBtn = document.getElementById('cancel-payment-btn');
        if (cancelBtn) cancelBtn.disabled = show;
    }
    function getSelectedMethod() {
        var checked = document.querySelector('input[name="pay_method"]:checked');
        return checked ? checked.value : 'qrph';
    }
    function findRedirectUrl(obj) {
        if (!obj || typeof obj !== 'object') return null;
        if (obj.redirect && obj.redirect.url) return obj.redirect.url;
        if (obj.url && typeof obj.url === 'string') return obj.url;
        for (var k in obj) {
            if (typeof obj[k] === 'object') {
                var found = findRedirectUrl(obj[k]);
                if (found) return found;
            }
        }
        return null;
    }

    function togglePaymentUi(method) {
        document.getElementById('card-form').classList.toggle('d-none', method !== 'card');
        document.getElementById('qrph-notice').classList.toggle('d-none', method !== 'qrph');
    }

    document.querySelectorAll('.payment-method-option').forEach(function(el) {
        el.addEventListener('click', function() {
            document.querySelectorAll('.payment-method-option').forEach(function(o) { o.classList.remove('selected'); });
            this.classList.add('selected');
            this.querySelector('input[type="radio"]').checked = true;
            togglePaymentUi(this.dataset.method);
            clearError();
        });
    });

    var cardInput = document.getElementById('card_number');
    if (cardInput) {
        cardInput.addEventListener('input', function() {
            var val = this.value.replace(/\D/g, '').substring(0, 16);
            this.value = val.replace(/(.{4})/g, '$1 ').trim();
        });
    }

    var expiryInput = document.getElementById('card_expiry');
    if (expiryInput) {
        expiryInput.addEventListener('input', function() {
            var val = this.value.replace(/\D/g, '');
            if (val.length >= 2) {
                this.value = val.substring(0, 2) + '/' + val.substring(2, 4);
            } else {
                this.value = val;
            }
        });
    }

    var changeMethodBtn = document.getElementById('change-method-btn');
    if (changeMethodBtn) {
        changeMethodBtn.addEventListener('click', function() {
            stopPolling();
            document.getElementById('qr-display').classList.add('d-none');
            document.getElementById('pay-btn').classList.remove('d-none');
            document.getElementById('qrph-notice').classList.remove('d-none');
            document.querySelectorAll('.payment-method-option').forEach(function(o) { 
                o.style.pointerEvents = ''; 
                o.style.opacity = ''; 
            });
            clearError();
        });
    }

    function showQrCode(imageUrl) {
        document.getElementById('qr-image').src = imageUrl;
        document.getElementById('qr-display').classList.remove('d-none');
        document.getElementById('pay-btn').classList.add('d-none');
        document.getElementById('qrph-notice').classList.add('d-none');
        document.querySelectorAll('.payment-method-option').forEach(function(o) { o.style.pointerEvents = 'none'; o.style.opacity = '0.6'; });
        startCountdown(30 * 60);
    }

    function startCountdown(seconds) {
        var remaining = seconds;
        var el = document.getElementById('qr-countdown');
        countdownTimer = setInterval(function() {
            remaining--;
            var m = Math.floor(remaining / 60);
            var s = remaining % 60;
            el.textContent = m + ':' + (s < 10 ? '0' : '') + s;
            if (remaining <= 0) {
                clearInterval(countdownTimer);
                el.textContent = 'Expired';
                stopPolling();
                setError('QR code has expired. Please try again.');
                document.getElementById('pay-btn').classList.remove('d-none');
                document.getElementById('qr-display').classList.add('d-none');
                document.querySelectorAll('.payment-method-option').forEach(function(o) { o.style.pointerEvents = ''; o.style.opacity = ''; });
            }
        }, 1000);
    }

    function startPolling(piId) {
        var url = checkUrl + '?payment_intent_id=' + encodeURIComponent(piId);
        pollingTimer = setInterval(async function() {
            try {
                var res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                var data = await res.json();
                if (data.status === 'succeeded') {
                    stopPolling();
                    paymentCompleted = true;
                    showPaymentSuccess();
                    setTimeout(function() {
                        window.location.href = returnBaseUrl + '&payment_intent_id=' + encodeURIComponent(piId);
                    }, 3000);
                } else if (data.status === 'awaiting_payment_method') {
                    stopPolling();
                    setError('Payment failed or was cancelled. Please try again.');
                    document.getElementById('pay-btn').classList.remove('d-none');
                    document.getElementById('qr-display').classList.add('d-none');
                    document.querySelectorAll('.payment-method-option').forEach(function(o) { o.style.pointerEvents = ''; o.style.opacity = ''; });
                }
            } catch (e) {}
        }, 5000);
    }

    function stopPolling() {
        if (pollingTimer) clearInterval(pollingTimer);
        if (countdownTimer) clearInterval(countdownTimer);
    }

    document.getElementById('pay-btn').addEventListener('click', async function() {
        clearError();
        setLoading(true);
        var method = getSelectedMethod();

        try {
            if (method === 'qrph') {
                var serverRes = await fetch(processUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ payment_type: 'qrph' })
                });
                var serverData = await serverRes.json();

                if (serverData.qr_image) {
                    setLoading(false);
                    showQrCode(serverData.qr_image);
                    startPolling(serverData.payment_intent_id);
                    return;
                }
                if (serverData.error) throw new Error(serverData.error);
                throw new Error('Failed to generate QR code. Please try again.');
            }

            var cardNumber = document.getElementById('card_number').value.replace(/\D/g, '');
            var expiry = document.getElementById('card_expiry').value.replace(/\D/g, '');
            var cvc = document.getElementById('cvc').value.replace(/\D/g, '');
            
            if (!cardNumber || !expiry || !cvc) throw new Error('Please fill in all card details.');
            if (cardNumber.length < 13 || cardNumber.length > 19) throw new Error('Please enter a valid card number.');
            if (expiry.length !== 4) throw new Error('Please enter expiry date as MM/YY.');
            if (cvc.length < 3 || cvc.length > 4) throw new Error('Please enter a valid CVC.');
            
            var expMonth = parseInt(expiry.substring(0, 2), 10);
            var expYear = parseInt(expiry.substring(2, 4), 10);
            
            if (expMonth < 1 || expMonth > 12) throw new Error('Invalid expiry month.');

            var serverRes = await fetch(processUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ 
                    payment_type: 'card', 
                    card_number: cardNumber, 
                    exp_month: expMonth, 
                    exp_year: expYear, 
                    cvc: cvc 
                })
            });
            var serverData = await serverRes.json();

            var redirectUrl = serverData.redirect_url;
            if (!redirectUrl && serverData.next_action) redirectUrl = findRedirectUrl(serverData.next_action);
            if (redirectUrl) { 
                paymentCompleted = true;
                window.location.href = redirectUrl; 
                return; 
            }
            if (serverData.error) throw new Error(serverData.error);
            if (serverData.status === 'succeeded' || serverData.status === 'processing') {
                paymentCompleted = true;
                showPaymentSuccess();
                setTimeout(function() {
                    window.location.href = returnBaseUrl + '&payment_intent_id=done';
                }, 3000);
                return;
            }
            throw new Error('Unexpected response. Please try again.');
        } catch (e) {
            setError(e.message || 'Payment failed. Please try again.');
            setLoading(false);
        }
    });
})();
</script>
@endif
@endpush
