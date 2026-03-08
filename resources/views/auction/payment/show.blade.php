@extends('layouts.toyshop')

@section('title', 'Pay for ' . $payment->auction->title . ' - ToyHaven')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auction.index') }}">Auction</a></li>
            <li class="breadcrumb-item"><a href="{{ route('auction.show', $payment->auction) }}">{{ Str::limit($payment->auction->title, 30) }}</a></li>
            <li class="breadcrumb-item active">Payment</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white py-3">
                    <h4 class="mb-0"><i class="bi bi-trophy me-2"></i>You Won: {{ $payment->auction->title }}</h4>
                </div>
                <div class="card-body p-4">
                    <p class="mb-2"><strong>Amount to pay:</strong></p>
                    <p class="fs-2 text-primary mb-3">₱{{ number_format($payment->amount, 2) }}</p>
                    <p class="mb-4 text-muted">
                        <i class="bi bi-clock me-1"></i>
                        Payment deadline: {{ $payment->payment_deadline?->format('M d, Y H:i') }}
                        @if($payment->isOverdue())
                            <span class="text-danger">(Overdue)</span>
                        @endif
                    </p>

                    <div class="alert alert-info">
                        <strong>Payment options coming soon.</strong> For now, please contact support to complete your payment.
                    </div>

                    <a href="{{ route('auction.index') }}" class="btn btn-outline-secondary">Back to Auctions</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
