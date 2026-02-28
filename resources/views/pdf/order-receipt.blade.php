<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Receipt - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #4CAF50;
            margin: 0;
        }
        .info-box {
            background: #f5f5f5;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #f5f5f5;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #ddd;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            font-weight: bold;
            background: #f5f5f5;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>TOYHAVEN</h1>
        <p>Order Receipt</p>
    </div>
    
    <div class="info-box">
        <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
        <p><strong>Order Date:</strong> {{ $order->created_at->format('M d, Y h:i A') }}</p>
        <p><strong>Payment Status:</strong> PAID</p>
        <p><strong>Payment Reference:</strong> {{ $order->payment_reference }}</p>
    </div>
    
    <h3>Customer Information</h3>
    <p><strong>Name:</strong> {{ $order->user->name }}</p>
    <p><strong>Email:</strong> {{ $order->user->email }}</p>
    
    <h3>Shipping Address</h3>
    <p>{{ $order->shipping_address }}</p>
    <p>{{ $order->shipping_city }}, {{ $order->shipping_province }} {{ $order->shipping_postal_code }}</p>
    <p><strong>Contact:</strong> {{ $order->shipping_phone }}</p>
    
    <h3>Order Items</h3>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th class="text-center">Quantity</th>
                <th class="text-right">Price</th>
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
        <tfoot>
            <tr>
                <td colspan="3" class="text-right"><strong>Base Amount:</strong></td>
                <td class="text-right">₱{{ number_format($order->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right">Admin Commission:</td>
                <td class="text-right">₱{{ number_format($order->admin_commission, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right">Tax (VAT):</td>
                <td class="text-right">₱{{ number_format($order->tax_amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right">Transaction Fee:</td>
                <td class="text-right">₱{{ number_format($order->transaction_fee, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right">Shipping Fee:</td>
                <td class="text-right">₱{{ number_format($order->shipping_fee, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="3" class="text-right"><strong>TOTAL PAID:</strong></td>
                <td class="text-right"><strong>₱{{ number_format($order->total, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    
    <div class="footer">
        <p>Thank you for shopping at ToyHaven!</p>
        <p>This is a computer-generated receipt and does not require a signature.</p>
        <p>For inquiries, please visit our website or contact our support team.</p>
    </div>
</body>
</html>
