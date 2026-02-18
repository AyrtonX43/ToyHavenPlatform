@extends('layouts.admin')

@section('title', 'Approved Products by Category - ToyHaven')
@section('page-title', 'Approved Products Organizer')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left me-1"></i> Back to Product Moderation
    </a>
    <a href="{{ route('admin.products.pending') }}" class="btn btn-outline-warning ms-2">
        <i class="bi bi-hourglass-split me-1"></i> Products Requesting Approval
    </a>
    <a href="{{ route('admin.products.rejected') }}" class="btn btn-outline-danger ms-2">
        <i class="bi bi-x-circle me-1"></i> Rejected Products
    </a>
</div>

<p class="text-muted mb-4">Products are grouped by category. Under each category are the shops that have approved products in that category, and under each shop is the list of admin-approved products.</p>

@forelse($organized as $group)
<div class="card mb-4">
    <div class="card-header bg-success bg-opacity-10 border-success border-start border-4">
        <h5 class="mb-0">
            <i class="bi bi-tag-fill text-success me-2"></i>
            {{ $group->category->name }}
            <span class="badge bg-success ms-2">{{ collect($group->shops)->sum(fn($s) => count($s->products)) }} approved product(s)</span>
        </h5>
    </div>
    <div class="card-body p-0">
        @foreach($group->shops as $shopGroup)
        <div class="border-bottom border-light">
            <div class="px-4 py-3 bg-light bg-opacity-50">
                <strong>
                    <i class="bi bi-shop me-2"></i>
                    <a href="{{ route('admin.sellers.show', $shopGroup->seller->id) }}">{{ $shopGroup->seller->business_name }}</a>
                </strong>
                <span class="badge bg-secondary ms-2">{{ count($shopGroup->products) }} product(s)</span>
            </div>
            <ul class="list-group list-group-flush">
                @foreach($shopGroup->products as $product)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        @if($product->primaryImage)
                            <img src="{{ asset('storage/' . $product->primaryImage->image_path) }}" alt="" class="rounded me-3" style="width: 48px; height: 48px; object-fit: cover;">
                        @else
                            <div class="rounded bg-light d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                        @endif
                        <div>
                            <strong>{{ $product->name }}</strong>
                            <br><small class="text-muted">SKU: {{ $product->sku }} · ₱{{ number_format($product->price, 2) }} · Stock: {{ $product->stock_quantity }}</small>
                        </div>
                    </div>
                    <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i> View
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
        @endforeach
    </div>
</div>
@empty
<div class="card">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-inbox display-4"></i>
        <p class="mb-0 mt-2">No approved products yet. Approve products from the <a href="{{ route('admin.products.index') }}">Product Moderation</a> page.</p>
    </div>
</div>
@endforelse
@endsection
