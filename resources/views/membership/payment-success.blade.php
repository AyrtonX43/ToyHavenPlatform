@extends('layouts.toyshop')

@section('title', 'Payment Successful - ToyHaven')

@push('styles')
<style>
    .ps-hero {
        background: linear-gradient(180deg, #003087 0%, #0070ba 100%);
        color: #fff;
        padding: 3rem 1.5rem;
        text-align: center;
    }
    .ps-hero .check-icon {
        width: 72px;
        height: 72px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }
    .ps-hero .check-icon svg { width: 40px; height: 40px; }
    .ps-hero h1 { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.5rem; }
    .ps-hero p { opacity: 0.9; font-size: 1rem; }
    .ps-receipt {
        max-width: 480px;
        margin: -2rem auto 0;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.1);
        overflow: hidden;
        position: relative;
        z-index: 1;
    }
    .ps-receipt-header {
        background: #f8f9fa;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e9ecef;
        font-weight: 600;
        font-size: 1rem;
    }
    .ps-receipt-body { padding: 1.5rem; }
    .ps-row { display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid #f1f3f5; }
    .ps-row:last-child { border-bottom: none; }
    .ps-row .label { color: #6c757d; font-size: 0.9rem; }
    .ps-row .value { font-weight: 600; color: #1a1a1a; }
    .ps-actions { padding: 1.5rem; border-top: 1px solid #e9ecef; }
    .btn-download {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        background: #003087;
        color: #fff;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.2s;
    }
    .btn-download:hover { background: #002964; color: #fff; }
    .btn-manage {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        background: #f8f9fa;
        color: #1a1a1a;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        margin-left: 0.75rem;
        transition: background 0.2s;
    }
    .btn-manage:hover { background: #e9ecef; color: #1a1a1a; }
</style>
@endpush

@section('content')
<div class="ps-hero">
    <div class="check-icon">
        <svg fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
    </div>
    <h1>Payment Successful</h1>
    <p>Your {{ $subscription->plan->name }} membership is now active. Receipt has been sent to your email.</p>
</div>

<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="ps-receipt" id="receipt">
        <div class="ps-receipt-header">
            <i class="bi bi-receipt me-2"></i>Payment Receipt
        </div>
        <div class="ps-receipt-body">
            @php $lastPayment = $subscription->payments()->where('status', 'paid')->latest()->first(); @endphp
            <div class="ps-row">
                <span class="label">Plan</span>
                <span class="value">{{ $subscription->plan->name }}</span>
            </div>
            <div class="ps-row">
                <span class="label">Amount</span>
                <span class="value">₱{{ number_format($lastPayment?->amount ?? $subscription->plan->price, 2) }}</span>
            </div>
            <div class="ps-row">
                <span class="label">Payment method</span>
                <span class="value">{{ strtoupper($lastPayment?->payment_method ?? 'paypal') }}</span>
            </div>
            <div class="ps-row">
                <span class="label">Status</span>
                <span class="value"><span class="badge bg-success">Active</span></span>
            </div>
        </div>
        <div class="ps-actions">
            @if($lastPayment)
                <a href="{{ route('membership.receipt', [$subscription, $lastPayment]) }}" class="btn-download" target="_blank">
                    <i class="bi bi-download"></i> Download Receipt
                </a>
            @endif
            <a href="{{ route('membership.manage') }}" class="btn-manage">
                <i class="bi bi-gear"></i> Manage Membership
            </a>
        </div>
    </div>
</div>
@endsection

@if(request('popup'))
@push('scripts')
<script>
(function() {
    if (window.opener) {
        var url = window.location.href.replace(/[?&]popup=1/, '').replace(/#.*/, '') + '#receipt';
        window.opener.location.href = url;
        window.close();
    }
})();
</script>
@endpush
@endif
