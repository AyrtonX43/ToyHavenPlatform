<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Auction Receipt #{{ $payment->id }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 13px; color: #1e293b; margin: 0; padding: 30px; }
        .header { border-bottom: 3px solid #0284c7; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 24px; color: #0284c7; margin: 0 0 2px 0; }
        .header .subtitle { margin: 0; color: #64748b; font-size: 12px; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .badge-held { background: #dbeafe; color: #1e40af; }
        .badge-released { background: #d1fae5; color: #065f46; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 5px 0; vertical-align: top; }
        .info-table .label { color: #64748b; width: 140px; font-size: 12px; }
        .section-title { font-size: 14px; font-weight: bold; color: #334155; margin: 25px 0 10px 0; padding-bottom: 5px; border-bottom: 1px solid #e2e8f0; }
        .items-table { margin-top: 10px; }
        .items-table th { background: #f1f5f9; padding: 8px 12px; text-align: left; font-size: 12px; color: #475569; border-bottom: 1px solid #cbd5e1; }
        .items-table td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; }
        .total-row td { font-weight: bold; font-size: 16px; border-top: 2px solid #0284c7; padding-top: 12px; }
        .escrow-notice { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 12px 15px; margin-top: 20px; font-size: 12px; color: #1e40af; }
        .footer { margin-top: 40px; text-align: center; color: #94a3b8; font-size: 11px; border-top: 1px solid #e2e8f0; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name', 'ToyHaven') }}</h1>
        <p class="subtitle">Official Auction Payment Receipt</p>
    </div>

    <table style="margin-bottom: 15px;">
        <tr>
            <td style="width:50%;">
                <p class="section-title" style="margin-top:0;">Receipt Details</p>
                <table class="info-table">
                    <tr><td class="label">Receipt #</td><td><strong>AUC-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</strong></td></tr>
                    <tr><td class="label">Date Paid</td><td>{{ $payment->paid_at?->format('F d, Y g:i A') ?? 'N/A' }}</td></tr>
                    <tr><td class="label">Payment Method</td><td>@if($payment->payment_method === 'paypal')PayPal @elseif($payment->payment_method === 'paymongo_qrph')GCash / Maya (QRPH) @else{{ ucfirst($payment->payment_method ?? 'N/A') }}@endif</td></tr>
                    <tr><td class="label">Reference ID</td><td style="font-size:11px;word-break:break-all;">{{ $payment->payment_reference ?? 'N/A' }}</td></tr>
                    <tr>
                        <td class="label">Payment Status</td>
                        <td>
                            @if($payment->status === 'held')
                                <span class="badge badge-held">HELD IN ESCROW</span>
                            @elseif($payment->status === 'released')
                                <span class="badge badge-released">RELEASED</span>
                            @else
                                <span class="badge badge-pending">{{ strtoupper($payment->status) }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width:50%;vertical-align:top;">
                <p class="section-title" style="margin-top:0;">Parties</p>
                <table class="info-table">
                    <tr><td class="label">Buyer</td><td><strong>{{ $payment->winner?->name ?? 'N/A' }}</strong></td></tr>
                    <tr><td class="label">Buyer Email</td><td>{{ $payment->winner?->email ?? 'N/A' }}</td></tr>
                    <tr><td class="label">Seller</td><td><strong>{{ $payment->auction->user?->name ?? 'N/A' }}</strong></td></tr>
                    <tr><td class="label">Seller Email</td><td>{{ $payment->auction->user?->email ?? 'N/A' }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <p class="section-title">Auction Item</p>
    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th style="width:100px;">Condition</th>
                <th style="text-align:right;width:140px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $payment->auction->title }}</strong><br>
                    <span style="color:#64748b;font-size:11px;">Auction ID: #{{ $payment->auction->id }}</span>
                    @if($payment->auction->description)
                        <br><span style="color:#64748b;font-size:11px;">{{ Str::limit($payment->auction->description, 120) }}</span>
                    @endif
                </td>
                <td style="font-size:12px;text-transform:capitalize;">{{ str_replace('_', ' ', $payment->auction->condition ?? 'N/A') }}</td>
                <td style="text-align:right;">PHP {{ number_format($payment->amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="2">Total Paid</td>
                <td style="text-align:right;color:#0284c7;">PHP {{ number_format($payment->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @if($payment->delivery_status)
        <p class="section-title">Delivery Information</p>
        <table class="info-table">
            <tr><td class="label">Delivery Status</td><td style="text-transform:capitalize;">{{ str_replace('_', ' ', $payment->delivery_status) }}</td></tr>
            @if($payment->tracking_number)
                <tr><td class="label">Tracking Number</td><td>{{ $payment->tracking_number }}</td></tr>
            @endif
            @if($payment->shipped_at)
                <tr><td class="label">Shipped On</td><td>{{ $payment->shipped_at->format('F d, Y g:i A') }}</td></tr>
            @endif
            @if($payment->confirmed_at)
                <tr><td class="label">Delivery Confirmed</td><td>{{ $payment->confirmed_at->format('F d, Y g:i A') }}</td></tr>
            @endif
        </table>
    @endif

    <div class="escrow-notice">
        <strong>Escrow Protection:</strong> Payment is held securely by {{ config('app.name', 'ToyHaven') }} until the buyer confirms receipt of the item. Funds are released to the seller after delivery confirmation and a 3-day holding period.
    </div>

    <div class="footer">
        <p>This is a computer-generated receipt and does not require a signature.</p>
        <p>{{ config('app.name', 'ToyHaven') }} &mdash; Generated on {{ now()->format('F d, Y g:i A') }}</p>
    </div>
</body>
</html>
