@extends('layouts.toyshop')

@section('title', 'Complete Subscription Payment - ToyHaven')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">Complete Your Subscription</h2>
                    <p class="text-muted mb-4">
                        You're subscribing to <strong>{{ $subscription->plan->name }}</strong> (₱{{ number_format($subscription->plan->price, 0) }}/{{ $subscription->plan->interval === 'monthly' ? 'month' : 'year' }}).
                    </p>
                    @if($paymentIntentId && $publicKey)
                        <p class="text-muted small">
                            Payment integration with PayMongo will be completed here. For now, your subscription has been created. Please contact support if you need to complete payment.
                        </p>
                    @endif
                    <a href="{{ route('membership.manage') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                        <i class="bi bi-arrow-left me-1"></i>Back to Membership
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
