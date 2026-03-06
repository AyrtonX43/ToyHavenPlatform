<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ToyHaven Auction Receipt - {{ $receiptNumber }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; line-height: 1.5; }
        .container { padding: 30px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #0891b2; padding-bottom: 20px; }
        .company-name { font-size: 26px; font-weight: bold; color: #0891b2; margin-bottom: 5px; }
        .company-info { font-size: 10px; color: #666; }
        .receipt-title { font-size: 18px; font-weight: bold; margin: 20px 0 10px 0; text-align: center; }
        .receipt-number { text-align: center; font-size: 12px; color: #666; margin-bottom: 20px; }
        .info-section { margin-bottom: 20px; }
        .info-row { margin-bottom: 12px; }
        .info-label { font-weight: bold; color: #555; margin-bottom: 3px; }
        .info-value { color: #333; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8fafc; }
        .amount-box { margin: 25px 0; padding: 20px; background: #f8fafc; border: 2px solid #0891b2; text-align: center; }
        .amount-box .value { font-size: 24px; font-weight: bold; color: #0891b2; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-name">{{ $companyName }}</div>
            <div class="company-info">{{ $companyAddress }} | Email: {{ $companyEmail }}</div>
        </div>

        <div class="receipt-title">AUCTION PAYMENT RECEIPT</div>
        <div class="receipt-number">Receipt No: {{ $receiptNumber }}</div>

        <div class="info-section">
            <div class="info-row">
                <div class="info-label">Buyer:</div>
                <div class="info-value">{{ $payment->winner->name }}<br>{{ $payment->winner->email }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Auction:</div>
                <div class="info-value">{{ $auction->title }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date:</div>
                <div class="info-value">{{ $generatedAt->format('M d, Y H:i') }}</div>
            </div>
        </div>

        <table>
            <tr><th>Description</th><th style="text-align: right;">Amount (₱)</th></tr>
            <tr><td>Bid Amount</td><td style="text-align: right;">{{ number_format($payment->bid_amount, 2) }}</td></tr>
            <tr><td>Buyer Premium</td><td style="text-align: right;">{{ number_format($payment->buyer_premium, 2) }}</td></tr>
            <tr><th>Total Paid</th><th style="text-align: right;">₱{{ number_format($payment->total_amount, 2) }}</th></tr>
        </table>

        <div class="amount-box">
            <div class="value">₱{{ number_format($payment->total_amount, 2) }}</div>
            <div>Total Amount Paid</div>
        </div>

        <div class="footer">
            <p>This is a computer-generated receipt for ToyHaven Auction. Thank you for your purchase.</p>
        </div>
    </div>
</body>
</html>
