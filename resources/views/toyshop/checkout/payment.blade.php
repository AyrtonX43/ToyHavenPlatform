@extends('layouts.toyshop')

@section('title', 'Payment - ToyHaven')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Complete Payment</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6>Order Number: {{ $order->order_number }}</h6>
                        <p class="mb-0">Total Amount: <strong>₱{{ number_format($order->total, 2) }}</strong></p>
                    </div>

                    <p class="text-muted">Payment method: <strong>{{ ucfirst($order->payment_method) }}</strong></p>

                    <!-- PayMongo Payment Integration -->
                    <div id="paymongo-payment-form">
                        <!-- This will be integrated with PayMongo payment form -->
                        <div class="alert alert-warning">
                            <p>PayMongo payment integration will be implemented here.</p>
                            <p>For now, you can proceed with a test payment.</p>
                        </div>

                        <!-- Temporary: Mark as paid for testing -->
                        <form action="{{ route('checkout.callback') }}" method="POST" class="mt-3">
                            @csrf
                            <input type="hidden" name="order_number" value="{{ $order->order_number }}">
                            <button type="submit" class="btn btn-primary w-100">Complete Payment (Test)</button>
                        </form>
                    </div>

                    <hr class="my-4">

                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-outline-secondary w-100">View Order Details</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
