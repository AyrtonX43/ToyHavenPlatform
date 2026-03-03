@extends('layouts.toyshop')

@push('styles')
<style>
    .confirm-delivery-actions {
        background: #fff;
        padding: 1.25rem 0 0;
        margin-top: 1.5rem;
        border-top: 2px solid #0d6efd;
    }
    @media (max-width: 575px) {
        .confirm-delivery-actions .d-flex { flex-direction: column; }
        .confirm-delivery-actions .btn { width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="container py-4 pb-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
        <div class="mb-4">
            <a href="{{ route('orders.show', $order->id) }}" class="text-primary text-decoration-none d-inline-flex align-items-center">
                <i class="bi bi-arrow-left me-1"></i>Back to Order
            </a>
        </div>

        <h1 class="h3 fw-bold mb-4">Confirm Delivery</h1>
        
        <div class="card shadow-sm mb-4">
            <div class="card-body">
            <h2 class="h5 fw-semibold mb-2">Order #{{ $order->order_number }}</h2>
            <p class="text-muted small mb-4">Please upload a photo as proof of delivery</p>
            
            <div class="border-top pt-3">
                <h3 class="h6 fw-semibold mb-2">Order Items:</h3>
                @foreach($order->items as $item)
                <div class="d-flex align-items-center py-2">
                    <div class="flex-grow-1">
                        <p class="fw-medium mb-0">{{ $item->product_name }}</p>
                        <p class="small text-muted mb-0">Quantity: {{ $item->quantity }}</p>
                    </div>
                    <p class="fw-semibold mb-0">₱{{ number_format($item->subtotal, 2) }}</p>
                </div>
                @endforeach
            </div>
            </div>
        </div>

        <form action="{{ route('orders.confirm-delivery.store', $order->id) }}" method="POST" enctype="multipart/form-data" class="card shadow-sm">
        <div class="card-body">
            @csrf
            
            <div class="mb-4">
                <label class="form-label fw-semibold">Proof of Delivery Photo <span class="text-danger">*</span></label>
                <input type="file" name="proof_image" accept="image/*" required class="form-control">
                <p class="small text-muted mt-1">Upload a clear photo showing the delivered package (max 5MB)</p>
                @error('proof_image')
                    <p class="text-danger small mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Notes (Optional)</label>
                <textarea name="notes" rows="3" class="form-control" placeholder="Any additional comments about the delivery..."></textarea>
                <p class="small text-muted mt-1">Maximum 500 characters</p>
                @error('notes')
                    <p class="text-danger small mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="alert alert-info mb-4">
                <strong>Note:</strong> By confirming delivery, you acknowledge that you have received the order in good condition. 
                You will be able to review the product after confirmation.
            </div>

            <div class="confirm-delivery-actions">
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-success btn-lg px-4 py-3">
                        <i class="bi bi-check2-circle me-2"></i>Confirm Delivery
                    </button>
                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-outline-secondary btn-lg px-4 py-3">
                        <i class="bi bi-x-lg me-2"></i>Cancel
                    </a>
                </div>
            </div>
        </div>
        </form>
        </div>
    </div>
</div>
@endsection
