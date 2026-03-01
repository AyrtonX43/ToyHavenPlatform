@extends('layouts.seller-new')

@section('title', 'Product Details - ToyHaven')

@section('page-title', 'Product Details')

@section('content')
<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('seller.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('seller.products.index') }}">Products</a></li>
        <li class="breadcrumb-item active">{{ Str::limit($product->name, 30) }}</li>
    </ol>
</nav>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">{{ $product->name }}</h4>
        <p class="text-muted mb-0">View and manage your product details</p>
    </div>
    <div>
        <a href="{{ route('seller.products.edit', $product->id) }}" class="btn btn-primary me-2">
            <i class="bi bi-pencil me-1"></i> Edit Product
        </a>
        <a href="{{ route('seller.products.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Products
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Product Images -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Product Images</h5>
            </div>
            <div class="card-body">
                @if($product->images && $product->images->count() > 0)
                    <div class="row g-3">
                        @foreach($product->images as $index => $image)
                            <div class="col-md-4 col-sm-6">
                                <div class="position-relative">
                                    <img src="{{ asset('storage/' . $image->image_path) }}" 
                                         class="img-thumbnail w-100" 
                                         style="height: 200px; object-fit: cover;"
                                         alt="{{ $product->name }}">
                                    @if($image->is_primary)
                                        <span class="badge bg-primary position-absolute top-0 start-0 m-2">Primary</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                        <p class="text-muted mt-3">No images uploaded</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Product Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Product Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong class="text-muted">Product Name:</strong><br>
                        <span class="fs-5">{{ $product->name }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted">SKU:</strong><br>
                        <code class="fs-6">{{ $product->sku }}</code>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong class="text-muted">Categories:</strong><br>
                        @if($product->categories && $product->categories->count() > 0)
                            @foreach($product->categories as $category)
                                <span class="badge bg-secondary me-1">{{ $category->name }}</span>
                            @endforeach
                        @else
                            <span class="text-muted">No categories assigned</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted">Brand:</strong><br>
                        {{ $product->brand ?? 'N/A' }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong class="text-muted">Condition:</strong><br>
                        <span class="badge bg-info">{{ ucfirst($product->condition ?? 'new') }}</span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong class="text-muted">Price Breakdown:</strong><br>
                        @php
                            $basePrice = $product->base_price ?? $product->price;
                            $platformFeePercent = $product->platform_fee_percentage ?? 5.00;
                            $taxPercent = $product->tax_percentage ?? 12.00;
                            $platformFee = ($basePrice * $platformFeePercent) / 100;
                            $subtotal = $basePrice + $platformFee;
                            $tax = ($subtotal * $taxPercent) / 100;
                            $calculatedFinal = $subtotal + $tax;
                            $displayFinal = $product->final_price ?? $calculatedFinal ?? $product->price;
                        @endphp
                        <div class="mt-2">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td>Base Price:</td>
                                    <td class="text-end"><strong>₱{{ number_format($basePrice, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Platform Fee ({{ $platformFeePercent }}%):</td>
                                    <td class="text-end"><strong class="text-warning">₱{{ number_format($platformFee, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Tax ({{ $taxPercent }}%):</td>
                                    <td class="text-end"><strong class="text-info">₱{{ number_format($tax, 2) }}</strong></td>
                                </tr>
                                <tr class="border-top">
                                    <td><strong>Final Price:</strong></td>
                                    <td class="text-end"><strong class="text-success fs-5">₱{{ number_format($displayFinal, 2) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                        @if($product->amazon_reference_price)
                            <div class="mt-2">
                                <small class="text-info">
                                    <i class="bi bi-amazon me-1"></i>Amazon Reference: ₱{{ number_format($product->amazon_reference_price, 2) }}
                                </small>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted">Stock Quantity:</strong><br>
                        <span class="badge bg-{{ $product->stock_quantity > 10 ? 'success' : ($product->stock_quantity > 0 ? 'warning' : 'danger') }} fs-6">
                            {{ $product->stock_quantity }} {{ $product->stock_quantity == 1 ? 'item' : 'items' }}
                        </span>
                    </div>
                </div>
                
                @if($product->description)
                <div class="mb-3">
                    <strong class="text-muted">Description:</strong><br>
                    <div class="mt-2 product-description-text" style="text-align: justify;">
                        {!! nl2br(e($product->description)) !!}
                    </div>
                </div>
                @endif
                
                @if($product->weight || $product->length || $product->width || $product->height)
                <div class="row mb-3">
                    <div class="col-12">
                        <strong class="text-muted">Dimensions & Weight:</strong>
                    </div>
                    @if($product->weight)
                        <div class="col-md-3">
                            <small class="text-muted">Weight:</small><br>
                            {{ $product->weight }} kg
                        </div>
                    @endif
                    @if($product->length)
                        <div class="col-md-3">
                            <small class="text-muted">Length:</small><br>
                            {{ $product->length }} cm
                        </div>
                    @endif
                    @if($product->width)
                        <div class="col-md-3">
                            <small class="text-muted">Width:</small><br>
                            {{ $product->width }} cm
                        </div>
                    @endif
                    @if($product->height)
                        <div class="col-md-3">
                            <small class="text-muted">Height:</small><br>
                            {{ $product->height }} cm
                        </div>
                    @endif
                </div>
                @endif
                
                @if(!empty(trim($product->video_url ?? '')))
                <div class="mb-3">
                    <strong class="text-muted">Product Video:</strong><br>
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
                            <a href="{{ $product->video_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary">
                                <i class="bi bi-play-circle me-1"></i> Watch Video
                            </a>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Product Variations -->
        @if($product->variations && $product->variations->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Product Variations</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Value</th>
                                <th>Price Adjustment</th>
                                <th>Stock Quantity</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->variations as $variation)
                                <tr>
                                    <td>{{ $variation->variation_type }}</td>
                                    <td><strong>{{ $variation->variation_value }}</strong></td>
                                    <td>
                                        @if($variation->price_adjustment > 0)
                                            <span class="text-success">+₱{{ number_format($variation->price_adjustment, 2) }}</span>
                                        @elseif($variation->price_adjustment < 0)
                                            <span class="text-danger">₱{{ number_format($variation->price_adjustment, 2) }}</span>
                                        @else
                                            <span class="text-muted">No adjustment</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $variation->stock_quantity > 0 ? 'success' : 'danger' }}">
                                            {{ $variation->stock_quantity }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $variation->is_available ? 'success' : 'secondary' }}">
                                            {{ $variation->is_available ? 'Available' : 'Unavailable' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Reviews -->
        @if($product->reviews && $product->reviews->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Customer Reviews ({{ $product->reviews->count() }})</h5>
            </div>
            <div class="card-body">
                @foreach($product->reviews as $review)
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between mb-2">
                            <div>
                                <strong>{{ $review->user->name ?? 'Anonymous' }}</strong>
                                <div class="text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                    @endfor
                                </div>
                            </div>
                            <small class="text-muted">{{ $review->created_at->format('M d, Y') }}</small>
                        </div>
                        @if($review->comment)
                            <p class="mb-0">{{ $review->comment }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <!-- Status Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Product Status</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <span class="badge bg-{{ $product->status === 'active' ? 'success' : ($product->status === 'pending' ? 'warning' : 'secondary') }} fs-5 px-4 py-2">
                        {{ ucfirst($product->status) }}
                    </span>
                </div>
                <div class="small text-muted text-center">
                    @if($product->status === 'pending')
                        Your product is awaiting admin approval. You'll be notified once it's reviewed.
                    @elseif($product->status === 'active')
                        Your product is live and visible to customers.
                    @else
                        Your product is currently inactive.
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Statistics</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Views:</span>
                        <strong>{{ number_format($product->views_count ?? 0) }}</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Sales:</span>
                        <strong>{{ number_format($product->sales_count ?? 0) }}</strong>
                    </div>
                </div>
                @if($product->reviews && $product->reviews->count() > 0)
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Average Rating:</span>
                        <strong>
                            @php
                                $avgRating = $product->reviews->avg('rating');
                            @endphp
                            {{ number_format($avgRating, 1) }}/5.0
                        </strong>
                    </div>
                </div>
                @endif
                <div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Created:</span>
                        <strong>{{ $product->created_at->format('M d, Y') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('seller.products.edit', $product->id) }}" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i> Edit Product
                    </a>
                    <a href="{{ route('seller.products.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Products
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
