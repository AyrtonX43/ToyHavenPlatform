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
    .analytics-card { border-left: 4px solid #0891b2; }
</style>
@endpush

@section('content')
<div class="container py-4">
    <h1 class="h2 fw-bold mb-4"><i class="bi bi-person-badge me-2"></i>Manage Membership</h1>

    @if($subscription && $subscription->isActive())
        {{-- Plan details --}}
        <div class="manage-card">
            <h3 class="h5 fw-bold mb-3">Plan details</h3>
            <p class="mb-2">
                <span class="plan-badge">{{ $subscription->plan->name }}</span>
            </p>
            @if($subscription->plan->description)
                <p class="text-muted mb-2">{{ $subscription->plan->description }}</p>
            @endif
            <p class="mb-2">
                <strong>₱{{ number_format($subscription->plan->price, 0) }}</strong>
                / {{ $subscription->plan->interval === 'yearly' ? 'year' : 'month' }}
            </p>
            <p class="text-muted mb-3">
                Current period: {{ $subscription->current_period_start?->format('M j, Y') }} – {{ $subscription->current_period_end?->format('M j, Y') }}
            </p>
            @php $plan = $subscription->plan; @endphp
            @if(!empty($plan->benefits) && is_array($plan->benefits))
                <h4 class="h6 fw-bold mt-3 mb-2">Benefits</h4>
                <ul class="mb-2">
                    @foreach($plan->benefits as $key => $val)
                        <li>{{ is_string($val) ? $val : $key . ': ' . json_encode($val) }}</li>
                    @endforeach
                </ul>
            @endif
            @if(!empty($plan->features) && is_array($plan->features))
                <h4 class="h6 fw-bold mt-2 mb-2">Features</h4>
                <ul class="mb-0">
                    @foreach($plan->features as $f)
                        <li>{{ is_string($f) ? $f : json_encode($f) }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Analytics (current billing period) --}}
        @if(isset($analytics))
        <div class="manage-card">
            <h3 class="h5 fw-bold mb-3">Usage this period</h3>
            <div class="row g-3">
                <div class="col-md-6 col-lg-3">
                    <div class="card analytics-card h-100">
                        <div class="card-body">
                            <div class="text-muted small">Auction bids placed</div>
                            <div class="h4 mb-0">{{ $analytics['bids_count'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card analytics-card h-100">
                        <div class="card-body">
                            <div class="text-muted small">Auctions won</div>
                            <div class="h4 mb-0">{{ $analytics['auctions_won'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card analytics-card h-100">
                        <div class="card-body">
                            <div class="text-muted small">Toyshop orders</div>
                            <div class="h4 mb-0">{{ $analytics['toyshop_orders_count'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card analytics-card h-100">
                        <div class="card-body">
                            <div class="text-muted small">Toyshop discount saved</div>
                            <div class="h4 mb-0">₱{{ number_format($analytics['toyshop_discount_saved'] ?? 0, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Upgrade options --}}
        @if(!empty($upgradePlans) && $upgradePlans->isNotEmpty())
        <div class="manage-card">
            <h3 class="h5 fw-bold mb-3">Upgrade</h3>
            <div class="row g-3">
                @foreach($upgradePlans as $upPlan)
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">{{ $upPlan->name }}</h5>
                            <p class="card-text text-muted small mb-2">₱{{ number_format($upPlan->price, 0) }}/{{ $upPlan->interval === 'yearly' ? 'year' : 'month' }}</p>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('membership.upgrade', ['plan_id' => $upPlan->id]) }}" class="btn btn-sm btn-primary">Upgrade now</a>
                                <form action="{{ route('membership.schedule-upgrade') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $upPlan->id }}">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Upgrade at renewal</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Unsubscribe --}}
        <div class="manage-card">
            <h3 class="h5 fw-bold mb-2">Subscription</h3>
            <p class="text-muted mb-3">Cancel before the end of your current period to avoid being charged again. You will keep access until {{ $subscription->current_period_end?->format('F j, Y') }}.</p>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#unsubscribeModal">
                <i class="bi bi-x-circle me-1"></i>Unsubscribe
            </button>
        </div>

        {{-- Unsubscribe confirmation modal --}}
        <div class="modal fade" id="unsubscribeModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Unsubscribe</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure? You will lose access at the end of your current period ({{ $subscription->current_period_end?->format('F j, Y') }}).
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form action="{{ route('membership.cancel') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger">Yes, Unsubscribe</button>
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
                <i class="bi bi-gem me-1"></i>View Plans
            </a>
        </div>
    @endif
</div>
@endsection
