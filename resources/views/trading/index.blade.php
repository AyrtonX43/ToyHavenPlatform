@extends('layouts.toyshop')

@section('title', 'Trade Marketplace - ToyHaven')

@push('styles')
<style>
    .listings-header { background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 2px 12px rgba(0,0,0,0.06); margin-bottom: 2rem; }
    .listing-card { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; transition: all 0.25s ease; height: 100%; }
    .listing-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.08); transform: translateY(-4px); }
    .listing-img { width: 100%; height: 200px; object-fit: cover; background: #f1f5f9; }
    .trade-type-badge { font-size: 0.7rem; padding: 0.25rem 0.5rem; border-radius: 8px; }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="listings-header">
        <h1 class="h3 fw-bold mb-2">Trade Marketplace</h1>
        <p class="text-muted mb-0">Buy, sell, and trade toys. Meet up locally - no delivery.</p>
    </div>

    <form action="{{ route('trading.index') }}" method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
        </div>
        <div class="col-md-2">
            <select name="trade_type" class="form-select">
                <option value="">All Types</option>
                <option value="exchange" {{ request('trade_type') === 'exchange' ? 'selected' : '' }}>Exchange</option>
                <option value="exchange_with_cash" {{ request('trade_type') === 'exchange_with_cash' ? 'selected' : '' }}>Exchange + Cash</option>
                <option value="cash" {{ request('trade_type') === 'cash' ? 'selected' : '' }}>Cash</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="category_id" class="form-select">
                <option value="">All Categories</option>
                @foreach($categories as $c)
                <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    @auth
    @if(!auth()->user()->isTradeSuspended())
    <div class="mb-4">
        <a href="{{ route('trading.listings.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Create Listing</a>
        <a href="{{ route('trading.listings.my') }}" class="btn btn-outline-secondary ms-2">My Listings</a>
        <a href="{{ route('trading.listings.saved') }}" class="btn btn-outline-secondary ms-2"><i class="bi bi-bookmark me-1"></i>Saved Listings</a>
        <a href="{{ route('trading.trades.index') }}" class="btn btn-outline-secondary ms-2">My Trades</a>
    </div>
    @endif
    @endauth

    @if($listings->count() > 0)
    <div class="row g-4">
        @foreach($listings as $listing)
        <div class="col-md-4 col-lg-3">
            <a href="{{ route('trading.listings.show', $listing->id) }}" class="text-decoration-none text-dark">
                <div class="listing-card">
                    @php $thumb = $listing->getThumbnailImage(); @endphp
                    @if($thumb)
                    <img src="{{ asset('storage/' . $thumb->image_path) }}" alt="{{ $listing->title }}" class="listing-img">
                    @else
                    <div class="listing-img d-flex align-items-center justify-content-center">
                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                    </div>
                    @endif
                    <div class="p-3">
                        <span class="trade-type-badge 
                            @if($listing->trade_type === 'cash') bg-success text-white
                            @elseif($listing->trade_type === 'exchange_with_cash') bg-info text-white
                            @else bg-primary text-white @endif">
                            {{ ucfirst(str_replace('_', ' ', $listing->trade_type)) }}
                        </span>
                        <h6 class="mt-2 mb-1">{{ Str::limit($listing->title, 40) }}</h6>
                        <p class="text-muted small mb-0">{{ $listing->location ?? 'Location TBD' }}</p>
                        @if($listing->cash_amount)
                        <p class="fw-bold text-primary mb-0 mt-1">₱{{ number_format($listing->cash_amount, 0) }}</p>
                        @endif
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $listings->links() }}</div>
    @else
    <div class="text-center py-5 bg-white rounded-3 border">
        <i class="bi bi-inbox display-4 text-muted"></i>
        <h4 class="mt-3">No listings found</h4>
        <p class="text-muted">Try adjusting your filters or create the first listing.</p>
        @auth
        @if(!auth()->user()->isTradeSuspended())
        <a href="{{ route('trading.listings.create') }}" class="btn btn-primary">Create Listing</a>
        @endif
        @endauth
    </div>
    @endif
</div>
@endsection
