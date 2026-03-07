<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pay with PayPal - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --paypal-blue: #003087;
            --paypal-blue-light: #0070ba;
            --paypal-yellow: #ffc439;
            --paypal-yellow-hover: #f2bb38;
        }
        * { box-sizing: border-box; }
        body {
            background: linear-gradient(180deg, #e8eef4 0%, #f0f4f8 100%);
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 1.5rem 0;
        }
        .paypal-checkout {
            max-width: 400px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0, 48, 135, 0.12);
            overflow: hidden;
        }
        .paypal-header {
            background: var(--paypal-blue);
            color: #fff;
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .paypal-header svg {
            width: 28px;
            height: 28px;
        }
        .paypal-header-title { font-weight: 600; font-size: 1.15rem; }
        .paypal-body { padding: 1.5rem; }
        .merchant-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .merchant-name { font-weight: 600; color: #1a1a1a; }
        .amount-box {
            background: #f7f9fc;
            border: 1px solid #e1e8ed;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }
        .amount-label { font-size: 0.8rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
        .amount-value { font-size: 1.35rem; font-weight: 700; color: #1a1a1a; }
        .form-group { margin-bottom: 1rem; }
        .form-label {
            display: block;
            font-weight: 600;
            font-size: 0.85rem;
            color: #333;
            margin-bottom: 0.4rem;
        }
        .form-control {
            width: 100%;
            padding: 0.65rem 0.9rem;
            border: 1px solid #cbd6e0;
            border-radius: 6px;
            font-size: 0.95rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--paypal-blue-light);
            box-shadow: 0 0 0 3px rgba(0, 112, 186, 0.15);
        }
        .form-control::placeholder { color: #9ca3af; }
        .row-fields { display: flex; gap: 0.75rem; }
        .row-fields .form-group { flex: 1; }
        .cvv-hint {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
        .btn-paypal {
            width: 100%;
            padding: 0.85rem 1rem;
            background: var(--paypal-yellow);
            color: #000;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 0.5rem;
        }
        .btn-paypal:hover { background: var(--paypal-yellow-hover); color: #000; }
        .btn-paypal:disabled { opacity: 0.7; cursor: not-allowed; }
        .secure-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.25rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }
        .secure-footer i { color: #22c55e; font-size: 1rem; }
        .secure-footer span { font-size: 0.8rem; color: #6c757d; }
    </style>
</head>
<body>
    <div class="paypal-checkout">
        <div class="paypal-header">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944 3.72a.77.77 0 0 1 .762-.657h5.782c2.974 0 5.077 1.306 5.567 4.185.244 1.442.117 2.645-.383 3.59-.994 1.887-2.875 2.83-5.416 2.83H9.48a.77.77 0 0 0-.762.657l-.599 3.556-.268 1.586a.64.64 0 0 0 .633.74h3.875c.526 0 .974-.383 1.06-.9l.269-1.586.067-.409.054-.314.268-1.586a1.08 1.08 0 0 1 1.06-.9h.67c2.974 0 5.077 1.306 5.567 4.185.242 1.442.114 2.645-.387 3.59-.992 1.887-2.873 2.83-5.414 2.83h-.719a.77.77 0 0 0-.762.657l-.599 3.556-.268 1.586a.64.64 0 0 0 .633.74h3.875c.526 0 .974-.383 1.06-.9l.268-1.586.067-.409.054-.314.268-1.586a1.08 1.08 0 0 1 1.06-.9h.67c4.034 0 6.726 2.084 7.203 5.493.222 1.632.023 2.985-.61 4.086-.99 1.73-2.806 2.595-5.416 2.595H14.2a.77.77 0 0 0-.762.657l-.599 3.556-.268 1.586a.641.641 0 0 0 .633.74h3.462a.641.641 0 0 0 .633-.74l.133-.786.268-1.586.067-.409a.77.77 0 0 1 .762-.657h.163c2.974 0 5.077 1.306 5.567 4.185.244 1.442.117 2.645-.383 3.59-.994 1.887-2.875 2.83-5.416 2.83h-2.655z"/></svg>
            <span class="paypal-header-title">Pay with PayPal</span>
        </div>
        <div class="paypal-body">
            <div class="merchant-row">
                <span class="merchant-name">{{ config('app.name') }}</span>
            </div>
            <div class="amount-box">
                <div class="amount-label">Amount due</div>
                <div class="amount-value">{{ $plan->name }} — ₱{{ number_format($plan->price, 2) }}</div>
            </div>

            <form id="paypal-checkout-form" action="{{ url('/membership/paypal/checkout') }}" method="POST">
                @csrf
                <input type="hidden" name="plan_id" value="{{ $plan->id }}">

                <div class="form-group">
                    <label class="form-label" for="card_number">Card number</label>
                    <input type="text" id="card_number" name="card_number" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19" autocomplete="cc-number" inputmode="numeric" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="card_name">Name on card</label>
                    <input type="text" id="card_name" name="card_name" class="form-control" placeholder="As shown on card" autocomplete="cc-name" required>
                </div>

                <div class="row-fields">
                    <div class="form-group">
                        <label class="form-label" for="card_expiry">Expiry (MM/YY)</label>
                        <input type="text" id="card_expiry" name="card_expiry" class="form-control" placeholder="MM/YY" maxlength="5" autocomplete="cc-exp" inputmode="numeric" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="card_cvv">CVV</label>
                        <input type="password" id="card_cvv" name="card_cvv" class="form-control" placeholder="•••" maxlength="4" autocomplete="cc-csc" inputmode="numeric" required>
                        <span class="cvv-hint">3 or 4 digits on back of card</span>
                    </div>
                </div>

                <button type="submit" class="btn-paypal" id="pay-btn">
                    Pay ₱{{ number_format($plan->price, 2) }}
                </button>
            </form>

            <div class="secure-footer">
                <i class="bi bi-shield-lock-fill"></i>
                <span>Secure payment</span>
            </div>
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
