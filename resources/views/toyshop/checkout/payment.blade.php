@extends('layouts.toyshop')

@section('title', 'Payment - ToyHaven')

@push('styles')
<style>
    .payment-header { background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%); color: white; border-radius: 14px; padding: 1.5rem 2rem; margin-bottom: 1.5rem; }
    .form-control:focus { border-color: #0891b2; box-shadow: 0 0 0 3px rgba(8,145,178,0.15); }
    .btn-primary { background: linear-gradient(135deg, #0891b2, #06b6d4); border: none; }
    .btn-primary:hover { background: linear-gradient(135deg, #0e7490, #0891b2); border: none; }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0" style="border-radius: 14px; overflow: hidden;">
                <div class="card-header payment-header border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-credit-card-2-front me-2"></i>Complete Payment</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <h6 class="mb-1">Order Number: {{ $order->order_number }}</h6>
                        <p class="mb-0">Total Amount: <strong>₱{{ number_format($order->total, 2) }}</strong></p>
                    </div>

                    <div class="alert alert-warning">
                        <p class="mb-0">Online payment is not currently configured. Please contact support to arrange payment, or view your order details below.</p>
                    </div>

                    <hr class="my-4">
                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-primary w-100">
                        <i class="bi bi-receipt me-2"></i>View Order Details
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
