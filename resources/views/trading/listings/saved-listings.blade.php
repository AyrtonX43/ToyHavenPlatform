@extends('layouts.toyshop')
@section('title', 'Saved Listings - ToyHaven Trade')
@section('content')
<div class="container py-4">
    <h1 class="h3 fw-bold mb-2">Saved Listings</h1>
    <p class="text-muted mb-4">Listings you've saved for later.</p>

    @if(session('success'))<div class="alert alert-success rounded-3">{{ session('success') }}</div>@endif
    @if(session('info'))<div class="alert alert-info rounded-3">{{ session('info') }}</div>@endif

    @if($saved->count() > 0)
    <div class="row g-4">
        @foreach($saved as $item)
        @php $listing = $item->tradeListing; @endphp
        @if($listing)
        <div class="col-md-4 col-lg-3">
            <div class="card h-100 border rounded-3 overflow-hidden shadow-sm">
                <div class="position-relative">
                    @php $thumb = $listing->getThumbnailImage(); @endphp
                    @if($thumb)
                    <img src="{{ asset('storage/' . $thumb->image_path) }}" class="card-img-top" style="height: 180px; object-fit: cover;" alt="{{ $listing->title }}">
                    @else
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px;"><i class="bi bi-image text-muted fs-1"></i></div>
                    @endif
                    @if($listing->isNoLongerAvailable())
                    <div class="position-absolute top-0 start-0 end-0 p-2 text-center" style="background: rgba(0,0,0,0.7);">
                        <span class="badge bg-secondary">Sold / No longer available</span>
                    </div>
                    @endif
                </div>
                <div class="card-body">
                    <span class="badge bg-{{ $listing->trade_type === 'cash' ? 'success' : ($listing->trade_type === 'exchange_with_cash' ? 'info' : 'primary') }} mb-2">
                        {{ ucfirst(str_replace('_', ' ', $listing->trade_type)) }}
                    </span>
                    <h6 class="card-title">{{ Str::limit($listing->title, 45) }}</h6>
                    <p class="card-text small text-muted mb-2">{{ $listing->location ?? 'Location TBD' }}</p>
                    @if($listing->cash_amount)
                    <p class="fw-bold text-primary mb-2">₱{{ number_format($listing->cash_amount, 0) }}</p>
                    @endif
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('trading.listings.show', $listing->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                        <form action="{{ route('trading.listings.unsave', $listing->id) }}" method="POST" class="d-inline">@csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>
    <div class="mt-4">{{ $saved->links() }}</div>
    @else
    <div class="text-center py-5 bg-light rounded-3 border">
        <i class="bi bi-bookmark display-4 text-muted"></i>
        <h5 class="mt-3">No saved listings</h5>
        <p class="text-muted">Save listings you like by clicking the heart icon when viewing them.</p>
        <a href="{{ route('trading.index') }}" class="btn btn-primary">Browse Listings</a>
    </div>
    @endif
</div>
@endsection
