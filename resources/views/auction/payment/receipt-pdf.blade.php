<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Auction Receipt #{{ $payment->id }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 13px; color: #1e293b; margin: 0; padding: 30px; }
        .header { border-bottom: 2px solid #0284c7; padding-bottom: 15px; margin-bottom: 25px; }
        .header h1 { font-size: 22px; color: #0284c7; margin: 0 0 4px 0; }
        .header p { margin: 0; color: #64748b; font-size: 12px; }
        .badge { display: inline-block; background: #d1fae5; color: #065f46; padding: 3px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 5px 0; vertical-align: top; }
        .info-table .label { color: #64748b; width: 140px; }
        .items-table { margin-top: 20px; }
        .items-table th { background: #f1f5f9; padding: 8px 12px; text-align: left; font-size: 12px; color: #475569; border-bottom: 1px solid #cbd5e1; }
        .items-table td { padding: 8px 12px; border-bottom: 1px solid #e2e8f0; }
        .total-row td { font-weight: bold; font-size: 15px; border-top: 2px solid #0284c7; padding-top: 10px; }
        .footer { margin-top: 40px; text-align: center; color: #94a3b8; font-size: 11px; border-top: 1px solid #e2e8f0; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name', 'ToyHaven') }}</h1>
        <p>Auction Payment Receipt</p>
    </div>

    <table class="info-table" style="margin-bottom: 20px;">
        <tr>
            <td style="width:50%;">
                <table class="info-table">
                    <tr><td class="label">Receipt #</td><td><strong>AUC-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</strong></td></tr>
                    <tr><td class="label">Date paid</td><td>{{ $payment->paid_at?->format('F d, Y g:i A') ?? 'N/A' }}</td></tr>
                    <tr><td class="label">Payment method</td><td style="text-transform:capitalize;">{{ $payment->payment_method ?? 'N/A' }}</td></tr>
                    <tr><td class="label">Reference</td><td style="font-size:11px;">{{ $payment->payment_reference ?? 'N/A' }}</td></tr>
                </table>
            </td>
            <td style="width:50%;">
                <table class="info-table">
                    <tr><td class="label">Buyer</td><td>{{ $payment->winner?->name ?? 'N/A' }}</td></tr>
                    <tr><td class="label">Email</td><td>{{ $payment->winner?->email ?? 'N/A' }}</td></tr>
                    <tr><td class="label">Seller</td><td>{{ $payment->auction->user?->name ?? 'N/A' }}</td></tr>
                    <tr>
                        <td class="label">Status</td>
                        <td><span class="badge">{{ strtoupper($payment->status) }}</span></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th style="text-align:right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $payment->auction->title }}</strong><br>
                    <span style="color:#64748b;font-size:12px;">Auction ID: {{ $payment->auction->id }}</span>
                </td>
                <td style="text-align:right;">PHP {{ number_format($payment->amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>Total Paid</td>
                <td style="text-align:right;color:#0284c7;">PHP {{ number_format($payment->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated receipt and does not require a signature.</p>
        <p>{{ config('app.name', 'ToyHaven') }} &mdash; Generated on {{ now()->format('F d, Y') }}</p>
    </div>
</body>
</html>
