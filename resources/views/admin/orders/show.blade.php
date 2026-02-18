@extends('layouts.admin')

@section('title', 'Order Details - ToyHaven')
@section('page-title', 'Order: ' . $order->order_number)

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">Order #{{ $order->order_number }}</h4>
                        <p class="text-muted mb-0">Placed on {{ $order->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }} fs-6">
                            {{ $order->getStatusLabel() }}
                        </span>
                        <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'danger' : 'warning') }} fs-6">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Order Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product->name ?? 'Product Deleted' }}</strong><br>
                                        <small class="text-muted">SKU: {{ $item->product->sku ?? 'N/A' }}</small>
                                    </td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>₱{{ number_format($item->price, 2) }}</td>
                                    <td>₱{{ number_format($item->quantity * $item->price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                <td><strong>₱{{ number_format($order->total_amount, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Shipping Fee:</strong></td>
                                <td><strong>₱{{ number_format($order->shipping_fee, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td><strong>₱{{ number_format($order->total, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Shipping Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Address:</strong> {{ $order->shipping_address }}</p>
                <p><strong>City:</strong> {{ $order->shipping_city }}</p>
                <p><strong>Province:</strong> {{ $order->shipping_province }}</p>
                <p><strong>Postal Code:</strong> {{ $order->shipping_postal_code }}</p>
                <p><strong>Phone:</strong> {{ $order->shipping_phone }}</p>
                @if($order->shipping_notes)
                    <p><strong>Notes:</strong> {{ $order->shipping_notes }}</p>
                @endif
                @if($order->tracking_number)
                    <p><strong>Tracking Number:</strong> <code>{{ $order->tracking_number }}</code></p>
                @endif
            </div>
        </div>

        @if($order->tracking->count() > 0)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Order Tracking</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @foreach($order->tracking as $track)
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $track->status }}</strong><br>
                                    <small class="text-muted">{{ $track->description }}</small>
                                </div>
                                <small class="text-muted">{{ $track->created_at->format('M d, Y h:i A') }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Customer Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong><br>
                    <a href="{{ route('admin.users.show', $order->user_id) }}">{{ $order->user->name }}</a>
                </p>
                <p><strong>Email:</strong><br>
                    {{ $order->user->email }}
                </p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Seller Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Business Name:</strong><br>
                    <a href="{{ route('admin.sellers.show', $order->seller_id) }}">{{ $order->seller->business_name }}</a>
                </p>
                <p><strong>Email:</strong><br>
                    {{ $order->seller->email ?? 'N/A' }}
                </p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Payment Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Payment Method:</strong><br>
                    {{ ucfirst($order->payment_method ?? 'N/A') }}
                </p>
                @if($order->payment_reference)
                    <p><strong>Payment Reference:</strong><br>
                        <code>{{ $order->payment_reference }}</code>
                    </p>
                @endif
                <p><strong>Payment Status:</strong><br>
                    <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'danger' : 'warning') }}">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
