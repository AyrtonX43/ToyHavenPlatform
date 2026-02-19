@extends('layouts.toyshop')

@section('title', 'Manage Membership - ToyHaven')

@push('styles')
<style>
    .manage-card {
        background: #fff;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 2px solid #e2e8f0;
        margin-bottom: 1.5rem;
    }
    .plan-badge {
        display: inline-block;
        background: linear-gradient(135deg, #0891b2, #06b6d4);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 700;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <h1 class="h2 fw-bold mb-4"><i class="bi bi-person-badge me-2"></i>Manage Membership</h1>

    @if($subscription && $subscription->isActive())
        <div class="manage-card">
            <h3 class="h5 fw-bold mb-3">Current Plan</h3>
            <p class="mb-2">
                <span class="plan-badge">{{ $subscription->plan->name }}</span>
            </p>
            <p class="text-muted mb-2">
                @if($subscription->current_period_end)
                    Renews / Expires: {{ $subscription->current_period_end->format('F j, Y') }}
                @endif
            </p>
            <form action="{{ route('membership.cancel') }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel your subscription?');">
                @csrf
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-x-circle me-1"></i>Cancel Subscription
                </button>
            </form>
        </div>
    @elseif($subscription && $subscription->isCancelled())
        <div class="manage-card">
            <p class="text-muted mb-2">Your subscription has been cancelled.</p>
            <p class="mb-3">
                @if($subscription->current_period_end && $subscription->current_period_end->isFuture())
                    You retain access until {{ $subscription->current_period_end->format('F j, Y') }}.
                @endif
            </p>
            <a href="{{ route('membership.index') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                <i class="bi bi-arrow-repeat me-1"></i>Resubscribe
            </a>
        </div>
    @else
        <div class="manage-card">
            <p class="text-muted mb-3">You don't have an active membership. Join to access auctions and premium perks.</p>
            <a href="{{ route('membership.index') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                <i class="bi bi-gem me-1"></i>View Plans
            </a>
        </div>
    @endif
</div>
@endsection
