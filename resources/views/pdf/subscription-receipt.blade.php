<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ToyHaven Membership Receipt - {{ $receiptNumber }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.5;
        }
        .container { padding: 30px; }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #0891b2;
            padding-bottom: 20px;
        }
        .header-logo { margin-bottom: 10px; }
        .header-logo img { max-height: 50px; max-width: 180px; }
        .company-name {
            font-size: 26px;
            font-weight: bold;
            color: #0891b2;
            margin-bottom: 5px;
        }
        .company-info { font-size: 10px; color: #666; }
        .receipt-title {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            text-align: center;
        }
        .receipt-number {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-bottom: 20px;
        }
        .info-section { margin-bottom: 20px; }
        .info-row { margin-bottom: 12px; }
        .info-label { font-weight: bold; color: #555; margin-bottom: 3px; }
        .info-value { color: #333; }
        .amount-box {
            margin: 25px 0;
            padding: 20px;
            background: #f8fafc;
            border: 2px solid #0891b2;
            text-align: center;
        }
        .amount-box .label { font-size: 12px; color: #64748b; margin-bottom: 5px; }
        .amount-box .value { font-size: 24px; font-weight: bold; color: #0891b2; }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: bold;
            background-color: #10B981;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($logoPath ?? false)
            <div class="header-logo">
                <img src="{{ $logoPath }}" alt="ToyHaven Logo">
            </div>
            @endif
            <div class="company-name">{{ $companyName }}</div>
            <div class="company-info">
                {{ $companyAddress }}<br>
                @if($companyPhone) Phone: {{ $companyPhone }} | @endif
                Email: {{ $companyEmail }}
            </div>
        </div>

        <div class="receipt-title">MEMBERSHIP PAYMENT RECEIPT</div>
        <div class="receipt-number">Receipt No: {{ $receiptNumber }}</div>

        <div class="info-section">
            <div class="info-row">
                <div class="info-label">Member:</div>
                <div class="info-value">
                    {{ $user->name }}<br>
                    {{ $user->email }}
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Plan:</div>
                <div class="info-value">{{ $plan->name }} ({{ $plan->interval === 'monthly' ? 'Monthly' : 'Annual' }})</div>
            </div>
            <div class="info-row">
                <div class="info-label">Payment Date:</div>
                <div class="info-value">{{ $subscriptionPayment->paid_at?->format('F d, Y h:i A') ?? $generatedAt->format('F d, Y h:i A') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value"><span class="status-badge">PAID</span></div>
            </div>
        </div>

        <div class="amount-box">
            <div class="label">Amount Paid</div>
            <div class="value">₱{{ number_format($subscriptionPayment->amount, 2) }}</div>
        </div>

        <div class="footer">
            <p><strong>Thank you for your membership!</strong></p>
            <p>This is a computer-generated receipt. Payment was made via QR Ph.</p>
            <p>Generated on: {{ $generatedAt->format('F d, Y h:i A') }}</p>
            <p style="margin-top: 10px;">For inquiries: {{ $companyEmail }}</p>
        </div>
    </div>
</body>
</html>
