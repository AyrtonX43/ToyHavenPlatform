@extends('layouts.admin-new')

@section('title', 'Edit Product - ToyHaven')
@section('page-title', 'Edit Product: ' . $product->name)

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Edit Product Information</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.products.update', $product->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row mb-3">
                <div class="col-md-8">
                    <label class="form-label">Product Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}" readonly>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Category <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Brand</label>
                    <input type="text" name="brand" class="form-control" value="{{ old('brand', $product->brand) }}">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Description <span class="text-danger">*</span></label>
                <textarea name="description" class="form-control" rows="5" required>{{ old('description', $product->description) }}</textarea>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Price <span class="text-danger">*</span></label>
                    <input type="number" name="price" class="form-control" step="0.01" min="0" value="{{ old('price', $product->price) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                    <input type="number" name="stock_quantity" class="form-control" min="0" value="{{ old('stock_quantity', $product->stock_quantity) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Amazon Reference Price (from seller)</label>
                    <input type="number" name="amazon_reference_price" class="form-control" step="0.01" min="0" value="{{ old('amazon_reference_price', $product->amazon_reference_price) }}" placeholder="Optional">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Amazon Reference Image URL (from seller)</label>
                    <input type="url" name="amazon_reference_image" class="form-control" value="{{ old('amazon_reference_image', $product->amazon_reference_image) }}" placeholder="https://...">
                    @if($product->amazon_reference_image)
                        <small class="text-muted d-block mt-1">Preview:</small>
                        <img src="{{ $product->amazon_reference_image }}" alt="Reference" class="img-thumbnail mt-1" style="max-width: 80px; max-height: 80px; object-fit: contain;" onerror="this.style.display='none'">
                    @endif
                </div>
                <div class="col-md-6">
                    <label class="form-label">Amazon Reference URL (from seller)</label>
                    <input type="url" name="amazon_reference_url" class="form-control" value="{{ old('amazon_reference_url', $product->amazon_reference_url) }}" placeholder="https://www.amazon.com/...">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Status <span class="text-danger">*</span></label>
                <select name="status" class="form-select" required>
                    <option value="pending" {{ old('status', $product->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="sold_out" {{ old('status', $product->status) == 'sold_out' ? 'selected' : '' }}>Sold Out</option>
                </select>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Product</button>
            </div>
        </form>
    </div>
</div>
@endsection
