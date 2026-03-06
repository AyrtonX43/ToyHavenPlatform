@extends('layouts.toyshop')

@section('title', 'Auction Seller Application Status - ToyHaven')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auctions.index') }}">Auctions</a></li>
            <li class="breadcrumb-item active">Application Status</li>
        </ol>
    </nav>

    <h2 class="mb-4">Application Status</h2>

    @if($profile->status === 'pending')
        <div class="alert alert-info">
            <i class="bi bi-hourglass-split me-2"></i>Your application is under review. We will notify you once it's processed.
        </div>
    @elseif($profile->status === 'approved')
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>You're approved! <a href="{{ route('auctions.seller.index') }}">Go to your auction listings</a>.
        </div>
    @elseif($profile->status === 'rejected')
        <div class="alert alert-danger">
            <i class="bi bi-x-circle me-2"></i>Your application was rejected.
            @if($profile->rejection_reason)
                <p class="mb-0 mt-2"><strong>Reason:</strong> {{ $profile->rejection_reason }}</p>
            @endif
            <a href="{{ route('auctions.seller-profile.create', ['type' => $profile->seller_type]) }}" class="btn btn-outline-danger mt-3">Reapply</a>
        </div>
    @elseif($profile->status === 'suspended')
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>Your auction seller account has been suspended. Contact support for more information.
        </div>
    @endif
</div>
@endsection
