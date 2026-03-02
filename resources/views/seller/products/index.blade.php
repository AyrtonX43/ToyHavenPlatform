@extends('layouts.seller-new')

@section('title', 'My Products - ToyHaven')

@section('page-title', 'Product Management')

@section('content')
<x-seller.page-header
    title="My Products"
    subtitle="Manage your product listings"
>
    <x-slot:actions>
        <a href="{{ route('seller.products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add New Product
        </a>
    </x-slot:actions>
</x-seller.page-header>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('seller.products.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Product name or SKU..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Stock</label>
                <select name="stock" class="form-select">
                    <option value="">All</option>
                    <option value="low_stock" {{ request('stock') === 'low_stock' ? 'selected' : '' }}>Low Stock (≤10)</option>
                    <option value="in_stock" {{ request('stock') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="out_of_stock" {{ request('stock') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary w-100 me-2">
                    <i class="bi bi-search me-1"></i> Filter
                </button>
                <a href="{{ route('seller.products.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

@if($products->count() > 0)
    <x-seller.data-table
        title="Products ({{ $products->total() }})"
        :subtitle="'Showing ' . $products->firstItem() . ' to ' . $products->lastItem() . ' of ' . $products->total() . ' products'"
    >
        <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Image</th>
                            <th>Product Name</th>
                            <th>Categories</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>
                                    @if($product->images && $product->images->count() > 0)
                                        <div class="position-relative">
                                            <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" 
                                                 class="rounded" 
                                                 style="width: 60px; height: 60px; object-fit: cover;"
                                                 alt="{{ $product->name }}">
                                            @if($product->images->count() > 1)
                                                <span class="badge bg-primary position-absolute top-0 end-0" style="font-size: 0.65rem;">
                                                    +{{ $product->images->count() - 1 }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center rounded" style="width: 60px; height: 60px;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <strong class="d-block">{{ $product->name }}</strong>
                                        <small class="text-muted">SKU: {{ $product->sku }}</small>
                                        @if($product->amazon_reference_price)
                                            <br><small class="text-info">
                                                <i class="bi bi-amazon me-1"></i>Amazon Reference
                                            </small>
                                        @endif
                                        @if($product->video_url)
                                            <br><small class="text-primary">
                                                <i class="bi bi-play-circle me-1"></i>Has Video
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($product->categories && $product->categories->count() > 0)
                                        @foreach($product->categories->take(2) as $category)
                                            <span class="badge bg-light text-dark me-1">{{ $category->name }}</span>
                                        @endforeach
                                        @if($product->categories->count() > 2)
                                            <span class="badge bg-secondary">+{{ $product->categories->count() - 2 }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">No category</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $basePrice = $product->base_price ?? $product->price;
                                        $displayPrice = $product->final_price ?? $product->price;
                                    @endphp
                                    <strong class="text-success">₱{{ number_format($displayPrice, 2) }}</strong>
                                    @if($product->base_price && $product->base_price != $displayPrice)
                                        <br><small class="text-muted">Base: ₱{{ number_format($basePrice, 2) }}</small>
                                    @endif
                                    @if($product->amazon_reference_price)
                                        <br><small class="text-info">
                                            <i class="bi bi-amazon me-1"></i>Ref: ₱{{ number_format($product->amazon_reference_price, 2) }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @if($product->stock_quantity <= 10 && $product->stock_quantity > 0)
                                        <span class="badge bg-warning">
                                            <i class="bi bi-exclamation-triangle me-1"></i>{{ $product->stock_quantity }}
                                        </span>
                                    @elseif($product->stock_quantity == 0)
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @else
                                        <span class="text-success">{{ $product->stock_quantity }}</span>
                                    @endif
                                </td>
                                <td>
                                    <x-seller.status-badge :status="$product->status" />
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('seller.products.show', $product->id) }}" 
                                           class="btn btn-outline-info" 
                                           title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('seller.products.edit', $product->id) }}" 
                                           class="btn btn-outline-primary" 
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('seller.products.destroy', $product->id) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
        </div>
        @if($products->hasPages())
            <x-slot:footer>
                <div class="d-flex justify-content-center">
                    {{ $products->links() }}
                </div>
            </x-slot:footer>
        @endif
    </x-seller.data-table>
@else
    <x-seller.empty-state
        icon="bi-box-seam"
        message="No products found. Try adjusting your filters or add your first product!"
        :action="route('seller.products.create')"
        actionLabel="Add Your First Product"
    />
@endif
@endsection
