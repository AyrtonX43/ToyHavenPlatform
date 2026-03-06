@extends('layouts.toyshop')

@section('title', 'Edit Auction')

@section('content')
<div class="container py-5">
    <h2 class="mb-4"><i class="bi bi-pencil me-2"></i>Edit Auction</h2>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <form action="{{ route('auctions.seller.update', $auction) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $auction->title) }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="5">{{ old('description', $auction->description) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select name="category_id" id="category_id" class="form-select">
                                <option value="">Select category</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" {{ old('category_id', $auction->category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Images</h5>
                        @foreach($auction->images as $img)
                            <div class="d-inline-block me-2 mb-2">
                                <img src="{{ Storage::url($img->image_path) }}" alt="" style="width:80px;height:80px;object-fit:cover" class="rounded">
                                <label class="d-block"><input type="checkbox" name="remove_images[]" value="{{ $img->id }}"> Remove</label>
                            </div>
                        @endforeach
                        <div class="mt-2">
                            <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Bidding</h5>
                        <div class="mb-3">
                            <label for="starting_bid" class="form-label">Starting Bid (₱) *</label>
                            <input type="number" name="starting_bid" id="starting_bid" class="form-control" step="0.01" min="1" value="{{ old('starting_bid', $auction->starting_bid) }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="bid_increment" class="form-label">Bid Increment (₱) *</label>
                            <input type="number" name="bid_increment" id="bid_increment" class="form-control" step="0.01" min="1" value="{{ old('bid_increment', $auction->bid_increment) }}" required>
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Schedule</h5>
                        <div class="mb-3">
                            <label for="start_at" class="form-label">Start At</label>
                            <input type="datetime-local" name="start_at" id="start_at" class="form-control" value="{{ old('start_at', $auction->start_at?->format('Y-m-d\TH:i')) }}">
                        </div>
                        <div class="mb-3">
                            <label for="end_at" class="form-label">End At *</label>
                            <input type="datetime-local" name="end_at" id="end_at" class="form-control" value="{{ old('end_at', $auction->end_at?->format('Y-m-d\TH:i')) }}" required>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                @if($auction->isDraft())
                    <form action="{{ route('auctions.seller.submit', $auction) }}" method="POST" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">Submit for Approval</button>
                    </form>
                @endif
                <a href="{{ route('auctions.seller.index') }}" class="btn btn-outline-secondary w-100 mt-2">Back to Listings</a>
            </div>
        </div>
    </form>
</div>
@endsection
