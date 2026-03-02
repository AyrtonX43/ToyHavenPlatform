@extends('layouts.toyshop')

@section('title', $product->name . ' - ToyHaven')

@push('styles')
<style>
    /* Modern Product Page Styles */
    .product-page {
        background: #f8f9fa;
        min-height: 100vh;
        padding: 2rem 0;
    }
    
    .product-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 1rem;
    }
    
    /* Breadcrumb */
    .breadcrumb {
        background: transparent;
        padding: 0.5rem 0;
        margin-bottom: 1.5rem;
    }
    
    /* Product Grid */
    .product-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    /* Image Gallery Section */
    .image-gallery {
        position: relative;
        overflow: visible;
    }
    
    .main-image-container {
        position: relative;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: visible;
        margin-bottom: 1rem;
    }
    
    .main-image-wrapper {
        position: relative;
        width: 100%;
        height: 600px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        overflow: hidden;
    }
    
    .main-image {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        cursor: crosshair;
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
    }
    
    /* 4K Badge */
    .quality-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        background: #10b981;
        color: white;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 600;
        z-index: 10;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    /* Zoom Hint */
    .zoom-hint {
        position: absolute;
        top: 12px;
        right: 12px;
        background: rgba(0,0,0,0.7);
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.875rem;
        z-index: 10;
    }
    
    /* Thumbnail Gallery */
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
    }
    
    .thumbnail.active {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }
    
    /* Amazon-Style Zoom Window */
    .zoom-window {
        position: absolute;
        top: 0;
        left: calc(100% + 20px);
        width: 500px;
        height: 600px;
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
        display: none;
        z-index: 1000;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    }
    
    .zoom-window.active {
        display: block !important;
    }
    
    .zoom-window-image {
        position: absolute;
        top: 0;
        left: 0;
        width: auto !important;
        height: auto !important;
        max-width: none !important;
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
    }
    
    /* Zoom Indicator */
    .zoom-indicator {
        position: absolute;
        border: 2px solid #f59e0b;
        background: rgba(255, 255, 255, 0.3);
        pointer-events: none;
        display: none;
        z-index: 15;
    }
    
    .zoom-indicator.active {
        display: block !important;
    }
    
    /* Product Info Section */
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
        padding: 6px 12px;
        background: #d1fae5;
        color: #065f46;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
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
    }
    
    /* Fullscreen Viewer */
    .fullscreen-viewer {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.98);
        z-index: 999999;
        align-items: center;
        justify-content: center;
    }
    
    .fullscreen-viewer.active {
        display: flex !important;
    }
    
    .fullscreen-image {
        width: auto !important;
        height: auto !important;
        max-width: 100vw !important;
        max-height: 100vh !important;
        object-fit: contain !important;
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
        display: block;
    }
    
    .fullscreen-close {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        font-size: 1.5rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .fullscreen-close:hover {
        background: rgba(255,255,255,0.3);
    }
    
    /* Responsive */
    @media (max-width: 1400px) {
        .zoom-window {
            display: none !important;
        }
        
        .main-image {
            cursor: zoom-in !important;
        }
    }
    
    @media (max-width: 768px) {
        .product-grid {
            grid-template-columns: 1fr;
            gap: 2rem;
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
                <div class="main-image-container">
                    @php
                        $hasImages = $product->images && $product->images->count() > 0;
                        $firstImageUrl = $hasImages ? ($imageDisplayUrls[0] ?? asset('storage/' . $product->images->first()->image_path)) : asset('images/no-image.png');
                        $is4K = str_contains($firstImageUrl, '_SL3000_') || str_contains($firstImageUrl, '_SL2000_');
                    @endphp
                    
                    @if($is4K)
                    <div class="quality-badge">
                        <i class="bi bi-badge-4k"></i>
                        <span>4K HDR Quality</span>
                    </div>
                    @endif
                    
                    <div class="zoom-hint">
                        <i class="bi bi-search"></i> Hover to zoom · Click for fullscreen
                    </div>
                    
                    <div class="main-image-wrapper" id="mainImageWrapper">
                        <img id="mainImage" 
                             src="{{ $firstImageUrl }}" 
                             alt="{{ $product->name }}"
                             class="main-image">
                        <div id="zoomIndicator" class="zoom-indicator"></div>
                    </div>
                    
                    <!-- Zoom Window -->
                    <div id="zoomWindow" class="zoom-window">
                        <img id="zoomWindowImage" 
                             src="{{ $firstImageUrl }}" 
                             alt="{{ $product->name }}"
                             class="zoom-window-image">
                    </div>
                </div>
                
                <!-- Thumbnails -->
                @if($hasImages && $product->images->count() > 1)
                <div class="thumbnail-gallery">
                    @foreach($product->images as $index => $image)
                        @php
                            $thumbUrl = $imageDisplayUrls[$index] ?? asset('storage/' . $image->image_path);
                        @endphp
                        <img src="{{ asset('storage/' . $image->image_path) }}" 
                             alt="{{ $product->name }}"
                             class="thumbnail {{ $index === 0 ? 'active' : '' }}"
                             data-full="{{ $thumbUrl }}"
                             onclick="changeImage('{{ $thumbUrl }}', this)">
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Product Info -->
            <div class="product-info">
                <h1 class="product-title">{{ $product->name }}</h1>
                
                <!-- Rating -->
                <div class="mb-3">
                    @if(isset($product->reviews_count) && $product->reviews_count > 0)
                        <div class="d-flex align-items-center gap-2">
                            <div class="text-warning">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star-fill"></i>
                                @endfor
                            </div>
                            <span class="text-muted">({{ $product->reviews_count }} reviews)</span>
                        </div>
                    @else
                        <span class="text-muted">No reviews yet</span>
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
                    <div class="stock-status" style="background: #fee2e2; color: #991b1b;">
                        <i class="bi bi-x-circle-fill"></i>
                        <span>Out of Stock</span>
                    </div>
                @endif
                
                <!-- Seller Info -->
                @if($product->seller)
                <div class="card mt-3 mb-3">
                    <div class="card-body">
                        <h6 class="mb-2">Sold By</h6>
                        <a href="{{ route('toyshop.business.show', $product->seller->business_slug) }}" class="text-decoration-none">
                            <strong>{{ $product->seller->business_name }}</strong>
                        </a>
                    </div>
                </div>
                @endif
                
                <!-- Quantity Selector -->
                @if($product->stock_quantity > 0)
                <div class="mb-3">
                    <label class="form-label fw-bold">Quantity</label>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-secondary" onclick="decrementQty()">-</button>
                        <input type="number" id="quantity" value="1" min="1" max="{{ $product->stock_quantity }}" class="form-control text-center" style="width: 80px;">
                        <button type="button" class="btn btn-outline-secondary" onclick="incrementQty()">+</button>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="action-buttons">
                    @auth
                    <form action="{{ route('cart.add') }}" method="POST" class="flex-fill">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" id="cart_quantity" value="1">
                        <button type="submit" class="btn-add-cart w-100">
                            <i class="bi bi-cart-plus me-2"></i>Add to Cart
                        </button>
                    </form>
                    
                    <form action="{{ isset($inWishlist) && $inWishlist ? route('wishlist.remove', $wishlistItem->id) : route('wishlist.add') }}" method="POST">
                        @csrf
                        @if(isset($inWishlist) && $inWishlist)
                            @method('DELETE')
                        @else
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                        @endif
                        <button type="submit" class="btn-wishlist">
                            <i class="bi bi-heart{{ isset($inWishlist) && $inWishlist ? '-fill text-danger' : '' }}"></i>
                        </button>
                    </form>
                    @else
                    <a href="{{ route('login') }}" class="btn-add-cart w-100 text-center text-decoration-none">
                        <i class="bi bi-cart-plus me-2"></i>Login to Purchase
                    </a>
                    @endauth
                </div>
                @endif
                
                <!-- Product Details -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">Product Details</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tbody>
                                @if($product->brand)
                                <tr>
                                    <td class="fw-bold" style="width: 120px;">Brand</td>
                                    <td>{{ $product->brand }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="fw-bold">SKU</td>
                                    <td>{{ $product->sku }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Condition</td>
                                    <td><span class="badge bg-primary">{{ ucfirst($product->condition ?? 'new') }}</span></td>
                                </tr>
                                @if($product->categories->count() > 0)
                                <tr>
                                    <td class="fw-bold">Categories</td>
                                    <td>
                                        @foreach($product->categories as $category)
                                            <span class="badge bg-secondary">{{ $category->name }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">Description</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $product->description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fullscreen Viewer -->
<div id="fullscreenViewer" class="fullscreen-viewer" onclick="closeFullscreen()">
    <button class="fullscreen-close" onclick="closeFullscreen()">
        <i class="bi bi-x"></i>
    </button>
    <img id="fullscreenImage" src="" alt="{{ $product->name }}" class="fullscreen-image">
</div>
@endsection

@push('scripts')
<script>
    // Product images array (4K URLs)
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
    
    // Quantity controls
    function incrementQty() {
        const input = document.getElementById('quantity');
        const max = parseInt(input.max);
        const current = parseInt(input.value);
        if (current < max) {
            input.value = current + 1;
            document.getElementById('cart_quantity').value = current + 1;
        }
    }
    
    function decrementQty() {
        const input = document.getElementById('quantity');
        const current = parseInt(input.value);
        if (current > 1) {
            input.value = current - 1;
            document.getElementById('cart_quantity').value = current - 1;
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
    
    // Change image
    function changeImage(src, element) {
        const mainImage = document.getElementById('mainImage');
        const zoomWindowImage = document.getElementById('zoomWindowImage');
        
        mainImage.src = src;
        zoomWindowImage.src = src;
        
        // Update active thumbnail
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.classList.remove('active');
        });
        element.classList.add('active');
        
        // Update current index
        const thumbnails = Array.from(document.querySelectorAll('.thumbnail'));
        currentImageIndex = thumbnails.indexOf(element);
    }
    
    // Amazon-style hover zoom
    function initHoverZoom() {
        const wrapper = document.getElementById('mainImageWrapper');
        const mainImage = document.getElementById('mainImage');
        const zoomWindow = document.getElementById('zoomWindow');
        const zoomWindowImage = document.getElementById('zoomWindowImage');
        const zoomIndicator = document.getElementById('zoomIndicator');
        
        console.log('Initializing hover zoom...', {
            wrapper: !!wrapper,
            mainImage: !!mainImage,
            zoomWindow: !!zoomWindow,
            zoomWindowImage: !!zoomWindowImage,
            zoomIndicator: !!zoomIndicator
        });
        
        if (!wrapper || !mainImage || !zoomWindow || !zoomWindowImage || !zoomIndicator) {
            console.error('Missing zoom elements');
            return;
        }
        
        const zoomLevel = 2.5;
        const indicatorSize = 150;
        let imageNaturalDimensions = { width: 0, height: 0 };
        
        // Preload image dimensions
        const preloadImg = new Image();
        preloadImg.onload = function() {
            imageNaturalDimensions.width = this.naturalWidth;
            imageNaturalDimensions.height = this.naturalHeight;
            console.log('Image dimensions loaded:', imageNaturalDimensions);
        };
        preloadImg.src = mainImage.src;
        
        wrapper.addEventListener('mouseenter', function() {
            console.log('Mouse entered wrapper');
            zoomWindow.classList.add('active');
            if (zoomIndicator) zoomIndicator.classList.add('active');
            
            // Update zoom window image
            zoomWindowImage.src = mainImage.src;
        });
        
        wrapper.addEventListener('mouseleave', function() {
            console.log('Mouse left wrapper');
            zoomWindow.classList.remove('active');
            if (zoomIndicator) zoomIndicator.classList.remove('active');
        });
        
        wrapper.addEventListener('mousemove', function(e) {
            if (!zoomWindow.classList.contains('active')) return;
            
            const rect = wrapper.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const xPercent = x / rect.width;
            const yPercent = y / rect.height;
            
            // Position indicator
            if (zoomIndicator) {
                const indicatorX = Math.max(0, Math.min(rect.width - indicatorSize, x - indicatorSize / 2));
                const indicatorY = Math.max(0, Math.min(rect.height - indicatorSize, y - indicatorSize / 2));
                
                zoomIndicator.style.left = indicatorX + 'px';
                zoomIndicator.style.top = indicatorY + 'px';
                zoomIndicator.style.width = indicatorSize + 'px';
                zoomIndicator.style.height = indicatorSize + 'px';
            }
            
            // Position zoomed image
            if (imageNaturalDimensions.width > 0) {
                const scaledWidth = imageNaturalDimensions.width * zoomLevel;
                const scaledHeight = imageNaturalDimensions.height * zoomLevel;
                
                zoomWindowImage.style.width = scaledWidth + 'px';
                zoomWindowImage.style.height = scaledHeight + 'px';
                
                const moveX = -xPercent * (scaledWidth - zoomWindow.offsetWidth);
                const moveY = -yPercent * (scaledHeight - zoomWindow.offsetHeight);
                
                zoomWindowImage.style.left = moveX + 'px';
                zoomWindowImage.style.top = moveY + 'px';
            }
        });
        
        // Click to open fullscreen
        wrapper.addEventListener('click', function() {
            console.log('Opening fullscreen');
            openFullscreen();
        });
    }
    
    // Fullscreen viewer
    function openFullscreen() {
        const viewer = document.getElementById('fullscreenViewer');
        const fullscreenImage = document.getElementById('fullscreenImage');
        
        console.log('Opening fullscreen with image:', productImages[currentImageIndex]);
        
        fullscreenImage.src = productImages[currentImageIndex];
        
        // Force image to load at full resolution
        fullscreenImage.onload = function() {
            console.log('Fullscreen image loaded:', {
                naturalWidth: this.naturalWidth,
                naturalHeight: this.naturalHeight,
                displayWidth: this.width,
                displayHeight: this.height
            });
        };
        
        viewer.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeFullscreen() {
        const viewer = document.getElementById('fullscreenViewer');
        viewer.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        initHoverZoom();
    });
    
    // ESC key to close fullscreen
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeFullscreen();
        }
    });
</script>
@endpush
