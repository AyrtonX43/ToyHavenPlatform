@extends('layouts.toyshop')

@section('title', 'Add Auction Listing - ToyHaven')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auction.index') }}">Auction</a></li>
            <li class="breadcrumb-item"><a href="{{ route('auction.seller.dashboard') }}">Seller Dashboard</a></li>
            <li class="breadcrumb-item active">Add Auction Listing</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add Auction Listing</h4>
                            <p class="mb-0 small opacity-90">Create a new auction for your item</p>
                        </div>
                        <a href="{{ route('auction.seller.dashboard') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('auction.listings.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title') }}" required maxlength="255" placeholder="e.g. Vintage LEGO Star Wars Set">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4"
                                maxlength="5000" placeholder="Describe your item, condition, and any details bidders should know.">{{ old('description') }}</textarea>
                            <small class="text-muted">Max 5000 characters</small>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Starting Bid (₱) <span class="text-danger">*</span></label>
                                <input type="number" name="starting_bid" class="form-control @error('starting_bid') is-invalid @enderror"
                                    value="{{ old('starting_bid') }}" required min="1" step="0.01" placeholder="0.00">
                                @error('starting_bid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bid Increment (₱) <span class="text-danger">*</span></label>
                                <input type="number" name="bid_increment" class="form-control @error('bid_increment') is-invalid @enderror"
                                    value="{{ old('bid_increment', 10) }}" required min="1" step="0.01" placeholder="10">
                                @error('bid_increment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Duration (hours) <span class="text-danger">*</span></label>
                            <input type="number" name="duration_hours" class="form-control @error('duration_hours') is-invalid @enderror"
                                value="{{ old('duration_hours', 24) }}" required min="1" max="720" placeholder="24">
                            <small class="text-muted">Auction will run for this many hours (1-720)</small>
                            @error('duration_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                <option value="">-- Select category (optional) --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="alert alert-secondary">
                            <small><i class="bi bi-info-circle me-1"></i>Your listing will be saved as a draft. You can edit and submit it for approval later.</small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Create Draft Listing
                        </button>
                        <a href="{{ route('auction.seller.dashboard') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
