@extends('layouts.toyshop')

@section('title', 'Membership Plans - ToyHaven')

@push('styles')
<style>
    .membership-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: white;
        padding: 3rem 0;
        margin-bottom: 2rem;
        border-radius: 0 0 24px 24px;
    }
    .plan-card {
        border: 2px solid #e2e8f0;
        border-radius: 20px;
        overflow: hidden;
        transition: all 0.3s ease;
        height: 100%;
    }
    .plan-card:hover {
        border-color: #0d9488;
        box-shadow: 0 12px 40px rgba(13, 148, 136, 0.15);
    }
    .plan-card.featured {
        border-color: #0d9488;
        box-shadow: 0 8px 32px rgba(13, 148, 136, 0.2);
    }
    .plan-header {
        padding: 2rem 1.5rem;
        text-align: center;
        background: #f8fafc;
    }
    .plan-card.featured .plan-header {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        color: white;
    }
    .plan-price {
        font-size: 2.5rem;
        font-weight: 800;
    }
    .plan-features {
        padding: 1.5rem;
    }
    .plan-features ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .plan-features li {
        padding: 0.5rem 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .plan-features li i {
        color: #0d9488;
        font-size: 1.1rem;
    }
</style>
@endpush

@section('content')
<div class="membership-hero">
    <div class="container text-center">
        <h1 class="mb-2"><i class="bi bi-gem me-2"></i>ToyHaven Auctions Membership</h1>
        <p class="mb-0 opacity-90">Join to view and bid on auctions. Upgrade for analytics and seller registration.</p>
    </div>
</div>

<div class="container py-4">
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-4 justify-content-center">
        @foreach($plans as $plan)
            <div class="col-lg-4 col-md-6">
                <div class="plan-card {{ $plan->slug === 'pro' ? 'featured' : '' }}">
                    <div class="plan-header">
                        <h3 class="h4 fw-bold mb-2">{{ $plan->name }}</h3>
                        <div class="plan-price">₱{{ number_format($plan->price, 0) }}<small class="fs-6 fw-normal">/mo</small></div>
                        @if($plan->description)
                            <p class="mb-0 mt-2 small opacity-90">{{ Str::limit($plan->description, 80) }}</p>
                        @endif
                    </div>
                    <div class="plan-features">
                        @if($plan->features && is_array($plan->features))
                            <ul>
                                @foreach($plan->features as $feature)
                                    <li><i class="bi bi-check-circle-fill"></i> {{ $feature }}</li>
                                @endforeach
                            </ul>
                        @endif
                        <div class="mt-3">
                            <a href="{{ route('membership.terms', $plan->slug) }}" class="btn btn-link text-decoration-none p-0">Terms & Conditions</a>
                        </div>
                        <a href="{{ route('membership.checkout', $plan->slug) }}" class="btn {{ $plan->slug === 'pro' ? 'btn-success' : 'btn-outline-primary' }} w-100 mt-3">Select Plan & View Terms</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
