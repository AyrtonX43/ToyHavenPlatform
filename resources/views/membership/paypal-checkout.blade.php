<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pay with PayPal - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .checkout-container { max-width: 420px; margin: 2rem auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 16px rgba(0,0,0,0.08); overflow: hidden; }
        .checkout-header { background: #003087; color: #fff; padding: 1rem 1.5rem; font-weight: 600; font-size: 1.1rem; }
        .checkout-body { padding: 1.5rem; }
        .amount-row { background: #f8f9fa; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; }
        .form-label { font-weight: 600; font-size: 0.875rem; color: #333; }
        .form-control:focus { border-color: #003087; box-shadow: 0 0 0 0.2rem rgba(0, 48, 135, 0.15); }
        .btn-paypal { background: #ffc439; color: #000; border: none; font-weight: 600; padding: 0.75rem; border-radius: 8px; }
        .btn-paypal:hover { background: #f2bb38; color: #000; }
        .btn-paypal:disabled { opacity: 0.8; cursor: not-allowed; }
        .secure-note { font-size: 0.75rem; color: #6c757d; margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="checkout-container">
        <div class="checkout-header">PayPal</div>
        <div class="checkout-body">
            <p class="mb-1"><strong>{{ config('app.name') }}</strong></p>
            <div class="amount-row">
                <span class="text-muted small">Membership</span>
                <div class="fw-bold">{{ $plan->name }} — ₱{{ number_format($plan->price, 2) }}</div>
            </div>

            <form id="paypal-checkout-form" action="{{ route('membership.paypal.checkout.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="plan_id" value="{{ $plan->id }}">

                <div class="mb-3">
                    <label class="form-label" for="card_number">Card number</label>
                    <input type="text" id="card_number" name="card_number" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19" autocomplete="cc-number" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="card_name">Name on card</label>
                    <input type="text" id="card_name" name="card_name" class="form-control" placeholder="As it appears on card" autocomplete="cc-name" required>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label" for="card_expiry">Expiry (MM/YY)</label>
                        <input type="text" id="card_expiry" name="card_expiry" class="form-control" placeholder="MM/YY" maxlength="5" autocomplete="cc-exp" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="card_cvv">CVV</label>
                        <input type="text" id="card_cvv" name="card_cvv" class="form-control" placeholder="123" maxlength="4" autocomplete="cc-csc" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-paypal w-100 mt-4" id="pay-btn">
                    Pay ₱{{ number_format($plan->price, 2) }}
                </button>
            </form>

            <p class="secure-note text-center mb-0">
                <span class="text-muted">Secure payment powered by PayPal</span>
            </p>
        </div>
    </div>
    <script>
        (function() {
            var form = document.getElementById('paypal-checkout-form');
            var cardNumber = document.getElementById('card_number');
            var cardExpiry = document.getElementById('card_expiry');

            cardNumber.addEventListener('input', function() {
                var v = this.value.replace(/\s/g, '').replace(/\D/g, '');
                var match = v.match(/.{1,4}/g) || [];
                this.value = match.join(' ');
            });

            cardExpiry.addEventListener('input', function() {
                var v = this.value.replace(/\D/g, '');
                if (v.length >= 2) {
                    this.value = v.slice(0, 2) + '/' + v.slice(2, 4);
                } else {
                    this.value = v;
                }
            });

            form.addEventListener('submit', function() {
                document.getElementById('pay-btn').disabled = true;
                document.getElementById('pay-btn').textContent = 'Processing...';
            });
        })();
    </script>
</body>
</html>
