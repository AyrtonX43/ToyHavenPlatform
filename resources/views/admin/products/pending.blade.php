@extends('layouts.admin-new')

@section('title', 'Products Requesting Approval - ToyHaven')
@section('page-title', 'Products Requesting Approval')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left me-1"></i> Back to Product Moderation
    </a>
    <a href="{{ route('admin.products.approved') }}" class="btn btn-outline-success ms-2">
        <i class="bi bi-check2-square me-1"></i> Approved Products
    </a>
    <a href="{{ route('admin.products.rejected') }}" class="btn btn-outline-danger ms-2">
        <i class="bi bi-x-circle me-1"></i> Rejected Products
    </a>
</div>

<p class="text-muted mb-4">Products awaiting admin approval are grouped by category. Under each category are the shops that have pending products, and under each shop is the list of products requesting approval.</p>

@forelse($organized as $group)
<div class="card mb-4">
    <div class="card-header bg-warning bg-opacity-10 border-warning border-start border-4">
        <h5 class="mb-0">
            <i class="bi bi-tag-fill text-warning me-2"></i>
            {{ $group->category->name }}
            <span class="badge bg-warning text-dark ms-2">{{ collect($group->shops)->sum(fn($s) => count($s->products)) }} pending product(s)</span>
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
                            <br><small class="text-muted">SKU: {{ $product->sku }} · ₱{{ number_format($product->price, 2) }} · Stock: {{ $product->stock_quantity }} · {{ $product->created_at->format('M d, Y') }}</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-outline-primary" title="View details">
                            <i class="bi bi-eye"></i>
                        </a>
                        <form action="{{ route('admin.products.approve', $product->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                <i class="bi bi-check-circle"></i>
                            </button>
                        </form>
                        <button type="button" class="btn btn-sm btn-danger" title="Reject" data-bs-toggle="modal" data-bs-target="#rejectModal-{{ $product->id }}">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </li>
                <!-- Reject modal per product -->
                <div class="modal fade" id="rejectModal-{{ $product->id }}" tabindex="-1" aria-labelledby="rejectModalLabel-{{ $product->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('admin.products.reject', $product->id) }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title" id="rejectModalLabel-{{ $product->id }}">Reject: {{ Str::limit($product->name, 40) }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="rejection_reason-{{ $product->id }}" class="form-label">Reason <span class="text-danger">*</span></label>
                                        <select class="form-select" id="rejection_reason-{{ $product->id }}" name="rejection_reason" required>
                                            <option value="">-- Select a reason --</option>
                                            <option value="inappropriate_content">Inappropriate Content</option>
                                            <option value="misleading_information">Misleading Information</option>
                                            <option value="poor_quality_images">Poor Quality Images</option>
                                            <option value="incorrect_category">Incorrect Category</option>
                                            <option value="violates_policies">Violates Platform Policies</option>
                                            <option value="duplicate_product">Duplicate Product</option>
                                            <option value="incomplete_information">Incomplete Information</option>
                                            <option value="pricing_issues">Pricing Issues</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="comment-{{ $product->id }}" class="form-label">Additional Comments (Optional)</label>
                                        <textarea class="form-control" id="comment-{{ $product->id }}" name="comment" rows="3" placeholder="Feedback for the seller..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle me-1"></i> Reject</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
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
        <p class="mb-0 mt-2">No products are currently requesting approval.</p>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-primary mt-3">View all products</a>
    </div>
</div>
@endforelse
@endsection
