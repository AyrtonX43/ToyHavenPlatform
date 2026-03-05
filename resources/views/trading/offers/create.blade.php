@extends('layouts.toyshop')
@section('title', 'Make an offer - ' . $listing->title . ' - ToyHaven Trade')
@push('styles')
<style>
.offer-listing-card { cursor: pointer; }
.listing-select-card { transition: border-color 0.2s, box-shadow 0.2s; }
.offer-listing-card:has(input:checked) .listing-select-card { border-color: #0ea5e9 !important; box-shadow: 0 0 0 2px rgba(14,165,233,0.3); }
.offer-listing-card:hover .listing-select-card { border-color: #bae6fd; }
</style>
@endpush
@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trading.index') }}">Trading</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trading.listings.show', $listing->id) }}">{{ Str::limit($listing->title, 30) }}</a></li>
            <li class="breadcrumb-item active">Make an offer</li>
        </ol>
    </nav>

    <h1 class="h4 mb-4">Make an offer</h1>
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h2 class="h6 text-muted mb-3">You are offering on</h2>
                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3">
                        @if($listing->images->isNotEmpty())
                        <img src="{{ asset('storage/' . ($listing->images->first()->image_path ?? '')) }}" alt="" class="rounded" style="width:64px;height:64px;object-fit:cover;">
                        @else
                        <div class="bg-white rounded d-flex align-items-center justify-content-center" style="width:64px;height:64px;"><i class="bi bi-image text-muted"></i></div>
                        @endif
                        <div>
                            <strong>{{ $listing->title }}</strong>
                            <div class="small text-muted">{{ ucfirst(str_replace('_', ' ', $listing->trade_type ?? 'exchange')) }} · Listed by {{ $listing->user->name }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('trading.offers.store', $listing->id) }}" method="POST" class="card shadow-sm border-0">
                @csrf
                <div class="card-body">
                    @if(in_array($listing->trade_type, ['exchange', 'exchange_with_cash']))
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-semibold mb-0">Select from My Listings <span class="text-danger">*</span></label>
                            <a href="{{ route('trading.listings.my') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-grid-3x3-gap me-1"></i>View My Listings</a>
                        </div>
                        <p class="small text-muted mb-3">Choose a listing from your My Listings to offer to the seller.</p>
                        @if($myListings->isEmpty())
                        <div class="alert alert-warning mb-0">
                            You have no active listings to offer. <a href="{{ route('trading.listings.create') }}" class="alert-link">Create a listing</a> first, or <a href="{{ route('trading.listings.my') }}" class="alert-link">view My Listings</a>.
                        </div>
                        @else
                        <div class="row g-3">
                            @foreach($myListings as $my)
                            <div class="col-md-6 col-lg-4">
                                <label class="offer-listing-card d-block m-0 cursor-pointer">
                                    <input type="radio" name="offer_listing_id" value="{{ $my->id }}" class="d-none" required>
                                    <div class="card h-100 border-2 shadow-sm listing-select-card">
                                        @php $thumb = $my->getThumbnailImage(); @endphp
                                        @if($thumb)
                                        <img src="{{ asset('storage/' . $thumb->image_path) }}" class="card-img-top" style="height:120px;object-fit:cover;" alt="">
                                        @else
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:120px;"><i class="bi bi-image text-muted"></i></div>
                                        @endif
                                        <div class="card-body py-2">
                                            <div class="small text-muted">{{ ucfirst(str_replace('_', ' ', $my->trade_type ?? 'exchange')) }}</div>
                                            <div class="fw-semibold text-truncate" title="{{ $my->title }}">{{ Str::limit($my->title, 30) }}</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @if($listing->trade_type === 'exchange_with_cash')
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Add cash (optional)</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" name="cash_amount" class="form-control" min="0" step="0.01" value="{{ old('cash_amount', $listing->cash_amount) }}" placeholder="0">
                        </div>
                    </div>
                    @endif
                    @endif

                    @if($listing->trade_type === 'cash')
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Your offer amount (₱) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" name="cash_amount" class="form-control" min="0" step="0.01" value="{{ old('cash_amount', $listing->cash_amount) }}" required>
                        </div>
                        @if($listing->cash_amount)
                        <p class="small text-muted mt-1">Seller's asking price: ₱{{ number_format($listing->cash_amount, 0) }}</p>
                        @endif
                    </div>
                    @endif

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Message (optional)</label>
                        <textarea name="message" class="form-control" rows="3" maxlength="1000" placeholder="Say something to the seller...">{{ old('message') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        @if(in_array($listing->trade_type, ['exchange', 'exchange_with_cash']) && $myListings->isEmpty())
                        <button type="button" class="btn btn-primary px-4" disabled><i class="bi bi-send me-2"></i>Send offer</button>
                        <span class="align-self-center small text-muted">Create an active listing first to offer.</span>
                        @else
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-send me-2"></i>Send offer</button>
                        @endif
                        <a href="{{ route('trading.listings.show', $listing->id) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
