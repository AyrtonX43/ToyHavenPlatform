@extends('layouts.toyshop')

@section('title', 'Create Auction - ToyHaven')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Create Auction Listing</h2>

    <form action="{{ route('auctions.seller.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">-- Select --</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" {{ old('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Starting Bid (₱) <span class="text-danger">*</span></label>
                        <input type="number" name="starting_bid" class="form-control" step="0.01" value="{{ old('starting_bid') }}" required>
                        @error('starting_bid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Reserve Price (₱)</label>
                        <input type="number" name="reserve_price" class="form-control" step="0.01" value="{{ old('reserve_price') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Bid Increment (₱)</label>
                        <input type="number" name="bid_increment" class="form-control" step="0.01" value="{{ old('bid_increment', 1) }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Start At <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="start_at" class="form-control" value="{{ old('start_at') }}" required>
                        @error('start_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">End At <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="end_at" class="form-control" value="{{ old('end_at') }}" required>
                        @error('end_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Allowed Bidders (leave empty for all plans)</label>
                    <select name="allowed_bidder_plan_ids[]" class="form-select" multiple size="4">
                        @foreach($plans as $p)
                            <option value="{{ $p->id }}" {{ in_array($p->id, old('allowed_bidder_plan_ids', [])) ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Hold Ctrl to select multiple. Empty = all plans can bid.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Images</label>
                    <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Submit for Approval</button>
        <a href="{{ route('auctions.seller.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
@endsection
