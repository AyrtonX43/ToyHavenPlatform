<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pay with PayPal - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=PayPal+Sans:opsz,wght@10..72,400;10..72,500;10..72,600;10..72,700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'PayPal Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .paypal-card {
            width: 100%;
            max-width: 440px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .paypal-top {
            background: #003087;
            padding: 24px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
        .paypal-logo {
            width: 120px;
            height: 32px;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 33"><text x="0" y="24" fill="%23fff" font-family="Arial" font-weight="bold" font-size="22">PayPal</text></svg>') no-repeat;
            background-size: contain;
        }
        .paypal-top span { color: #fff; font-size: 18px; font-weight: 600; }
        .paypal-content { padding: 24px; }
        .merchant { font-size: 14px; color: #6c757d; margin-bottom: 4px; }
        .merchant strong { color: #1a1a1a; }
        .order-summary {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 16px;
            margin: 20px 0 24px 0;
            border: 1px solid #e9ecef;
        }
        .order-summary .label { font-size: 12px; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
        .order-summary .amount { font-size: 22px; font-weight: 700; color: #1a1a1a; }
        .field { margin-bottom: 16px; }
        .field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 6px;
        }
        .field input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 16px;
            font-family: inherit;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .field input:focus {
            outline: none;
            border-color: #0070ba;
            box-shadow: 0 0 0 3px rgba(0,112,186,0.2);
        }
        .field input::placeholder { color: #adb5bd; }
        .field-row { display: flex; gap: 12px; }
        .field-row .field { flex: 1; }
        .cvv-hint { font-size: 11px; color: #6c757d; margin-top: 4px; }
        .btn-pay {
            width: 100%;
            padding: 14px;
            background: #ffc439;
            color: #1a1a1a;
            border: none;
            border-radius: 24px;
            font-size: 16px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.2s;
        }
        .btn-pay:hover { background: #f5bd2e; }
        .btn-pay:disabled { opacity: 0.7; cursor: not-allowed; }
        .secure-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        .secure-row svg { width: 16px; height: 16px; }
        .secure-row span { font-size: 12px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="paypal-card">
        <div class="paypal-top">
            <span style="font-size:22px;font-weight:700;letter-spacing:-0.5px;">PayPal</span>
            <span style="opacity:0.9;font-size:14px;font-weight:400;">Pay with your debit or credit card</span>
        </div>
        <div class="paypal-content">
            <div class="merchant">Paying <strong>{{ config('app.name') }}</strong></div>
            <div class="order-summary">
                <div class="label">Order total</div>
                <div class="amount">{{ $plan->name }} — ₱{{ number_format($plan->price, 2) }} PHP</div>
            </div>

            <form id="pay-form" action="{{ url('/membership/paypal/checkout') }}" method="POST">
                @csrf
                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                <div class="field">
                    <label for="card_number">Card number</label>
                    <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19" autocomplete="cc-number" inputmode="numeric" required>
                </div>
                <div class="field">
                    <label for="card_name">Name on card</label>
                    <input type="text" id="card_name" name="card_name" placeholder="Name as it appears on your card" autocomplete="cc-name" required>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label for="card_expiry">Expiration date</label>
                        <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/YY" maxlength="5" autocomplete="cc-exp" inputmode="numeric" required>
                    </div>
                    <div class="field">
                        <label for="card_cvv">Security code (CVV)</label>
                        <input type="password" id="card_cvv" name="card_cvv" placeholder="•••" maxlength="4" autocomplete="cc-csc" inputmode="numeric" required>
                        <div class="cvv-hint">3 or 4 digits on the back of your card</div>
                    </div>
                </div>
                <button type="submit" class="btn-pay" id="pay-btn">Pay Now</button>
            </form>

            <div class="secure-row">
                <svg viewBox="0 0 24 24" fill="#22c55e"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>
                <span>PayPal protects your financial information</span>
            </div>
        </div>
    </div>
    <script>
(function(){
    var form = document.getElementById('pay-form');
    var cardNum = document.getElementById('card_number');
    var expiry = document.getElementById('card_expiry');
    cardNum.oninput = function() {
        var v = this.value.replace(/\s/g, '').replace(/\D/g, '');
        this.value = (v.match(/.{1,4}/g) || []).join(' ');
    };
    expiry.oninput = function() {
        var v = this.value.replace(/\D/g, '');
        this.value = v.length >= 2 ? v.slice(0,2) + '/' + v.slice(2,4) : v;
    };
    form.onsubmit = function() {
        document.getElementById('pay-btn').disabled = true;
        document.getElementById('pay-btn').textContent = 'Processing...';
    };
})();
    </script>
</body>
</html>
