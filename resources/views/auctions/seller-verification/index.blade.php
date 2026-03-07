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
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-shop me-2"></i>Create Auction Seller Account</h5>
                <p class="mb-2">As a VIP member, you can sell your collection at ToyHaven Auctions:</p>
                <ul class="mb-3">
                    <li><strong>Individual Seller</strong> — 2 Government IDs, 1 Facial photo, Bank Statement</li>
                    <li><strong>Business Auction Shop</strong> — Requires Fully Verified Trusted Toyshop</li>
                </ul>
                <a href="{{ route('auctions.verification.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Apply Now
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
