@extends('layouts.admin-new')

@section('title', 'Product Details - ToyHaven')
@section('page-title', 'Product: ' . $product->name)

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">{{ $product->name }}</h4>
                        <p class="text-muted mb-0">SKU: {{ $product->sku }} | Seller: <a href="{{ route('admin.sellers.show', $product->seller_id) }}">{{ $product->seller->business_name }}</a></p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-{{ $product->status === 'active' ? 'success' : ($product->status === 'pending' ? 'warning' : 'secondary') }} fs-6">
                            {{ ucfirst($product->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Product Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Name:</strong><br>
                        {{ $product->name }}
                    </div>
                    <div class="col-md-6">
                        <strong>SKU:</strong><br>
                        <code>{{ $product->sku }}</code>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Category:</strong><br>
                        {{ $product->category->name }}
                    </div>
                    <div class="col-md-6">
                        <strong>Brand:</strong><br>
                        {{ $product->brand ?? 'N/A' }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Price:</strong><br>
                        ₱{{ number_format($product->price, 2) }}
                    </div>
                    <div class="col-md-6">
                        <strong>Stock Quantity:</strong><br>
                        <span class="badge bg-{{ $product->stock_quantity > 0 ? 'success' : 'danger' }} fs-6">
                            {{ $product->stock_quantity }}
                        </span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Condition:</strong><br>
                        <span class="badge bg-info">{{ ucfirst($product->condition ?? 'new') }}</span>
                    </div>
                </div>
                @if($product->amazon_reference_price || $product->amazon_reference_image || $product->amazon_reference_url)
                <div class="mb-3 p-3 bg-light rounded">
                    <strong>Selected Amazon Reference (from seller):</strong>
                    <div class="row mt-2">
                        @if($product->amazon_reference_price)
                            <div class="col-md-4">
                                <small class="text-muted">Reference Price:</small><br>
                                ₱{{ number_format($product->amazon_reference_price, 2) }}
                            </div>
                        @endif
                        @if($product->amazon_reference_image)
                            <div class="col-md-4">
                                <small class="text-muted">Reference Image:</small><br>
                                <a href="{{ $product->amazon_reference_image }}" target="_blank" rel="noopener noreferrer" class="d-inline-block mt-1">
                                    <img src="{{ $product->amazon_reference_image }}" alt="Amazon reference" class="img-thumbnail" style="max-width: 80px; max-height: 80px; object-fit: contain;" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'80\' height=\'80\'%3E%3Crect fill=\'%23f8f9fa\' width=\'80\' height=\'80\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%236c757d\' font-size=\'10\'%3ENo preview%3C/text%3E%3C/svg%3E';">
                                </a>
                            </div>
                        @endif
                        @if($product->amazon_reference_url)
                            <div class="col-md-4">
                                <small class="text-muted">Reference URL:</small><br>
                                <a href="{{ $product->amazon_reference_url }}" target="_blank" rel="noopener noreferrer" class="small">{{ Str::limit($product->amazon_reference_url, 50) }}</a>
                            </div>
                        @endif
                    </div>
                </div>
                @endif
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Sales Count:</strong><br>
                        {{ $product->sales_count }}
                    </div>
                </div>
                @if($product->rejection_reason)
                <div class="mb-3">
                    <div class="alert alert-danger">
                        <strong>Rejection Reason:</strong><br>
                        <pre class="mb-0" style="white-space: pre-wrap;">{{ $product->rejection_reason }}</pre>
                    </div>
                </div>
                @endif
                <div class="mb-3">
                    <strong>Description:</strong><br>
                    <p class="mb-0 product-description-text" style="text-align: justify;">{{ $product->description ?? '—' }}</p>
                </div>
                
                @if($product->images->count() > 0)
                <div class="mb-3">
                    <strong>Images:</strong><br>
                    <div class="row mt-2">
                        @foreach($product->images as $image)
                            <div class="col-md-3 mb-2">
                                <img src="{{ asset('storage/' . $image->image_path) }}" class="img-thumbnail" style="max-height: 150px;">
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(!empty(trim($product->video_url ?? '')))
                <div class="mb-3">
                    <strong>Product Video:</strong><br>
                    <div class="mt-2">
                        @php $videoUrlLower = strtolower(trim($product->video_url)); @endphp
                        @if(str_contains($videoUrlLower, 'youtube.com') || str_contains($videoUrlLower, 'youtu.be'))
                            @php
                                $videoId = null;
                                if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\n?#]+)/i', $product->video_url, $matches)) {
                                    $videoId = trim($matches[1]);
                                }
                            @endphp
                            @if($videoId)
                                <iframe width="100%" height="400" src="https://www.youtube.com/embed/{{ $videoId }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            @else
                                <a href="{{ $product->video_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary">
                                    <i class="bi bi-play-circle me-1"></i> Watch Video
                                </a>
                            @endif
                        @elseif(str_contains($videoUrlLower, 'vimeo.com'))
                            @php
                                $vimeoId = null;
                                if (preg_match('/vimeo\.com\/(\d+)/i', $product->video_url, $matches)) {
                                    $vimeoId = $matches[1];
                                }
                            @endphp
                            @if($vimeoId)
                                <iframe width="100%" height="400" src="https://player.vimeo.com/video/{{ $vimeoId }}" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
                            @else
                                <a href="{{ $product->video_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary">
                                    <i class="bi bi-play-circle me-1"></i> Watch Video
                                </a>
                            @endif
                        @else
                            @php
                                $videoSrc = (str_starts_with($product->video_url, 'http://') || str_starts_with($product->video_url, 'https://'))
                                    ? $product->video_url
                                    : asset(ltrim($product->video_url, '/'));
                            @endphp
                            <video controls width="100%" style="max-height: 400px;" class="rounded">
                                <source src="{{ $videoSrc }}" type="video/mp4">
                                Your browser does not support the video tag. <a href="{{ $videoSrc }}" target="_blank" rel="noopener noreferrer">Open video</a>.
                            </video>
                            <small class="text-muted d-block mt-1">
                                <a href="{{ $videoSrc }}" target="_blank" rel="noopener noreferrer">Open in new tab</a>
                            </small>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($reports->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Reports ({{ $reports->count() }})</h5>
            </div>
            <div class="card-body">
                @foreach($reports as $report)
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between mb-2">
                            <strong>{{ $report->reporter->name }}</strong>
                            <span class="badge bg-{{ $report->status === 'pending' ? 'warning' : 'info' }}">
                                {{ ucfirst($report->status) }}
                            </span>
                        </div>
                        <p class="mb-1"><strong>Type:</strong> {{ $report->report_type }}</p>
                        <p class="mb-1"><strong>Reason:</strong> {{ $report->reason }}</p>
                        @if($report->description)
                            <p class="mb-1"><strong>Description:</strong> {{ $report->description }}</p>
                        @endif
                        <small class="text-muted">{{ $report->created_at->format('M d, Y h:i A') }}</small>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Moderation Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($product->status === 'pending')
                        <form action="{{ route('admin.products.approve', $product->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-circle me-1"></i> Approve Product
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectProductModal">
                            <i class="bi bi-x-circle me-1"></i> Reject Product
                        </button>
                    @elseif($product->status === 'active')
                        <p class="text-success mb-0 text-center small">
                            <i class="bi bi-check-circle-fill me-1"></i> This product has been approved. It cannot be approved or rejected again.
                        </p>
                    @else
                        <form action="{{ route('admin.products.approve', $product->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-circle me-1"></i> Reactivate Product
                            </button>
                        </form>
                        <p class="text-muted mb-0 text-center small">Rejected products can be reactivated; they cannot be rejected again.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Statistics</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Views:</strong> {{ $product->views_count }}
                </div>
                <div class="mb-2">
                    <strong>Sales:</strong> {{ $product->sales_count }}
                </div>
                <div class="mb-2">
                    <strong>Rating:</strong> 
                    @if($product->rating > 0)
                        <i class="bi bi-star-fill text-warning"></i> {{ number_format($product->rating, 1) }} ({{ $product->reviews_count }} reviews)
                    @else
                        No ratings yet
                    @endif
                </div>
                <div>
                    <strong>Created:</strong> {{ $product->created_at->format('M d, Y') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Product Modal -->
<div class="modal fade" id="rejectProductModal" tabindex="-1" aria-labelledby="rejectProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.products.reject', $product->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectProductModalLabel">Reject Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Please select a reason for rejecting this product. The seller will be notified via email.</p>
                    
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                        <select class="form-select" id="rejection_reason" name="rejection_reason" required>
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
                        <label for="comment" class="form-label">Additional Comments (Optional)</label>
                        <textarea class="form-control" id="comment" name="comment" rows="4" placeholder="Provide additional details or specific feedback for the seller..."></textarea>
                        <small class="text-muted">This will be included in the rejection notification sent to the seller.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i> Reject Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
