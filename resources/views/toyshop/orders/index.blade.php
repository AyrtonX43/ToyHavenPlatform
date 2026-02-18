@extends('layouts.toyshop')

@section('title', 'My Orders - ToyHaven')

@push('styles')
<style>
    .orders-header {
        background: white;
        border-radius: 16px;
        padding: 1.5rem 2rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
    }
    
    .order-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }
    
    .order-card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }
    
    .order-card.pending { border-left-color: #f59e0b; }
    .order-card.processing { border-left-color: #3b82f6; }
    .order-card.shipped { border-left-color: #8b5cf6; }
    .order-card.delivered { border-left-color: #10b981; }
    .order-card.cancelled { border-left-color: #ef4444; }
    
    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .order-number {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2d2a26;
    }
    
    .order-date {
        color: #64748b;
        font-size: 0.875rem;
    }
    
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
    }
    
    .order-items {
        margin-bottom: 1rem;
    }
    
    .order-item-mini {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .order-item-mini:last-child {
        border-bottom: none;
    }
    
    .order-item-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #e5e7eb;
    }
    
    .order-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1rem;
        border-top: 2px solid #e5e7eb;
    }
    
    .order-total {
        font-size: 1.25rem;
        font-weight: 700;
        color: #ff6b6b;
    }
    
    .empty-orders {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }
    
    .empty-orders-icon {
        font-size: 5rem;
        color: #cbd5e1;
        margin-bottom: 1.5rem;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="orders-header reveal">
        <h2 class="fw-bold mb-0"><i class="bi bi-box-seam me-2"></i>My Orders</h2>
    </div>

    @if($orders->count() > 0)
        @foreach($orders as $order)
            <div class="order-card reveal {{ $order->status }}" style="animation-delay: {{ min($loop->index, 5) * 0.1 }}s;">
                <div class="order-header">
                    <div>
                        <div class="order-number">Order #{{ $order->order_number }}</div>
                        <div class="order-date">
                            <i class="bi bi-calendar me-1"></i>{{ $order->created_at->format('M d, Y h:i A') }}
                        </div>
                    </div>
                    <div>
                        <span class="status-badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">
                            {{ $order->getStatusLabel() }}
                        </span>
                    </div>
                </div>
                
                <div class="order-items">
                    <div class="mb-2">
                        <strong class="text-muted">
                            <i class="bi bi-shop me-1"></i>{{ $order->seller->business_name }}
                        </strong>
                    </div>
                    @foreach($order->items->take(3) as $item)
                        <div class="order-item-mini">
                            @if($item->product && $item->product->images->first())
                                <img src="{{ asset('storage/' . $item->product->images->first()->image_path) }}" 
                                     class="order-item-image" 
                                     alt="{{ $item->product_name }}">
                            @else
                                <div class="order-item-image bg-light d-flex align-items-center justify-content-center">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $item->product_name }}</div>
                                <small class="text-muted">Qty: {{ $item->quantity }} × ₱{{ number_format($item->price, 2) }}</small>
                            </div>
                        </div>
                    @endforeach
                    @if($order->items->count() > 3)
                        <div class="text-muted text-center mt-2">
                            <small>+{{ $order->items->count() - 3 }} more item(s)</small>
                        </div>
                    @endif
                </div>
                
                <div class="order-footer">
                    <div>
                        <span class="text-muted">Total: </span>
                        <span class="order-total">₱{{ number_format($order->total, 2) }}</span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye me-1"></i>View Details
                        </a>
                        <a href="{{ route('orders.tracking', $order->id) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-truck me-1"></i>Track Order
                        </a>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="d-flex justify-content-center mt-4">
            {{ $orders->links() }}
        </div>
    @else
        <div class="empty-orders reveal">
            <i class="bi bi-inbox empty-orders-icon"></i>
            <h4 class="fw-bold mb-2">No orders yet</h4>
            <p class="text-muted mb-4">Start shopping to see your orders here!</p>
            <a href="{{ route('toyshop.products.index') }}" class="btn btn-primary btn-lg">
                <i class="bi bi-bag me-2"></i>Browse Products
            </a>
        </div>
    @endif
</div>
@endsection
