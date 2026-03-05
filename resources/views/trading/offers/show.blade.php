@extends('layouts.toyshop')
@section('title', 'Offer Details - ToyHaven Trade')
@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trading.index') }}">Trading</a></li>
            @if($offer->offerer_id === auth()->id())
            <li class="breadcrumb-item"><a href="{{ route('trading.offers.my') }}">My Offers</a></li>
            @else
            <li class="breadcrumb-item"><a href="{{ route('trading.offers.received') }}">Offers Received</a></li>
            @endif
            <li class="breadcrumb-item active">Offer #{{ $offer->id }}</li>
        </ol>
    </nav>

    <h1 class="h4 mb-4">Offer Details</h1>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="row g-4">
        {{-- Seller's listing (what the offer is on) --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header fw-semibold">Seller's listing (your item)</div>
                <div class="card-body">
                    <div class="d-flex gap-3">
                        @php $listing = $offer->tradeListing; $thumb = $listing->getThumbnailImage(); @endphp
                        @if($thumb)
                        <img src="{{ asset('storage/' . $thumb->image_path) }}" alt="" class="rounded" style="width:100px;height:100px;object-fit:cover;">
                        @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:100px;height:100px;"><i class="bi bi-image text-muted"></i></div>
                        @endif
                        <div>
                            <h6 class="mb-1">{{ $listing->title }}</h6>
                            <p class="small text-muted mb-1">Listed by {{ $listing->user->name }}</p>
                            <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $listing->trade_type)) }}</span>
                            @if($listing->cash_amount)<p class="mb-0 mt-1">₱{{ number_format($listing->cash_amount, 0) }}</p>@endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Offerer's offered item (clickable to view full listing) --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header fw-semibold">Offered by {{ $offer->offerer->name }}</div>
                <div class="card-body">
                    @php
                        $offeredListing = $offer->offeredTradeListing ?? null;
                        $offeredProduct = $offer->offeredProduct;
                        $offeredUserProduct = $offer->offeredUserProduct;
                        $hasItem = $offeredListing || $offeredProduct || $offeredUserProduct;
                        $offeredListingUrl = $offeredListing ? route('trading.listings.show', ['id' => $offeredListing->id, 'offer_id' => $offer->id]) : null;
                    @endphp
                    @if($hasItem)
                    <div class="d-flex gap-3">
                        @if($offeredListing)
                            <a href="{{ $offeredListingUrl }}" class="text-decoration-none text-dark d-flex gap-3 align-items-start flex-grow-1" title="View full listing">
                            @php $oThumb = $offeredListing->getThumbnailImage(); @endphp
                            @if($oThumb)
                            <img src="{{ asset('storage/' . $oThumb->image_path) }}" alt="" class="rounded" style="width:100px;height:100px;object-fit:cover;">
                            @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:100px;height:100px;"><i class="bi bi-image text-muted"></i></div>
                            @endif
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $offeredListing->title }}</h6>
                                <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $offeredListing->trade_type)) }}</span>
                                <p class="small text-primary mb-0 mt-1"><i class="bi bi-box-arrow-up-right me-1"></i>View full listing</p>
                            </div>
                            </a>
                        @elseif($offeredUserProduct)
                            <a href="{{ route('trading.products.show', $offeredUserProduct->id) }}" class="text-decoration-none text-dark d-flex gap-3 align-items-start flex-grow-1" title="View product details">
                            @php $oImg = $offeredUserProduct->images->first(); @endphp
                            @if($oImg)
                            <img src="{{ asset('storage/' . $oImg->image_path) }}" alt="" class="rounded" style="width:100px;height:100px;object-fit:cover;">
                            @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:100px;height:100px;"><i class="bi bi-image text-muted"></i></div>
                            @endif
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $offeredUserProduct->title ?? 'Item' }}</h6>
                                <p class="small text-primary mb-0"><i class="bi bi-box-arrow-up-right me-1"></i>View details</p>
                            </div>
                            </a>
                        @elseif($offeredProduct)
                            @php
                                $productShowRoute = \Illuminate\Support\Facades\Route::has('products.show') ? route('products.show', $offeredProduct->slug ?? $offeredProduct->id) : null;
                            @endphp
                            @if($productShowRoute)
                            <a href="{{ $productShowRoute }}" class="text-decoration-none text-dark d-flex gap-3 align-items-start flex-grow-1" title="View product details">
                            @endif
                            @php $oImg = $offeredProduct->images->first(); @endphp
                            @if($oImg)
                            <img src="{{ asset('storage/' . $oImg->image_path) }}" alt="" class="rounded" style="width:100px;height:100px;object-fit:cover;">
                            @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:100px;height:100px;"><i class="bi bi-image text-muted"></i></div>
                            @endif
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $offeredProduct->name ?? 'Item' }}</h6>
                                @if($productShowRoute ?? false)
                                <p class="small text-primary mb-0"><i class="bi bi-box-arrow-up-right me-1"></i>View details</p>
                                @endif
                            </div>
                            @if($productShowRoute ?? false)
                            </a>
                            @endif
                        @endif
                        @if($offer->cash_amount)
                        <div class="ms-auto align-self-center">
                            <span class="text-success fw-bold">+ ₱{{ number_format($offer->cash_amount, 0) }}</span>
                        </div>
                        @endif
                    </div>
                    @elseif($offer->cash_amount)
                    <p class="mb-0 fw-semibold">₱{{ number_format($offer->cash_amount, 0) }} cash</p>
                    @else
                    <p class="text-muted mb-0">No item details</p>
                    @endif
                    @if($offer->message)
                    <hr>
                    <p class="mb-0 small"><strong>Message:</strong> {{ $offer->message }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <span class="badge bg-{{ $offer->status === 'pending' ? 'warning' : ($offer->status === 'accepted' ? 'success' : 'secondary') }} text-dark fs-6">{{ ucfirst($offer->status) }}</span>
                <span class="text-muted ms-2">{{ $offer->created_at->format('M j, Y g:i A') }}</span>
            </div>
            @if($offer->tradeListing->user_id === auth()->id() && $offer->status === 'pending')
            <div class="d-flex gap-2">
                <form action="{{ route('trading.offers.accept', $offer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Accept this offer? A conversation will be created.');">
                    @csrf
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Accept Offer</button>
                </form>
                <form action="{{ route('trading.offers.reject', $offer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deny this offer?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger"><i class="bi bi-x-lg me-1"></i>Deny</button>
                </form>
            </div>
            @endif
        </div>
    </div>

    <div class="mt-3">
        @if($offer->offerer_id === auth()->id())
        <a href="{{ route('trading.offers.my') }}" class="btn btn-outline-secondary">Back to My Offers</a>
        @else
        <a href="{{ route('trading.offers.received') }}" class="btn btn-outline-secondary">Back to Offers Received</a>
        @endif
        <a href="{{ route('trading.listings.show', $offer->tradeListing->id) }}" class="btn btn-outline-primary ms-2">View listing</a>
    </div>
</div>
@endsection
