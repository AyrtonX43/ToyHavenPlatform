@extends('layouts.toyshop')

@section('title', 'Auctions - ToyHaven')

@push('styles')
<style>
    /* Hero / Intro Section */
    .auction-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0e7490 100%);
        border-radius: 24px;
        padding: 3.5rem 2.5rem;
        color: white;
        margin-bottom: 3rem;
        box-shadow: 0 20px 60px rgba(8, 145, 178, 0.25);
        position: relative;
        overflow: hidden;
    }
    .auction-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 60%;
        height: 200%;
        background: radial-gradient(ellipse, rgba(6, 182, 212, 0.2) 0%, transparent 70%);
        pointer-events: none;
    }
    .auction-hero h1 {
        font-size: 2.25rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        margin-bottom: 0.75rem;
        position: relative;
    }
    .auction-hero .hero-subtitle {
        font-size: 1.15rem;
        opacity: 0.9;
        line-height: 1.7;
        max-width: 600px;
        position: relative;
    }
    .auction-hero .hero-cta {
        margin-top: 1.75rem;
        position: relative;
    }
    .auction-hero .hero-icon {
        width: 72px;
        height: 72px;
        border-radius: 20px;
        background: rgba(255,255,255,0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-bottom: 1.5rem;
        position: relative;
    }

    /* Intro for non-members */
    .auction-intro-section {
        background: linear-gradient(180deg, #f8fafc 0%, #fff 100%);
        border-radius: 20px;
        padding: 2.5rem;
        margin-bottom: 3rem;
        border: 2px solid #e2e8f0;
    }
    .intro-step {
        display: flex;
        align-items: flex-start;
        gap: 1.25rem;
        padding: 1.25rem 0;
        border-bottom: 1px solid #e2e8f0;
    }
    .intro-step:last-child { border-bottom: none; }
    .intro-step-num {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, #0891b2, #06b6d4);
        color: white;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .intro-step h4 { font-size: 1.1rem; font-weight: 700; margin-bottom: 0.35rem; }
    .intro-step p { color: #64748b; margin: 0; font-size: 0.95rem; }

    /* Membership Comparison */
    .membership-comparison-section {
        margin-bottom: 3rem;
    }
    .section-label {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #0891b2;
        margin-bottom: 0.5rem;
    }
    .section-heading {
        font-size: 1.75rem;
        font-weight: 800;
        color: #1e293b;
        letter-spacing: -0.02em;
        margin-bottom: 0.5rem;
    }
    .plan-comparison-card {
        background: #fff;
        border-radius: 18px;
        padding: 2rem;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        border: 2px solid #e2e8f0;
        height: 100%;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .plan-comparison-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 16px 48px rgba(8, 145, 178, 0.15);
        border-color: #0891b2;
    }
    .plan-comparison-card.featured {
        border-color: #0891b2;
        border-width: 3px;
        box-shadow: 0 8px 32px rgba(8, 145, 178, 0.2);
    }
    .plan-comparison-card.featured::before {
        content: 'Most Popular';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, #0891b2, #06b6d4);
        color: white;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.5rem;
        text-align: center;
        letter-spacing: 0.05em;
    }
    .plan-comparison-card.featured { padding-top: 3rem; }
    .plan-comparison-card .plan-name { font-size: 1.5rem; font-weight: 800; color: #1e293b; }
    .plan-comparison-card .plan-price {
        font-size: 2.25rem;
        font-weight: 800;
        color: #0891b2;
        margin: 0.5rem 0 1rem;
    }
    .plan-comparison-card .plan-price span { font-size: 0.9rem; font-weight: 600; color: #64748b; }
    .plan-comparison-card .plan-benefits {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .plan-comparison-card .plan-benefits li {
        padding: 0.6rem 0;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-weight: 500;
        color: #334155;
        font-size: 0.95rem;
    }
    .plan-comparison-card .plan-benefits li i {
        color: #10b981;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    /* Auction cards */
    .auctions-header {
        background: linear-gradient(135deg, #0891b2 0%, #06b6d4 50%, #fb923c 100%);
        border-radius: 18px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
        box-shadow: 0 8px 32px rgba(8, 145, 178, 0.3);
    }
    .auction-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 16px rgba(0,0,0,0.06);
        border: 2px solid #e2e8f0;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .auction-card:hover {
        border-color: #0891b2;
        box-shadow: 0 12px 40px rgba(8, 145, 178, 0.15);
        transform: translateY(-6px);
    }
    .auction-card.teaser {
        position: relative;
    }
    .auction-card.teaser .teaser-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, transparent 40%, rgba(15, 23, 42, 0.85) 100%);
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding: 2rem 1rem 1.5rem;
        pointer-events: none;
    }
    .auction-card.teaser .teaser-overlay .teaser-cta {
        display: inline-flex;
        align-items: center;
        background: linear-gradient(135deg, #0891b2, #06b6d4);
        color: white !important;
        padding: 0.6rem 1.25rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.9rem;
        box-shadow: 0 4px 16px rgba(8, 145, 178, 0.4);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .auction-card.teaser:hover .teaser-overlay .teaser-cta {
        transform: scale(1.05);
        box-shadow: 0 6px 24px rgba(8, 145, 178, 0.5);
    }
    .auction-image-wrap {
        height: 220px;
        background: #f8fafc;
        overflow: hidden;
        position: relative;
    }
    .auction-image-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .auction-card:hover .auction-image-wrap img {
        transform: scale(1.06);
    }
    .auction-price {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0891b2;
    }
    .members-only-badge {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        background: rgba(245, 158, 11, 0.95);
        color: white;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.3rem 0.6rem;
        border-radius: 8px;
    }

    /* Responsive */
    @media (max-width: 767px) {
        .auction-hero { border-radius: 14px; padding: 2rem 1.25rem; }
        .auction-hero h1 { font-size: 1.5rem; }
        .auction-hero p { font-size: 0.9375rem; }
        .auction-card-body { padding: 1rem; }
        .auction-card-body h5 { font-size: 0.9375rem; }
        .auction-price { font-size: 1.1875rem; }
    }
    @media (max-width: 575px) {
        .auction-hero { border-radius: 10px; padding: 1.5rem 1rem; }
        .auction-hero h1 { font-size: 1.25rem; }
        .auction-card-body { padding: 0.75rem; }
        .auction-card-body h5 { font-size: 0.8125rem; }
        .auction-price { font-size: 1.0625rem; }
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    {{-- Hero Section --}}
    <div class="auction-hero reveal">
        <div class="hero-icon">
            <i class="bi bi-hammer"></i>
        </div>
        <h1>Live Auctions</h1>
        <p class="hero-subtitle">
            Bid on rare toys and collectibles. Discover limited editions, vintage finds, and exclusive items from verified sellers.
        </p>
        <div class="hero-cta d-flex align-items-center flex-wrap gap-3">
            @if($hasMembership)
                <a href="{{ route('auctions.my-bids') }}" class="btn btn-light btn-lg fw-bold px-4" style="color: #0891b2; border-radius: 12px;">
                    <i class="bi bi-list-check me-2"></i>My Bids
                </a>
            @else
                <a href="#membership-plans" class="btn btn-light btn-lg fw-bold px-4" style="color: #0891b2; border-radius: 12px;">
                    <i class="bi bi-gem me-2"></i>Join Membership to Bid
                </a>
            @endif
            @if($auctions->isNotEmpty())
                <span class="badge bg-white text-dark px-3 py-2" style="font-size: 0.9rem;">
                    <i class="bi bi-lightning-charge-fill text-warning me-1"></i>{{ $auctions->total() }} active auction(s)
                </span>
            @endif
        </div>
    </div>

    @if(!$hasMembership && $plans->isNotEmpty())
        {{-- Introduction: How Auctions Work --}}
        <div class="auction-intro-section reveal">
            <p class="section-label mb-1">How it works</p>
            <h2 class="section-heading mb-4">Why join ToyHaven Auctions?</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="intro-step">
                        <div class="intro-step-num">1</div>
                        <div>
                            <h4>Browse Live Auctions</h4>
                            <p>Explore rare toys, collectibles, and limited editions from our community.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="intro-step">
                        <div class="intro-step-num">2</div>
                        <div>
                            <h4>Place Your Bids</h4>
                            <p>Members get exclusive access to bid and compete for items they love.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="intro-step">
                        <div class="intro-step-num">3</div>
                        <div>
                            <h4>Win & Collect</h4>
                            <p>Win auctions and enjoy premium perks like lower fees and toyshop discounts.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Membership Plan Comparison --}}
        <div id="membership-plans" class="membership-comparison-section">
            <p class="section-label text-center mb-1">Choose your plan</p>
            <h2 class="section-heading text-center mb-2">Membership Comparison</h2>
            <p class="text-center text-muted mb-4">Join to unlock auction access and exclusive benefits</p>

            <div class="row g-4">
                @foreach($plans as $index => $plan)
                    <div class="col-lg-4">
                        <div class="plan-comparison-card {{ $plan->slug === 'pro' ? 'featured' : '' }} reveal" style="transition-delay: {{ $index * 0.1 }}s;">
                            <h3 class="plan-name">{{ $plan->name }}</h3>
                            <p class="text-muted small mb-0">{{ $plan->description }}</p>
                            <div class="plan-price">
                                ₱{{ number_format($plan->price, 0) }}
                                <span>/{{ $plan->interval === 'monthly' ? 'month' : 'year' }}</span>
                            </div>
                            <ul class="plan-benefits">
                                @foreach($plan->features ?? [] as $feature)
                                    <li><i class="bi bi-check-circle-fill"></i> {{ $feature }}</li>
                                @endforeach
                            </ul>
                            @auth
                                @if(auth()->user()->hasActiveMembership() && auth()->user()->currentPlan()?->id === $plan->id)
                                    <button class="btn btn-outline-secondary w-100 mt-3" disabled>Current Plan</button>
                                @else
                                    <form action="{{ route('membership.subscribe') }}" method="POST" class="mt-3">
                                        @csrf
                                        <input type="hidden" name="plan" value="{{ $plan->slug }}">
                                        <button type="submit" class="btn w-100 {{ $plan->slug === 'pro' ? 'btn-primary' : 'btn-outline-primary' }} py-3 fw-bold" style="{{ $plan->slug === 'pro' ? 'background: linear-gradient(135deg, #0891b2, #06b6d4); border: none; border-radius: 12px;' : 'border-radius: 12px;' }}">
                                            Get {{ $plan->name }}
                                        </button>
                                    </form>
                                @endif
                            @else
                                <a href="{{ route('login', ['redirect' => route('membership.index', ['intent' => 'auction'])]) }}" class="btn btn-primary w-100 py-3 fw-bold mt-3" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none; border-radius: 12px;">
                                    Sign in to Subscribe
                                </a>
                            @endauth
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Auctions Section --}}
    <div class="reveal">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <h2 class="h4 fw-bold mb-0" style="color: #1e293b;">
                <i class="bi bi-lightning-charge-fill text-warning me-2"></i>Live Auctions
            </h2>
            @if($hasMembership)
                <a href="{{ route('auctions.my-bids') }}" class="btn btn-outline-primary">
                    <i class="bi bi-list-check me-1"></i>My Bids
                </a>
            @endif
        </div>

        @if($auctions->isEmpty())
            <div class="text-center py-5 reveal">
                <div class="d-inline-block p-4 rounded-3 bg-light">
                    <i class="bi bi-hammer text-muted" style="font-size: 4rem;"></i>
                </div>
                <h4 class="mt-4 fw-bold text-muted">No live auctions at the moment</h4>
                <p class="text-muted mb-4">Check back soon for new items. Join membership to be notified!</p>
                @if(!$hasMembership)
                    <a href="#membership-plans" class="btn btn-primary px-4 py-3" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none; border-radius: 12px;">
                        <i class="bi bi-gem me-2"></i>Join Membership
                    </a>
                @endif
            </div>
        @else
            <div class="row g-4">
                @foreach($auctions as $index => $auction)
                    <div class="col-md-6 col-lg-4">
                        <a href="{{ $hasMembership ? route('auctions.show', $auction) : route('membership.index', ['intent' => 'auction']) }}" class="text-decoration-none text-dark">
                            <div class="auction-card {{ !$hasMembership ? 'teaser' : '' }} reveal" style="transition-delay: {{ min($index, 6) * 0.05 }}s;">
                                <div class="auction-image-wrap">
                                    @if($imgUrl = $auction->getPrimaryImageUrl())
                                        <img src="{{ $imgUrl }}" alt="{{ $auction->title }}">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                            <i class="bi bi-image" style="font-size: 3rem;"></i>
                                        </div>
                                    @endif
                                    @if($auction->is_members_only)
                                        <span class="members-only-badge">Members Only</span>
                                    @endif
                                    @if(!$hasMembership)
                                        <div class="teaser-overlay">
                                            <span class="teaser-cta">
                                                <i class="bi bi-gem me-1"></i>Join to Bid
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="p-3 flex-grow-1 d-flex flex-column">
                                    <h5 class="fw-bold mb-2 text-truncate" title="{{ $auction->title }}">{{ $auction->title }}</h5>
                                    <div class="auction-price mb-2">
                                        ₱{{ number_format($auction->getCurrentPrice(), 0) }}
                                        @if($auction->bids_count > 0)
                                            <small class="text-muted fw-normal">({{ $auction->bids_count }} bid{{ $auction->bids_count !== 1 ? 's' : '' }})</small>
                                        @endif
                                    </div>
                                    <div class="mt-auto text-muted small">
                                        <i class="bi bi-clock me-1"></i>Ends {{ $auction->end_at?->diffForHumans() ?? 'TBD' }}
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
            <div class="d-flex justify-content-center mt-4 reveal">
                {{ $auctions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
