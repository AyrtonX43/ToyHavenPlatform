@extends('layouts.toyshop')

@section('title', 'Suggested Products - ToyHaven')

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white reveal">
                <div class="card-body py-4">
                    <h1 class="display-5 fw-bold mb-2">
                        <i class="bi bi-stars me-2"></i>Products Just For You!
                    </h1>
                    <p class="lead mb-0">
                        Based on your interest in: 
                        <strong>{{ implode(', ', $selectedCategories) }}</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Products Grid -->
    @if($suggestedProducts->count() > 0)
        <div class="row product-grid">
            @foreach($suggestedProducts as $product)
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card product-card h-100 reveal">
                        @if($product->images->first())
                            <div class="img-hover-zoom">
                                <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" 
                                     class="card-img-top" 
                                     alt="{{ $product->name }}"
                                     style="height: 250px; object-fit: cover;">
                            </div>
                        @else
                            <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 250px;">
                                <i class="bi bi-image text-white" style="font-size: 3rem;"></i>
                            </div>
                        @endif
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="card-text text-muted small">{{ Str::limit($product->description, 100) }}</p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="price-display text-primary fw-bold">₱{{ number_format($product->price, 2) }}</span>
                                    @if($product->amazon_reference_price)
                                        <small class="price-original text-muted">
                                            <del>₱{{ number_format($product->amazon_reference_price, 2) }}</del>
                                        </small>
                                    @endif
                                </div>
                                <div class="mb-3">
                                    <div class="rating-stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi bi-star{{ $i <= $product->rating ? '-fill text-warning' : '' }}"></i>
                                        @endfor
                                    </div>
                                    <small class="text-muted ms-2">({{ $product->reviews_count }} reviews)</small>
                                </div>
                                <div class="mb-2">
                                    <span class="badge bg-info">{{ $product->category->name }}</span>
                                    @if($product->isInStock())
                                        <span class="badge bg-success">In Stock</span>
                                    @else
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @endif
                                </div>
                                <div class="d-grid gap-2 mt-2">
                                    <a href="{{ route('toyshop.products.show', $product->slug) }}" class="btn btn-primary">
                                        <i class="bi bi-eye me-2"></i>View Details
                                    </a>
                                    @auth
                                        <form action="{{ route('cart.add') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn btn-outline-primary w-100" {{ !$product->isInStock() ? 'disabled' : '' }}>
                                                <i class="bi bi-cart-plus me-2"></i>Add to Cart
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('login') }}" class="btn btn-outline-primary">
                                            <i class="bi bi-box-arrow-in-right me-2"></i>Login to Add to Cart
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Browse More Products -->
        <div class="text-center mt-5 mb-4">
            <a href="{{ route('toyshop.products.index') }}" class="btn btn-outline-primary btn-lg">
                <i class="bi bi-grid me-2"></i>Browse All Products
            </a>
        </div>
    @else
        <div class="alert alert-info text-center reveal">
            <i class="bi bi-inbox" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
            <h5>No products found in your selected categories</h5>
            <p>Don't worry! We're adding new products every day. Check back soon or browse all our products.</p>
            <a href="{{ route('toyshop.products.index') }}" class="btn btn-primary mt-3">
                <i class="bi bi-grid me-2"></i>Browse All Products
            </a>
        </div>
    @endif
</div>

<style>
    .img-hover-zoom {
        overflow: hidden;
    }
    
    .img-hover-zoom img {
        transition: transform 0.3s ease;
    }
    
    .img-hover-zoom:hover img {
        transform: scale(1.1);
    }
    
    .product-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .rating-stars {
        color: #ffc107;
    }
</style>
@endsection
