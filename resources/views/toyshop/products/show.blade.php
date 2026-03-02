@extends('layouts.toyshop')

@section('title', $product->name . ' - ToyHaven')

@push('styles')
<style>
    /* Product Page - 1080P-4K HDR Quality */
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
        transition: transform 0.3s ease;
    }
    
    .main-image-container:hover .main-image {
        transform: scale(1.05);
    }
    
    /* Quality Badge */
    .quality-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 700;
        z-index: 10;
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
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
    
    /* NEW FULLSCREEN VIEWER WITH ARROW KEY NAVIGATION */
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
    
    .fullscreen-content {
        position: relative;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .fullscreen-image-container {
        position: relative;
        max-width: 95vw;
        max-height: 95vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .fullscreen-image {
        max-width: 100%;
        max-height: 95vh;
        width: auto;
        height: auto;
        object-fit: contain;
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
        cursor: zoom-in;
    }
    
    .fullscreen-image.zoomed {
        cursor: move;
        max-width: none;
        max-height: none;
        width: 250%;
        height: auto;
    }
    
    /* Fullscreen Controls */
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
        z-index: 10;
        backdrop-filter: blur(10px);
    }
    
    .fullscreen-close:hover {
        background: rgba(255,255,255,0.3);
        transform: rotate(90deg);
    }
    
    .fullscreen-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        font-size: 1.5rem;
        cursor: pointer;
        transition: all 0.2s;
        z-index: 10;
        backdrop-filter: blur(10px);
    }
    
    .fullscreen-nav:hover {
        background: rgba(255,255,255,0.3);
        transform: translateY(-50%) scale(1.1);
    }
    
    .fullscreen-nav.prev {
        left: 20px;
    }
    
    .fullscreen-nav.next {
        right: 20px;
    }
    
    .fullscreen-counter {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0,0,0,0.75);
        color: white;
        padding: 10px 20px;
        border-radius: 20px;
        font-size: 0.875rem;
        backdrop-filter: blur(10px);
    }
    
    .fullscreen-zoom-hint {
        position: absolute;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0,0,0,0.75);
        color: white;
        padding: 10px 20px;
        border-radius: 20px;
        font-size: 0.875rem;
        backdrop-filter: blur(10px);
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .fullscreen-zoom-hint.show {
        opacity: 1;
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
        
        .fullscreen-nav {
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
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
                    $is4K = str_contains($firstImageUrl, '_SL2500_') || str_contains($firstImageUrl, '_SL2000_') || str_contains($firstImageUrl, '_SL3000_');
                @endphp

                <!-- Main Image -->
                <div class="main-image-container" onclick="openFullscreen(0)">
                    <div class="quality-badge">
                        <i class="bi bi-badge-hd-fill"></i>
                        <span>{{ $is4K ? '1080P-4K HDR' : 'HD' }}</span>
                    </div>
                    <div class="zoom-hint">
                        <i class="bi bi-arrows-fullscreen me-1"></i>
                        Click for fullscreen
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
            </div>

            <!-- Product Info -->
            <div class="product-info">
                <h1 class="product-title">{{ $product->name }}</h1>

                <!-- Rating -->
                <div class="mb-3">
                    @if(isset($product->reviews_count) && $product->reviews_count > 0)
                        <div class="d-flex align-items-center gap-2">
                            <div class="text-warning">
                                @for($i = 0; $i < 5; $i++)
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

                <!-- Description -->
                @if($product->description)
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Description</h5>
                        <p class="mb-0">{{ $product->description }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- NEW FULLSCREEN VIEWER WITH ARROW KEY NAVIGATION & ZOOM -->
<div id="fullscreenViewer" class="fullscreen-viewer">
    <button class="fullscreen-close" onclick="closeFullscreen()">
        <i class="bi bi-x"></i>
    </button>
    
    @if($hasImages && $product->images->count() > 1)
    <button class="fullscreen-nav prev" onclick="previousImage()">
        <i class="bi bi-chevron-left"></i>
    </button>
    <button class="fullscreen-nav next" onclick="nextImage()">
        <i class="bi bi-chevron-right"></i>
    </button>
    <div class="fullscreen-counter">
        <span id="currentImageIndex">1</span> / <span id="totalImages">{{ $product->images->count() }}</span>
    </div>
    @endif
    
    <div class="fullscreen-zoom-hint" id="zoomHint">
        Click to zoom • Drag to pan • Click again to zoom out
    </div>
    
    <div class="fullscreen-content" id="fullscreenContent">
        <div class="fullscreen-image-container" id="fullscreenImageContainer">
            <img id="fullscreenImage" 
                 src="" 
                 alt="{{ $product->name }}" 
                 class="fullscreen-image"
                 onclick="toggleZoom(event)">
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
    let isZoomed = false;
    let isDragging = false;
    let startX, startY, scrollLeft, scrollTop;
    
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
    
    // Change image (thumbnail click)
    function changeImage(src, index, element) {
        const mainImage = document.getElementById('mainImage');
        mainImage.src = src;
        
        // Update active thumbnail
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.classList.remove('active');
        });
        element.classList.add('active');
        
        currentImageIndex = index;
    }
    
    // NEW FULLSCREEN VIEWER FUNCTIONS
    function openFullscreen(index = 0) {
        currentImageIndex = index;
        const viewer = document.getElementById('fullscreenViewer');
        const fullscreenImage = document.getElementById('fullscreenImage');
        const zoomHint = document.getElementById('zoomHint');
        
        fullscreenImage.src = productImages[currentImageIndex];
        viewer.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        updateImageCounter();
        
        // Show zoom hint briefly
        zoomHint.classList.add('show');
        setTimeout(() => {
            zoomHint.classList.remove('show');
        }, 3000);
        
        // Reset zoom state
        isZoomed = false;
        fullscreenImage.classList.remove('zoomed');
    }
    
    function closeFullscreen() {
        const viewer = document.getElementById('fullscreenViewer');
        viewer.classList.remove('active');
        document.body.style.overflow = '';
        isZoomed = false;
    }
    
    function nextImage() {
        if (currentImageIndex < productImages.length - 1) {
            currentImageIndex++;
        } else {
            currentImageIndex = 0; // Loop back to first
        }
        updateFullscreenImage();
    }
    
    function previousImage() {
        if (currentImageIndex > 0) {
            currentImageIndex--;
        } else {
            currentImageIndex = productImages.length - 1; // Loop to last
        }
        updateFullscreenImage();
    }
    
    function updateFullscreenImage() {
        const fullscreenImage = document.getElementById('fullscreenImage');
        fullscreenImage.src = productImages[currentImageIndex];
        updateImageCounter();
        
        // Reset zoom when changing images
        isZoomed = false;
        fullscreenImage.classList.remove('zoomed');
        fullscreenImage.style.transform = '';
    }
    
    function updateImageCounter() {
        const counter = document.getElementById('currentImageIndex');
        if (counter) {
            counter.textContent = currentImageIndex + 1;
        }
    }
    
    // NEW ACCURATE ZOOM FUNCTION
    function toggleZoom(event) {
        const fullscreenImage = document.getElementById('fullscreenImage');
        const container = document.getElementById('fullscreenImageContainer');
        
        if (!isZoomed) {
            // Zoom in
            fullscreenImage.classList.add('zoomed');
            fullscreenImage.style.cursor = 'move';
            isZoomed = true;
            
            // Calculate zoom position based on click
            const rect = fullscreenImage.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;
            const xPercent = (x / rect.width) * 100;
            const yPercent = (y / rect.height) * 100;
            
            fullscreenImage.style.transformOrigin = `${xPercent}% ${yPercent}%`;
        } else {
            // Zoom out
            fullscreenImage.classList.remove('zoomed');
            fullscreenImage.style.cursor = 'zoom-in';
            fullscreenImage.style.transform = '';
            isZoomed = false;
        }
    }
    
    // Drag to pan when zoomed
    const fullscreenImage = document.getElementById('fullscreenImage');
    const fullscreenContent = document.getElementById('fullscreenContent');
    
    fullscreenImage.addEventListener('mousedown', function(e) {
        if (!isZoomed) return;
        isDragging = true;
        startX = e.pageX - fullscreenContent.offsetLeft;
        startY = e.pageY - fullscreenContent.offsetTop;
        scrollLeft = fullscreenContent.scrollLeft;
        scrollTop = fullscreenContent.scrollTop;
        e.preventDefault();
    });
    
    fullscreenContent.addEventListener('mousemove', function(e) {
        if (!isDragging || !isZoomed) return;
        e.preventDefault();
        const x = e.pageX - fullscreenContent.offsetLeft;
        const y = e.pageY - fullscreenContent.offsetTop;
        const walkX = (x - startX) * 2;
        const walkY = (y - startY) * 2;
        fullscreenContent.scrollLeft = scrollLeft - walkX;
        fullscreenContent.scrollTop = scrollTop - walkY;
    });
    
    fullscreenContent.addEventListener('mouseup', function() {
        isDragging = false;
    });
    
    fullscreenContent.addEventListener('mouseleave', function() {
        isDragging = false;
    });
    
    // ARROW KEY NAVIGATION
    document.addEventListener('keydown', function(e) {
        const viewer = document.getElementById('fullscreenViewer');
        if (!viewer.classList.contains('active')) return;
        
        if (e.key === 'Escape') {
            closeFullscreen();
        } else if (e.key === 'ArrowRight') {
            nextImage();
        } else if (e.key === 'ArrowLeft') {
            previousImage();
        }
    });
    
    console.log('✅ Product view loaded with 1080P-4K HDR support');
    console.log('📸 Total images:', productImages.length);
    console.log('🎯 Image URLs:', productImages);
</script>
@endpush
