@extends('layouts.toyshop')

@section('title', 'Auctions - ToyHaven')

@push('styles')
<style>
    .auction-hero {
        background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0284c7 100%);
        color: white;
        padding: 3.5rem 0;
        margin-bottom: 2.5rem;
        border-radius: 0 0 28px 28px;
        box-shadow: 0 10px 40px rgba(2, 132, 199, 0.25);
    }
    .auction-hero h1 { font-weight: 700; letter-spacing: -0.5px; }
    .auction-card {
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        overflow: hidden;
        transition: all 0.35s ease;
        height: 100%;
        background: #fff;
    }
    .auction-card:hover {
        box-shadow: 0 16px 40px rgba(0,0,0,0.12);
        border-color: #0284c7;
        transform: translateY(-4px);
    }
    .auction-card img { height: 220px; object-fit: cover; }
    .auction-card .card-body { padding: 1.25rem 1.5rem; }
    .auction-card .card-title { font-weight: 600; font-size: 1.1rem; }
    .badge-live { background: linear-gradient(135deg, #059669, #10b981); font-weight: 600; padding: 0.4em 0.8em; }
    .badge-ended { background: #64748b; font-weight: 500; padding: 0.4em 0.8em; }
    .plan-card {
        border: 2px solid #e2e8f0;
        border-radius: 24px;
        overflow: hidden;
        transition: all 0.35s ease;
        height: 100%;
        background: #fff;
    }
    .plan-card:hover {
        border-color: #0284c7;
        box-shadow: 0 20px 50px rgba(2, 132, 199, 0.15);
        transform: translateY(-6px);
    }
    .plan-card.featured {
        border-color: #0284c7;
        box-shadow: 0 12px 40px rgba(2, 132, 199, 0.2);
    }
    .plan-header {
        padding: 2.25rem 1.5rem;
        text-align: center;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    }
    .plan-card.featured .plan-header {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        color: white;
    }
    .plan-price { font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; }
    .search-bar-auction {
        background: #fff;
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        margin-bottom: 2rem;
    }
    .search-bar-auction .form-control, .search-bar-auction .form-select {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 0.6rem 1rem;
    }
    .search-bar-auction .btn-primary {
        background: linear-gradient(135deg, #0284c7, #0369a1);
        border: none;
        border-radius: 12px;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="auction-hero">
    <div class="container text-center">
        <h1 class="mb-2"><i class="bi bi-hammer me-2"></i>ToyHaven Auctions</h1>
        <p class="mb-0 opacity-90 fs-5">@if(!empty($showPlansOnly) && $showPlansOnly)Join membership to browse and bid on exclusive collectibles@else Discover rare toys and place your bids @endif</p>
    </div>
</div>

<div class="container py-4">
    @if(session('success') || request('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') ?? request('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(!empty($currentPlan) && $currentPlan)
        <div class="alert alert-light border rounded-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <span><i class="bi bi-gem text-primary me-2"></i><strong>Your current plan:</strong> {{ $currentPlan->name }}</span>
            <a href="{{ route('membership.manage') }}" class="btn btn-sm btn-outline-primary">Manage</a>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show rounded-3">
            <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(!empty($showPlansOnly) && $showPlansOnly && $plans->isNotEmpty())
        <div class="text-center mb-5">
            <h4 class="fw-bold mb-2">Membership Required</h4>
            <p class="text-muted fs-5">Choose a plan to browse auction listings, place bids, and access your bidding history.</p>
        </div>
        <div class="row g-4 justify-content-center">
            @foreach($plans as $plan)
                <div class="col-lg-4 col-md-6">
                    <div class="plan-card card h-100 {{ $plan->slug === 'pro' ? 'featured' : '' }}">
                        <div class="plan-header">
                            <h3 class="h4 fw-bold mb-2">{{ $plan->name }}</h3>
                            <div class="plan-price">₱{{ number_format($plan->price, 0) }}<small class="fs-6 fw-normal opacity-90">/mo</small></div>
                            @if($plan->description)
                                <p class="mb-0 mt-2 small opacity-90">{{ Str::limit($plan->description, 80) }}</p>
                            @endif
                        </div>
                        <div class="card-body">
                            @if($plan->features && is_array($plan->features))
                                <ul class="list-unstyled mb-4">
                                    @foreach($plan->features as $feature)
                                        <li class="py-2"><i class="bi bi-check-circle-fill text-success me-2"></i>{{ $feature }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            <a href="{{ route('membership.checkout', $plan->slug) }}" class="btn {{ $plan->slug === 'pro' ? 'btn-primary' : 'btn-outline-primary' }} w-100 py-2 fw-semibold rounded-3">Select {{ $plan->name }}</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
    <div class="search-bar-auction">
        <form action="{{ route('auctions.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search auctions..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Category</label>
                <select name="category" class="form-select">
                    <option value="">All categories</option>
                    @foreach($categories ?? [] as $c)
                        <option value="{{ $c->id }}" {{ request('category') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Search</button>
            </div>
        </form>
    </div>

    @if(!$canBid)
        <div class="alert alert-light border rounded-3 mb-4">
            <i class="bi bi-info-circle me-2"></i> <a href="{{ route('membership.index') }}" class="fw-semibold">Join membership</a> to place bids on auctions.
        </div>
    @endif

    @if($auctions->isEmpty())
        <div class="text-center py-5 bg-light rounded-3">
            <i class="bi bi-hammer display-4 text-muted mb-3"></i>
            <h4 class="fw-bold">No live auctions</h4>
            <p class="text-muted mb-4">Check back soon for new listings.</p>
            <a href="{{ route('membership.index') }}" class="btn btn-primary rounded-3 px-4">View Membership Plans</a>
        </div>
    @else
        <div class="row g-4">
            @foreach($auctions as $auction)
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('auctions.show', $auction) }}" class="text-decoration-none text-dark">
                        <div class="auction-card card h-100">
                            <div class="position-relative">
                                @if($auction->primaryImage())
                                    <img src="{{ Storage::url($auction->primaryImage()->image_path) }}" class="card-img-top w-100" alt="{{ $auction->title }}">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height:220px"><i class="bi bi-image text-muted display-4"></i></div>
                                @endif
                                <span class="position-absolute top-0 end-0 m-2 badge {{ $auction->hasEnded() ? 'badge-ended' : 'badge-live' }}">{{ $auction->hasEnded() ? 'Ended' : 'Live' }}</span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $auction->title }}</h5>
                                @if($auction->category)<span class="badge bg-light text-dark border">{{ $auction->category->name }}</span>@endif
                                <p class="mb-0 mt-2 fw-bold text-success fs-5">₱{{ number_format($auction->currentPrice(), 0) }}</p>
                                <small class="text-muted">Ends {{ $auction->end_at?->diffForHumans() }}</small>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        <div class="mt-5">{{ $auctions->links() }}</div>
    @endif
    @endif
</div>
@endsection
