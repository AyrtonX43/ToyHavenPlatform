@extends('layouts.toyshop')

@section('title', 'Pay with PayPal - ' . $plan->name)

@push('styles')
<style>
    .paypal-hero {
        background: linear-gradient(135deg, #003087 0%, #009cde 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 20px;
        box-shadow: 0 8px 30px rgba(0, 48, 135, 0.25);
    }
    .paypal-form-card {
        border: 2px solid #e2e8f0;
        border-radius: 20px;
        overflow: hidden;
        max-width: 480px;
        margin: 0 auto;
    }
</style>
@endpush

@section('content')
<div class="paypal-hero">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 opacity-90">
                <li class="breadcrumb-item"><a href="{{ route('membership.index') }}" class="text-white-50">Membership</a></li>
                <li class="breadcrumb-item"><a href="{{ route('membership.payment-selection', $plan->slug) }}" class="text-white-50">Payment</a></li>
                <li class="breadcrumb-item active text-white">PayPal</li>
            </ol>
        </nav>
        <h2 class="mt-3 mb-1 fw-bold"><i class="bi bi-paypal me-2"></i>Pay with PayPal</h2>
        <p class="mb-0 opacity-90">{{ $plan->name }} — ₱{{ number_format($plan->price, 0) }}/mo</p>
    </div>
</div>

<div class="container py-4">
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="paypal-form-card card border-0 shadow-sm">
        <div class="card-header py-3" style="background: #003087; color: white;">
            <h5 class="mb-0 fw-bold">Payment Details</h5>
            <small class="opacity-90">Enter your PayPal account information</small>
        </div>
        <div class="card-body p-4">
            <form id="paypal-demo-form" action="{{ route('membership.paypal.demo-pay') }}" method="POST">
                @csrf
                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Name on Account <span class="text-danger">*</span></label>
                    <input type="text" name="paypal_demo_name" class="form-control form-control-lg" placeholder="Full name as on PayPal account" value="{{ auth()->user()?->name }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">PayPal Email <span class="text-danger">*</span></label>
                    <input type="email" name="paypal_demo_email" class="form-control form-control-lg" placeholder="your@paypal.com" required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Confirm PayPal Email <span class="text-danger">*</span></label>
                    <input type="email" name="paypal_demo_email_confirm" class="form-control form-control-lg" placeholder="Confirm your PayPal email" required>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-semibold">
                        <i class="bi bi-paypal me-2"></i>Pay ₱{{ number_format($plan->price, 0) }}
                    </button>
                    <a href="{{ route('membership.payment-selection', $plan->slug) }}" class="btn btn-outline-secondary rounded-3">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var form = document.getElementById('paypal-demo-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            var email = form.querySelector('input[name="paypal_demo_email"]').value;
            var confirm = form.querySelector('input[name="paypal_demo_email_confirm"]').value;
            if (email !== confirm) {
                e.preventDefault();
                alert('PayPal email and confirmation do not match. Please check and try again.');
                return false;
            }
        });
    }
})();
</script>
@endpush
@endsection
