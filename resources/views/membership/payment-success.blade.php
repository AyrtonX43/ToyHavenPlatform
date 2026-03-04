@extends('layouts.toyshop')

@section('title', 'Payment Successful - ToyHaven Membership')

@push('styles')
<style>
    .success-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 2px solid #e2e8f0;
        overflow: hidden;
        max-width: 560px;
        margin: 0 auto;
    }
    .success-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 2rem 2rem;
        text-align: center;
    }
    .success-header h1 { margin: 0; font-weight: 700; font-size: 1.5rem; }
    .success-body { padding: 2rem; }
    .success-body .plan-name {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1rem;
    }
    .receipt-download {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        background: #f0fdfa;
        border: 2px solid #10b981;
        border-radius: 12px;
        color: #059669;
        font-weight: 600;
        text-decoration: none;
        margin-bottom: 1.5rem;
        transition: all 0.2s;
    }
    .receipt-download:hover {
        background: #ccfbf1;
        color: #047857;
        border-color: #059669;
    }
    .action-links { margin-top: 1.5rem; }
    .action-links .btn { margin-right: 0.5rem; margin-bottom: 0.5rem; }
</style>
@endpush

@section('content')
<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="success-card">
        <div class="success-header">
            <div class="mb-2"><i class="bi bi-check-circle-fill" style="font-size: 3rem;"></i></div>
            <h1>Payment Successful</h1>
            <p class="mb-0 mt-2 opacity-90">Your membership is now active.</p>
        </div>
        <div class="success-body">
            <p class="plan-name">
                <i class="bi bi-gem me-2 text-primary"></i>{{ $subscription->plan->name }} Membership
            </p>
            <p class="text-muted mb-3">
                {{ $subscription->plan->interval === 'monthly' ? 'Monthly' : 'Annual' }} billing
                @if($subscription->current_period_end)
                    — Active until {{ $subscription->current_period_end->format('F j, Y') }}
                @endif
            </p>

            @if($latestPayment && $latestPayment->hasReceipt())
                <a href="{{ route('membership.receipt', $subscription) }}" class="receipt-download" download>
                    <i class="bi bi-file-earmark-pdf"></i> Download Receipt (PDF)
                </a>
            @elseif($latestPayment)
                <a href="{{ route('membership.receipt', $subscription) }}" class="receipt-download">
                    <i class="bi bi-file-earmark-pdf"></i> Download Receipt (PDF)
                </a>
            @endif

            <div class="action-links">
                <a href="{{ route('membership.manage') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                    <i class="bi bi-person-badge me-1"></i>Manage Membership
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-house me-1"></i>Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
