@extends('layouts.toyshop')

@section('title', 'Complete Subscription Payment - ToyHaven')

@push('styles')
<style>
    .payment-order-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 2px solid #e2e8f0;
        overflow: hidden;
    }
    .payment-plan-header {
        background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
        color: white;
        padding: 1.5rem 2rem;
    }
    .payment-plan-header h2 {
        margin: 0;
        font-weight: 700;
        font-size: 1.5rem;
    }
    .payment-plan-header .plan-badge {
        display: inline-block;
        background: rgba(255,255,255,0.25);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-top: 0.5rem;
    }
    .benefits-section {
        padding: 1.5rem 2rem;
    }
    .benefits-section h4 {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .benefits-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .benefits-list li {
        padding: 0.6rem 0;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        font-size: 0.95rem;
        color: #475569;
        border-bottom: 1px solid #f1f5f9;
    }
    .benefits-list li:last-child {
        border-bottom: none;
    }
    .benefits-list li i {
        color: #10b981;
        font-size: 1.1rem;
        flex-shrink: 0;
        margin-top: 0.1rem;
    }
    .order-summary-box {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        margin-top: 1.5rem;
    }
    .order-summary-box .price-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 1.1rem;
    }
    .order-summary-box .price-row .total {
        font-weight: 800;
        color: #0891b2;
        font-size: 1.35rem;
    }
    .payment-actions {
        padding: 1.5rem 2rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="payment-order-card mb-4">
                {{-- Plan header --}}
                <div class="payment-plan-header">
                    <h2><i class="bi bi-gem me-2"></i>{{ $subscription->plan->name }} Membership</h2>
                    <span class="plan-badge">{{ $subscription->plan->interval === 'monthly' ? 'Monthly billing' : 'Annual billing' }}</span>
                </div>

                {{-- Purchase description --}}
                <div class="benefits-section">
                    <h4><i class="bi bi-receipt text-primary"></i> What you're purchasing</h4>
                    <p class="mb-0 text-muted">
                        {{ $subscription->plan->description }}
                    </p>
                    <p class="mt-2 mb-0 small text-muted">
                        You're subscribing to <strong>{{ $subscription->plan->name }}</strong> at
                        <strong>₱{{ number_format($subscription->plan->price, 0) }}/{{ $subscription->plan->interval === 'monthly' ? 'month' : 'year' }}</strong>.
                        {{ $subscription->plan->interval === 'monthly' ? 'Billed monthly. Cancel anytime.' : 'Billed annually. Cancel anytime.' }}
                    </p>
                </div>

                {{-- Benefits elaboration --}}
                <div class="benefits-section pt-0">
                    <h4><i class="bi bi-stars text-warning"></i> Benefits you'll get</h4>
                    <ul class="benefits-list">
                        @foreach($subscription->plan->features ?? [] as $feature)
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                        @if(($subscription->plan->features ?? []) === [])
                            <li><i class="bi bi-check-circle-fill"></i> Access to auctions and bidding</li>
                            <li><i class="bi bi-check-circle-fill"></i> Member badge on your profile</li>
                        @endif
                    </ul>
                </div>

                {{-- Order summary --}}
                <div class="benefits-section pt-0">
                    <div class="order-summary-box">
                        <div class="price-row mb-1">
                            <span>{{ $subscription->plan->name }} ({{ $subscription->plan->interval === 'monthly' ? 'per month' : 'per year' }})</span>
                            <span class="total">₱{{ number_format($subscription->plan->price, 0) }}</span>
                        </div>
                        <p class="mb-0 small text-muted mt-2">
                            {{ $subscription->plan->interval === 'monthly' ? 'First payment today. Then charged monthly.' : 'First payment today. Then charged yearly.' }}
                        </p>
                    </div>
                </div>

                {{-- Payment section --}}
                <div class="payment-actions">
                    <p class="text-muted small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Online payment is not currently configured. Please contact support to arrange payment for your subscription.
                    </p>
                    <a href="{{ route('membership.manage') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                        <i class="bi bi-gear me-1"></i>Back to Membership
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
