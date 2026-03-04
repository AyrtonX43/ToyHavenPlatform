@extends('layouts.toyshop')

@section('title', 'My Listings - ToyHaven Trading')

@push('styles')
<style>
    .my-listings-page { max-width: 1200px; margin: 0 auto; padding: 0 1rem; }
    .my-listings-header {
        background: white;
        border-radius: 14px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        margin-bottom: 1.5rem;
    }
    .my-listings-header h1 { font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0 0 0.25rem 0; }
    .listing-card {
        background: white;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        transition: box-shadow 0.25s ease, border-color 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .listing-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        border-color: #0ea5e9;
    }
    .listing-image-wrap {
        position: relative;
        width: 100%;
        height: 220px;
        overflow: hidden;
        background: #f8fafc;
    }
    .listing-image-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .listing-badge {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        background: #0ea5e9;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .listing-status-badge {
        position: absolute;
        top: 0.75rem;
        left: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    .listing-status-badge.pending_approval { background: #fef3c7; color: #92400e; }
    .listing-status-badge.active { background: #d1fae5; color: #065f46; }
    .listing-card-body { padding: 1.25rem; flex-grow: 1; display: flex; flex-direction: column; }
    .listing-title { font-size: 1rem; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .listing-meta { font-size: 0.8125rem; color: #64748b; margin-top: auto; padding-top: 0.75rem; display: flex; gap: 1rem; }
    .listing-actions { display: flex; gap: 0.5rem; margin-top: 0.75rem; flex-wrap: wrap; }
    .btn-listing { font-size: 0.8125rem; padding: 0.375rem 0.75rem; border-radius: 8px; font-weight: 500; }
    .empty-listings { text-align: center; padding: 3rem 2rem; background: #f8fafc; border-radius: 14px; border: 2px dashed #e2e8f0; }
</style>
@endpush

@section('content')
<div class="container py-4">
<div class="my-listings-page">
    <div class="my-listings-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1>My Listings</h1>
                <p class="text-muted mb-0">Manage your trade listings</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('trading.trades.history') }}" class="btn btn-outline-primary">
                    <i class="bi bi-clock-history me-1"></i>Trade History
                </a>
                <a href="{{ route('trading.listings.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Create Listing
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($listings->count() > 0)
    <div class="row g-4">
        @foreach($listings as $listing)
        <div class="col-md-6 col-lg-4">
            <div class="listing-card">
                <a href="{{ route('trading.listings.show', $listing->id) }}" class="text-decoration-none">
                    <div class="listing-image-wrap">
                        @php
                            $item = $listing->getItem();
                            $primaryImage = $listing->images->first() ?? ($item ? ($item->images->first() ?? null) : null);
                            if (!$primaryImage && $listing->image_path) {
                                $primaryImage = (object)['image_path' => $listing->image_path];
                            }
                        @endphp
                        @if($primaryImage)
                        <img src="{{ asset('storage/' . $primaryImage->image_path) }}" alt="{{ $listing->title }}">
                        @else
                        <div class="d-flex align-items-center justify-content-center h-100">
                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                        </div>
                        @endif
                        <span class="listing-status-badge {{ $listing->status }}">{{ $listing->getStatusLabel() }}</span>
                        <span class="listing-badge">{{ ucfirst(str_replace('_', ' ', $listing->trade_type ?? 'Trade')) }}</span>
                    </div>
                </a>
                <div class="listing-card-body">
                    <a href="{{ route('trading.listings.show', $listing->id) }}" class="text-decoration-none text-dark">
                        <div class="listing-title">{{ Str::limit($listing->title, 50) }}</div>
                    </a>
                    <div class="listing-meta">
                        <span><i class="bi bi-eye me-1"></i>{{ $listing->views_count }} views</span>
                        <span><i class="bi bi-chat-dots me-1"></i>{{ $listing->offers_count }} offers</span>
                    </div>
                    <div class="listing-actions">
                        <a href="{{ route('trading.listings.show', $listing->id) }}" class="btn btn-outline-primary btn-listing">
                            <i class="bi bi-eye me-1"></i>View
                        </a>
                        @if(in_array($listing->status, ['active', 'pending_approval']))
                        <a href="{{ route('trading.listings.edit', $listing->id) }}" class="btn btn-outline-secondary btn-listing">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </a>
                        @endif
                        @if($listing->status !== 'pending_trade')
                        <form method="POST" action="{{ route('trading.listings.destroy', $listing->id) }}" class="d-inline" onsubmit="return confirm('Cancel this listing?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-listing">
                                <i class="bi bi-trash me-1"></i>Cancel
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $listings->links() }}
    </div>
    @else
    <div class="empty-listings">
        <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
        <h5 class="mt-3">No listings yet</h5>
        <p class="text-muted mb-4">Create your first trade listing to start swapping toys with other collectors</p>
        <a href="{{ route('trading.listings.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Create Listing
        </a>
    </div>
    @endif
</div>
</div>
@endsection
