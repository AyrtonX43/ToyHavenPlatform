@extends('layouts.toyshop')

@section('title', 'Become a Seller - ToyHaven')

@section('content')
<div class="container">
    <!-- Explanation Section -->
    <div class="row justify-content-center mb-5">
        <div class="col-lg-10">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-shop-window text-primary" style="font-size: 4rem;"></i>
                        <h2 class="mt-3 mb-3">Join ToyHaven as a Seller</h2>
                        <p class="lead text-muted">Turn your passion for toys into a thriving business</p>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    <i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
                                </div>
                                <h5 class="mb-2">Reach Thousands</h5>
                                <p class="text-muted small">Connect with thousands of toy enthusiasts and families looking for quality products</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    <i class="bi bi-graph-up-arrow text-success" style="font-size: 2rem;"></i>
                                </div>
                                <h5 class="mb-2">Grow Your Business</h5>
                                <p class="text-muted small">Expand your reach and grow your toy business with our supportive platform</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    <i class="bi bi-shield-check text-info" style="font-size: 2rem;"></i>
                                </div>
                                <h5 class="mb-2">Secure & Trusted</h5>
                                <p class="text-muted small">Enjoy secure transactions, reliable payment processing, and customer support</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-light rounded p-4 mb-4">
                        <h5 class="mb-3"><i class="bi bi-star-fill text-warning me-2"></i>Why Choose ToyHaven?</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Easy product management</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Real-time order tracking</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Marketing tools and analytics</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Fast and secure payments</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Dedicated seller support</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Build your brand reputation</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <p class="mb-4">Ready to start your journey with ToyHaven? Choose your registration type below!</p>
                        <div class="d-flex gap-3 justify-content-center flex-wrap">
                            <a href="{{ route('seller.register', ['type' => 'basic']) }}" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-arrow-right-circle me-2"></i>Proceed to Register
                            </a>
                            <a href="{{ route('seller.register', ['type' => 'verified']) }}" class="btn btn-success btn-lg px-5">
                                <i class="bi bi-shield-check me-2"></i>Register as Full Verified Trusted Shop
                            </a>
                        </div>
                        <p class="text-muted small mt-3">
                            <strong>Full Verified Trusted Shop:</strong> Get verified badge, priority support, and enhanced trust from customers by providing additional business documents.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
