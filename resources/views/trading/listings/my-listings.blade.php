@extends('layouts.toyshop')

@section('title', 'My Listings - ToyHaven Trading')

@push('styles')
<style>
    .my-listings-page { max-width: 1200px; margin: 0 auto; padding: 0 1rem; }
    .listings-header { background: white; border-radius: 14px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; }
    .listing-card { background: white; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; transition: box-shadow 0.2s, border-color 0.2s; display: flex; flex-direction: column; height: 100%; }
    .listing-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); border-color: #e2e8f0; }
    .listing-card-image { height: 180px; background: #f8fafc; overflow: hidden; }
    .listing-card-image img { width: 100%; height: 100%; object-fit: cover; }
    .listing-card-body { padding: 1.25rem; flex: 1; display: flex; flex-direction: column; }
    .listing-card-title { font-size: 1rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .listing-card-meta { font-size: 0.8125rem; color: #64748b; margin-bottom: 0.75rem; }
    .listing-card-actions { margin-top: auto; display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .listing-card-actions .btn { font-size: 0.8125rem; }
    .badge-status { font-size: 0.75rem; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="my-listings-page">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('trading.index') }}">Trading</a></li>
                <li class="breadcrumb-item active">My Listings</li>
            </ol>
        </nav>

        <div class="listings-header">
            <div>
                <h1 class="h4 mb-1 fw-bold">My Listings</h1>
                <p class="text-muted small mb-0">Manage your trade listings and view trade history</p>
            </div>
            <a href="{{ route('trading.listings.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Create Listing
            </a>
        </div>

        @if($listings->count() > 0)
            <div class="row g-4">
                @foreach($listings as $listing)
                    <div class="col-md-6 col-lg-4">
                        <div class="listing-card">
                            <div class="listing-card-image">
                                @php
                                    $img = $listing->images->first();
                                    $item = $listing->getItem();
                                    if (!$img && $item && $item->images->isNotEmpty()) {
                                        $img = $item->images->first();
                                    }
                                @endphp
                                @if($img)
                                    <img src="{{ asset('storage/' . $img->image_path) }}" alt="{{ $listing->title }}">
                                @else
                                    <div class="d-flex align-items-center justify-content-center h-100">
                                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="listing-card-body">
                                <h2 class="listing-card-title">{{ Str::limit($listing->title, 60) }}</h2>
                                <div class="listing-card-meta">
                                    <span class="badge badge-status rounded-pill
                                        @if($listing->status === 'active') bg-success
                                        @elseif($listing->status === 'pending_approval') bg-warning text-dark
                                        @elseif($listing->status === 'rejected') bg-danger
                                        @elseif($listing->status === 'completed') bg-secondary
                                        @elseif($listing->status === 'pending_trade') bg-info
                                        @else bg-light text-dark
                                        @endif
                                    ">{{ $listing->getStatusLabel() }}</span>
                                    <span class="ms-1">{{ ucfirst(str_replace('_', ' ', $listing->trade_type ?? 'trade')) }}</span>
                                    <span class="d-block mt-1">{{ $listing->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="listing-card-actions">
                                    <a href="{{ route('trading.listings.show', $listing->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    @if(in_array($listing->status, ['active', 'completed', 'pending_trade']))
                                        <a href="{{ route('trading.listings.history', $listing->id) }}" class="btn btn-sm btn-outline-secondary">Trade History</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                {{ $listings->links() }}
            </div>
        @else
            <div class="text-center py-5 bg-white rounded-3 border">
                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3 fw-bold">No listings yet</h5>
                <p class="text-muted mb-4">Create your first trade listing to start trading with others.</p>
                <a href="{{ route('trading.listings.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Create Listing</a>
            </div>
        @endif
    </div>
</div>
@endsection
