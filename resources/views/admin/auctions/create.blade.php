@extends('layouts.admin-new')

@section('title', 'Create Auction - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <h1 class="text-2xl font-bold mb-6">Create Auction</h1>

                <form action="{{ route('admin.auctions.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Owner (User ID)</label>
                        <input type="number" name="user_id" class="form-control" value="{{ old('user_id', auth()->id()) }}" required>
                        @error('user_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                        @error('title')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                        @error('description')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Starting Bid (₱) *</label>
                            <input type="number" name="starting_bid" class="form-control" step="0.01" value="{{ old('starting_bid', 100) }}" required>
                            @error('starting_bid')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Reserve Price (₱)</label>
                            <input type="number" name="reserve_price" class="form-control" step="0.01" value="{{ old('reserve_price') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Bid Increment (₱) *</label>
                            <input type="number" name="bid_increment" class="form-control" step="0.01" value="{{ old('bid_increment', 10) }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End At *</label>
                            <input type="datetime-local" name="end_at" class="form-control" value="{{ old('end_at') }}" required>
                            @error('end_at')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start At</label>
                            <input type="datetime-local" name="start_at" class="form-control" value="{{ old('start_at') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="draft">Draft</option>
                                <option value="live">Live</option>
                                <option value="pending_approval">Pending Approval</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">-- Select --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="is_members_only" value="1" class="form-check-input" {{ old('is_members_only') ? 'checked' : '' }}>
                                <label class="form-check-label">Members Only</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Product ID (optional)</label>
                        <input type="number" name="product_id" class="form-control" value="{{ old('product_id') }}" placeholder="Link to seller product">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">User Product ID (optional)</label>
                        <input type="number" name="user_product_id" class="form-control" value="{{ old('user_product_id') }}" placeholder="Link to user product">
                    </div>
                    <button type="submit" class="btn btn-primary">Create Auction</button>
                    <a href="{{ route('admin.auctions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
