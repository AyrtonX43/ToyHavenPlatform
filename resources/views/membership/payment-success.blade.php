@extends('layouts.toyshop')

@section('title', 'Payment Successful')

@section('content')
<div class="container py-5 text-center">
    <div class="mb-4">
        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
    </div>
    <h2>Payment Successful!</h2>
    <p class="lead">Your {{ $subscription->plan->name }} membership is now active.</p>
    <a href="{{ route('membership.manage') }}" class="btn btn-primary">Manage Membership</a>
    <a href="{{ route('auctions.index') }}" class="btn btn-outline-primary ms-2">Browse Auctions</a>
</div>
@endsection
