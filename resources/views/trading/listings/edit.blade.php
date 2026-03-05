@extends('layouts.toyshop')
@section('title', 'Edit Listing - ToyHaven Trade')
@section('content')
<div class="container py-4">
    <h1 class="h3 mb-4">Edit Listing</h1>
    @if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ route('trading.listings.update', $listing->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Trade Type</label>
            <select name="trade_type" class="form-select" required>
                <option value="exchange" {{ $listing->trade_type === 'exchange' ? 'selected' : '' }}>Exchange</option>
                <option value="exchange_with_cash" {{ $listing->trade_type === 'exchange_with_cash' ? 'selected' : '' }}>Exchange + Cash</option>
                <option value="cash" {{ $listing->trade_type === 'cash' ? 'selected' : '' }}>Cash only</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="{{ old('title', $listing->title) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4" required>{{ old('description', $listing->description) }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select" required>
                @foreach($categories as $c)
                <option value="{{ $c->id }}" {{ $listing->category_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Brand</label>
            <input type="text" name="brand" class="form-control" value="{{ old('brand', $listing->brand) }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Condition</label>
            <select name="condition" class="form-select" required>
                <option value="new" {{ $listing->condition === 'new' ? 'selected' : '' }}>New</option>
                <option value="like_new" {{ $listing->condition === 'like_new' ? 'selected' : '' }}>Like New</option>
                <option value="good" {{ $listing->condition === 'good' ? 'selected' : '' }}>Good</option>
                <option value="fair" {{ $listing->condition === 'fair' ? 'selected' : '' }}>Fair</option>
                <option value="used" {{ $listing->condition === 'used' ? 'selected' : '' }}>Used</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Location</label>
            <input type="text" name="location" class="form-control" value="{{ old('location', $listing->location) }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Cash Amount (₱)</label>
            <input type="number" name="cash_amount" class="form-control" value="{{ old('cash_amount', $listing->cash_amount) }}" min="0" step="0.01">
        </div>
        <div class="mb-3">
            <label class="form-label">Replace Images (optional)</label>
            <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('trading.listings.my') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
@endsection
