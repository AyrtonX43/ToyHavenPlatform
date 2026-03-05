@extends('layouts.toyshop')
@section('title', 'Create Listing - ToyHaven Trade')
@section('content')
<div class="container py-4">
    <h1 class="h3 mb-4">Create Trade Listing</h1>
    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('trading.listings.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="form-label">Trade Type</label>
            <select name="trade_type" class="form-select" required>
                <option value="exchange">Exchange (item for item)</option>
                <option value="exchange_with_cash">Exchange + Add Cash</option>
                <option value="cash">Cash only</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Product Source</label>
            <select name="product_source" class="form-select" id="productSource">
                <option value="user_product">My Product</option>
                <option value="product">Shop Product</option>
            </select>
        </div>
        <div class="mb-3" id="userProductField">
            <label class="form-label">Select Your Product</label>
            <select name="user_product_id" class="form-select">
                <option value="">-- Select --</option>
                @foreach($myProducts as $p)
                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->condition }})</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3 d-none" id="productField">
            <label class="form-label">Select Shop Product</label>
            <select name="product_id" class="form-select">
                <option value="">-- Select --</option>
                @foreach($tradeableProducts as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4" required>{{ old('description') }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select" required>
                @foreach($categories as $c)
                <option value="{{ $c->id }}" {{ old('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Brand</label>
                <input type="text" name="brand" class="form-control" value="{{ old('brand') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Condition</label>
                <select name="condition" class="form-select" required>
                    <option value="new">New</option>
                    <option value="like_new">Like New</option>
                    <option value="good">Good</option>
                    <option value="fair">Fair</option>
                    <option value="used">Used</option>
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Location (meetup area)</label>
            <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="e.g. Manila, QC">
        </div>
        <div class="mb-3">
            <label class="form-label">Meetup References</label>
            <input type="text" name="meet_up_references" class="form-control" value="{{ old('meet_up_references') }}" placeholder="Landmarks, etc.">
        </div>
        <div class="mb-3" id="cashAmountField">
            <label class="form-label">Cash Amount (₱)</label>
            <input type="number" name="cash_amount" class="form-control" value="{{ old('cash_amount') }}" min="0" step="0.01" placeholder="For cash or add-cash">
        </div>
        <div class="mb-3">
            <label class="form-label">Images (1-10)</label>
            <input type="file" name="images[]" class="form-control" accept="image/*" multiple required>
        </div>
        <button type="submit" class="btn btn-primary">Submit for Review</button>
        <a href="{{ route('trading.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
<script>
document.getElementById('productSource').addEventListener('change', function() {
    const v = this.value;
    document.getElementById('userProductField').classList.toggle('d-none', v !== 'user_product');
    document.getElementById('productField').classList.toggle('d-none', v !== 'product');
});
</script>
@endsection
