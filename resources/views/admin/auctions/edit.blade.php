@extends('layouts.admin-new')

@section('title', 'Edit Auction - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <h1 class="text-2xl font-bold mb-6">Edit Auction #{{ $auction->id }}</h1>

                <form action="{{ route('admin.auctions.update', $auction) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $auction->title) }}" required>
                        @error('title')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description', $auction->description) }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Starting Bid (₱) *</label>
                            <input type="number" name="starting_bid" class="form-control" step="0.01" value="{{ old('starting_bid', $auction->starting_bid) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Bid Increment (₱) *</label>
                            <input type="number" name="bid_increment" class="form-control" step="0.01" value="{{ old('bid_increment', $auction->bid_increment) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">End At *</label>
                            <input type="datetime-local" name="end_at" class="form-control" value="{{ old('end_at', $auction->end_at?->format('Y-m-d\TH:i')) }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="draft" {{ $auction->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="live" {{ $auction->status === 'live' ? 'selected' : '' }}>Live</option>
                                <option value="ended" {{ $auction->status === 'ended' ? 'selected' : '' }}>Ended</option>
                                <option value="cancelled" {{ $auction->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="is_members_only" value="1" class="form-check-input" {{ $auction->is_members_only ? 'checked' : '' }}>
                                <label class="form-check-label">Members Only</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('admin.auctions.show', $auction) }}" class="btn btn-outline-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
