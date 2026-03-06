@extends('layouts.toyshop')

@section('title', 'Payment - ' . $subscription->plan->name)

@section('content')
<div class="container py-5">
    <h2 class="mb-4">Complete Payment</h2>
    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ $subscription->plan->name }} - ₱{{ number_format($subscription->plan->price, 2) }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('membership.process-payment', $subscription) }}" method="POST" id="paymentForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="qrph" value="qrph" checked>
                                <label class="form-check-label" for="qrph">QR Ph (PayMongo)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal">
                                <label class="form-check-label" for="paypal">PayPal (Sandbox)</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Proceed to Payment</button>
                    </form>
                </div>
            </div>
            <a href="{{ route('membership.cancel-pending', $subscription) }}" class="text-danger" onclick="return confirm('Cancel this subscription?');">Cancel</a>
        </div>
    </div>
</div>
@endsection
