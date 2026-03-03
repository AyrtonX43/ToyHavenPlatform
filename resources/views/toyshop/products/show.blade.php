@extends('layouts.toyshop')

@section('title', $product->name . ' - ToyHaven')

@push('styles')
<style>
    /* Product Page - Maximized width layout */
    .product-page {
        background: #f8f9fa;
        min-height: 100vh;
        padding: 2rem 0;
        width: 100%;
    }
    
    .product-container {
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
        padding: 0 2rem;
    }
    
    /* Breadcrumb */
    .breadcrumb {
        background: transparent;
        padding: 0.5rem 0;
        margin-bottom: 1.5rem;
    }
    
    /* Product Grid - maximized width, image gets more space */
    .product-grid {
        display: grid;
        grid-template-columns: 1.1fr 0.9fr;
        gap: 3rem;
        background: white;
        padding: 2.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        width: 100%;
        max-width: 100%;
    }
    
    /* Image Gallery */
    .image-gallery {
        position: relative;
    }
    
    .main-image-container {
        position: relative;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 1rem;
        cursor: zoom-in;
    }
    
    .main-image-container.no-fullscreen {
        cursor: zoom-in;
    }
    
    .main-image-wrapper {
        position: relative;
        width: 100%;
        height: 600px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
    }
    
    .main-image {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
        transition: transform 0.15s ease-out;
        transform-origin: center center;
    }
    
    /* Zoom Hint */
    .zoom-hint {
        position: absolute;
        bottom: 12px;
        right: 12px;
        background: rgba(0,0,0,0.75);
        color: white;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 0.875rem;
        z-index: 10;
        backdrop-filter: blur(10px);
    }
    
    /* Thumbnails */
    .thumbnail-gallery {
        display: flex;
        gap: 0.75rem;
        overflow-x: auto;
        padding: 0.5rem 0;
    }
    
    .thumbnail {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border: 2px solid #e5e7eb;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    
    .thumbnail:hover {
        border-color: #3b82f6;
        transform: translateY(-2px);
    }
    
    .thumbnail.active {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }
    
    .product-description {
        text-align: justify;
        line-height: 1.7;
    }
    
    /* Product Info */
    .product-info {
        position: sticky;
        top: 20px;
        height: fit-content;
    }
    
    .product-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 1rem;
        line-height: 1.3;
    }
    
    .product-rating-stars {
        font-size: 1.125rem;
        letter-spacing: 1px;
    }
    
    /* Customer Reviews section (right column) */
    .product-reviews-section {
        padding-top: 0;
    }
    .product-review-upload-area { border: 2px dashed #dee2e6; border-radius: 10px; padding: 1rem; text-align: center; cursor: pointer; transition: border-color 0.2s, background 0.2s; }
    .product-review-upload-area:hover, .product-review-upload-area.dragover { border-color: #0ea5e9; background: #f0f9ff; }
    .product-review-preview-grid { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 0.75rem; }
    .product-review-preview-item { position: relative; width: 80px; height: 80px; border-radius: 8px; overflow: hidden; border: 1px solid #dee2e6; flex-shrink: 0; }
    .product-review-preview-item img { width: 100%; height: 100%; object-fit: cover; cursor: pointer; }
    .product-review-preview-item .btn-del { position: absolute; top: 2px; right: 2px; width: 24px; height: 24px; padding: 0; border-radius: 50%; background: rgba(220,53,69,0.9); color: white; border: none; cursor: pointer; font-size: 0.75rem; display: flex; align-items: center; justify-content: center; }
    .product-review-preview-item .btn-view { position: absolute; bottom: 2px; left: 2px; width: 24px; height: 24px; padding: 0; border-radius: 50%; background: rgba(0,0,0,0.6); color: white; border: none; cursor: pointer; font-size: 0.65rem; display: flex; align-items: center; justify-content: center; }
    .product-review-fullscreen { position: fixed; inset: 0; background: rgba(0,0,0,0.9); z-index: 9999; display: none; align-items: center; justify-content: center; padding: 2rem; }
    .product-review-fullscreen.active { display: flex; }
    .product-review-fullscreen img { max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 8px; }
    .product-review-fullscreen .btn-close-fs { position: absolute; top: 1rem; right: 1rem; background: rgba(255,255,255,0.2); color: white; border: none; width: 40px; height: 40px; border-radius: 50%; font-size: 1.5rem; cursor: pointer; }
    
    .product-price {
        font-size: 2rem;
        font-weight: 700;
        color: #111827;
        margin: 1.5rem 0;
    }
    
    .stock-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: #d1fae5;
        color: #065f46;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }
    
    .out-of-stock {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .action-buttons {
        display: flex;
        gap: 1rem;
        margin: 2rem 0;
    }
    
    .btn-add-cart {
        flex: 1;
        background: #3b82f6;
        color: white;
        border: none;
        padding: 1rem;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-add-cart:hover {
        background: #2563eb;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
    
    .btn-wishlist {
        padding: 1rem;
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-wishlist:hover {
        border-color: #ef4444;
        color: #ef4444;
        transform: translateY(-2px);
    }
    
    /* Responsive */
    @media (max-width: 992px) {
        .product-grid {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 768px) {
        .product-container {
            padding: 0 1rem;
        }
        
        .product-grid {
            grid-template-columns: 1fr;
            gap: 2rem;
            padding: 1.5rem;
        }
        
        .main-image-wrapper {
            height: 400px;
        }
        
        .product-info {
            position: static;
        }
    }
</style>
@endpush

@section('content')
<div class="product-page">
    <div class="product-container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('toyshop.products.index') }}">Products</a></li>
                @if($product->categories && $product->categories->first())
                    <li class="breadcrumb-item"><a href="{{ route('toyshop.products.index', ['category' => $product->categories->first()->id]) }}">{{ $product->categories->first()->name }}</a></li>
                @endif
                <li class="breadcrumb-item active">{{ Str::limit($product->name, 50) }}</li>
            </ol>
        </nav>

        <!-- Product Grid -->
        <div class="product-grid">
            <!-- Image Gallery -->
            <div class="image-gallery">
                @php
                    $hasImages = $product->images && $product->images->count() > 0;
                    $firstImageUrl = $hasImages ? ($imageDisplayUrls[0] ?? asset('storage/' . $product->images->first()->image_path)) : asset('images/no-image.png');
                @endphp

                <!-- Main Image -->
                <div class="main-image-container no-fullscreen" id="mainImageContainer">
                    <div class="zoom-hint">
                        <i class="bi bi-zoom-in me-1"></i>
                        Hover to zoom
                    </div>
                    <div class="main-image-wrapper">
                        <img id="mainImage"
                             src="{{ $firstImageUrl }}"
                             alt="{{ $product->name }}"
                             class="main-image">
                    </div>
                </div>

                <!-- Thumbnails -->
                @if($hasImages && $product->images->count() > 1)
                <div class="thumbnail-gallery">
                    @foreach($product->images as $index => $image)
                        @php
                            $thumbUrl = $imageDisplayUrls[$index] ?? asset('storage/' . $image->image_path);
                        @endphp
                        <img src="{{ $thumbUrl }}"
                             alt="{{ $product->name }}"
                             class="thumbnail {{ $index === 0 ? 'active' : '' }}"
                             onclick="changeImage('{{ $thumbUrl }}', {{ $index }}, this)">
                    @endforeach
                </div>
                @endif

                <!-- Description (left column) -->
                @if($product->description)
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Description</h5>
                        <p class="mb-0 product-description">{{ $product->description }}</p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Product Info -->
            <div class="product-info">
                <h1 class="product-title">{{ $product->name }}</h1>

                <!-- Rating / Reviews -->
                @php
                    $avgRating = (float) ($product->rating ?? 0);
                    $reviewsCount = (int) ($product->reviews_count ?? 0);
                    $fullStars = (int) floor($avgRating);
                    $hasHalfStar = ($avgRating - $fullStars) >= 0.5;
                @endphp
                <div class="mb-3">
                    @if($reviewsCount > 0)
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="product-rating-stars text-warning">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $fullStars)
                                        <i class="bi bi-star-fill"></i>
                                    @elseif($i == $fullStars + 1 && $hasHalfStar)
                                        <i class="bi bi-star-half"></i>
                                    @else
                                        <i class="bi bi-star"></i>
                                    @endif
                                @endfor
                            </div>
                            <span class="product-rating-value fw-semibold">{{ number_format($avgRating, 1) }}</span>
                            <span class="text-muted">({{ $reviewsCount }} {{ Str::plural('review', $reviewsCount) }})</span>
                        </div>
                    @else
                        <div class="d-flex align-items-center gap-2">
                            <div class="product-rating-stars text-muted">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star"></i>
                                @endfor
                            </div>
                            <span class="text-muted">No reviews yet</span>
                        </div>
                    @endif
                </div>

                <!-- Price -->
                <div class="product-price">₱{{ number_format($product->price, 2) }}</div>

                <!-- Stock Status -->
                @if($product->stock_quantity > 0)
                    <div class="stock-status">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>In Stock ({{ $product->stock_quantity }} available)</span>
                    </div>
                @else
                    <div class="stock-status out-of-stock">
                        <i class="bi bi-x-circle-fill"></i>
                        <span>Out of Stock</span>
                    </div>
                @endif

                <!-- Seller Info -->
                @if($product->seller)
                <div class="card mt-3 mb-3">
                    <div class="card-body">
                        <small class="text-muted">Sold by:</small>
                        <a href="{{ route('toyshop.business.show', $product->seller->business_slug) }}" class="text-decoration-none">
                            <strong>{{ $product->seller->business_name }}</strong>
                        </a>
                    </div>
                </div>
                @endif

                <!-- Quantity & Actions -->
                @if($product->stock_quantity > 0)
                <div class="mb-3">
                    <label class="form-label fw-bold">Quantity</label>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-secondary" onclick="decrementQty()">-</button>
                        <input type="number" id="quantity" value="1" min="1" max="{{ $product->stock_quantity }}" class="form-control text-center" style="width: 80px;">
                        <button type="button" class="btn btn-outline-secondary" onclick="incrementQty()">+</button>
                    </div>
                </div>

                <div class="action-buttons">
                    @auth
                        <form action="{{ route('cart.add') }}" method="POST" class="flex-grow-1">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" id="cart_quantity" value="1">
                            <button type="submit" class="btn-add-cart w-100">
                                <i class="bi bi-cart-plus me-2"></i>
                                Add to Cart
                            </button>
                        </form>

                        <form action="{{ isset($inWishlist) && $inWishlist ? route('wishlist.remove', $product->id) : route('wishlist.add') }}" method="POST">
                            @csrf
                            @if(isset($inWishlist) && $inWishlist)
                                @method('DELETE')
                            @else
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                            @endif
                            <button type="submit" class="btn-wishlist">
                                <i class="bi {{ isset($inWishlist) && $inWishlist ? 'bi-heart-fill text-danger' : 'bi-heart' }}"></i>
                            </button>
                        </form>
                    @else
                        <button onclick="alert('Please login to add items to cart')" class="btn-add-cart w-100">
                            <i class="bi bi-cart-plus me-2"></i>
                            Add to Cart
                        </button>
                        <button onclick="alert('Please login to add to wishlist')" class="btn-wishlist">
                            <i class="bi bi-heart"></i>
                        </button>
                    @endauth
                </div>
                @endif

                <!-- Product Details -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Product Details</h5>
                        <table class="table table-sm">
                            <tbody>
                                @if($product->brand)
                                <tr>
                                    <td class="fw-bold" style="width: 120px;">Brand</td>
                                    <td>{{ $product->brand }}</td>
                                </tr>
                                @endif
                                @if($product->sku)
                                <tr>
                                    <td class="fw-bold">SKU</td>
                                    <td>{{ $product->sku }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="fw-bold">Condition</td>
                                    <td><span class="badge bg-primary">{{ ucfirst($product->condition) }}</span></td>
                                </tr>
                                @if($product->categories && $product->categories->count() > 0)
                                <tr>
                                    <td class="fw-bold">Categories</td>
                                    <td>
                                        @foreach($product->categories as $category)
                                            <span class="badge bg-info me-1">{{ $category->name }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Customer Reviews (right column) -->
                @php
                    $approvedReviews = $product->reviews->where('status', 'approved')->sortByDesc('created_at');
                @endphp
                <div class="product-reviews-section mt-4">
                <h5 class="mb-4">
                    <i class="bi bi-chat-square-text me-2"></i>Customer Reviews
                    @if($reviewsCount > 0)
                        <span class="text-muted fw-normal fs-6">({{ $reviewsCount }} {{ Str::plural('review', $reviewsCount) }})</span>
                    @endif
                </h5>

                @if(isset($canUserReview) && $canUserReview && $eligibleOrderId)
                <div class="card mb-4 border-primary">
                    <div class="card-body">
                        <h6 class="card-title mb-3"><i class="bi bi-pencil-square me-2"></i>Write a Review</h6>
                        <p class="text-muted small mb-3">You purchased this product. Share your experience!</p>
                        <form id="productReviewForm" action="{{ route('reviews.product.store', $product->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="order_id" value="{{ $eligibleOrderId }}">
                            <div class="mb-3">
                                <label class="form-label">Rating <span class="text-danger">*</span></label>
                                <div class="d-flex gap-1 product-review-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        <button type="button" class="btn btn-outline-warning p-2 product-star-btn" data-rating="{{ $i }}" aria-label="{{ $i }} star">
                                            <i class="bi bi-star"></i>
                                        </button>
                                    @endfor
                                </div>
                                <input type="hidden" name="rating" id="productReviewRating" value="0" required>
                                @error('rating')<p class="text-danger small mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Product Images (Optional, 1–10)</label>
                                <p class="small text-muted mb-2">Upload photos of the product (max 2MB each)</p>
                                <input type="file" id="productReviewFileInput" accept="image/jpeg,image/png,image/jpg" multiple class="d-none">
                                <div id="productReviewUploadArea" class="product-review-upload-area" onclick="document.getElementById('productReviewFileInput').click()">
                                    <i class="bi bi-cloud-arrow-up text-muted"></i>
                                    <span class="small text-muted ms-2">Click or drag to add photos</span>
                                </div>
                                <div id="productReviewPreviewGrid" class="product-review-preview-grid"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Your review (optional)</label>
                                <textarea name="review_text" class="form-control" rows="3" placeholder="What did you think of this product?" maxlength="1000">{{ old('review_text') }}</textarea>
                                <small class="text-muted">Max 1000 characters</small>
                            </div>
                            <button type="submit" class="btn btn-primary" id="productReviewSubmitBtn"><i class="bi bi-send me-1"></i>Submit Review</button>
                        </form>
                    </div>
                </div>
                <div id="productReviewFullscreen" class="product-review-fullscreen" onclick="if(event.target===this) window.closeProductReviewFullscreen()">
                    <button type="button" class="btn-close-fs" onclick="window.closeProductReviewFullscreen()">&times;</button>
                    <img id="productReviewFullscreenImg" src="" alt="">
                </div>
                @endif

                @if($approvedReviews->count() > 0)
                    <div class="review-summary mb-4">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="product-rating-stars text-warning" style="font-size: 1.5rem;">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $fullStars)
                                        <i class="bi bi-star-fill"></i>
                                    @elseif($i == $fullStars + 1 && $hasHalfStar)
                                        <i class="bi bi-star-half"></i>
                                    @else
                                        <i class="bi bi-star"></i>
                                    @endif
                                @endfor
                            </div>
                            <span class="fw-bold fs-4">{{ number_format($avgRating, 1) }}</span>
                            <span class="text-muted">out of 5</span>
                        </div>
                    </div>
                    <div class="review-comments-list">
                        @foreach($approvedReviews as $review)
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <strong>{{ $review->user->name ?? 'Anonymous' }}</strong>
                                            @if($review->isVerifiedPurchase())
                                                <span class="badge bg-success" style="font-size: 0.7rem;">Verified Purchase</span>
                                            @endif
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="text-warning">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                                @endfor
                                            </div>
                                            <small class="text-muted">{{ $review->created_at->format('M d, Y') }}</small>
                                        </div>
                                    </div>
                                    @if($review->review_text)
                                        <p class="mb-0">{{ $review->review_text }}</p>
                                    @endif
                                    @if($review->review_images && count($review->review_images) > 0)
                                        <div class="d-flex gap-2 mt-2 flex-wrap">
                                            @foreach($review->review_images as $imgPath)
                                                <a href="{{ asset('storage/' . $imgPath) }}" target="_blank" class="d-inline-block">
                                                    <img src="{{ asset('storage/' . $imgPath) }}" alt="Review image" class="rounded" style="max-width: 80px; max-height: 80px; object-fit: cover;">
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-light border">
                        <p class="mb-0 text-muted">
                            <i class="bi bi-info-circle me-2"></i>No reviews yet. Be the first to review this product!
                        </p>
                    </div>
                @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Product images array (1080P-4K HDR URLs)
    const productImages = [
        @if($hasImages)
            @foreach($product->images as $index => $image)
                '{{ $imageDisplayUrls[$index] ?? asset('storage/' . $image->image_path) }}'{{ $loop->last ? '' : ',' }}
            @endforeach
        @else
            '{{ asset('images/no-image.png') }}'
        @endif
    ];
    
    let currentImageIndex = 0;
    let slideshowTimer = null;
    const SLIDESHOW_INTERVAL = 10000; // 10 seconds
    
    // Quantity controls
    function incrementQty() {
        const input = document.getElementById('quantity');
        const cartQty = document.getElementById('cart_quantity');
        const max = parseInt(input.max);
        const current = parseInt(input.value);
        if (current < max) {
            input.value = current + 1;
            if (cartQty) cartQty.value = current + 1;
        }
    }
    
    function decrementQty() {
        const input = document.getElementById('quantity');
        const cartQty = document.getElementById('cart_quantity');
        const current = parseInt(input.value);
        if (current > 1) {
            input.value = current - 1;
            if (cartQty) cartQty.value = current - 1;
        }
    }
    
    // Sync quantity inputs
    const qtyInput = document.getElementById('quantity');
    const cartQtyInput = document.getElementById('cart_quantity');
    if (qtyInput && cartQtyInput) {
        qtyInput.addEventListener('change', function() {
            cartQtyInput.value = this.value;
        });
    }
    
    // Change image (thumbnail click or slideshow)
    function changeImage(src, index, element) {
        const mainImage = document.getElementById('mainImage');
        if (mainImage) mainImage.src = src;
        
        currentImageIndex = index;
        
        // Update active thumbnail if element provided
        if (element) {
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            element.classList.add('active');
        } else {
            document.querySelectorAll('.thumbnail').forEach((thumb, i) => {
                thumb.classList.toggle('active', i === index);
            });
        }
        
        resetSlideshowTimer();
    }
    
    // Advance to next image (for slideshow)
    function advanceSlide() {
        if (productImages.length <= 1) return;
        const nextIndex = (currentImageIndex + 1) % productImages.length;
        changeImage(productImages[nextIndex], nextIndex, null);
    }
    
    function startSlideshowTimer() {
        stopSlideshowTimer();
        if (productImages.length <= 1) return;
        slideshowTimer = setInterval(advanceSlide, SLIDESHOW_INTERVAL);
    }
    
    function stopSlideshowTimer() {
        if (slideshowTimer) {
            clearInterval(slideshowTimer);
            slideshowTimer = null;
        }
    }
    
    function resetSlideshowTimer() {
        stopSlideshowTimer();
        if (productImages.length > 1) {
            slideshowTimer = setInterval(advanceSlide, SLIDESHOW_INTERVAL);
        }
    }
    
    // HOVER-TO-ZOOM (inline)
    const mainImageContainer = document.getElementById('mainImageContainer');
    const mainImageEl = document.getElementById('mainImage');
    if (mainImageContainer && mainImageEl) {
        mainImageContainer.addEventListener('mousemove', function(e) {
            const rect = mainImageContainer.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            mainImageEl.style.transformOrigin = x + '% ' + y + '%';
            mainImageEl.style.transform = 'scale(2.2)';
        });
        mainImageContainer.addEventListener('mouseleave', function() {
            mainImageEl.style.transform = 'scale(1)';
            mainImageEl.style.transformOrigin = 'center center';
        });
    }
    
    // Slideshow: 10 seconds when not hovering
    if (mainImageContainer) {
        mainImageContainer.addEventListener('mouseenter', stopSlideshowTimer);
        mainImageContainer.addEventListener('mouseleave', startSlideshowTimer);
    }
    
    // Start slideshow on page load (if multiple images)
    startSlideshowTimer();

    // Product review star rating
    document.querySelectorAll('.product-star-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var r = parseInt(this.getAttribute('data-rating'));
            var hid = document.getElementById('productReviewRating');
            if (hid) hid.value = r;
            document.querySelectorAll('.product-star-btn').forEach(function(b, i) {
                var icon = b.querySelector('i');
                if (icon) icon.className = (i + 1) <= r ? 'bi bi-star-fill' : 'bi bi-star';
            });
        });
    });

    // Product review image upload (1-10)
    var productReviewFiles = [];
    var PR_MAX = 10, PR_MAX_SIZE = 2 * 1024 * 1024;
    window.closeProductReviewFullscreen = function() {
        var fs = document.getElementById('productReviewFullscreen');
        if (fs) { fs.classList.remove('active'); document.body.style.overflow = ''; }
    };
    function renderProductReviewPreviews() {
        var grid = document.getElementById('productReviewPreviewGrid');
        var area = document.getElementById('productReviewUploadArea');
        if (!grid) return;
        grid.innerHTML = '';
        productReviewFiles.forEach(function(f, i) {
            var reader = new FileReader();
            reader.onload = (function(idx) {
                return function(e) {
                    var s = e.target.result;
                    var div = document.createElement('div');
                    div.className = 'product-review-preview-item';
                    div.innerHTML = '<img src="' + s + '" alt="Preview">' +
                        '<button type="button" class="btn-del" data-idx="' + idx + '"><i class="bi bi-trash"></i></button>' +
                        '<button type="button" class="btn-view"><i class="bi bi-zoom-in"></i></button>';
                    grid.appendChild(div);
                    div.querySelector('.btn-del').onclick = function() {
                        productReviewFiles.splice(parseInt(this.getAttribute('data-idx')), 1);
                        renderProductReviewPreviews();
                        if (area) area.style.display = '';
                    };
                    div.querySelector('.btn-view').onclick = function() {
                        document.getElementById('productReviewFullscreenImg').src = s;
                        document.getElementById('productReviewFullscreen').classList.add('active');
                        document.body.style.overflow = 'hidden';
                    };
                    div.querySelector('img').onclick = function() {
                        document.getElementById('productReviewFullscreenImg').src = s;
                        document.getElementById('productReviewFullscreen').classList.add('active');
                        document.body.style.overflow = 'hidden';
                    };
                };
            })(i);
            reader.readAsDataURL(f);
        });
        if (productReviewFiles.length >= PR_MAX && area) area.style.display = 'none';
    }
    var prInput = document.getElementById('productReviewFileInput');
    var prArea = document.getElementById('productReviewUploadArea');
    if (prInput && prArea) {
        prInput.onchange = function() {
            for (var i = 0; i < this.files.length && productReviewFiles.length < PR_MAX; i++) {
                var f = this.files[i];
                if (f.type.match(/^image\/(jpeg|png|jpg)$/) && f.size <= PR_MAX_SIZE) productReviewFiles.push(f);
            }
            renderProductReviewPreviews();
            this.value = '';
        };
        prArea.ondragover = function(e) { e.preventDefault(); prArea.classList.add('dragover'); };
        prArea.ondragleave = function() { prArea.classList.remove('dragover'); };
        prArea.ondrop = function(e) {
            e.preventDefault();
            prArea.classList.remove('dragover');
            for (var i = 0; i < e.dataTransfer.files.length && productReviewFiles.length < PR_MAX; i++) {
                var f = e.dataTransfer.files[i];
                if (f.type.match(/^image\/(jpeg|png|jpg)$/) && f.size <= PR_MAX_SIZE) productReviewFiles.push(f);
            }
            renderProductReviewPreviews();
        };
    }

    var reviewForm = document.getElementById('productReviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var r = parseInt(document.getElementById('productReviewRating')?.value || 0);
            if (r < 1 || r > 5) {
                alert('Please select a rating (1-5 stars).');
                return;
            }
            var fd = new FormData(reviewForm);
            for (var i = 0; i < productReviewFiles.length; i++) fd.append('review_images[]', productReviewFiles[i]);
            var btn = document.getElementById('productReviewSubmitBtn');
            btn.disabled = true;
            fetch(reviewForm.action, {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            }).then(function(res) {
                if (res.redirected) { window.location.href = res.url; return; }
                if (!res.ok) return res.json();
            }).then(function(data) {
                if (data && data.errors) {
                    btn.disabled = false;
                    var msg = (data.errors.rating || data.errors['review_images'] || ['Please check your input.'])[0];
                    alert(msg);
                }
            }).catch(function() { btn.disabled = false; });
        });
    }
    
    console.log('✅ Product view loaded with 1080P-4K HDR support');
    console.log('📸 Total images:', productImages.length);
    console.log('🎯 Image URLs:', productImages);
</script>
@endpush
