@extends('layouts.toyshop')

@section('title', 'Checkout - ' . $plan->name)

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auctions.index') }}">Auctions</a></li>
            <li class="breadcrumb-item"><a href="{{ route('membership.index') }}">Membership</a></li>
            <li class="breadcrumb-item active">{{ $plan->name }} Checkout</li>
        </ol>
    </nav>

    <h2 class="mb-4">Complete Your Membership</h2>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">{{ $plan->name }} - ₱{{ number_format($plan->price, 0) }}/mo</h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Terms & Conditions</h6>
                    <div class="terms-content border rounded p-3 mb-4" style="max-height: 280px; overflow-y: auto;">
                        @include('membership.terms-content')
                    </div>
                    <form action="{{ route('membership.subscribe') }}" method="POST" id="checkoutForm">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="terms_accepted" id="termsAccepted" value="1" required>
                            <label class="form-check-label" for="termsAccepted">
                                I have read and agree to the Terms & Conditions
                            </label>
                        </div>

                        <h6 class="fw-bold mb-3">Payment Method</h6>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="qrph" value="qrph" checked required>
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
        </div>
    </div>
</div>
@endsection
