<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $receiptNumber }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.5;
        }
        .container {
            padding: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #4F46E5;
            margin-bottom: 5px;
        }
        .company-info {
            font-size: 10px;
            color: #666;
        }
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
        .info-section {
            margin-bottom: 20px;
        }
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .info-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 3px;
        }
        .info-value {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background-color: #4F46E5;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-section {
            margin-top: 20px;
            float: right;
            width: 300px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .total-row.grand-total {
            font-size: 14px;
            font-weight: bold;
            border-top: 2px solid #4F46E5;
            border-bottom: 2px solid #4F46E5;
            margin-top: 10px;
            padding: 12px 0;
        }
        .footer {
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
            clear: both;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-paid {
            background-color: #10B981;
            color: white;
        }
        .status-pending {
            background-color: #F59E0B;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-name">{{ $companyName }}</div>
            <div class="company-info">
                {{ $companyAddress }}<br>
                @if($companyPhone)
                    Phone: {{ $companyPhone }} |
                @endif
                Email: {{ $companyEmail }}
            </div>
        </div>

        <div class="receipt-title">OFFICIAL RECEIPT</div>
        <div class="receipt-number">Receipt No: {{ $receiptNumber }}</div>

        <div class="info-section">
            <div class="info-row">
                <div class="info-column">
                    <div class="info-label">Bill To:</div>
                    <div class="info-value">
                        {{ $order->user->name }}<br>
                        {{ $order->user->email }}<br>
                        @if($order->user->phone)
                            {{ $order->user->phone }}<br>
                        @endif
                    </div>
                </div>
                <div class="info-column">
                    <div class="info-label">Receipt Details:</div>
                    <div class="info-value">
                        <strong>Order Number:</strong> {{ $order->order_number }}<br>
                        <strong>Date:</strong> {{ $order->created_at->format('F d, Y') }}<br>
                        <strong>Payment Status:</strong>
                        <span class="status-badge status-{{ $order->payment_status }}">
                            {{ strtoupper($order->payment_status) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-column">
                    <div class="info-label">Shipping Address:</div>
                    <div class="info-value">
                        {{ $order->shipping_address }}<br>
                        {{ $order->shipping_city }}, {{ $order->shipping_province }} {{ $order->shipping_postal_code }}<br>
                        Phone: {{ $order->shipping_phone }}
                    </div>
                </div>
                <div class="info-column">
                    <div class="info-label">Seller:</div>
                    <div class="info-value">
                        {{ $order->seller->business_name }}<br>
                        {{ $order->seller->email }}
                    </div>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">₱{{ number_format($item->price, 2) }}</td>
                    <td class="text-right">₱{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>₱{{ number_format($order->total_amount, 2) }}</span>
            </div>
            @if($order->shipping_fee > 0)
            <div class="total-row">
                <span>Shipping Fee:</span>
                <span>₱{{ number_format($order->shipping_fee, 2) }}</span>
            </div>
            @endif
            <div class="total-row">
                <span>Admin Commission ({{ $order->admin_commission_rate }}%):</span>
                <span>₱{{ number_format($order->admin_commission, 2) }}</span>
            </div>
            <div class="total-row">
                <span>VAT ({{ $order->tax_rate }}%):</span>
                <span>₱{{ number_format($order->tax_amount, 2) }}</span>
            </div>
            @if($order->transaction_fee > 0)
            <div class="total-row">
                <span>Transaction Fee:</span>
                <span>₱{{ number_format($order->transaction_fee, 2) }}</span>
            </div>
            @endif
            <div class="total-row grand-total">
                <span>TOTAL AMOUNT:</span>
                <span>₱{{ number_format($order->total, 2) }}</span>
            </div>
        </div>

        <div class="footer">
            <p><strong>Thank you for your purchase!</strong></p>
            <p>This is a computer-generated receipt and does not require a signature.</p>
            <p>Generated on: {{ $generatedAt->format('F d, Y h:i A') }}</p>
            <p style="margin-top: 10px;">
                For inquiries, please contact us at {{ $companyEmail }}
            </p>
        </div>
    </div>
</body>
</html>
