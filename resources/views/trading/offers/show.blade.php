@extends('layouts.toyshop')

@section('title', 'Offer #' . $offer->id . ' - ToyHaven Trading')

@push('styles')
<style>
    .offer-show-card { background: white; border-radius: 14px; padding: 1.5rem 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 1.5rem; }
    .offer-product-gallery { display: flex; gap: 0.75rem; flex-wrap: wrap; }
    .offer-product-thumb { width: 80px; height: 80px; object-fit: cover; border-radius: 10px; border: 1px solid #e2e8f0; cursor: pointer; }
    .offer-product-thumb:hover { border-color: #0ea5e9; }
    .offer-product-main { max-width: 100%; max-height: 400px; object-fit: contain; border-radius: 12px; background: #f8fafc; }
</style>
@endpush

@section('content')
<div class="container my-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trading.index') }}">Trading</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trading.listings.show', $offer->trade_listing_id) }}">{{ Str::limit($offer->tradeListing->title, 30) }}</a></li>
            <li class="breadcrumb-item active">Offer #{{ $offer->id }}</li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="offer-show-card">
                <h5 class="fw-bold mb-3">Full Product Offer</h5>
                @php
                    $offeredItem = $offer->getOfferedItem();
                    $offeredImages = $offeredItem ? $offeredItem->images : collect();
                @endphp
                @if($offeredItem)
                <div class="row g-4">
                    <div class="col-md-5">
                        @if($offeredImages->isNotEmpty())
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $offeredImages->first()->image_path) }}" alt="{{ $offeredItem->name }}" class="offer-product-main w-100" id="offerMainImage">
                        </div>
                        @if($offeredImages->count() > 1)
                        <div class="offer-product-gallery">
                            @foreach($offeredImages as $img)
                            <img src="{{ asset('storage/' . $img->image_path) }}" alt="{{ $offeredItem->name }}" class="offer-product-thumb" onclick="document.getElementById('offerMainImage').src='{{ asset('storage/' . $img->image_path) }}'">
                            @endforeach
                        </div>
                        @endif
                        @else
                        <div class="d-flex align-items-center justify-content-center bg-light rounded" style="height: 200px;">
                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                        </div>
                        @endif
                    </div>
                    <div class="col-md-7">
                        <h4 class="fw-bold mb-2">{{ $offeredItem->name }}</h4>
                        <p class="text-muted mb-3">{{ $offeredItem->description ?? 'No description.' }}</p>
                        @if($offeredItem instanceof \App\Models\Product)
                        <p class="mb-1"><strong>Value:</strong> ₱{{ number_format($offeredItem->price ?? 0, 2) }}</p>
                        @elseif($offeredItem instanceof \App\Models\UserProduct && $offeredItem->estimated_value)
                        <p class="mb-1"><strong>Estimated value:</strong> ₱{{ number_format($offeredItem->estimated_value, 2) }}</p>
                        @endif
                        @if($offeredItem->brand ?? null)
                        <p class="mb-1"><strong>Brand:</strong> {{ $offeredItem->brand }}</p>
                        @endif
                        @if($offeredItem->condition ?? null)
                        <p class="mb-1"><strong>Condition:</strong> {{ ucfirst($offeredItem->condition) }}</p>
                        @endif
                        @if($offer->cash_amount)
                        <p class="mb-0 text-success"><strong>+ Cash:</strong> ₱{{ number_format($offer->cash_amount, 2) }}</p>
                        @endif
                    </div>
                </div>
                @else
                <p class="text-muted mb-0">Product details not available.</p>
                @endif
            </div>

            @if($offer->message)
            <div class="offer-show-card">
                <h5 class="fw-bold mb-2">Message from {{ $offer->offerer->name }}</h5>
                <p class="mb-0">{{ $offer->message }}</p>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="offer-show-card">
                <h5 class="fw-bold mb-3">Offer Details</h5>
                <p class="mb-2"><strong>Status:</strong> {{ $offer->getStatusLabel() }}</p>
                <p class="mb-2"><strong>From:</strong> {{ $offer->offerer->name }}</p>
                <p class="mb-2"><strong>Listing:</strong> <a href="{{ route('trading.listings.show', $offer->trade_listing_id) }}">{{ Str::limit($offer->tradeListing->title, 40) }}</a></p>
                <p class="mb-3 text-muted small">{{ $offer->created_at->format('M j, Y g:i A') }}</p>

                @if($canManage && $offer->tradeListing->user_id === Auth::id() && $offer->status === 'pending')
                <div class="d-flex gap-2 flex-wrap">
                    <form method="POST" action="{{ route('trading.offers.accept', $offer->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">Accept Offer</button>
                    </form>
                    <form method="POST" action="{{ route('trading.offers.reject', $offer->id) }}" class="d-inline" onsubmit="return confirm('Reject this offer?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">Reject</button>
                    </form>
                </div>
                @endif

                @if($canManage && $offer->offerer_id === Auth::id() && $offer->canBeWithdrawn())
                <form method="POST" action="{{ route('trading.offers.withdraw', $offer->id) }}" class="mt-2" onsubmit="return confirm('Withdraw this offer?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Withdraw Offer</button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
