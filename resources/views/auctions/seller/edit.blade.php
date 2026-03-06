@extends('layouts.toyshop')

@section('title', 'Edit Auction - ToyHaven')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Edit Auction</h2>

    <form action="{{ route('auctions.seller.update', $auction) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $auction->title) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description', $auction->description) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">-- Select --</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" {{ old('category_id', $auction->category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Starting Bid (₱) <span class="text-danger">*</span></label>
                        <input type="number" name="starting_bid" class="form-control" step="0.01" value="{{ old('starting_bid', $auction->starting_bid) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Reserve Price (₱)</label>
                        <input type="number" name="reserve_price" class="form-control" step="0.01" value="{{ old('reserve_price', $auction->reserve_price) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Bid Increment (₱)</label>
                        <input type="number" name="bid_increment" class="form-control" step="0.01" value="{{ old('bid_increment', $auction->bid_increment) }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Start At <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="start_at" class="form-control" value="{{ old('start_at', $auction->start_at?->format('Y-m-d\TH:i')) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">End At <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="end_at" class="form-control" value="{{ old('end_at', $auction->end_at?->format('Y-m-d\TH:i')) }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Allowed Bidders</label>
                    <select name="allowed_bidder_plan_ids[]" class="form-select" multiple size="4">
                        @foreach($plans as $p)
                            <option value="{{ $p->id }}" {{ in_array($p->id, old('allowed_bidder_plan_ids', $auction->allowed_bidder_plan_ids ?? [])) ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('auctions.seller.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
@endsection
