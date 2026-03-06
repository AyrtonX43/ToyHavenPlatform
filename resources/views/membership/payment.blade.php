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
                    @if(empty($client_key))
                    <form action="{{ route('membership.process-payment', $subscription) }}" method="POST" id="paymentForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            @php $savedMethod = session('membership_payment_method', 'qrph'); @endphp
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="qrph" value="qrph" {{ $savedMethod === 'qrph' ? 'checked' : '' }}>
                                <label class="form-check-label" for="qrph">QR Ph (PayMongo)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal" {{ $savedMethod === 'paypal' ? 'checked' : '' }}>
                                <label class="form-check-label" for="paypal">PayPal (Sandbox)</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Proceed to Payment</button>
                    </form>
                    @else
                    <div id="paymongo-container"></div>
                    @endif
                </div>
            </div>
            <button type="button" class="btn btn-link text-danger p-0" data-bs-toggle="modal" data-bs-target="#cancelModal">Cancel and return to membership</button>
        </div>
    </div>
</div>

<!-- Cancel confirmation modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Cancel Membership</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to cancel? You will return to the auction membership selection page.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep Payment</button>
                <a href="{{ route('membership.cancel-pending', $subscription) }}" class="btn btn-danger">Yes, Cancel</a>
            </div>
        </div>
    </div>
</div>
@endsection
