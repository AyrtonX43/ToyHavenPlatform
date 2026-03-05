@extends('layouts.toyshop')
@section('title', 'Make an offer - ' . $listing->title . ' - ToyHaven Trade')
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
                        <label class="form-label fw-semibold">Select from My Listings <span class="text-danger">*</span></label>
                        <select name="offer_listing_id" class="form-select form-select-lg" required>
                            <option value="">— Choose from My Listings —</option>
                            @foreach($myListings as $my)
                            <option value="{{ $my->id }}">{{ $my->title }} ({{ ucfirst(str_replace('_', ' ', $my->trade_type ?? 'exchange')) }})</option>
                            @endforeach
                        </select>
                        <p class="small text-muted mt-1 mb-0">Active listings from <a href="{{ route('trading.listings.my') }}">My Listings</a></p>
                        @if($myListings->isEmpty())
                        <p class="small text-warning mt-2 mb-0">You have no active listings in My Listings. <a href="{{ route('trading.listings.create') }}">Create a listing</a> or check <a href="{{ route('trading.listings.my') }}">My Listings</a>.</p>
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
