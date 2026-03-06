<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Membership Receipt - {{ $receiptNumber }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; line-height: 1.5; }
        .container { padding: 30px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #0d9488; padding-bottom: 20px; }
        .company-name { font-size: 22px; font-weight: bold; color: #0d9488; margin-bottom: 5px; }
        .receipt-title { font-size: 18px; font-weight: bold; margin: 20px 0 10px 0; text-align: center; }
        .receipt-number { text-align: center; font-size: 12px; color: #666; margin-bottom: 20px; }
        .info-row { margin-bottom: 8px; }
        .info-label { font-weight: bold; display: inline-block; width: 140px; }
        .value { font-size: 14px; font-weight: bold; margin: 15px 0; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        @if($logoPath)
            <div class="header-logo"><img src="{{ $logoPath }}" alt="Logo"></div>
        @endif
        <div class="company-name">{{ $companyName }}</div>
        <div class="company-info">{{ $companyAddress }} | {{ $companyEmail }}</div>
    </div>
    <div class="receipt-title">Membership Payment Receipt</div>
    <div class="receipt-number">{{ $receiptNumber }}</div>
    <div class="info-section">
        <div class="info-row"><span class="info-label">Date:</span> {{ $generatedAt->format('F d, Y h:i A') }}</div>
        <div class="info-row"><span class="info-label">Plan:</span> {{ $plan->name }}</div>
        <div class="info-row"><span class="info-label">Member:</span> {{ $subscription->user->name ?? 'N/A' }}</div>
    </div>
    <div class="value">Amount: ₱{{ number_format($subscriptionPayment->amount, 2) }}</div>
    <p><strong>Thank you for your membership!</strong></p>
</div>
</body>
</html>
