@extends('layouts.toyshop')

@section('title', 'Membership Plans - ToyHaven')

@push('styles')
<style>
    .membership-hero {
        background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 40%, #0284c7 100%);
        color: white;
        padding: 4rem 0;
        margin-bottom: 3rem;
        border-radius: 0 0 32px 32px;
        box-shadow: 0 12px 40px rgba(2, 132, 199, 0.25);
        position: relative;
        overflow: hidden;
    }
    .membership-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 60%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
        pointer-events: none;
    }
    .plan-card {
        border: 2px solid #e2e8f0;
        border-radius: 24px;
        overflow: hidden;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        background: #fff;
        position: relative;
    }
    .plan-card:hover {
        border-color: #0284c7;
        box-shadow: 0 20px 50px rgba(2, 132, 199, 0.15);
        transform: translateY(-6px);
    }
    .plan-card.featured {
        border-color: #0284c7;
        box-shadow: 0 12px 40px rgba(2, 132, 199, 0.2);
        border-width: 3px;
    }
    .plan-card.featured::before {
        content: 'Most Popular';
        position: absolute;
        top: 16px;
        right: -32px;
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        color: white;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 4px 36px;
        transform: rotate(45deg);
        z-index: 1;
        letter-spacing: 0.5px;
    }
    .plan-header {
        padding: 2.5rem 1.5rem;
        text-align: center;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    }
    .plan-card.featured .plan-header {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        color: white;
    }
    .plan-price {
        font-size: 2.75rem;
        font-weight: 800;
        letter-spacing: -0.02em;
    }
    .plan-features {
        padding: 1.75rem 1.5rem;
    }
    .plan-features ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .plan-features li {
        padding: 0.65rem 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.95rem;
    }
    .plan-features li i {
        color: #0284c7;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .plan-card.featured .plan-features li i {
        color: rgba(255,255,255,0.9);
    }
    .plan-cta {
        display: block;
        padding: 0.85rem 1.5rem;
        font-weight: 600;
        border-radius: 12px;
        transition: all 0.2s ease;
        text-align: center;
        text-decoration: none !important;
    }
    .plan-cta-primary {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        color: white !important;
        border: none;
    }
    .plan-cta-primary:hover {
        background: linear-gradient(135deg, #0369a1 0%, #0c4a6e 100%);
        color: white !important;
        transform: translateY(-1px);
    }
    .plan-card.featured .plan-cta-primary {
        background: white;
        color: #0284c7 !important;
    }
    .plan-card.featured .plan-cta-primary:hover {
        background: #f8fafc;
        color: #0369a1 !important;
    }
    .plan-cta-outline {
        border: 2px solid #0284c7;
        color: #0284c7 !important;
    }
    .plan-cta-outline:hover {
        background: #0284c7;
        color: white !important;
    }
    .step-badges {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }
    .step-badge {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: #e0f2fe;
        color: #0369a1;
        border-radius: 999px;
        font-size: 0.875rem;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="membership-hero">
    <div class="container text-center position-relative">
        <h1 class="mb-3 fw-bold" style="font-size: 2.25rem;">
            <i class="bi bi-gem me-2"></i>ToyHaven Auctions Membership
        </h1>
        <p class="mb-0 opacity-90 lead">Join for exclusive benefits and upgrades. Perfect for collectors and enthusiasts.</p>
        <div class="step-badges mt-4">
            <span class="step-badge"><i class="bi bi-1-circle-fill"></i> Select Plan</span>
            <span class="step-badge"><i class="bi bi-2-circle"></i> Terms & Conditions</span>
            <span class="step-badge"><i class="bi bi-3-circle"></i> Payment</span>
        </div>
    </div>
</div>

<div class="container py-4 pb-5">
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show rounded-3 shadow-sm">
            <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4 justify-content-center">
        @foreach($plans as $plan)
            <div class="col-lg-4 col-md-6">
                <div class="plan-card {{ $plan->slug === 'pro' ? 'featured' : '' }}">
                    <div class="plan-header">
                        <h3 class="h4 fw-bold mb-2">{{ $plan->name }}</h3>
                        <div class="plan-price">₱{{ number_format($plan->price, 0) }}<small class="fs-6 fw-normal opacity-90">/mo</small></div>
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
                        <div class="mt-2">
                            <a href="{{ route('membership.terms', $plan->slug) }}" class="btn btn-link text-decoration-none p-0 small text-muted">
                                <i class="bi bi-file-text me-1"></i>Read Terms & Conditions
                            </a>
                        </div>
                        <a href="{{ route('membership.checkout', $plan->slug) }}" class="plan-cta {{ $plan->slug === 'pro' ? 'plan-cta-primary' : 'plan-cta-outline' }} w-100 mt-4">
                            <i class="bi bi-arrow-right-circle me-2"></i>Select Plan & Proceed to Terms
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
