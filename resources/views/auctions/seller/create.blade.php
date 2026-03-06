@extends('layouts.toyshop')

@section('title', 'Create Auction')

@section('content')
<div class="container py-5">
    <h2 class="mb-4"><i class="bi bi-plus-circle me-2"></i>Create Auction</h2>

    <form action="{{ route('auctions.seller.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="5">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select name="category_id" id="category_id" class="form-select">
                                <option value="">Select category</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" {{ old('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Images</h5>
                        <input type="file" name="images[]" class="form-control mb-2" accept="image/*" multiple>
                        <small class="text-muted">Add at least 1 image (max 10). JPEG, PNG, GIF, WebP. Max 5MB each.</small>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Verification Video (optional)</h5>
                        <input type="file" name="verification_video" class="form-control" accept="video/*">
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Bidding</h5>
                        <div class="mb-3">
                            <label for="starting_bid" class="form-label">Starting Bid (₱) *</label>
                            <input type="number" name="starting_bid" id="starting_bid" class="form-control" step="0.01" min="1" value="{{ old('starting_bid', 100) }}" required>
                            @error('starting_bid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="bid_increment" class="form-label">Bid Increment (₱) *</label>
                            <input type="number" name="bid_increment" id="bid_increment" class="form-control" step="0.01" min="1" value="{{ old('bid_increment', 10) }}" required>
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Schedule</h5>
                        <div class="mb-3">
                            <label for="start_at" class="form-label">Start At</label>
                            <input type="datetime-local" name="start_at" id="start_at" class="form-control" value="{{ old('start_at') }}">
                        </div>
                        <div class="mb-3">
                            <label for="end_at" class="form-label">End At *</label>
                            <input type="datetime-local" name="end_at" id="end_at" class="form-control @error('end_at') is-invalid @enderror" value="{{ old('end_at') }}" required>
                            @error('end_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Create Auction</button>
                <a href="{{ route('auctions.seller.index') }}" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
