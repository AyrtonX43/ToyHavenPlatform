@extends('layouts.toyshop')

@section('title', 'Manage Membership')

@push('styles')
<style>
    .mm-hero {
        background: linear-gradient(135deg, #003087 0%, #0070ba 100%);
        color: #fff;
        padding: 2.5rem 1.5rem;
        border-radius: 16px;
        margin-bottom: 2rem;
    }
    .mm-plan-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255,255,255,0.2);
        padding: 0.5rem 1rem;
        border-radius: 999px;
        font-weight: 600;
        font-size: 1rem;
    }
    .mm-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        border: 1px solid #e9ecef;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .mm-card-header {
        padding: 1.25rem 1.5rem;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        font-weight: 600;
    }
    .mm-card-body { padding: 1.5rem; }
    .mm-upgrade-grid { display: flex; flex-wrap: wrap; gap: 0.75rem; }
    .mm-upgrade-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.65rem 1.25rem;
        background: #003087;
        color: #fff;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        font-size: 0.9rem;
        transition: background 0.2s;
    }
    .mm-upgrade-btn:hover { background: #002964; color: #fff; }
    .mm-table-wrap { overflow-x: auto; border-radius: 8px; border: 1px solid #e9ecef; }
    .mm-table { margin: 0; }
    .mm-table th { background: #f8f9fa; font-weight: 600; font-size: 0.85rem; padding: 0.875rem 1rem; }
    .mm-table td { padding: 0.875rem 1rem; vertical-align: middle; }
    .mm-table tbody tr { border-bottom: 1px solid #f1f3f5; }
    .mm-table tbody tr:last-child { border-bottom: none; }
    .mm-receipt-btn {
        padding: 0.35rem 0.75rem;
        font-size: 0.8rem;
        background: #003087;
        color: #fff;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 500;
    }
    .mm-receipt-btn:hover { background: #002964; color: #fff; }
    .mm-empty { text-align: center; padding: 2rem; color: #6c757d; }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="mm-hero">
        <h1 class="h4 mb-2 fw-bold"><i class="bi bi-gem me-2"></i>Manage Membership</h1>
        @if($activeSubscription)
            <span class="mm-plan-badge">{{ $activeSubscription->plan->name }} Member</span>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($activeSubscription)
        <div class="mm-card">
            <div class="mm-card-header">Current Plan</div>
            <div class="mm-card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="fw-bold mb-1">{{ $activeSubscription->plan->name }}</h5>
                        <p class="text-muted mb-0 small">
                            {{ $activeSubscription->current_period_start?->format('M d, Y') }} – {{ $activeSubscription->current_period_end?->format('M d, Y') }}
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-2 mt-md-0">
                        <form action="{{ route('membership.cancel') }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel your membership? You will lose access at the end of the billing period.');">
                            @csrf
                            @if(auth()->user()->currentPlan()?->slug === 'vip')
                                <div class="form-check mb-2 text-start">
                                    <input class="form-check-input" type="checkbox" name="deactivate_shop" id="deactivate_shop" value="1">
                                    <label class="form-check-label small" for="deactivate_shop">Deactivate auction seller shop</label>
                                </div>
                            @endif
                            <button type="submit" class="btn btn-outline-danger btn-sm">Cancel Membership</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @php
            $currentSlug = $activeSubscription->plan->slug;
            $currentSort = $activeSubscription->plan->sort_order ?? 99;
        @endphp
        @if($currentSlug !== 'vip')
            @php $upgradePlans = $plans->filter(fn($p) => ($p->sort_order ?? 0) > $currentSort); @endphp
            @if($upgradePlans->isNotEmpty())
                <div class="mm-card">
                    <div class="mm-card-header">Upgrade Plan</div>
                    <div class="mm-card-body">
                        <div class="mm-upgrade-grid">
                            @foreach($upgradePlans as $p)
                                <a href="{{ route('membership.upgrade', $p->slug) }}" class="mm-upgrade-btn">
                                    <i class="bi bi-arrow-up-circle"></i> {{ $p->name }} — ₱{{ number_format($p->price, 0) }}/mo
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endif
    @else
        <div class="mm-card">
            <div class="mm-card-body text-center py-5">
                <p class="text-muted mb-3">You don't have an active membership.</p>
                <a href="{{ route('membership.index') }}" class="btn btn-primary px-4">
                    <i class="bi bi-gem me-2"></i>View Plans
                </a>
            </div>
        </div>
    @endif

    <div class="mm-card">
        <div class="mm-card-header">Payment History</div>
        <div class="mm-card-body p-0">
            <div class="mm-table-wrap">
                <table class="mm-table table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $hasPayments = false; @endphp
                        @foreach($subscriptions as $sub)
                            @foreach($sub->payments()->orderByDesc('created_at')->get() as $payment)
                                @php $hasPayments = true; @endphp
                                <tr>
                                    <td>{{ $payment->paid_at?->format('M d, Y') ?? $payment->created_at->format('M d, Y') }}</td>
                                    <td>{{ $sub->plan->name }}</td>
                                    <td>₱{{ number_format($payment->amount, 2) }}</td>
                                    <td><span class="badge bg-{{ $payment->status === 'paid' ? 'success' : 'secondary' }}">{{ $payment->status }}</span></td>
                                    <td>
                                        <a href="{{ route('membership.receipt', [$sub, $payment]) }}" class="mm-receipt-btn" target="_blank">
                                            <i class="bi bi-download me-1"></i>Receipt
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                        @if(!$hasPayments)
                            <tr><td colspan="5" class="mm-empty">No payments yet</td></tr>
                        @endif
                    </tbody>
                </table>
        </div>
    </div>
</div>
@endsection
