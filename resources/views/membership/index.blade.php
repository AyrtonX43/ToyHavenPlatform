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
        top: 0; left: 0; right: 0; bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.5;
    }
    .membership-hero .container { position: relative; z-index: 1; }
    .membership-hero h1 { font-weight: 700; text-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .membership-steps {
        display: flex;
        justify-content: center;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 2rem;
        padding: 1rem;
    }
    .step-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255,255,255,0.2);
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.9rem;
    }
    .step-badge i { font-size: 1.1rem; }
    .plan-card {
        border: 2px solid #e2e8f0;
        border-radius: 24px;
        overflow: hidden;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        background: #fff;
    }
    .plan-card:hover {
        border-color: #0284c7;
        box-shadow: 0 20px 50px rgba(2, 132, 199, 0.18);
        transform: translateY(-6px);
    }
    .plan-card.featured {
        border-color: #0284c7;
        box-shadow: 0 12px 40px rgba(2, 132, 199, 0.22);
        position: relative;
    }
    .plan-card.featured::before {
        content: 'Most Popular';
        position: absolute;
        top: 0; right: 0;
        background: linear-gradient(135deg, #0284c7, #0369a1);
        color: white;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.35rem 0.9rem;
        border-radius: 0 24px 0 12px;
        z-index: 2;
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
        padding: 2rem 1.5rem;
    }
    .plan-features ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .plan-features li {
        padding: 0.6rem 0;
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
    .btn-select-plan {
        padding: 0.9rem 1.5rem;
        font-weight: 600;
        border-radius: 12px;
        transition: all 0.25s ease;
        width: 100%;
    }
    .plan-card.featured .btn-select-plan {
        background: linear-gradient(135deg, #0369a1, #0284c7);
        border: none;
        color: white;
    }
    .plan-card.featured .btn-select-plan:hover {
        background: linear-gradient(135deg, #0284c7, #0ea5e9);
        color: white;
        transform: translateY(-2px);
    }
    .vip-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        margin-top: 0.25rem;
    }
</style>
@endpush

@section('content')
<div class="membership-hero">
    <div class="container text-center">
        <h1 class="mb-3"><i class="bi bi-gem me-2"></i>ToyHaven Auctions Membership</h1>
        <p class="mb-0 opacity-90 fs-5">Join to view and bid on auctions. Upgrade for analytics and auction seller registration.</p>
        <div class="membership-steps">
            <span class="step-badge"><i class="bi bi-1-circle-fill"></i> Select Plan</span>
            <span class="step-badge"><i class="bi bi-2-circle"></i> Terms & Conditions</span>
            <span class="step-badge"><i class="bi bi-3-circle"></i> Payment</span>
            <span class="step-badge"><i class="bi bi-4-circle"></i> Receipt</span>
        </div>
    </div>
</div>

<div class="container py-4">
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 justify-content-center">
        @foreach($plans as $plan)
            <div class="col-lg-4 col-md-6">
                <div class="plan-card {{ $plan->slug === 'pro' ? 'featured' : '' }}">
                    <div class="plan-header">
                        <h3 class="h4 fw-bold mb-2">{{ $plan->name }}</h3>
                        @if($plan->slug === 'vip')
                            <span class="vip-badge"><i class="bi bi-stars"></i> Auction Seller Access</span>
                        @endif
                        <div class="plan-price mt-2">₱{{ number_format($plan->price, 0) }}<small class="fs-6 fw-normal opacity-90">/mo</small></div>
                        @if($plan->description)
                            <p class="mb-0 mt-2 small opacity-90">{{ Str::limit($plan->description, 100) }}</p>
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
                        <a href="{{ route('membership.checkout', $plan->slug) }}" class="btn btn-select-plan {{ $plan->slug === 'pro' ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="bi bi-arrow-right-circle me-2"></i>Select Plan
                        </a>
                        <p class="text-muted small text-center mt-3 mb-0">You will review terms & conditions next</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
