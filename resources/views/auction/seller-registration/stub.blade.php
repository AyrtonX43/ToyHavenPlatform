@extends('layouts.toyshop')

@section('title', 'Auction Seller Registration')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body text-center py-5 px-4">
                    <div class="mb-4">
                        <i class="bi bi-hammer text-primary" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="fw-bold mb-2">Auction Seller Registration</h3>
                    <p class="text-muted mb-4">
                        @if($type === 'business')
                            Business Auction Seller registration with requirements submission is coming soon.
                        @else
                            Individual Auction Seller registration with requirements submission is coming soon.
                        @endif
                    </p>
                    <p class="small text-muted mb-4">
                        You have VIP membership. The full registration flow (document upload, requirements, admin approval) will be available in a future update.
                    </p>
                    <a href="{{ route('membership.manage') }}" class="btn btn-primary rounded-3">
                        <i class="bi bi-arrow-left me-2"></i>Back to Membership
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
