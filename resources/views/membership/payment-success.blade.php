@extends('layouts.toyshop')

@section('title', 'Payment Successful')

@push('styles')
<style>
    .receipt-hero {
        background: linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%);
        color: white;
        padding: 2.5rem 0;
        margin-bottom: 2rem;
        border-radius: 24px;
        box-shadow: 0 10px 40px rgba(5, 150, 105, 0.25);
    }
    .current-plan-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255,255,255,0.2);
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 600;
        margin-top: 0.5rem;
    }
    .vip-cta-box {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border: 2px solid #f59e0b;
        border-radius: 16px;
        padding: 1.5rem;
        margin-top: 2rem;
    }
</style>
@endpush

@section('content')
<div class="receipt-hero">
    <div class="container text-center">
        <div class="mb-3">
            <i class="bi bi-check-circle-fill" style="font-size: 4rem;"></i>
        </div>
        <h2 class="mb-1 fw-bold">Payment Successful!</h2>
        <p class="mb-0 opacity-90">Your membership is now active. Receipt has been sent to your email.</p>
        <div class="current-plan-badge">
            <i class="bi bi-gem"></i> Current Plan: {{ $subscription->plan->name }}
        </div>
    </div>
</div>

<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-receipt me-2"></i>Receipt</h5>
                </div>
                <div class="card-body p-4">
                    @php $lastPayment = $subscription->payments()->where('status', 'paid')->latest()->first(); @endphp
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                        <div>
                            <span class="text-muted small">Plan</span>
                            <h5 class="mb-0 fw-bold">{{ $subscription->plan->name }}</h5>
                        </div>
                        <div class="text-end">
                            <span class="text-muted small">Amount Paid</span>
                            <h5 class="mb-0 fw-bold text-success">₱{{ number_format($lastPayment?->amount ?? $subscription->plan->price, 2) }}</h5>
                        </div>
                    </div>
                    @if($lastPayment && $lastPayment->hasReceipt())
                        <a href="{{ route('membership.receipt', [$subscription, $lastPayment]) }}" class="btn btn-outline-primary" target="_blank">
                            <i class="bi bi-download me-2"></i> Download Receipt PDF
                        </a>
                    @endif
                    <p class="text-muted small mt-3 mb-0">
                        <i class="bi bi-envelope-check me-1"></i> A copy has been sent to {{ auth()->user()->email }}
                    </p>
                </div>
            </div>

            @if($subscription->plan->slug === 'vip')
            <div class="vip-cta-box">
                <h5 class="fw-bold mb-2"><i class="bi bi-stars me-2"></i>You're a VIP Member!</h5>
                <p class="mb-3">Create a business auction shop or register as an individual seller to sell your collection at ToyHaven Auctions.</p>
                <a href="{{ route('auctions.verification.index') }}" class="btn btn-warning fw-semibold">
                    <i class="bi bi-shop me-2"></i>Create Auction Seller Account
                </a>
            </div>
            @endif

            <div class="d-flex gap-2 justify-content-center flex-wrap mt-4">
                <a href="{{ route('membership.manage') }}" class="btn btn-primary btn-lg rounded-3">Manage Membership</a>
                <a href="{{ route('auctions.index') }}" class="btn btn-outline-primary btn-lg rounded-3">Browse Auctions</a>
            </div>
        </div>
    </div>
</div>
@endsection
