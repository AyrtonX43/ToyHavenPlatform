@extends('layouts.toyshop')

@section('title', 'Manage Membership')

@push('styles')
<style>
    .mm-hero {
        background: linear-gradient(135deg, #003087 0%, #0070ba 100%);
        color: #fff;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 0 0 24px 24px;
    }
    .mm-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .mm-card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #eee;
        font-weight: 600;
        font-size: 1.1rem;
    }
    .mm-plan-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: #fff;
        border-radius: 12px;
        font-weight: 600;
    }
    .mm-plan-badge.vip { background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%); }
    .mm-plan-badge.pro { background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%); }
    .mm-row { display: flex; justify-content: space-between; padding: 0.75rem 1.5rem; border-bottom: 1px solid #f1f3f5; }
    .mm-row:last-child { border-bottom: none; }
    .mm-row .label { color: #6c757d; }
    .mm-row .value { font-weight: 600; color: #1a1a1a; }
    .mm-upgrade-grid { display: grid; gap: 0.75rem; }
    .mm-upgrade-btn {
        display: block;
        padding: 1rem 1.25rem;
        background: #fff;
        border: 2px solid #dee2e6;
        border-radius: 12px;
        color: #1a1a1a;
        text-decoration: none;
        transition: all 0.2s;
    }
    .mm-upgrade-btn:hover { border-color: #0070ba; background: #f8fafc; color: #0070ba; }
    .mm-table { width: 100%; border-collapse: collapse; }
    .mm-table th, .mm-table td { padding: 0.85rem 1rem; text-align: left; border-bottom: 1px solid #eee; }
    .mm-table th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; }
    .mm-table tbody tr:hover { background: #f8fafc; }
    .mm-btn-receipt { padding: 0.4rem 0.75rem; font-size: 0.875rem; }
</style>
@endpush

@section('content')
<div class="mm-hero">
    <div class="container">
        <h1 class="h4 mb-1 fw-bold"><i class="bi bi-gem me-2"></i>Manage Membership</h1>
        <p class="mb-0 opacity-90">View your plan, upgrade options, and payment history</p>
    </div>
</div>

<div class="container py-4 pb-5">
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
            <div class="p-4">
                <div class="mm-plan-badge {{ $activeSubscription->plan->slug }}">
                    <i class="bi bi-star-fill"></i>
                    {{ $activeSubscription->plan->name }}
                </div>
                <div class="mt-4">
                    <div class="mm-row">
                        <span class="label">Billing period</span>
                        <span class="value">{{ $activeSubscription->current_period_start?->format('M d, Y') }} – {{ $activeSubscription->current_period_end?->format('M d, Y') }}</span>
                    </div>
                    <div class="mm-row">
                        <span class="label">Status</span>
                        <span class="value"><span class="badge bg-success">Active</span></span>
                    </div>
                </div>
                <form action="{{ route('membership.cancel') }}" method="POST" onsubmit="return confirm('Cancel your membership? You will lose access at the end of the billing period.');" class="mt-3 pt-3 border-top">
                    @csrf
                    @if(auth()->user()->currentPlan()?->slug === 'vip')
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="deactivate_shop" id="deactivate_shop" value="1">
                            <label class="form-check-label" for="deactivate_shop">Temporarily deactivate my auction seller shop</label>
                        </div>
                    @endif
                    <button type="submit" class="btn btn-outline-danger btn-sm">Cancel Membership</button>
                </form>
            </div>
        </div>

        @php
            $currentPlan = $activeSubscription->plan;
            $upgradePlans = $plans->filter(fn($p) => $p->id !== $currentPlan->id && $p->sort_order > $currentPlan->sort_order);
        @endphp
        @if($upgradePlans->isNotEmpty())
            <div class="mm-card">
                <div class="mm-card-header">Upgrade Your Plan</div>
                <div class="p-4">
                    <div class="mm-upgrade-grid">
                        @foreach($upgradePlans as $p)
                            <a href="{{ route('membership.upgrade', $p->slug) }}" class="mm-upgrade-btn">
                                <span class="d-block fw-semibold">{{ $p->name }}</span>
                                <span class="text-muted small">₱{{ number_format($p->price, 0) }}/month · {{ Str::limit($p->description, 50) }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="mm-card">
            <div class="p-4 text-center">
                <p class="text-muted mb-3">You don't have an active membership.</p>
                <a href="{{ route('membership.index') }}" class="btn btn-primary rounded-3 px-4">
                    <i class="bi bi-gem me-2"></i>Subscribe Now
                </a>
            </div>
        </div>
    @endif

    <div class="mm-card">
        <div class="mm-card-header">Payment History</div>
        <div class="overflow-hidden">
            <table class="mm-table">
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
                    @foreach($subscriptions as $sub)
                        @foreach($sub->payments()->orderByDesc('created_at')->get() as $payment)
                            <tr>
                                <td>{{ $payment->paid_at?->format('M d, Y') ?? $payment->created_at->format('M d, Y') }}</td>
                                <td>{{ $sub->plan->name }}</td>
                                <td>₱{{ number_format($payment->amount, 2) }}</td>
                                <td><span class="badge {{ $payment->status === 'paid' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($payment->status) }}</span></td>
                                <td>
                                    <a href="{{ route('membership.receipt', [$sub, $payment]) }}" class="btn btn-sm btn-outline-primary mm-btn-receipt" target="_blank">
                                        <i class="bi bi-download me-1"></i>Receipt
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
            @if($subscriptions->isEmpty() || $subscriptions->flatMap->payments->isEmpty())
                <div class="p-4 text-center text-muted">No payments yet</div>
            @endif
        </div>
    </div>
</div>
@endsection
