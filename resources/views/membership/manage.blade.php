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
    .plan-badge.vip { background: linear-gradient(135deg, #f59e0b, #eab308); }
    .analytics-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1rem; }
    .analytics-card {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1rem;
        text-align: center;
        border: 1px solid #e2e8f0;
    }
    .analytics-card .value { font-size: 1.5rem; font-weight: 800; color: #0891b2; }
    .analytics-card .label { font-size: 0.85rem; color: #64748b; margin-top: 0.25rem; }
    .upgrade-plan-card {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .upgrade-plan-card:hover { border-color: #0891b2; background: #f0fdfa; }
    .unsubscribe-modal .modal-content { border-radius: 16px; }
</style>
@endpush

@section('content')
<div class="container py-4">
    <h1 class="h2 fw-bold mb-4"><i class="bi bi-gem me-2"></i>Manage Membership</h1>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show">
            <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($subscription && $subscription->isActive())
        @php $plan = $subscription->plan; @endphp

        <div class="manage-card">
            <h3 class="h5 fw-bold mb-3">Current Plan</h3>
            <p class="mb-2">
                <span class="plan-badge {{ $plan->slug === 'vip' ? 'vip' : '' }}">{{ $plan->name }}</span>
            </p>
            <p class="text-muted mb-1">
                ₱{{ number_format($plan->price, 0) }} / {{ $plan->interval === 'monthly' ? 'month' : 'year' }}
            </p>
            <p class="text-muted small mb-3">
                @if($subscription->current_period_end)
                    Current period ends: {{ $subscription->current_period_end->format('F j, Y') }}
                @endif
            </p>
            @if($plan->description)
                <p class="mb-3">{{ $plan->description }}</p>
            @endif
            @if(!empty($plan->features))
                <h6 class="fw-bold mb-2">Plan benefits</h6>
                <ul class="mb-0">
                    @foreach($plan->features as $f)
                        <li>{{ $f }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        @if(isset($analytics))
        <div class="manage-card">
            <h3 class="h5 fw-bold mb-3">Benefit usage this period</h3>
            <p class="text-muted small mb-3">
                {{ $analytics['period_start']->format('M j, Y') }} – {{ $analytics['period_end']->format('M j, Y') }}
            </p>
            <div class="analytics-grid">
                <div class="analytics-card">
                    <div class="value">{{ $analytics['auction_bids'] }}</div>
                    <div class="label">Auction bids</div>
                </div>
                <div class="analytics-card">
                    <div class="value">{{ $analytics['auction_wins'] }}</div>
                    <div class="label">Auction wins</div>
                </div>
                @if($plan->canCreateAuction())
                <div class="analytics-card">
                    <div class="value">{{ $analytics['active_auction_listings'] }}</div>
                    <div class="label">Active listings</div>
                </div>
                @endif
                <div class="analytics-card">
                    <div class="value">{{ $analytics['toyshop_orders_count'] }}</div>
                    <div class="label">Toyshop orders</div>
                </div>
                <div class="analytics-card">
                    <div class="value">₱{{ number_format($analytics['toyshop_discount_saved'], 0) }}</div>
                    <div class="label">Discount saved</div>
                </div>
            </div>
        </div>
        @endif

        @if($plans->count() > 1)
        <div class="manage-card">
            <h3 class="h5 fw-bold mb-3">Change plan</h3>
            <p class="text-muted small mb-3">Upgrade or switch to another plan. Your current plan will be replaced after payment.</p>
            @foreach($plans as $p)
                @if($p->id !== $plan->id)
                <div class="upgrade-plan-card">
                    <div>
                        <strong>{{ $p->name }}</strong>
                        <span class="text-muted ms-2">₱{{ number_format($p->price, 0) }}/{{ $p->interval === 'monthly' ? 'mo' : 'yr' }}</span>
                    </div>
                    <a href="{{ route('membership.upgrade', $p->slug) }}" class="btn btn-primary btn-sm">Upgrade to {{ $p->name }}</a>
                </div>
                @endif
            @endforeach
        </div>
        @endif

        <div class="manage-card">
            <h3 class="h5 fw-bold mb-2">Cancel subscription</h3>
            <p class="text-muted small mb-3">You will keep access until the end of your current billing period.</p>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#unsubscribeModal">
                <i class="bi bi-x-circle me-1"></i>Unsubscribe
            </button>
        </div>

        <div class="modal fade unsubscribe-modal" id="unsubscribeModal" tabindex="-1" aria-labelledby="unsubscribeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="unsubscribeModalLabel">
                            <i class="bi bi-exclamation-triangle text-warning me-2"></i>Unsubscribe?
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">Are you sure you want to cancel your <strong>{{ $plan->name }}</strong> membership?</p>
                        <p class="text-muted small mb-0">You will keep access until <strong>{{ $subscription->current_period_end?->format('F j, Y') }}</strong>. After that, you will no longer have access to membership benefits (auctions, discounts, etc.).</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Keep subscription</button>
                        <form action="{{ route('membership.cancel') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger">Yes, unsubscribe</button>
                        </form>
                    </div>
                </div>
            </div>
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
                <i class="bi bi-gem me-1"></i>View plans
            </a>
        </div>
    @endif
</div>
@endsection
