@extends('layouts.toyshop')

@section('title', 'Auction Seller Verification')

@section('content')
<div class="container py-5">
    <h2 class="mb-4"><i class="bi bi-person-badge me-2"></i>Auction Seller Verification</h2>

    @if($verification)
        @if($verification->isApproved())
            <div class="alert alert-success">
                You are verified! <a href="{{ route('auctions.seller.index') }}">Go to Auction Seller Dashboard</a>
            </div>
        @elseif($verification->isPending())
            <div class="alert alert-warning">
                Your verification is pending review. <a href="{{ route('auctions.verification.status') }}">Check Status</a>
            </div>
        @else
            <div class="alert alert-danger">
                Your verification was not approved. You may submit a new application.
                <a href="{{ route('auctions.verification.create') }}" class="btn btn-sm btn-primary ms-2">Apply Again</a>
            </div>
        @endif
    @else
        <div class="card">
            <div class="card-body">
                <h5>Become an Auction Seller</h5>
                <p>Individual sellers need: 2 Government IDs, 1 Facial photo, Bank Statement.</p>
                <p>Business sellers need: Fully Verified Trusted Toyshop status.</p>
                <a href="{{ route('auctions.verification.create') }}" class="btn btn-primary">Apply Now</a>
            </div>
        </div>
    @endif
</div>
@endsection
