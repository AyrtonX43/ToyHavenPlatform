@extends('layouts.toyshop')

@section('title', 'Select Payment - ' . $plan->name)

@section('content')
<div class="container py-5">
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auctions.index') }}">Auctions</a></li>
            <li class="breadcrumb-item"><a href="{{ route('membership.checkout', $plan->slug) }}">Terms</a></li>
            <li class="breadcrumb-item active">Payment</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-success text-white py-3">
                    <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Select Payment Method</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="text-muted mb-1">Plan</h6>
                        <h4 class="mb-0">{{ $plan->name }} - ₱{{ number_format($plan->price, 0) }}/mo</h4>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <form action="{{ route('membership.subscribe') }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <input type="hidden" name="terms_accepted" value="1">
                                <input type="hidden" name="payment_method" value="qrph">
                                <div class="card h-100 border-2 hover-shadow" style="cursor:pointer" onclick="this.closest('form').submit()">
                                    <div class="card-body text-center py-4">
                                        <i class="bi bi-qr-code-scan display-4 text-success mb-3"></i>
                                        <h5 class="card-title">QR Ph</h5>
                                        <p class="card-text text-muted small">Scan with GCash, Maya, or banking app</p>
                                        <button type="submit" class="btn btn-success w-100">Pay with QR Ph</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form action="{{ route('membership.subscribe') }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <input type="hidden" name="terms_accepted" value="1">
                                <input type="hidden" name="payment_method" value="paypal">
                                <div class="card h-100 border-2 hover-shadow" style="cursor:pointer" onclick="this.closest('form').submit()">
                                    <div class="card-body text-center py-4">
                                        <i class="bi bi-paypal display-4 text-primary mb-3"></i>
                                        <h5 class="card-title">PayPal</h5>
                                        <p class="card-text text-muted small">Pay with your PayPal account</p>
                                        <button type="submit" class="btn btn-primary w-100">Pay with PayPal</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="mt-4 text-center">
                        <a href="{{ route('membership.checkout', $plan->slug) }}" class="text-muted"><i class="bi bi-arrow-left me-1"></i>Back to Terms</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.hover-shadow:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
</style>
@endsection
