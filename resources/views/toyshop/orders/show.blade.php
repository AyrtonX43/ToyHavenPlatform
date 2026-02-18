@extends('layouts.toyshop')

@section('title', 'Order Details - ToyHaven')

@push('styles')
<style>
    .order-detail-header {
        background: white;
        border-radius: 16px;
        padding: 1.5rem 2rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
    }
    
    .detail-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
    }
    
    .detail-card-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .detail-card-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #ff6b6b;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .detail-card-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2d2a26;
        margin: 0;
    }
    
    .order-item-detail {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .order-item-detail:last-child {
        border-bottom: none;
    }
    
    .order-item-image {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 12px;
        border: 2px solid #e5e7eb;
    }
    
    .summary-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        position: sticky;
        top: 100px;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .summary-total {
        font-size: 1.5rem;
        font-weight: 700;
        color: #ff6b6b;
    }
    
    .info-row {
        display: flex;
        padding: 0.75rem 0;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: 600;
        color: #5c554d;
        width: 140px;
    }
    
    .info-value {
        color: #2d2a26;
        font-weight: 500;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="order-detail-header reveal">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="fw-bold mb-1">Order Details</h2>
                <p class="text-muted mb-0">Order #{{ $order->order_number }}</p>
            </div>
            <div>
                <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }} px-3 py-2" style="font-size: 0.875rem; font-weight: 600;">
                    {{ $order->getStatusLabel() }}
                </span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Order Items -->
            <div class="detail-card reveal">
                <div class="detail-card-header">
                    <div class="detail-card-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <h3 class="detail-card-title">Order Items</h3>
                </div>
                
                @foreach($order->items as $item)
                    <div class="order-item-detail">
                        @if($item->product && $item->product->images->first())
                            <img src="{{ asset('storage/' . $item->product->images->first()->image_path) }}" 
                                 class="order-item-image" 
                                 alt="{{ $item->product_name }}">
                        @else
                            <div class="order-item-image bg-light d-flex align-items-center justify-content-center">
                                <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                            </div>
                        @endif
                        <div class="flex-grow-1">
                            <h6 class="fw-semibold mb-1">{{ $item->product_name }}</h6>
                            <p class="text-muted mb-0">Quantity: {{ $item->quantity }} × ₱{{ number_format($item->price, 2) }}</p>
                        </div>
                        <div class="text-end">
                            <strong class="text-primary" style="font-size: 1.1rem;">₱{{ number_format($item->subtotal, 2) }}</strong>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Shipping Information -->
            <div class="detail-card reveal" style="animation-delay: 0.1s;">
                <div class="detail-card-header">
                    <div class="detail-card-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h3 class="detail-card-title">Shipping Information</h3>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Address:</div>
                    <div class="info-value">{{ $order->shipping_address }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">City:</div>
                    <div class="info-value">{{ $order->shipping_city }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Province:</div>
                    <div class="info-value">{{ $order->shipping_province }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Postal Code:</div>
                    <div class="info-value">{{ $order->shipping_postal_code }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Phone:</div>
                    <div class="info-value">{{ $order->shipping_phone }}</div>
                </div>
                @if($order->shipping_notes)
                    <div class="info-row">
                        <div class="info-label">Notes:</div>
                        <div class="info-value">{{ $order->shipping_notes }}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Order Summary -->
            <div class="summary-card reveal" style="animation-delay: 0.2s;">
                <h5 class="fw-bold mb-4"><i class="bi bi-receipt me-2"></i>Order Summary</h5>
                
                <div class="summary-row">
                    <span class="text-muted">Subtotal:</span>
                    <span class="fw-semibold">₱{{ number_format($order->total_amount, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span class="text-muted">Shipping:</span>
                    <span class="fw-semibold">₱{{ number_format($order->shipping_fee, 2) }}</span>
                </div>
                <hr class="my-3">
                <div class="summary-row">
                    <span class="fw-bold">Total:</span>
                    <span class="summary-total">₱{{ number_format($order->total, 2) }}</span>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Status:</span>
                            <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">
                                {{ $order->getStatusLabel() }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Payment:</span>
                            <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </div>
                    </div>

                    @if($order->tracking_number)
                        <div class="mb-3 p-3 bg-light rounded">
                            <div class="text-muted small mb-1">Tracking Number:</div>
                            <code class="fw-bold" style="font-size: 0.875rem;">{{ $order->tracking_number }}</code>
                        </div>
                    @endif

                    <a href="{{ route('orders.tracking', $order->id) }}" class="btn btn-primary w-100">
                        <i class="bi bi-truck me-2"></i>Track Order
                    </a>
                </div>
            </div>

            <!-- Seller Information -->
            <div class="detail-card reveal" style="animation-delay: 0.3s;">
                <div class="detail-card-header">
                    <div class="detail-card-icon">
                        <i class="bi bi-shop"></i>
                    </div>
                    <h3 class="detail-card-title">Seller</h3>
                </div>
                <h6 class="fw-bold mb-2">{{ $order->seller->business_name }}</h6>
                <p class="text-muted mb-0">
                    <i class="bi bi-geo-alt me-1"></i>{{ $order->seller->city }}, {{ $order->seller->province }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
