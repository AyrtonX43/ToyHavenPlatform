@extends('layouts.toyshop')

@section('title', 'Auction Seller Verification - ToyHaven')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auctions.index') }}">Auctions</a></li>
            <li class="breadcrumb-item active">Seller Verification</li>
        </ol>
    </nav>

    @if(!$hasVip)
        <div class="card border-0 shadow-sm">
            <div class="card-body p-5 text-center">
                <i class="bi bi-gem text-warning" style="font-size: 4rem;"></i>
                <h2 class="mt-3 fw-bold">VIP Membership Required</h2>
                <p class="text-muted lead">You need a VIP membership plan to list products for auction.</p>
                <a href="{{ route('membership.index', ['intent' => 'auction']) }}" class="btn btn-warning btn-lg px-5 mt-2" style="background: linear-gradient(135deg, #f59e0b, #eab308); border: none; color: white;">
                    <i class="bi bi-gem me-2"></i>Upgrade to VIP
                </a>
            </div>
        </div>
    @elseif($verification && $verification->isPending())
        <div class="card border-0 shadow-sm">
            <div class="card-body p-5 text-center">
                <i class="bi bi-hourglass-split text-warning" style="font-size: 4rem;"></i>
                <h2 class="mt-3 fw-bold">Verification Under Review</h2>
                <p class="text-muted lead">Your auction seller verification is being reviewed by our team. We'll notify you once it's processed.</p>
                <p class="text-muted">Submitted {{ $verification->created_at->diffForHumans() }}</p>
                <a href="{{ route('auctions.verification.status') }}" class="btn btn-outline-primary mt-2">
                    <i class="bi bi-eye me-1"></i>View Status
                </a>
            </div>
        </div>
    @elseif($verification && $verification->status === 'rejected')
        <div class="card border-0 shadow-sm border-danger">
            <div class="card-body p-5 text-center">
                <i class="bi bi-x-circle text-danger" style="font-size: 4rem;"></i>
                <h2 class="mt-3 fw-bold">Verification Rejected</h2>
                <p class="text-muted">{{ $verification->rejection_reason }}</p>
                <a href="{{ route('auctions.verification.create', ['type' => 'individual']) }}" class="btn btn-primary mt-2">
                    <i class="bi bi-arrow-repeat me-1"></i>Resubmit Verification
                </a>
            </div>
        </div>
    @elseif($verification && $verification->status === 'requires_resubmission')
        <div class="card border-0 shadow-sm border-warning">
            <div class="card-body p-5 text-center">
                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                <h2 class="mt-3 fw-bold">Resubmission Required</h2>
                <p class="text-muted">{{ $verification->rejection_reason }}</p>
                <a href="{{ route('auctions.verification.create', ['type' => $verification->seller_type]) }}" class="btn btn-warning mt-2">
                    <i class="bi bi-arrow-repeat me-1"></i>Resubmit Documents
                </a>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-lg">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-hammer text-primary" style="font-size: 4rem;"></i>
                    <h2 class="mt-3 mb-2 fw-bold">Become an Auction Seller</h2>
                    <p class="text-muted lead">Complete verification to list your toys and collectibles for auction</p>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-person-vcard text-primary" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="mb-2">Verify Identity</h5>
                            <p class="text-muted small">Submit government IDs and a clear selfie for identity verification</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-shield-check text-success" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="mb-2">Get Approved</h5>
                            <p class="text-muted small">Our team reviews your documents to ensure trust and safety</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-hammer text-warning" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="mb-2">List Auctions</h5>
                            <p class="text-muted small">Start listing your rare toys and collectibles for auction</p>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <p class="mb-4 fw-semibold">Choose your seller type:</p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="{{ route('auctions.verification.create', ['type' => 'individual']) }}" class="btn btn-primary btn-lg px-5">
                            <i class="bi bi-person me-2"></i>Individual Seller
                        </a>
                        <a href="{{ route('auctions.verification.create', ['type' => 'business']) }}" class="btn btn-success btn-lg px-5">
                            <i class="bi bi-building me-2"></i>Business Seller
                        </a>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="bg-light rounded p-3">
                                <h6 class="fw-bold"><i class="bi bi-person me-1"></i>Individual Seller</h6>
                                <ul class="list-unstyled small text-muted mb-0">
                                    <li><i class="bi bi-check text-success me-1"></i>2-3 Government IDs</li>
                                    <li><i class="bi bi-check text-success me-1"></i>Clear Selfie</li>
                                    <li><i class="bi bi-check text-success me-1"></i>Bank Statement</li>
                                    <li><i class="bi bi-check text-success me-1"></i>Phone & Address</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light rounded p-3">
                                <h6 class="fw-bold"><i class="bi bi-building me-1"></i>Business Seller</h6>
                                <ul class="list-unstyled small text-muted mb-0">
                                    <li><i class="bi bi-check text-success me-1"></i>Government ID + Selfie</li>
                                    <li><i class="bi bi-check text-success me-1"></i>Bank Statement</li>
                                    <li><i class="bi bi-check text-success me-1"></i>Business Permit</li>
                                    <li><i class="bi bi-check text-success me-1"></i>DTI/SEC + BIR + OR Sample</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
