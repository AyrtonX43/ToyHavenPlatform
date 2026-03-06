@extends('layouts.toyshop')

@section('title', 'Payment - ' . $subscription->plan->name)

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('membership.index') }}">Membership</a></li>
            <li class="breadcrumb-item"><a href="{{ route('membership.checkout', $subscription->plan->slug) }}">Checkout</a></li>
            <li class="breadcrumb-item active">Payment</li>
        </ol>
    </nav>

    <h2 class="mb-4">Complete Payment</h2>

    @if(session('error'))
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>
                {{ session('error') }}
                <br><small>Select a payment method below to try again.</small>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">{{ $subscription->plan->name }} - ₱{{ number_format($subscription->plan->price, 2) }}</h5>
                </div>
                <div class="card-body">
                    @if(empty($client_key))
                    <h6 class="fw-bold mb-3">Select Payment Method</h6>
                    <form action="{{ route('membership.process-payment', $subscription) }}" method="POST" id="paymentForm">
                        @csrf
                        <div class="mb-4">
                            @php $savedMethod = session('membership_payment_method', 'qrph'); @endphp
                            <div class="form-check p-3 border rounded mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" id="qrph" value="qrph" {{ $savedMethod === 'qrph' ? 'checked' : '' }}>
                                <label class="form-check-label w-100" for="qrph">
                                    <strong>QR Ph (PayMongo)</strong>
                                    <br><small class="text-muted">Scan QR code with your e-wallet or banking app</small>
                                </label>
                            </div>
                            <div class="form-check p-3 border rounded">
                                <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal" {{ $savedMethod === 'paypal' ? 'checked' : '' }}>
                                <label class="form-check-label w-100" for="paypal">
                                    <strong>PayPal</strong>
                                    <br><small class="text-muted">Pay with your PayPal account</small>
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg">Proceed to Payment</button>
                    </form>
                    @else
                    <div class="mb-3">
                        <span class="badge bg-success">QR Ph</span>
                        <p class="text-muted small mb-0 mt-1">Scan the QR code below with your e-wallet or banking app to complete payment.</p>
                    </div>
                    <div id="paymongo-container" class="mb-4"></div>
                    <a href="{{ route('membership.payment', $subscription) }}" class="btn btn-outline-secondary btn-sm">Choose different payment method</a>
                    @endif
                </div>
            </div>
            <button type="button" class="btn btn-link text-danger p-0" data-bs-toggle="modal" data-bs-target="#cancelModal">
                <i class="bi bi-x-circle me-1"></i> Cancel and return to membership plans
            </button>
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
                Are you sure you want to cancel? You will return to the membership plans page.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep Payment</button>
                <a href="{{ route('membership.cancel-pending', $subscription) }}" class="btn btn-danger">Yes, Cancel</a>
            </div>
        </div>
    </div>
</div>
@endsection
