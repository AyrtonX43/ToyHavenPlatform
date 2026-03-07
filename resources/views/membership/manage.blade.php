@extends('layouts.toyshop')

@section('title', 'Manage Membership')

@section('content')
<div class="container py-5">
    <h2 class="mb-4 fw-bold"><i class="bi bi-gem me-2"></i>Manage Membership</h2>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show rounded-3 shadow-sm">
            <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($activeSubscription)
        @if(auth()->user()->currentPlan()?->slug === 'vip')
            <div class="card mb-4 border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header py-3" style="background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%); color: white;">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-hammer me-2"></i>VIP: Auction Seller Registration</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">With VIP membership, you can register as an auction seller.</p>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('auction.seller.register', 'individual') }}" class="btn btn-outline-primary rounded-3">
                            <i class="bi bi-person-badge me-2"></i>Individual Auction Seller
                        </a>
                        <a href="{{ route('auction.seller.register', 'business') }}" class="btn btn-outline-primary rounded-3">
                            <i class="bi bi-shop me-2"></i>Business Auction Seller
                        </a>
                    </div>
                </div>
            </div>
        @endif
        <div class="card mb-4 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header text-white" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                <h5 class="mb-0">Active Plan: {{ $activeSubscription->plan->name }}</h5>
            </div>
            <div class="card-body">
                <p><strong>Period:</strong> {{ $activeSubscription->current_period_start?->format('M d, Y') }} – {{ $activeSubscription->current_period_end?->format('M d, Y') }}</p>
                <form action="{{ route('membership.cancel') }}" method="POST" onsubmit="return confirm('Cancel your membership? You will lose access at the end of the billing period.');">
                    @csrf
                    @if(auth()->user()->currentPlan()?->slug === 'vip')
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="deactivate_shop" id="deactivate_shop" value="1">
                            <label class="form-check-label" for="deactivate_shop">Temporarily deactivate my auction seller shop</label>
                        </div>
                    @endif
                    <button type="submit" class="btn btn-outline-danger">Cancel Membership</button>
                </form>
            </div>
        </div>

        <h5 class="mb-3">Upgrade</h5>
        <div class="row g-2 mb-4">
            @foreach($plans as $p)
                @if($p->id !== $activeSubscription->plan_id)
                    <div class="col-md-4">
                        <a href="{{ route('membership.upgrade', $p->slug) }}" class="btn btn-outline-primary w-100">
                            Upgrade to {{ $p->name }} - ₱{{ number_format($p->price, 0) }}/mo
                        </a>
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <div class="alert alert-warning">
            You don't have an active membership. <a href="{{ route('membership.index') }}">Subscribe now</a> to continue.
        </div>
    @endif

    <h5 class="mb-3">Payment History</h5>
    <div class="table-responsive">
        <table class="table">
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
                            <td>{{ $payment->status }}</td>
                            <td>
                                @if($payment->hasReceipt())
                                    <a href="{{ route('membership.receipt', [$sub, $payment]) }}" class="btn btn-sm btn-outline-primary">Receipt</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endforeach
                @if($subscriptions->isEmpty() || $subscriptions->flatMap->payments->isEmpty())
                    <tr><td colspan="5" class="text-muted">No payments yet</td></tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
