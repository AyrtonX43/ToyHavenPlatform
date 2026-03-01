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
                                <i class="bi bi-shop me-2"></i>Register as Local Business Toyshop
                            </a>
                            <a href="{{ route('seller.register', ['type' => 'verified']) }}" class="btn btn-success btn-lg px-5">
                                <i class="bi bi-shield-check me-2"></i>Register as Verified Trusted Toyshop
                            </a>
                        </div>
                        <div class="row mt-4 text-start">
                            <div class="col-md-6">
                                <div class="card border-primary h-100">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bi bi-shop text-primary me-2"></i>Local Business Toyshop</h6>
                                        <p class="card-text small text-muted">Perfect for small local toy businesses and individual sellers</p>
                                        <ul class="small">
                                            <li>Basic verification required</li>
                                            <li>Primary ID & Facial Verification</li>
                                            <li>Bank Statement</li>
                                            <li>Start selling quickly</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-success h-100">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bi bi-shield-check text-success me-2"></i>Verified Trusted Toyshop</h6>
                                        <p class="card-text small text-muted">For established businesses with proper documentation</p>
                                        <ul class="small">
                                            <li>Enhanced verification & trust badge</li>
                                            <li>Business Permit & BIR Certificate</li>
                                            <li>Product Sample verification</li>
                                            <li>Priority support & featured placement</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
