@extends('layouts.seller')

@section('title', 'Order Details - Seller Dashboard')

@section('page-title', 'Order Details')

@section('content')
<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('seller.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('seller.orders.index') }}">Orders</a></li>
        <li class="breadcrumb-item active">#{{ $order->order_number }}</li>
    </ol>
</nav>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Order #{{ $order->order_number }}</h4>
        <p class="text-muted mb-0">Order placed on {{ $order->created_at->format('F d, Y \a\t h:i A') }}</p>
    </div>
    <a href="{{ route('seller.orders.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Orders
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Order Items -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Order Items</h5>
            </div>
            <div class="card-body">
                @foreach($order->items as $item)
                    <div class="d-flex align-items-center border-bottom pb-3 mb-3">
                        @if($item->product && $item->product->images->first())
                            <img src="{{ asset('storage/' . $item->product->images->first()->image_path) }}" 
                                 class="img-thumbnail me-3 rounded" 
                                 style="width: 100px; height: 100px; object-fit: cover;"
                                 alt="{{ $item->product_name }}">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center me-3 rounded" style="width: 100px; height: 100px;">
                                <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                            </div>
                        @endif
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $item->product_name }}</h6>
                            <p class="text-muted mb-0 small">
                                Quantity: <strong>{{ $item->quantity }}</strong> × ₱{{ number_format($item->price, 2) }}
                            </p>
                            @if($item->product)
                                <a href="{{ route('seller.products.show', $item->product->id) }}" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="bi bi-eye me-1"></i> View Product
                                </a>
                            @endif
                        </div>
                        <div class="text-end">
                            <strong class="text-success fs-5">₱{{ number_format($item->subtotal, 2) }}</strong>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Shipping Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Shipping Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong class="text-muted d-block mb-1">Full Address</strong>
                        <p class="mb-0">{{ $order->shipping_address }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <strong class="text-muted d-block mb-1">City</strong>
                        <p class="mb-0">{{ $order->shipping_city }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <strong class="text-muted d-block mb-1">Province</strong>
                        <p class="mb-0">{{ $order->shipping_province }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong class="text-muted d-block mb-1">Postal Code</strong>
                        <p class="mb-0">{{ $order->shipping_postal_code }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong class="text-muted d-block mb-1">Phone</strong>
                        <p class="mb-0">
                            <i class="bi bi-telephone me-1"></i>{{ $order->shipping_phone }}
                        </p>
                    </div>
                    @if($order->shipping_notes)
                        <div class="col-12">
                            <strong class="text-muted d-block mb-1">Shipping Notes</strong>
                            <p class="mb-0">{{ $order->shipping_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Order Tracking -->
        @if($order->trackings && $order->trackings->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Order Tracking</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @foreach($order->trackings->sortBy('created_at') as $tracking)
                        <div class="timeline-item mb-3 pb-3 border-bottom">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-check-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">{{ $tracking->status }}</h6>
                                    <p class="text-muted mb-1 small">{{ $tracking->description }}</p>
                                    @if($tracking->location)
                                        <p class="text-muted mb-0 small">
                                            <i class="bi bi-geo-alt me-1"></i>{{ $tracking->location }}
                                        </p>
                                    @endif
                                    <small class="text-muted">{{ $tracking->created_at->format('M d, Y h:i A') }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Order Summary -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Order Summary</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal:</span>
                    <span>₱{{ number_format($order->total_amount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Shipping:</span>
                    <span>₱{{ number_format($order->shipping_fee, 2) }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-3">
                    <strong>Total:</strong>
                    <strong class="text-primary fs-5">₱{{ number_format($order->total, 2) }}</strong>
                </div>

                <div class="mb-3">
                    <strong class="text-muted d-block mb-2">Order Status:</strong>
                    @php
                        $statusColors = [
                            'pending' => 'warning',
                            'processing' => 'info',
                            'packed' => 'primary',
                            'shipped' => 'primary',
                            'in_transit' => 'info',
                            'out_for_delivery' => 'warning',
                            'delivered' => 'success',
                            'cancelled' => 'danger'
                        ];
                        $color = $statusColors[$order->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $color }} fs-6 px-3 py-2">
                        {{ $order->getStatusLabel() }}
                    </span>
                </div>

                <div class="mb-3">
                    <strong class="text-muted d-block mb-2">Payment Status:</strong>
                    <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }} fs-6 px-3 py-2">
                        <i class="bi bi-{{ $order->payment_status === 'paid' ? 'check-circle' : 'clock' }} me-1"></i>
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </div>

                @if($order->tracking_number)
                    <div class="mb-3">
                        <strong class="text-muted d-block mb-2">Tracking Number:</strong>
                        <code class="bg-light p-2 rounded d-block">{{ $order->tracking_number }}</code>
                    </div>
                @endif
            </div>
        </div>

        <!-- Update Order Status -->
        @if($order->payment_status === 'paid' && !in_array($order->status, ['delivered', 'cancelled']))
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Update Order Status</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('seller.orders.updateStatus', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="packed" {{ $order->status === 'packed' ? 'selected' : '' }}>Packed</option>
                                <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="in_transit" {{ $order->status === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                                <option value="out_for_delivery" {{ $order->status === 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                                <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tracking Number</label>
                            <input type="text" name="tracking_number" class="form-control" value="{{ $order->tracking_number }}" placeholder="Enter tracking number">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Location (Optional)</label>
                            <input type="text" name="location" class="form-control" placeholder="Current location">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Estimated Delivery Date (Optional)</label>
                            <input type="date" name="estimated_delivery_date" class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Status update description"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Internal Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Internal notes (not visible to customer)"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-lg me-1"></i> Update Status
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
