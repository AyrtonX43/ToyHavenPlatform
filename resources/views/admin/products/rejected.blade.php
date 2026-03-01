@extends('layouts.admin-new')

@section('title', 'Rejected Products - ToyHaven')
@section('page-title', 'Rejected Products Organizer')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left me-1"></i> Back to Product Moderation
    </a>
    <a href="{{ route('admin.products.pending') }}" class="btn btn-outline-warning ms-2">
        <i class="bi bi-hourglass-split me-1"></i> Products Requesting Approval
    </a>
    <a href="{{ route('admin.products.approved') }}" class="btn btn-outline-success ms-2">
        <i class="bi bi-check2-square me-1"></i> Approved Products
    </a>
</div>

<p class="text-muted mb-4">Rejected products are grouped by category. Under each category are the shops that have rejected products, and under each shop is the list of rejected products. You can reactivate any product to approve it again.</p>

@forelse($organized as $group)
<div class="card mb-4">
    <div class="card-header bg-danger bg-opacity-10 border-danger border-start border-4">
        <h5 class="mb-0">
            <i class="bi bi-tag-fill text-danger me-2"></i>
            {{ $group->category->name }}
            <span class="badge bg-danger ms-2">{{ collect($group->shops)->sum(fn($s) => count($s->products)) }} rejected product(s)</span>
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
                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center flex-grow-1">
                        @if($product->primaryImage)
                            <img src="{{ asset('storage/' . $product->primaryImage->image_path) }}" alt="" class="rounded me-3" style="width: 48px; height: 48px; object-fit: cover;">
                        @else
                            <div class="rounded bg-light d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <strong>{{ $product->name }}</strong>
                            <br><small class="text-muted">SKU: {{ $product->sku }} · ₱{{ number_format($product->price, 2) }} · Stock: {{ $product->stock_quantity }}</small>
                            @if($product->rejection_reason)
                                <br><small class="text-danger">{{ Str::limit($product->rejection_reason, 80) }}</small>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-outline-primary" title="View details">
                            <i class="bi bi-eye"></i>
                        </a>
                        <form action="{{ route('admin.products.approve', $product->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success" title="Reactivate / Approve">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reactivate
                            </button>
                        </form>
                    </div>
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
        <p class="mb-0 mt-2">No rejected products.</p>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-primary mt-3">View all products</a>
    </div>
</div>
@endforelse
@endsection
