@extends('layouts.toyshop')
@section('title', 'My Offers - ToyHaven Trade')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h1 class="h3 mb-0">My Offers</h1>
        <a href="{{ route('trading.index') }}" class="btn btn-outline-primary"><i class="bi bi-arrow-left me-1"></i>Return to Trade Listings</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    @if($offers->count() > 0)
    <div class="row g-4">
        @foreach($offers as $offer)
        <div class="col-12">
            <div class="card border shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                <span class="badge bg-{{ $offer->status === 'pending' ? 'warning' : ($offer->status === 'accepted' ? 'success' : 'secondary') }} text-dark">{{ ucfirst($offer->status) }}</span>
                                <span class="text-muted small">{{ $offer->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="mb-1"><strong>Your offer on:</strong> {{ $offer->tradeListing->title }} (listed by {{ $offer->tradeListing->user->name }})</p>
                            <p class="mb-1 text-muted small">
                                <strong>You offered:</strong>
                                @if($offer->offeredTradeListing)
                                    {{ $offer->offeredTradeListing->title }}
                                @elseif($offer->offeredUserProduct)
                                    {{ $offer->offeredUserProduct->title ?? 'Your item' }}
                                @elseif($offer->offeredProduct)
                                    {{ $offer->offeredProduct->name ?? 'Your item' }}
                                @elseif($offer->cash_amount)
                                    ₱{{ number_format($offer->cash_amount, 0) }} cash
                                @else
                                    —
                                @endif
                                @if($offer->cash_amount && ($offer->offeredTradeListing || $offer->offeredUserProduct || $offer->offeredProduct))
                                    + ₱{{ number_format($offer->cash_amount, 0) }}
                                @endif
                            </p>
                            @if($offer->message)<p class="mb-0 small text-muted">Message: {{ Str::limit($offer->message, 100) }}</p>@endif
                        </div>
                        <div class="col-md-4 text-md-end mt-2 mt-md-0">
                            @if($offer->status === 'accepted' && $offer->tradeListing->trade)
                            <a href="{{ route('trading.trades.show', $offer->tradeListing->trade->id) }}" class="btn btn-success"><i class="bi bi-chat-dots me-1"></i>View Trade / Chat</a>
                            @elseif($offer->status === 'pending')
                            <a href="{{ route('trading.offers.show', $offer->id) }}" class="btn btn-outline-primary"><i class="bi bi-eye me-1"></i>View Offer</a>
                            @else
                            <a href="{{ route('trading.offers.show', $offer->id) }}" class="btn btn-outline-secondary">View details</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-3">{{ $offers->links() }}</div>
    @else
    <div class="card border-0 bg-light">
        <div class="card-body text-center py-5">
            <i class="bi bi-handbag text-muted" style="font-size: 4rem;"></i>
            <p class="text-muted mt-3 mb-0">No offers yet. Make an offer on a listing to get started.</p>
            <a href="{{ route('trading.listings.index') }}" class="btn btn-primary mt-3">Browse Trade Listings</a>
        </div>
    </div>
    @endif

    @if(isset($suggestedListings) && $suggestedListings->isNotEmpty())
    <hr class="my-5">
    <h2 class="h5 mb-3">You might also like</h2>
    <div class="row g-3">
        @foreach($suggestedListings as $s)
        <div class="col-6 col-md-4 col-lg-3">
            <a href="{{ route('trading.listings.show', $s->id) }}" class="text-decoration-none text-dark">
                <div class="card h-100 border shadow-sm">
                    @php $thumb = $s->getThumbnailImage(); @endphp
                    @if($thumb)
                    <img src="{{ asset('storage/' . $thumb->image_path) }}" class="card-img-top" style="height:140px;object-fit:cover;" alt="">
                    @else
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:140px;"><i class="bi bi-image text-muted"></i></div>
                    @endif
                    <div class="card-body py-2">
                        <span class="badge bg-primary mb-1">{{ ucfirst(str_replace('_', ' ', $s->trade_type)) }}</span>
                        <h6 class="card-title mb-0 small text-truncate">{{ Str::limit($s->title, 30) }}</h6>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
    <div class="mt-3">
        <a href="{{ route('trading.index') }}" class="btn btn-outline-primary">View all listings</a>
    </div>
    @endif
</div>
@endsection
