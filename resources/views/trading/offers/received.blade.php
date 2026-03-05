@extends('layouts.toyshop')
@section('title', 'Offers Received - ToyHaven Trade')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h1 class="h3 mb-0">Offers Received</h1>
        <a href="{{ route('trading.index') }}" class="btn btn-outline-primary"><i class="bi bi-arrow-left me-1"></i>Back to Trade Listings</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    @if($listings->count() > 0)
    @foreach($listings as $listing)
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <a href="{{ route('trading.listings.show', $listing->id) }}" class="text-dark text-decoration-none fw-semibold">{{ $listing->title }}</a>
            <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $listing->trade_type)) }}</span>
        </div>
        <div class="card-body">
            <div class="row align-items-center mb-3">
                @php $thumb = $listing->getThumbnailImage(); @endphp
                <div class="col-auto">
                    @if($thumb)
                    <img src="{{ asset('storage/' . $thumb->image_path) }}" alt="" class="rounded" style="width:80px;height:80px;object-fit:cover;">
                    @else
                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:80px;height:80px;"><i class="bi bi-image text-muted"></i></div>
                    @endif
                </div>
                <div class="col">
                    <p class="mb-0 small text-muted">Your listing</p>
                </div>
            </div>

            @foreach($listing->activeOffers as $offer)
            <div class="border rounded p-3 mb-3 bg-light">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div>
                                <strong>{{ $offer->offerer->name }}</strong>
                                <span class="text-muted small ms-2">{{ $offer->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="d-flex gap-2 align-items-center flex-wrap">
                                @php
                                    $ol = $offer->offeredTradeListing ?? null;
                                    $op = $offer->offeredProduct ?? null;
                                    $oup = $offer->offeredUserProduct ?? null;
                                @endphp
                                @if($ol)
                                    <a href="{{ route('trading.listings.show', $ol->id) }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-2" title="View full listing">
                                    @php $oThumb = $ol->getThumbnailImage(); @endphp
                                    @if($oThumb)
                                    <img src="{{ asset('storage/' . $oThumb->image_path) }}" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:8px;">
                                    @endif
                                    <span class="small">{{ $ol->title }}</span>
                                    <i class="bi bi-box-arrow-up-right small"></i>
                                    </a>
                                @elseif($oup)
                                    @php $oImg = $oup->images->first(); @endphp
                                    @if($oImg)
                                    <img src="{{ asset('storage/' . $oImg->image_path) }}" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:8px;">
                                    @endif
                                    <span class="small">{{ $oup->title ?? 'Item' }}</span>
                                @elseif($op)
                                    @php $oImg = $op->images->first(); @endphp
                                    @if($oImg)
                                    <img src="{{ asset('storage/' . $oImg->image_path) }}" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:8px;">
                                    @endif
                                    <span class="small">{{ $op->name ?? 'Item' }}</span>
                                @endif
                                @if($offer->cash_amount)
                                <span class="text-success fw-semibold">+ ₱{{ number_format($offer->cash_amount, 0) }}</span>
                                @endif
                            </div>
                        </div>
                        @if($offer->message)
                        <p class="small text-muted mb-0 mt-1">"{{ Str::limit($offer->message, 120) }}"</p>
                        @endif
                    </div>
                    <div class="col-md-4 text-md-end mt-2 mt-md-0">
                        <a href="{{ route('trading.offers.show', $offer->id) }}" class="btn btn-sm btn-outline-primary me-1">View details</a>
                        <form action="{{ route('trading.offers.accept', $offer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Accept this offer?');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">Accept</button>
                        </form>
                        <form action="{{ route('trading.offers.reject', $offer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deny this offer?');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">Deny</button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
    <div class="mt-3">{{ $listings->links() }}</div>
    @else
    <div class="card border-0 bg-light">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
            <p class="text-muted mt-3 mb-0">No pending offers on your listings.</p>
            <a href="{{ route('trading.index') }}" class="btn btn-primary mt-3">Browse Trade Listings</a>
        </div>
    </div>
    @endif
</div>
@endsection
