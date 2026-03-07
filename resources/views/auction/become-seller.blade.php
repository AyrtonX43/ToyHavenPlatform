@extends('layouts.toyshop')

@section('title', $requiresUpgrade ? 'Upgrade to Become a Seller - ToyHaven' : 'Become an Auction Seller - ToyHaven')

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auction.index') }}">Auctions</a></li>
            <li class="breadcrumb-item active">Become a Seller</li>
        </ol>
    </nav>

    @if($requiresUpgrade)
    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="bi bi-gem me-2"></i>Upgrade to VIP to Sell</h4>
        </div>
        <div class="card-body p-4">
            <p class="mb-4">Auction seller registration is available only for <strong>VIP</strong> members. Upgrade your plan to register as an Individual or Business auction seller.</p>
            <a href="{{ route('membership.manage') }}" class="btn btn-primary">
                <i class="bi bi-arrow-up-circle me-1"></i>Manage Membership & Upgrade
            </a>
            <a href="{{ route('membership.index') }}" class="btn btn-outline-secondary ms-2">View Plans</a>
        </div>
    </div>
    @else
    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="bi bi-person-check me-2"></i>Choose Seller Type</h4>
        </div>
        <div class="card-body p-4">
            <p class="mb-4">As a VIP member, you can register as either an Individual or Business auction seller. Select the option that fits you:</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <a href="#" class="card text-decoration-none h-100 border-2 border-primary text-dark">
                        <div class="card-body text-center py-4">
                            <i class="bi bi-person-badge text-primary" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 mb-2">Individual Seller</h5>
                            <p class="text-muted small mb-0">For personal selling. Requires 2 government IDs, facial photo, and bank statement.</p>
                            <span class="btn btn-outline-primary mt-3">Coming Soon</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="#" class="card text-decoration-none h-100 border-2 border-primary text-dark">
                        <div class="card-body text-center py-4">
                            <i class="bi bi-building text-primary" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 mb-2">Business Seller</h5>
                            <p class="text-muted small mb-0">For stores and businesses. Same verification as Fully Verified Trusted Toyshop.</p>
                            <span class="btn btn-outline-primary mt-3">Coming Soon</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
