@extends('layouts.toyshop')

@section('title', 'Payment Successful - ToyHaven')

@push('styles')
<style>
    .success-hero {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: white;
        padding: 2.5rem 0;
        margin-bottom: 2rem;
        border-radius: 20px;
        box-shadow: 0 8px 30px rgba(5, 150, 105, 0.25);
    }
    .receipt-card {
        border: 2px solid #e2e8f0;
        border-radius: 20px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    }
    .vip-cta-card {
        background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%);
        color: white;
        border-radius: 20px;
        padding: 1.5rem;
        border: none;
    }
</style>
@endpush

@section('content')
<div class="success-hero">
    <div class="container text-center">
        <i class="bi bi-check-circle-fill mb-3" style="font-size: 4rem;"></i>
        <h2 class="mb-2 fw-bold">Payment Successful!</h2>
        <p class="mb-0 opacity-90 lead">Your {{ $subscription->plan->name }} membership is now your current plan. Receipt has been sent to your email.</p>
    </div>
</div>

<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-8">
            {{-- Receipt Section --}}
            <div class="receipt-card card border-0 mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-receipt me-2"></i>Receipt</h5>
                </div>
                <div class="card-body">
                    @php $lastPayment = $subscription->payments()->where('status', 'paid')->latest()->first(); @endphp
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <small class="text-muted">Plan</small>
                            <p class="fw-bold mb-0">{{ $subscription->plan->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Amount</small>
                            <p class="fw-bold mb-0">₱{{ number_format($lastPayment?->amount ?? $subscription->plan->price, 2) }}</p>
                        </div>
                        <div class="col-md-6 mt-3">
                            <small class="text-muted">Payment Method</small>
                            <p class="mb-0">{{ strtoupper($lastPayment?->payment_method ?? 'QRPH') }}</p>
                        </div>
                        <div class="col-md-6 mt-3">
                            <small class="text-muted">Status</small>
                            <p class="mb-0"><span class="badge bg-success">Current Plan</span></p>
                        </div>
                    </div>
                    @if($lastPayment && $lastPayment->hasReceipt())
                        <a href="{{ route('membership.receipt', [$subscription, $lastPayment]) }}" class="btn btn-outline-primary rounded-3" target="_blank">
                            <i class="bi bi-download me-2"></i>Download Receipt
                        </a>
                    @endif
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-center flex-wrap mt-3">
                <a href="{{ route('membership.manage') }}" class="btn btn-primary btn-lg rounded-3 px-4">
                    <i class="bi bi-gear me-2"></i>Manage Membership
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@if(request('popup'))
@push('scripts')
<script>
(function() {
    if (window.opener) {
        var url = window.location.href.replace(/[?&]popup=1/, '');
        window.opener.location.href = url;
        window.close();
    }
})();
</script>
@endpush
@endif
