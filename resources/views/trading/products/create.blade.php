@extends('layouts.toyshop')

@section('title', 'Add Product - ToyHaven Trading')

@push('styles')
<style>
    .create-product-card {
        background: white;
        border-radius: 14px;
        padding: 2rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
    }
    
    .create-product-card h2 {
        font-size: 1.375rem;
        font-weight: 700;
        color: #1e293b;
        letter-spacing: -0.02em;
    }
    
    .form-section {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.5rem 1.75rem;
        margin-bottom: 1.5rem;
        border: 1px solid #e2e8f0;
    }
    
    .form-section-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 1rem;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #0891b2;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
    }
    
    .image-preview-container {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }
    
    .image-preview {
        position: relative;
        width: 120px;
        height: 120px;
        border-radius: 10px;
        overflow: hidden;
        border: 2px solid #e2e8f0;
    }
    
    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .image-preview .remove-btn {
        position: absolute;
        top: 0.25rem;
        right: 0.25rem;
        background: rgba(0,0,0,0.7);
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
</style>
@endpush

@section('content')
<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="create-product-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Add New Product</h2>
                    <a href="{{ route('trading.products.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i>Cancel
                    </a>
                </div>

                <form method="POST" action="{{ route('trading.products.store') }}" enctype="multipart/form-data" id="productForm">
                    @csrf

                    <!-- Basic Information -->
                    <div class="form-section">
                        <div class="form-section-title">Basic Information</div>
                        <div class="mb-3">
                            <label class="form-label">Product Name *</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control" required placeholder="e.g., Vintage Transformers Action Figure">
                            @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea name="description" rows="4" class="form-control" required placeholder="Describe your product in detail...">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Toy category *</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select toy category</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Brand</label>
                                <input type="text" name="brand" value="{{ old('brand') }}" class="form-control" placeholder="e.g., Hasbro, Mattel">
                                @error('brand')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Product Details -->
                    <div class="form-section">
                        <div class="form-section-title">Product Details</div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Condition *</label>
                                <select name="condition" class="form-select" required>
                                    <option value="new" {{ old('condition') == 'new' ? 'selected' : '' }}>New</option>
                                    <option value="used" {{ old('condition') == 'used' ? 'selected' : '' }}>Used</option>
                                    <option value="refurbished" {{ old('condition') == 'refurbished' ? 'selected' : '' }}>Refurbished</option>
                                </select>
                                @error('condition')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estimated Value (PHP)</label>
                                <input type="number" name="estimated_value" value="{{ old('estimated_value') }}" step="0.01" min="0" class="form-control" placeholder="0.00">
                                <small class="text-muted">Approximate value for trading</small>
                                @error('estimated_value')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Images -->
                    <div class="form-section">
                        <div class="form-section-title">Product Images</div>
                        <div class="mb-3">
                            <label class="form-label">Upload Images (Max 5)</label>
                            <input type="file" name="images[]" multiple accept="image/*" class="form-control" id="imageInput">
                            <small class="text-muted">You can upload up to 5 images. First image will be the main image.</small>
                            @error('images.*')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="image-preview-container" id="imagePreview"></div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('trading.products.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Create Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('imageInput').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    const files = Array.from(e.target.files).slice(0, 10);
    files.forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'image-preview';
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Preview ${index + 1}">
                    <button type="button" class="remove-btn" onclick="removeImage(${index})">
                        <i class="bi bi-x"></i>
                    </button>
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        }
    });
});

function removeImage(index) {
    const input = document.getElementById('imageInput');
    const dt = new DataTransfer();
    const files = Array.from(input.files);
    files.splice(index, 1);
    files.forEach(file => dt.items.add(file));
    input.files = dt.files;
    input.dispatchEvent(new Event('change'));
}
</script>
@endsection
