<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Complete your purchase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .paypal-container { max-width: 400px; margin: 2rem auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .paypal-header { background: #003087; color: #fff; padding: 1rem 1.5rem; font-weight: 600; }
        .paypal-body { padding: 1.5rem; }
        .btn-paypal { background: #ffc439; color: #000; border: none; font-weight: 600; padding: 0.6rem 1.5rem; }
        .btn-paypal:hover { background: #f2bb38; color: #000; }
    </style>
</head>
<body>
    <div class="paypal-container">
        <div class="paypal-header">PayPal</div>
        <div class="paypal-body">
            <p class="mb-2"><strong>{{ config('app.name') }}</strong></p>
            <p class="text-muted small mb-3">{{ $plan->name }} — ₱{{ number_format($plan->price, 2) }}</p>
            <form id="demo-form" action="{{ route('membership.paypal.demo-pay') }}" method="POST">
                @csrf
                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                <button type="submit" class="btn btn-paypal w-100" id="pay-btn">
                    Complete Purchase
                </button>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('demo-form').addEventListener('submit', function() {
            document.getElementById('pay-btn').disabled = true;
            document.getElementById('pay-btn').textContent = 'Processing...';
        });
    </script>
</body>
</html>
