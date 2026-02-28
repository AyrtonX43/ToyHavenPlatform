<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Receipt - {{ $order->order_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #4CAF50;">Payment Successful!</h2>
        
        <p>Dear {{ $order->user->name }},</p>
        
        <p>Thank you for your payment! Your order has been confirmed and is being processed.</p>
        
        <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin-top: 0;">Order Details</h3>
            <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
            <p><strong>Order Date:</strong> {{ $order->created_at->format('M d, Y h:i A') }}</p>
            <p><strong>Payment Status:</strong> <span style="color: #4CAF50;">Paid</span></p>
        </div>
        
        <h3>Items Ordered</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f5f5f5;">
                    <th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;">Product</th>
                    <th style="padding: 10px; text-align: center; border-bottom: 2px solid #ddd;">Qty</th>
                    <th style="padding: 10px; text-align: right; border-bottom: 2px solid #ddd;">Price</th>
                    <th style="padding: 10px; text-align: right; border-bottom: 2px solid #ddd;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #eee;">{{ $item->product_name }}</td>
                    <td style="padding: 10px; text-align: center; border-bottom: 1px solid #eee;">{{ $item->quantity }}</td>
                    <td style="padding: 10px; text-align: right; border-bottom: 1px solid #eee;">₱{{ number_format($item->price, 2) }}</td>
                    <td style="padding: 10px; text-align: right; border-bottom: 1px solid #eee;">₱{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="padding: 10px; text-align: right;"><strong>Total Amount:</strong></td>
                    <td style="padding: 10px; text-align: right;"><strong>₱{{ number_format($order->total, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
        
        <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;">
            <h3 style="margin-top: 0;">Shipping Address</h3>
            <p style="margin: 5px 0;">{{ $order->shipping_address }}</p>
            <p style="margin: 5px 0;">{{ $order->shipping_city }}, {{ $order->shipping_province }} {{ $order->shipping_postal_code }}</p>
            <p style="margin: 5px 0;"><strong>Contact:</strong> {{ $order->shipping_phone }}</p>
        </div>
        
        <p style="margin-top: 30px;">Your order is now being processed by the seller. You will receive updates via email and notifications.</p>
        
        <p style="margin-top: 20px;">
            <a href="{{ route('orders.show', $order->id) }}" style="display: inline-block; padding: 12px 24px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">View Order Details</a>
        </p>
        
        <p style="margin-top: 30px; font-size: 12px; color: #666;">
            Thank you for shopping at ToyHaven!<br>
            If you have any questions, please contact our support team.
        </p>
    </div>
</body>
</html>
