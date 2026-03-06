@extends('layouts.toyshop')

@section('title', 'Auctions - ToyHaven')

@push('styles')
<style>
    .auction-hero { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white; padding: 2.5rem 0; margin-bottom: 2rem; border-radius: 0 0 24px 24px; }
    .auction-card { border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; transition: all 0.3s; height: 100%; }
    .auction-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.08); border-color: #0d9488; }
    .auction-card img { height: 200px; object-fit: cover; }
    .auction-card .badge-live { background: #059669; }
    .auction-card .badge-ended { background: #6b7280; }
</style>
@endpush

@section('content')
<div class="auction-hero">
    <div class="container text-center">
        <h1 class="mb-2"><i class="bi bi-hammer me-2"></i>ToyHaven Auctions</h1>
        <p class="mb-0 opacity-90">Browse and bid on rare collectibles</p>
    </div>
</div>

<div class="container py-4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if(session('info'))<div class="alert alert-info">{{ session('info') }}</div>@endif

    <form action="{{ route('auctions.index') }}" method="GET" class="row g-2 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search auctions..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
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

    @if(!$canBid)
        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle me-2"></i> <a href="{{ route('membership.index') }}">Join membership</a> to place bids on auctions.
        </div>
    @endif

    @if($auctions->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-hammer display-4 text-muted mb-3"></i>
            <h4>No live auctions</h4>
            <p class="text-muted">Check back soon for new listings.</p>
            <a href="{{ route('membership.index') }}" class="btn btn-primary">View Membership Plans</a>
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
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height:200px"><i class="bi bi-image text-muted display-4"></i></div>
                                @endif
                                <span class="position-absolute top-0 end-0 m-2 badge {{ $auction->hasEnded() ? 'bg-secondary' : 'bg-success' }}">{{ $auction->hasEnded() ? 'Ended' : 'Live' }}</span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title text-truncate">{{ $auction->title }}</h5>
                                @if($auction->category)<span class="badge bg-light text-dark">{{ $auction->category->name }}</span>@endif
                                <p class="mb-0 mt-2 fw-bold text-success">Current: ₱{{ number_format($auction->currentPrice(), 0) }}</p>
                                <small class="text-muted">Ends {{ $auction->end_at?->diffForHumans() }}</small>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $auctions->links() }}</div>
    @endif
</div>
@endsection
