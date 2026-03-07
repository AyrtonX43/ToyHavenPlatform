@extends('layouts.toyshop')

@section('title', 'Payment Successful - ToyHaven')

@section('content')
<div class="container py-4">
    <div class="alert alert-success">
        <h4><i class="bi bi-check-circle me-2"></i>Payment Successful</h4>
        <p class="mb-0">Your payment for "{{ $auctionPayment->auction->title }}" has been completed. A receipt has been sent to your email.</p>
        <p class="mb-0 mt-2">
            <a href="{{ route('auctions.wins.show', $auctionPayment) }}" class="btn btn-sm btn-outline-success">Track your win & confirm receipt</a>
        </p>
    </div>
</div>
@endsection
