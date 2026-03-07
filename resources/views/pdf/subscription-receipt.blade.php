<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Official Receipt - {{ $receiptNumber }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; line-height: 1.5; }
        .receipt { padding: 30px; max-width: 210mm; margin: 0 auto; }
        .receipt-header {
            border-bottom: 3px solid #003087;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .company-name { font-size: 24px; font-weight: bold; color: #003087; margin-bottom: 4px; }
        .company-info { font-size: 10px; color: #6c757d; }
        .receipt-title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 6px;
            color: #1a1a1a;
        }
        .receipt-number {
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 20px;
        }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #6c757d; margin-bottom: 10px; }
        .row { display: table; width: 100%; margin-bottom: 8px; }
        .row-label { display: table-cell; width: 140px; color: #6c757d; }
        .row-value { display: table-cell; font-weight: bold; }
        .divider { border-top: 1px dashed #dee2e6; margin: 16px 0; }
        .amount-row {
            background: #f8f9fa;
            padding: 12px 16px;
            margin: 16px 0;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
        }
        .footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #dee2e6;
            font-size: 10px;
            color: #6c757d;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="receipt">
    <div class="receipt-header">
        @if($logoPath)
            <div style="margin-bottom: 12px;"><img src="{{ $logoPath }}" alt="Logo" style="max-height: 48px;"></div>
        @endif
        <div class="company-name">{{ $companyName }}</div>
        <div class="company-info">{{ $companyAddress }}</div>
        <div class="company-info">{{ $companyEmail }}</div>
    </div>

    <div class="receipt-title">OFFICIAL RECEIPT</div>
    <div class="receipt-number">{{ $receiptNumber }}</div>

    <div class="section">
        <div class="section-title">Transaction Details</div>
        <div class="row">
            <span class="row-label">Date & Time:</span>
            <span class="row-value">{{ $generatedAt->format('F d, Y h:i A') }}</span>
        </div>
        <div class="row">
            <span class="row-label">Receipt Number:</span>
            <span class="row-value">{{ $receiptNumber }}</span>
        </div>
        <div class="row">
            <span class="row-label">Transaction Type:</span>
            <span class="row-value">Membership Subscription</span>
        </div>
        <div class="row">
            <span class="row-label">Payment Method:</span>
            <span class="row-value">{{ strtoupper($subscriptionPayment->payment_method ?? 'CARD') }}</span>
        </div>
        @if(!empty($subscriptionPayment->payment_reference))
        <div class="row">
            <span class="row-label">Reference:</span>
            <span class="row-value">{{ $subscriptionPayment->payment_reference }}</span>
        </div>
        @endif
    </div>

    <div class="divider"></div>

    <div class="section">
        <div class="section-title">Member Information</div>
        <div class="row">
            <span class="row-label">Member Name:</span>
            <span class="row-value">{{ $subscription->user->name ?? 'N/A' }}</span>
        </div>
        <div class="row">
            <span class="row-label">Email:</span>
            <span class="row-value">{{ $subscription->user->email ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="divider"></div>

    <div class="section">
        <div class="section-title">Subscription Details</div>
        <div class="row">
            <span class="row-label">Plan:</span>
            <span class="row-value">{{ $plan->name }}</span>
        </div>
        <div class="row">
            <span class="row-label">Billing Period:</span>
            <span class="row-value">Monthly</span>
        </div>
    </div>

    <div class="divider"></div>

    <div class="amount-row">
        <span style="color: #6c757d;">Amount Paid:</span>
        <span style="float: right;">₱{{ number_format($subscriptionPayment->amount, 2) }} PHP</span>
    </div>

    <div class="footer">
        This is an official receipt from {{ $companyName }}. Please retain for your records.
        <br>Generated on {{ $generatedAt->format('F d, Y \a\t h:i A') }}
    </div>
</div>
</body>
</html>
