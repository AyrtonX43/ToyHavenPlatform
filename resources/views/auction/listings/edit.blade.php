@extends('layouts.toyshop')

@section('title', 'Edit Auction Listing - ToyHaven')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auction.index') }}">Auction</a></li>
            <li class="breadcrumb-item"><a href="{{ route('auction.seller.dashboard') }}">Seller Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('auction.listings.index') }}">My Listings</a></li>
            <li class="breadcrumb-item active">Edit Listing</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Auction Listing</h4>
                            <p class="mb-0 small opacity-90">{{ $listing->title }}</p>
                        </div>
                        <a href="{{ route('auction.listings.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if($listing->rejection_reason)
                        <div class="alert alert-warning">
                            <strong>Feedback from reviewer:</strong><br>{{ $listing->rejection_reason }}
                        </div>
                    @endif

                    <form action="{{ route('auction.listings.update', $listing) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title', $listing->title) }}" required maxlength="255">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4"
                                maxlength="5000">{{ old('description', $listing->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Starting Bid (₱) <span class="text-danger">*</span></label>
                                <input type="number" name="starting_bid" class="form-control @error('starting_bid') is-invalid @enderror"
                                    value="{{ old('starting_bid', $listing->starting_bid) }}" required min="1" step="0.01">
                                @error('starting_bid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bid Increment (₱) <span class="text-danger">*</span></label>
                                <input type="number" name="bid_increment" class="form-control @error('bid_increment') is-invalid @enderror"
                                    value="{{ old('bid_increment', $listing->bid_increment) }}" required min="1" step="0.01">
                                @error('bid_increment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Duration (hours) <span class="text-danger">*</span></label>
                            <input type="number" name="duration_hours" class="form-control @error('duration_hours') is-invalid @enderror"
                                value="{{ old('duration_hours', $listing->duration_hours ?? 24) }}" required min="1" max="720">
                            @error('duration_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                <option value="">-- Select category (optional) --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $listing->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Save Changes
                        </button>
                        <a href="{{ route('auction.listings.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
