@extends('layouts.toyshop')

@section('title', 'Membership Plans - ToyHaven')

@push('styles')
<style>
    .membership-hero {
        background: linear-gradient(135deg, #0891b2 0%, #06b6d4 50%, #fb923c 100%);
        border-radius: 20px;
        padding: 3rem;
        color: white;
        text-align: center;
        margin-bottom: 2.5rem;
        box-shadow: 0 8px 32px rgba(8, 145, 178, 0.3);
    }
    .membership-hero h1 {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
    }
    .membership-hero p {
        opacity: 0.95;
        font-size: 1.1rem;
    }
    .plan-card {
        background: #fff;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 2px solid #e2e8f0;
        height: 100%;
        transition: all 0.3s ease;
    }
    .plan-card:hover {
        border-color: #0891b2;
        box-shadow: 0 8px 32px rgba(8, 145, 178, 0.15);
        transform: translateY(-4px);
    }
    .plan-card.featured {
        border-color: #0891b2;
        border-width: 3px;
        position: relative;
    }
    .plan-card.featured::before {
        content: 'Popular';
        position: absolute;
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, #0891b2, #06b6d4);
        color: white;
        padding: 0.25rem 1rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .plan-card.vip-plan {
        border-color: #f59e0b;
        border-width: 3px;
        position: relative;
        background: linear-gradient(180deg, #fffbeb 0%, #fff 30%);
    }
    .plan-card.vip-plan::before {
        content: 'Best Value';
        position: absolute;
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, #f59e0b, #eab308);
        color: white;
        padding: 0.25rem 1rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .plan-card.vip-plan:hover {
        border-color: #d97706;
        box-shadow: 0 8px 32px rgba(245, 158, 11, 0.2);
    }
    .vip-auction-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #92400e;
        padding: 0.4rem 0.75rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-top: 0.75rem;
        border: 1px solid #fcd34d;
    }
    .plan-price {
        font-size: 2.5rem;
        font-weight: 800;
        color: #0891b2;
    }
    .plan-price span {
        font-size: 1rem;
        font-weight: 600;
        color: #64748b;
    }
    .plan-features {
        list-style: none;
        padding: 0;
        margin: 1.5rem 0;
    }
    .plan-features li {
        padding: 0.5rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
    }
    .plan-features li i {
        color: #10b981;
        font-size: 1.1rem;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="membership-hero reveal">
        <h1><i class="bi bi-gem me-2"></i>Join ToyHaven Membership</h1>
        <p>Access exclusive auctions, place bids on rare collectibles, and enjoy premium perks across our platform.</p>
        @if($intent === 'auction')
            <p class="mb-0"><strong>Join now to start bidding on our active auctions!</strong></p>
        @endif
        @if($activeAuctionsCount > 0)
            <p class="mb-0 mt-2"><i class="bi bi-hammer me-1"></i> {{ $activeAuctionsCount }} active auction(s) right now</p>
        @endif
    </div>

    <div class="row g-4">
        @foreach($plans as $plan)
            <div class="col-lg-4">
                <div class="plan-card {{ $plan->slug === 'pro' ? 'featured' : '' }} {{ $plan->slug === 'vip' ? 'vip-plan' : '' }} reveal">
                    <h3 class="h4 fw-bold mb-2">{{ $plan->name }}</h3>
                    <p class="text-muted small mb-3">{{ $plan->description }}</p>
                    <div class="plan-price mb-3">
                        ₱{{ number_format($plan->price, 0) }}
                        <span>/{{ $plan->interval === 'monthly' ? 'mo' : 'yr' }}</span>
                    </div>
                    <ul class="plan-features">
                        @foreach($plan->features ?? [] as $feature)
                            <li><i class="bi bi-check-circle-fill"></i> {{ $feature }}</li>
                        @endforeach
                    </ul>
                    @if($plan->canCreateAuction())
                        <div class="vip-auction-badge">
                            <i class="bi bi-hammer"></i> Auction your own products
                        </div>
                    @endif
                    @auth
                        @if(auth()->user()->hasActiveMembership() && auth()->user()->currentPlan()?->id === $plan->id)
                            <button class="btn btn-outline-secondary w-100 mt-3" disabled>Current Plan</button>
                        @else
                            <form action="{{ route('membership.subscribe') }}" method="POST" class="mt-3">
                                @csrf
                                <input type="hidden" name="plan" value="{{ $plan->slug }}">
                                @if($plan->slug === 'vip')
                                    <button type="submit" class="btn btn-warning w-100 fw-bold" style="background: linear-gradient(135deg, #f59e0b, #eab308); border: none; color: white;">
                                        Get {{ $plan->name }}
                                    </button>
                                @elseif($plan->slug === 'pro')
                                    <button type="submit" class="btn btn-primary w-100" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                                        Get {{ $plan->name }}
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-outline-primary w-100">
                                        Get {{ $plan->name }}
                                    </button>
                                @endif
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login', ['redirect' => route('membership.index')]) }}" class="btn btn-primary w-100 mt-3" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                            Sign in to Subscribe
                        </a>
                    @endauth
                </div>
            </div>
        @endforeach
    </div>

    @if($plans->isEmpty())
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle me-2"></i> Membership plans are being set up. Please check back soon.
        </div>
    @endif
</div>
@endsection
