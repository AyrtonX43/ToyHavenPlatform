@extends('layouts.toyshop')

@section('title', 'Become an Auction Seller - ToyHaven')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auctions.index') }}">Auctions</a></li>
            <li class="breadcrumb-item active">Become a Seller</li>
        </ol>
    </nav>

    <h2 class="mb-4">Become an Auction Seller</h2>
    <p class="text-muted mb-4">You have VIP membership. Choose how you want to list auctions:</p>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100 border-primary">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-person me-2"></i>Individual</h5>
                    <p class="card-text text-muted">List auctions under your own name. No business registration required.</p>
                    <a href="{{ route('auctions.seller-profile.create', ['type' => 'individual']) }}" class="btn btn-primary">Register as Individual</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 border-warning">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-building me-2"></i>Business</h5>
                    <p class="card-text text-muted">Register your business for auction listings. Requires BIR certificate and official receipt sample.</p>
                    <a href="{{ route('auctions.seller-profile.create', ['type' => 'business']) }}" class="btn btn-warning">Register Business</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
