@extends('layouts.toyshop')

@section('title', 'Payment Successful')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <div class="mb-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
            </div>
            <h2 class="mb-2">Payment Successful!</h2>
            <p class="lead text-muted mb-4">Your {{ $subscription->plan->name }} membership is now active. Receipt has been sent to your email.</p>
            @php $lastPayment = $subscription->payments()->where('status', 'paid')->latest()->first(); @endphp
            @if($lastPayment && $lastPayment->hasReceipt())
                <a href="{{ route('membership.receipt', [$subscription, $lastPayment]) }}" class="btn btn-outline-secondary mb-3" target="_blank">
                    <i class="bi bi-download me-2"></i> Download Receipt
                </a>
            @endif
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <a href="{{ route('auctions.index') }}" class="btn btn-primary btn-lg">Go to Auctions</a>
                <a href="{{ route('membership.manage') }}" class="btn btn-outline-secondary btn-lg">Manage Membership</a>
            </div>
        </div>
    </div>
</div>
@endsection
