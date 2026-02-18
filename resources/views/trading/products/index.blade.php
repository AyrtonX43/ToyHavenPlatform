@extends('layouts.toyshop')

@section('title', 'My Products - ToyHaven Trading')

@push('styles')
<style>
    .trading-products-page { max-width: 1400px; margin-left: auto; margin-right: auto; padding-left: 1rem; padding-right: 1rem; }
    @media (min-width: 576px) { .trading-products-page { padding-left: 1.5rem; padding-right: 1.5rem; } }
    @media (min-width: 992px) { .trading-products-page { padding-left: 2rem; padding-right: 2rem; } }
    
    .products-header {
        background: white;
        border-radius: 16px;
        padding: 2rem 2.25rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
        border: 1px solid rgba(240,230,220,0.8);
        margin-bottom: 2rem;
    }
    
    .products-header h2 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1e1b18;
        letter-spacing: -0.025em;
    }
    
    .products-header .btn-primary {
        border-radius: 12px;
        font-weight: 600;
        padding: 0.625rem 1.25rem;
    }
    
    .product-card-simple {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
        transition: all 0.25s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .product-card-simple:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        transform: translateY(-4px);
        border-color: rgba(255,107,107,0.4);
    }
    
    .product-image-simple {
        width: 100%;
        height: 220px;
        object-fit: cover;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    }
    
    .status-badge {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        padding: 0.35rem 0.875rem;
        border-radius: 20px;
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }
    
    .status-available {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        box-shadow: 0 2px 8px rgba(16,185,129,0.35);
    }
    
    .status-in_trade {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        box-shadow: 0 2px 8px rgba(245,158,11,0.35);
    }
    
    .status-traded {
        background: linear-gradient(135deg, #64748b, #475569);
        color: white;
    }
    
    .product-card-simple h6 { font-size: 1rem; font-weight: 700; color: #1e1b18; line-height: 1.4; }
    .product-card-simple .btn { border-radius: 10px; font-weight: 600; font-size: 0.875rem; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
<div class="trading-products-page">
    <div class="products-header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h2 class="mb-1">My Products</h2>
                <p class="text-muted mb-0" style="font-size: 0.9375rem;">Manage your products for trading</p>
            </div>
            <a href="{{ route('trading.products.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Add Product
            </a>
        </div>
    </div>

    @if($products->count() > 0)
    <div class="row g-4">
        @foreach($products as $product)
        <div class="col-xl-3 col-lg-4 col-md-4 col-sm-6">
            <div class="product-card-simple">
                <div class="position-relative">
                    @if($product->images->count() > 0)
                    <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" 
                         alt="{{ $product->name }}" 
                         class="product-image-simple">
                    @else
                    <div class="product-image-simple d-flex align-items-center justify-content-center">
                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                    </div>
                    @endif
                    <span class="status-badge status-{{ $product->status }}">
                        {{ ucfirst(str_replace('_', ' ', $product->status)) }}
                    </span>
                </div>
                <div class="p-3 flex-grow-1 d-flex flex-column">
                    <h6 class="mb-2">{{ Str::limit($product->name, 50) }}</h6>
                    <p class="text-muted small mb-3 flex-grow-1" style="font-size: 0.8125rem; line-height: 1.5;">{{ Str::limit($product->description, 70) }}</p>
                    <div class="d-flex gap-2 mt-auto">
                        <a href="{{ route('trading.products.show', $product->id) }}" class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="bi bi-eye me-1"></i>View
                        </a>
                        <a href="{{ route('trading.products.edit', $product->id) }}" class="btn btn-sm btn-outline-secondary flex-fill">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
    @else
    <div class="text-center py-5 px-4" style="background: white; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
        <i class="bi bi-inbox" style="font-size: 4rem; color: #cbd5e1;"></i>
        <h4 class="mt-3 fw-bold" style="color: #475569;">No products yet</h4>
        <p class="text-muted mb-4">Add your first product to start trading</p>
        <a href="{{ route('trading.products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Add Your First Product
        </a>
    </div>
    @endif
</div>
</div>
@endsection
