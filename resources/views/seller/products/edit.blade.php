@extends('layouts.seller')

@section('title', 'Edit Product - ToyHaven')

@section('page-title', 'Edit Product')

@section('content')
<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('seller.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('seller.products.index') }}">Products</a></li>
        <li class="breadcrumb-item active">Edit Product</li>
    </ol>
</nav>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Edit Product</h4>
        <p class="text-muted mb-0">Update your product listing</p>
    </div>
    <a href="{{ route('seller.products.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Products
    </a>
</div>

    <form action="{{ route('seller.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-8">
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" id="product_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea id="product_description" name="description" class="form-control @error('description') is-invalid @enderror" rows="5" required>{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label">Product Categories <span class="text-danger">*</span></label>
                                <p class="text-muted small mb-3">Select one or more categories that best describe your product</p>
                                
                                <div class="row g-3" id="categorySelectionGrid">
                                    @foreach($categories as $category)
                                        <div class="col-lg-3 col-md-4 col-sm-6">
                                            <div class="category-selection-card">
                                                <input type="checkbox" 
                                                       name="categories[]" 
                                                       value="{{ $category->id }}" 
                                                       id="category_{{ $category->id }}"
                                                       class="category-selection-checkbox d-none"
                                                       {{ in_array($category->id, old('categories', $selectedCategoryIds ?? [])) ? 'checked' : '' }}>
                                                <label for="category_{{ $category->id }}" 
                                                       class="category-selection-label card h-100 border shadow-sm position-relative overflow-hidden cursor-pointer">
                                                    <!-- Selection Indicator -->
                                                    <div class="category-selection-indicator">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                    </div>
                                                    
                                                    <!-- Category Content (synced with algorithm/category selection: image, Flaticon icon, or display icon) -->
                                                    <div class="category-selection-content text-center p-3">
                                                        @if($category->image)
                                                            <img src="{{ asset('storage/' . $category->image) }}" 
                                                                 alt="{{ $category->name }}"
                                                                 class="category-selection-image mb-2"
                                                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                                        @elseif($category->getAnimatedIconPngUrl())
                                                            <img src="{{ $category->getAnimatedIconPngUrl() }}" 
                                                                 alt="{{ $category->name }}"
                                                                 class="category-selection-image mb-2"
                                                                 style="width: 60px; height: 60px; object-fit: contain; border-radius: 8px;"
                                                                 loading="lazy">
                                                        @else
                                                            <div class="category-selection-icon mb-2">
                                                                <i class="bi {{ $category->getDisplayIcon() }}" style="font-size: 2.5rem; color: #6c757d;"></i>
                                                            </div>
                                                        @endif
                                                        <h6 class="category-selection-name mb-0 fw-bold">{{ $category->name }}</h6>
                                                        @if($category->description)
                                                            <small class="text-muted d-block mt-1">{{ Str::limit($category->description, 50) }}</small>
                                                        @endif
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        <span id="selectedCategoriesCount">0</span> categor{{ $categories->count() > 1 ? 'ies' : 'y' }} selected
                                    </small>
                                </div>
                                
                                @error('categories')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Brand</label>
                                <input type="text" id="product_brand" name="brand" class="form-control @error('brand') is-invalid @enderror" value="{{ old('brand', $product->brand) }}">
                                @error('brand')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">SKU <span class="text-danger">*</span></label>
                                <input type="text" id="product_sku" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $product->sku) }}" readonly>
                                <input type="hidden" name="sku" value="{{ $product->sku }}">
                                <small class="text-muted">SKU cannot be changed after product creation</small>
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pricing -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Pricing</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 col-lg-5 mb-3">
                                <label class="form-label">Base Price (₱) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" id="product_price" name="price" step="0.01" min="0" max="9999999.99" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $product->base_price ?? $product->price) }}" placeholder="0.00" required onblur="formatPriceInput(this)">
                                    <button class="btn btn-outline-secondary" type="button" onclick="adjustPrice(-10)" title="-₱10"><i class="bi bi-dash-lg"></i></button>
                                    <button class="btn btn-outline-secondary" type="button" onclick="adjustPrice(-1)" title="-₱1"><i class="bi bi-dash"></i></button>
                                    <button class="btn btn-outline-secondary" type="button" onclick="adjustPrice(1)" title="+₱1"><i class="bi bi-plus"></i></button>
                                    <button class="btn btn-outline-secondary" type="button" onclick="adjustPrice(10)" title="+₱10"><i class="bi bi-plus-lg"></i></button>
                                </div>
                                <small class="text-muted">Your base selling price. Platform fee and VAT shown in breakdown below.</small>
                                @error('price')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <input type="hidden" id="platform_fee_percentage" name="platform_fee_percentage" value="{{ old('platform_fee_percentage', $product->platform_fee_percentage ?? 5.00) }}">
                        
                        <!-- Price Breakdown -->
                        <div id="priceBreakdown" class="card border" style="display: none;">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Price breakdown</h6>
                                <small class="text-muted">Transparent split between you, the customer, and the platform</small>
                            </div>
                            <div class="card-body py-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="small text-uppercase text-muted fw-semibold mb-2">What the customer pays</div>
                                        <table class="table table-sm table-borderless mb-0">
                                            <tr>
                                                <td>Item price (your listing)</td>
                                                <td class="text-end" id="breakdown_base_price">₱0.00</td>
                                            </tr>
                                            <tr>
                                                <td>Platform fee (<span id="breakdown_fee_percent">5</span>%)</td>
                                                <td class="text-end" id="breakdown_platform_fee">₱0.00</td>
                                            </tr>
                                            <tr>
                                                <td class="border-top pt-1">Subtotal</td>
                                                <td class="text-end border-top pt-1" id="breakdown_subtotal">₱0.00</td>
                                            </tr>
                                            <tr>
                                                <td>VAT (<span id="breakdown_tax_percent">12</span>%)</td>
                                                <td class="text-end" id="breakdown_tax">₱0.00</td>
                                            </tr>
                                            <tr class="border-top">
                                                <td><strong>Customer pays (total)</strong></td>
                                                <td class="text-end"><strong id="breakdown_final_price" class="text-success">₱0.00</strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="small text-uppercase text-muted fw-semibold mb-2">Your earnings & platform</div>
                                        <table class="table table-sm table-borderless mb-0">
                                            <tr>
                                                <td>Listing price</td>
                                                <td class="text-end" id="breakdown_listing_display">₱0.00</td>
                                            </tr>
                                            <tr>
                                                <td>Less platform fee (<span id="breakdown_fee_pct_2">5</span>%)</td>
                                                <td class="text-end text-danger" id="breakdown_platform_fee_deduction">−₱0.00</td>
                                            </tr>
                                            <tr class="border-top">
                                                <td><strong>You receive (net)</strong></td>
                                                <td class="text-end"><strong id="breakdown_seller_receives" class="text-primary">₱0.00</strong></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted small">Platform receives</td>
                                                <td class="text-end text-muted small" id="breakdown_platform_receives">₱0.00</td>
                                            </tr>
                                        </table>
                                        <div class="mt-3 small text-muted border-top pt-2">
                                            <p class="mb-1"><i class="bi bi-info-circle me-1"></i> VAT is remitted per Philippine regulations. Platform fee covers payment processing, listing, and marketplace services.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="final_price_hidden" name="final_price" value="">
                            <input type="hidden" id="base_price_hidden" name="base_price" value="{{ old('base_price', $product->base_price ?? '') }}">
                        </div>

                        <!-- Amazon Reference Price Checker -->
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="mb-3">
                                <i class="bi bi-amazon me-2"></i>Check Amazon Reference Price
                                <small class="text-muted">(Optional - For price comparison only)</small>
                            </h6>
                            
                            <!-- Tabs for Search Methods -->
                            <ul class="nav nav-tabs mb-3" id="amazonSearchTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="name-tab" data-bs-toggle="tab" data-bs-target="#name-search" type="button" role="tab">
                                        <i class="bi bi-search me-1"></i> Search by Name
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="url-tab" data-bs-toggle="tab" data-bs-target="#url-search" type="button" role="tab">
                                        <i class="bi bi-link-45deg me-1"></i> Paste URL/ASIN
                                    </button>
                                </li>
                            </ul>

                            <!-- Tab Content -->
                            <div class="tab-content" id="amazonSearchTabsContent">
                                <!-- Product Name Search -->
                                <div class="tab-pane fade show active" id="name-search" role="tabpanel">
                                    <div class="mb-3">
                                        <label class="form-label">Search Product Name</label>
                                        <div class="input-group">
                                            <input type="text" id="amazon_name_input" class="form-control" placeholder="e.g., LEGO Star Wars">
                                            <button type="button" class="btn btn-primary" id="searchByNameBtn">
                                                <i class="bi bi-search me-1"></i> Search
                                            </button>
                                        </div>
                                        <small class="text-muted">Search Amazon products by name</small>
                                    </div>
                                    <div id="nameSearchResults" class="mt-3"></div>
                                </div>

                                <!-- URL/ASIN Search -->
                                <div class="tab-pane fade" id="url-search" role="tabpanel">
                                    <div class="mb-3">
                                        <label class="form-label">Amazon URL or ASIN</label>
                                        <div class="input-group">
                                            <input type="text" id="amazon_url_input" class="form-control" placeholder="https://amazon.com/dp/B08XYZ1234 or B08XYZ1234">
                                            <button type="button" class="btn btn-primary" id="searchByUrlBtn">
                                                <i class="bi bi-search me-1"></i> Check Price
                                            </button>
                                        </div>
                                        <small class="text-muted">Paste Amazon product URL or enter ASIN (10 characters)</small>
                                    </div>
                                    <div id="urlSearchResult" class="mt-3"></div>
                                </div>
                            </div>

                            <!-- Reference Price Display -->
                            <div id="amazonReferenceDisplay" class="mt-3" style="display: none;">
                                <div class="alert alert-info">
                                    <h6 class="mb-2"><i class="bi bi-info-circle me-2"></i>Amazon Reference Selected</h6>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <strong id="amazonRefProductTitle" class="d-block mb-2"></strong>
                                            <div class="mb-2">
                                                <span class="text-muted">Reference Price: </span>
                                                <strong id="amazonRefPrice" class="text-primary"></strong>
                                            </div>
                                            <div class="mb-2">
                                                <small class="text-success">
                                                    <i class="bi bi-check-circle me-1"></i> All product images and videos have been automatically imported. You can still upload additional images/videos if needed.
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearAmazonReference()">
                                                <i class="bi bi-x me-1"></i> Clear
                                            </button>
                                        </div>
                                    </div>
                                    <input type="hidden" name="amazon_reference_price" id="amazon_reference_price_hidden" value="{{ old('amazon_reference_price', $product->amazon_reference_price) }}">
                                    <input type="hidden" name="amazon_reference_image" id="amazon_reference_image_hidden" value="{{ old('amazon_reference_image', $product->amazon_reference_image) }}">
                                    <input type="hidden" name="amazon_reference_url" id="amazon_reference_url_hidden" value="{{ old('amazon_reference_url', $product->amazon_reference_url) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product options (variants) -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                        <div>
                            <h5 class="mb-1"><i class="bi bi-collection me-2"></i>Product options (variants)</h5>
                            <p class="text-muted small mb-0">Add or edit variants like Color, Size, or Style. Customer price is based on base price + adjustment, plus platform fee and VAT.</p>
                        </div>
                        <span class="badge bg-primary rounded-pill px-3 py-2" id="variationsCount">0 variants</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="variationsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-nowrap">Option type</th>
                                        <th class="text-nowrap">Option value</th>
                                        <th class="text-nowrap text-end">Price adjustment (₱)</th>
                                        <th class="text-nowrap text-end">Customer price <small class="text-muted fw-normal">(incl. fee &amp; tax)</small></th>
                                        <th class="text-nowrap text-end">Stock</th>
                                        <th width="52" class="text-center"></th>
                                    </tr>
                                </thead>
                                <tbody id="variationsTableBody"></tbody>
                            </table>
                        </div>
                        <p class="small text-muted mt-2 mb-0">
                            <i class="bi bi-info-circle me-1"></i> Customer price = (base price + adjustment) + platform fee + 12% VAT.
                        </p>
                        <button type="button" class="btn btn-outline-primary mt-3" id="addVariationRow">
                            <i class="bi bi-plus-lg me-1"></i> Add variant
                        </button>
                    </div>
                </div>
                @php
                    $editVariationsJson = $product->variations->isEmpty() ? '[]' : json_encode($product->variations->map(function ($v) {
                        return ['variation_type' => $v->variation_type, 'variation_value' => $v->variation_value, 'price_adjustment' => (float) $v->price_adjustment, 'stock_quantity' => (int) $v->stock_quantity];
                    })->values()->all());
                @endphp
                <input type="hidden" name="variations_json" id="variations_json" value="{{ old('variations_json', $editVariationsJson) }}">

                <!-- Inventory -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Inventory</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="stock_quantity" min="0" class="form-control @error('stock_quantity') is-invalid @enderror" value="{{ old('stock_quantity', $product->stock_quantity) }}" required>
                            @error('stock_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Product Images -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Product Images</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info py-2 mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <small>You can update images anytime, even when the product is pending approval or already approved.</small>
                        </div>
                        <!-- Imported Images Preview -->
                        <div id="importedImagesPreview" class="mb-3" style="display: none;">
                            <label class="form-label">Imported Images Preview</label>
                            <div id="importedImagesContainer" class="d-flex flex-wrap gap-2 mb-2"></div>
                            <small class="text-muted">These images will be downloaded and added to your product</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Upload Additional Images</label>
                            <input type="file" id="product_images" name="images[]" class="form-control @error('images') is-invalid @enderror" multiple accept="image/*">
                            <small class="text-muted">You can upload additional images (up to 10 total). Existing images will be kept. Images imported from Amazon reference will be added automatically.</small>
                            @error('images')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('images.*')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Existing Images (removable when pending or approved) -->
                        @if($product->images && $product->images->count() > 0)
                            <div class="mb-3">
                                <label class="form-label">Current Images</label>
                                <div class="d-flex flex-wrap gap-2" id="currentImagesContainer">
                                    @foreach($product->images as $image)
                                        <div class="position-relative image-to-keep" data-image-id="{{ $image->id }}" style="width: 120px; height: 120px;">
                                            <img src="{{ asset('storage/' . $image->image_path) }}" alt="Product Image" class="img-thumbnail" style="width: 100%; height: 100%; object-fit: cover;">
                                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 remove-image-btn" data-image-id="{{ $image->id }}" title="Remove image" style="padding: 2px 6px;">
                                                <i class="bi bi-x"></i>
                                            </button>
                                            <small class="d-block text-center mt-1">
                                                @if($image->is_primary)
                                                    <span class="badge bg-primary">Primary</span>
                                                @endif
                                            </small>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">Click the × on an image to remove it. Upload new images to add more.</small>
                                <div id="images_to_delete_container"></div>
                            </div>
                        @endif
                        
                        <!-- Hidden inputs for imported image URLs -->
                        <input type="hidden" name="imported_image_urls" id="imported_image_urls" value="">
                        <!-- Hidden input for imported video URLs -->
                        <input type="hidden" name="imported_video_urls" id="imported_video_urls" value="">
                    </div>
                </div>

                <!-- Product Videos (Optional) -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Product Videos <small class="text-muted">(Optional)</small></h5>
                    </div>
                    <div class="card-body">
                        <!-- Imported Video Preview -->
                        <div id="importedVideoPreview" class="mb-3" style="display: none;">
                            <label class="form-label">Imported Video Preview</label>
                            <div id="importedVideoContainer" class="mb-2"></div>
                            <small class="text-muted">Video URL will be saved with your product</small>
                        </div>
                        
                        <!-- Uploaded Video Preview -->
                        <div id="uploadedVideoPreview" class="mb-3" style="display: none;">
                            <label class="form-label">Uploaded Video Preview</label>
                            <div id="uploadedVideoContainer" class="mb-2">
                                <div class="alert alert-info">
                                    <i class="bi bi-file-play me-2"></i>
                                    <span id="uploadedVideoName"></span>
                                    <button type="button" class="btn btn-sm btn-danger float-end" onclick="removeUploadedVideo()">
                                        <i class="bi bi-x"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Video URL Input -->
                        <div class="mb-3">
                            <label class="form-label">Video URL</label>
                            <div class="input-group">
                                <input type="url" id="product_video_url" name="video_url" class="form-control @error('video_url') is-invalid @enderror" value="{{ old('video_url', $product->video_url) }}" placeholder="https://www.youtube.com/watch?v=... or video URL">
                                <button type="button" class="btn btn-outline-danger" onclick="clearVideoUrl()" title="Remove video">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                            <small class="text-muted">Enter a video URL (YouTube, Vimeo, etc.) or click Remove to clear</small>
                            @error('video_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Video File Upload -->
                        <div class="mb-3">
                            <label class="form-label">Or Upload Video File</label>
                            <input type="file" id="product_video_file" name="video_file" class="form-control @error('video_file') is-invalid @enderror" accept="video/*">
                            <small class="text-muted">Upload a video file (MP4, MOV, AVI, etc.) - Max size: 50MB</small>
                            @error('video_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Product Submission</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <small>Your product will be reviewed by admin before going live. You'll be notified once it's approved.</small>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-save me-1"></i> Update Product
                        </button>
                        <a href="{{ route('seller.products.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </a>
                        <hr>
                        <div class="small text-muted">
                            <strong>Tips:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Use clear, high-quality images</li>
                                <li>Write detailed descriptions</li>
                                <li>Set competitive prices</li>
                                <li>Keep stock updated</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

@push('styles')
<style>
    .selected-product {
        border: 3px solid #198754 !important;
        background-color: #f0f9ff !important;
        box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25) !important;
        transition: all 0.3s ease;
    }
    
    .selected-product .badge {
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.8); }
        to { opacity: 1; transform: scale(1); }
    }
    
    .list-group-item.selected-product {
        position: relative;
    }
    
    .list-group-item.selected-product::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background-color: #198754;
    }
    
    /* Category Selection Styles */
    .category-selection-card {
        position: relative;
    }
    
    .category-selection-label {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid #e9ecef !important;
        background: #fff;
    }
    
    .category-selection-label:hover {
        border-color: #0d6efd !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15) !important;
    }
    
    .category-selection-checkbox:checked + .category-selection-label {
        border-color: #198754 !important;
        background: linear-gradient(135deg, rgba(25, 135, 84, 0.05) 0%, rgba(40, 167, 69, 0.05) 100%);
        box-shadow: 0 4px 15px rgba(25, 135, 84, 0.2) !important;
    }
    
    .category-selection-indicator {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 28px;
        height: 28px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transform: scale(0);
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        z-index: 10;
        box-shadow: 0 2px 8px rgba(25, 135, 84, 0.3);
    }
    
    .category-selection-indicator i {
        color: #198754;
        font-size: 1.2rem;
    }
    
    .category-selection-checkbox:checked + .category-selection-label .category-selection-indicator {
        opacity: 1;
        transform: scale(1);
    }
    
    .category-selection-checkbox:checked + .category-selection-label .category-selection-name {
        color: #198754;
    }
    
    .category-selection-content {
        min-height: 120px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    
    .category-selection-name {
        color: #212529;
        transition: color 0.3s ease;
        font-size: 0.95rem;
    }
    
    .category-selection-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.3s ease;
    }
    
    .category-selection-label:hover .category-selection-icon {
        transform: scale(1.1);
    }
    
    .category-selection-image {
        transition: transform 0.3s ease;
    }
    
    .category-selection-label:hover .category-selection-image {
        transform: scale(1.05);
    }
    
    .cursor-pointer {
        cursor: pointer;
    }
    #variationsTable .form-control-sm { min-width: 4rem; }
    #variationsTable .form-select-sm { min-width: 6rem; }
    #variationsTable .variation-final-price { min-width: 6rem; }
</style>
@endpush

@push('scripts')
<script>
// --- Product options (variants) ---
var variationsData = [];
var VARIATION_TYPES = ['Color', 'Size', 'Model', 'Style', 'Material', 'Pattern', 'Variant'];

function getVariationFinalPrice(priceAdjustment) {
    var base = parseFloat(document.getElementById('product_price').value) || 0;
    var feePct = parseFloat(document.getElementById('platform_fee_percentage').value) || 5;
    var taxPct = 12;
    var withAdjustment = base + (parseFloat(priceAdjustment) || 0);
    return withAdjustment * (1 + feePct / 100) * (1 + taxPct / 100);
}

function updateVariationFinalPriceCells() {
    var tbody = document.getElementById('variationsTableBody');
    if (!tbody) return;
    tbody.querySelectorAll('tr').forEach(function(row, i) {
        var cell = row.querySelector('.variation-final-price');
        if (cell && variationsData[i] != null)
            cell.textContent = '₱' + getVariationFinalPrice(variationsData[i].price_adjustment).toFixed(2);
    });
}

function syncVariationsToInput() {
    var payload = variationsData.map(function(v) {
        return {
            variation_type: v.variation_type,
            variation_value: v.variation_value,
            price_adjustment: parseFloat(v.price_adjustment) || 0,
            stock_quantity: parseInt(v.stock_quantity, 10) || 0,
            is_available: true
        };
    });
    document.getElementById('variations_json').value = JSON.stringify(payload);
    var el = document.getElementById('variationsCount');
    if (el) el.textContent = payload.length + ' variant' + (payload.length !== 1 ? 's' : '');
}

function removeVariationRow(index) {
    variationsData.splice(index, 1);
    renderVariationsTable();
}

function addVariationRow(variationType, variationValue, priceAdjustment, stockQuantity) {
    variationsData.push({
        variation_type: variationType || 'Color',
        variation_value: variationValue || '',
        price_adjustment: priceAdjustment != null ? priceAdjustment : 0,
        stock_quantity: stockQuantity != null ? stockQuantity : 0
    });
    renderVariationsTable();
}

function renderVariationsTable() {
    var tbody = document.getElementById('variationsTableBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    variationsData.forEach(function(v, index) {
        var tr = document.createElement('tr');
        var typeOpts = VARIATION_TYPES.map(function(t) {
            return '<option value="' + t + '"' + (v.variation_type === t ? ' selected' : '') + '>' + t + '</option>';
        }).join('');
        var finalPrice = getVariationFinalPrice(v.price_adjustment);
        tr.innerHTML =
            '<td><select class="form-select form-select-sm variation-type">' + typeOpts + '</select></td>' +
            '<td><input type="text" class="form-control form-control-sm variation-value" placeholder="e.g. Red, Large" value="' + (v.variation_value || '').replace(/"/g, '&quot;') + '"></td>' +
            '<td class="text-end"><input type="number" step="0.01" class="form-control form-control-sm variation-price text-end" placeholder="0" value="' + (v.price_adjustment != null ? v.price_adjustment : '') + '"></td>' +
            '<td class="variation-final-price text-end align-middle fw-semibold text-success">₱' + finalPrice.toFixed(2) + '</td>' +
            '<td class="text-end"><input type="number" min="0" class="form-control form-control-sm variation-stock text-end" placeholder="0" value="' + (v.stock_quantity != null ? v.stock_quantity : '') + '"></td>' +
            '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeVariationRow(' + index + ')" title="Remove variant"><i class="bi bi-trash"></i></button></td>';
        tbody.appendChild(tr);
    });
    tbody.querySelectorAll('.variation-type').forEach(function(el, i) { el.onchange = function() { variationsData[i].variation_type = this.value; syncVariationsToInput(); }; });
    tbody.querySelectorAll('.variation-value').forEach(function(el, i) { el.oninput = function() { variationsData[i].variation_value = this.value; syncVariationsToInput(); }; });
    tbody.querySelectorAll('.variation-price').forEach(function(el, i) {
        el.oninput = function() {
            variationsData[i].price_adjustment = this.value;
            syncVariationsToInput();
            var cell = this.closest('tr').querySelector('.variation-final-price');
            if (cell) cell.textContent = '₱' + getVariationFinalPrice(this.value).toFixed(2);
        };
    });
    tbody.querySelectorAll('.variation-stock').forEach(function(el, i) { el.oninput = function() { variationsData[i].stock_quantity = this.value; syncVariationsToInput(); }; });
    syncVariationsToInput();
}

// Format price to 2 decimals when leaving the field (allows free typing of many digits)
function formatPriceInput(el) {
    if (!el || el.id !== 'product_price') return;
    const num = parseFloat(el.value);
    if (!isNaN(num) && num >= 0) {
        el.value = Math.min(9999999.99, num).toFixed(2);
        const inputEvent = new Event('input', { bubbles: true });
        el.dispatchEvent(inputEvent);
    }
}

// Function to adjust price using buttons - must be global for onclick handlers
function adjustPrice(amount) {
    const priceInput = document.getElementById('product_price');
    if (!priceInput) return;
    
    const currentPrice = parseFloat(priceInput.value) || 0;
    const newPrice = Math.max(0, currentPrice + amount); // Ensure price doesn't go below 0
    
    priceInput.value = newPrice.toFixed(2);
    
    // Trigger price calculation by dispatching input event
    if (typeof isPriceFromAmazon !== 'undefined') {
        isPriceFromAmazon = false; // Reset flag so it treats as manual adjustment
    }
    const inputEvent = new Event('input', { bubbles: true });
    priceInput.dispatchEvent(inputEvent);
}

document.addEventListener('DOMContentLoaded', function() {
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Load initial variants (edit page)
    var initialJson = document.getElementById('variations_json');
    if (initialJson && initialJson.value) {
        try {
            var arr = JSON.parse(initialJson.value);
            if (Array.isArray(arr) && arr.length) {
                variationsData = arr.map(function(v) {
                    return {
                        variation_type: v.variation_type || 'Variant',
                        variation_value: v.variation_value || '',
                        price_adjustment: v.price_adjustment != null ? v.price_adjustment : 0,
                        stock_quantity: v.stock_quantity != null ? v.stock_quantity : 0
                    };
                });
                renderVariationsTable();
            }
        } catch (e) { /* ignore */ }
    }
    var addBtn = document.getElementById('addVariationRow');
    if (addBtn) addBtn.addEventListener('click', function() { addVariationRow('Color', '', 0, 0); });
    
    // Remove product image (mark for deletion on submit)
    document.querySelectorAll('.remove-image-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var imageId = this.getAttribute('data-image-id');
            var container = document.getElementById('images_to_delete_container');
            if (!container) return;
            var existing = container.querySelector('input[value="' + imageId + '"]');
            if (existing) return;
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'images_to_delete[]';
            input.value = imageId;
            container.appendChild(input);
            this.closest('.position-relative').style.display = 'none';
        });
    });
    
    // Search by URL/ASIN
    document.getElementById('searchByUrlBtn').addEventListener('click', function() {
        const url = document.getElementById('amazon_url_input').value.trim();
        const resultDiv = document.getElementById('urlSearchResult');
        
        if (!url) {
            resultDiv.innerHTML = '<div class="alert alert-warning">Please enter an Amazon URL or ASIN.</div>';
            return;
        }
        
        clearProductHighlights();
        window.__lastUrlSearchProduct = null;
        
        resultDiv.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div> <span class="ms-2">Searching...</span></div>';
        
        fetch('{{ route("api.amazon.search-url") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ url: url })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.product) {
                const product = data.product;
                const pricePhp = product.price_php || product.price || '';
                const priceDisplay = pricePhp ? `₱${parseFloat(pricePhp).toFixed(2)}` : 'Price not available';
                const productImage = product.image ? `<img src="${product.image}" alt="${product.title || 'Product'}" class="img-thumbnail mb-2" style="max-width: 200px; max-height: 200px; object-fit: contain;" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'200\\' height=\\'200\\'%3E%3Crect width=\\'200\\' height=\\'200\\' fill=\\'%23f8f9fa\\'/%3E%3Ctext x=\\'50%25\\' y=\\'50%25\\' text-anchor=\\'middle\\' dy=\\'.3em\\' fill=\\'%236c757d\\'%3ENo Image%3C/text%3E%3C/svg%3E';">` : '';
                
                // Prepare product data with price_php and variations (so "Use as Reference" has variants)
                const productData = {
                    ...product,
                    price_php: pricePhp || product.price || 0,
                    images: product.images && Array.isArray(product.images) ? product.images : (product.image ? [product.image] : []),
                    videos: product.videos && Array.isArray(product.videos) ? product.videos : [],
                    variations: Array.isArray(product.variations) ? product.variations : []
                };
                window.__lastUrlSearchProduct = productData;
                
                const productAsin = product.asin || '';
                resultDiv.innerHTML = `
                    <div class="alert alert-success" id="product-result-${productAsin}" data-asin="${productAsin}">
                        <div class="d-flex gap-3">
                            ${productImage ? `<div class="flex-shrink-0 position-relative">
                                ${productImage}
                                <span class="badge bg-success position-absolute top-0 start-0" id="selected-badge-${productAsin}" style="display: none;">
                                    <i class="bi bi-check-circle"></i> Selected
                                </span>
                            </div>` : ''}
                            <div class="flex-grow-1">
                                <h6><i class="bi bi-check-circle me-2"></i>Product Found!</h6>
                                <p class="mb-2"><strong>${product.title || 'Product'}</strong></p>
                                ${product.brand ? `<p class="mb-1 text-muted small">Brand: <strong>${product.brand}</strong></p>` : ''}
                                <p class="mb-2">Amazon Price: <strong>${priceDisplay}</strong></p>
                                <button type="button" id="btn-use-ref-${productAsin}" class="btn btn-sm btn-primary" onclick="setAmazonReferenceFromData('${productAsin}', '${(product.title || '').replace(/'/g, "\\'")}', '${pricePhp}', '${product.url || ''}', '${product.image || ''}', '${(product.brand || '').replace(/'/g, "\\'")}')">
                                    <i class="bi bi-check me-1"></i> Use as Reference
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `<div class="alert alert-warning">${data.message || 'Product not found. Please check the URL or ASIN.'}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultDiv.innerHTML = '<div class="alert alert-danger">Error searching Amazon. Please try again.</div>';
        });
    });
    
    // Search by Name
    document.getElementById('searchByNameBtn').addEventListener('click', function() {
        const query = document.getElementById('amazon_name_input').value.trim();
        const resultsDiv = document.getElementById('nameSearchResults');
        
        if (!query || query.length < 3) {
            resultsDiv.innerHTML = '<div class="alert alert-warning">Please enter at least 3 characters.</div>';
            return;
        }
        
        clearProductHighlights();
        window.__lastUrlSearchProduct = null;
        
        resultsDiv.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div> <span class="ms-2">Searching...</span></div>';
        
        fetch('{{ route("api.amazon.search-name") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ query: query })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.results && data.results.length > 0) {
                let html = '<h6 class="mb-3">Search Results:</h6><div class="list-group">';
                data.results.forEach((product, index) => {
                    const pricePhp = product.price_php || product.price || '';
                    const priceDisplay = pricePhp ? `₱${parseFloat(pricePhp).toFixed(2)}` : 'Price not available';
                    const productImage = product.image ? `<img src="${product.image}" alt="${product.title || 'Product'}" class="img-thumbnail" style="max-width: 120px; max-height: 120px; object-fit: contain;" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\\'bg-light d-flex align-items-center justify-content-center\\' style=\\'width: 120px; height: 120px;\\'><i class=\\'bi bi-image text-muted\\'></i></div>';">` : '<div class="bg-light d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;"><i class="bi bi-image text-muted"></i></div>';
                    
                    // Prepare product data for setAmazonReference
                    const productData = {
                        ...product,
                        price_php: pricePhp || product.price || 0,
                        // Ensure images array exists (may be single image or array)
                        images: product.images && Array.isArray(product.images) ? product.images : (product.image ? [product.image] : [])
                    };
                    
                    const productAsin = product.asin || `temp-${index}`;
                    html += `
                        <div class="list-group-item" id="product-result-${productAsin}" data-asin="${productAsin}">
                            <div class="d-flex gap-3">
                                <div class="flex-shrink-0 position-relative">
                                    ${productImage}
                                    <span class="badge bg-success position-absolute top-0 start-0" id="selected-badge-${productAsin}" style="display: none;">
                                        <i class="bi bi-check-circle"></i> Selected
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${product.title || 'Product'}</h6>
                                    <p class="mb-2 text-muted small">Price: <strong>${priceDisplay}</strong></p>
                                    ${product.brand ? `<p class="mb-1 text-muted small">Brand: <strong>${product.brand}</strong></p>` : ''}
                                    <button type="button" id="btn-use-ref-${productAsin}" class="btn btn-sm btn-primary" onclick="setAmazonReferenceFromData('${product.asin || ''}', '${(product.title || '').replace(/'/g, "\\'")}', '${pricePhp}', '${product.url || ''}', '${product.image || ''}', '${(product.brand || '').replace(/'/g, "\\'")}')">
                                        <i class="bi bi-check me-1"></i> Use as Reference
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                resultsDiv.innerHTML = html;
            } else {
                resultsDiv.innerHTML = `<div class="alert alert-warning">${data.message || 'No products found. Please try a different search term.'}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultsDiv.innerHTML = '<div class="alert alert-danger">Error searching Amazon. Please try again.</div>';
        });
    });
    
    // Allow Enter key to trigger search
    document.getElementById('amazon_url_input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('searchByUrlBtn').click();
        }
    });
    
    document.getElementById('amazon_name_input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('searchByNameBtn').click();
        }
    });
});

// Normalize Amazon brand: "Visit the LEGO Store" -> "LEGO", "Visit the Hot Wheels Store" -> "Hot Wheels"
function normalizeAmazonBrand(brand) {
    if (!brand || typeof brand !== 'string') return brand || '';
    const trimmed = brand.trim();
    const match = trimmed.match(/^Visit the (.+?) Store$/i);
    return match ? match[1].trim() : trimmed;
}

// Helper function to set reference from individual parameters (for search results)
function setAmazonReferenceFromData(asin, title, pricePhp, url, imageUrl, brand) {
    brand = normalizeAmazonBrand(brand);
    // Highlight immediately for visual feedback
    if (asin) {
        highlightSelectedProduct(asin);
    }
    
    const productData = {
        asin: asin,
        title: title,
        price_php: parseFloat(pricePhp) || 0,
        url: url,
        image: imageUrl,
        brand: brand,
        // Ensure images array exists
        images: imageUrl ? [imageUrl] : []
    };
    
    // If we have ASIN, fetch full details (which will include all images and videos)
    if (asin && asin.length === 10) {
        fetchFullProductDetails(asin, productData);
    } else {
        setAmazonReference(productData);
    }
}

// Function to fetch full product details by ASIN (for search results)
function fetchFullProductDetails(asin, productData) {
    if (!asin) {
        setAmazonReference(productData);
        return;
    }
    if (window.__lastUrlSearchProduct && window.__lastUrlSearchProduct.asin === asin) {
        setAmazonReference(window.__lastUrlSearchProduct);
        return;
    }
    
    // Show loading
    const resultsDiv = document.getElementById('nameSearchResults');
    const originalContent = resultsDiv.innerHTML;
    resultsDiv.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> <span class="ms-2">Loading full product details...</span></div>';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch('{{ route("api.amazon.search-url") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ url: asin })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.product) {
            const p = data.product;
            const fullProduct = {
                ...productData,
                ...p,
                price_php: p.price_php || productData.price_php || productData.price || 0,
                variations: Array.isArray(p.variations) ? p.variations : (productData.variations || [])
            };
            setAmazonReference(fullProduct);
            resultsDiv.innerHTML = originalContent; // Restore results
        } else {
            // Fallback to search result data
            setAmazonReference(productData);
            resultsDiv.innerHTML = originalContent;
        }
    })
    .catch(error => {
        console.error('Error fetching full details:', error);
        // Fallback to search result data
        setAmazonReference(productData);
        resultsDiv.innerHTML = originalContent;
    });
}

// Function to clear all product highlights
function clearProductHighlights() {
    document.querySelectorAll('.list-group-item.selected-product, .alert-success.selected-product').forEach(el => {
        el.classList.remove('selected-product', 'border-success', 'border-3');
        el.style.backgroundColor = '';
        el.style.boxShadow = '';
    });
    
    document.querySelectorAll('[id^="selected-badge-"]').forEach(badge => {
        badge.style.display = 'none';
    });
    
    document.querySelectorAll('[id^="btn-use-ref-"]').forEach(btn => {
        if (btn.classList.contains('btn-success')) {
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
            btn.innerHTML = '<i class="bi bi-check me-1"></i> Use as Reference';
            btn.disabled = false;
        }
    });
}

// Function to highlight selected product
function highlightSelectedProduct(asin) {
    // Clear previous highlights first
    clearProductHighlights();
    // Remove previous highlights from both search result areas
    document.querySelectorAll('.list-group-item.selected-product, .alert-success.selected-product').forEach(el => {
        el.classList.remove('selected-product', 'border-success', 'border-3');
        el.style.backgroundColor = '';
        el.style.boxShadow = '';
    });
    
    document.querySelectorAll('[id^="selected-badge-"]').forEach(badge => {
        badge.style.display = 'none';
    });
    
    document.querySelectorAll('[id^="btn-use-ref-"]').forEach(btn => {
        if (btn.classList.contains('btn-success')) {
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
            btn.innerHTML = '<i class="bi bi-check me-1"></i> Use as Reference';
            btn.disabled = false;
        }
    });
    
    // Highlight the selected product
    if (asin) {
        const productElement = document.getElementById(`product-result-${asin}`);
        const badgeElement = document.getElementById(`selected-badge-${asin}`);
        const buttonElement = document.getElementById(`btn-use-ref-${asin}`);
        
        if (productElement) {
            productElement.classList.add('selected-product', 'border-success', 'border-3');
            productElement.style.backgroundColor = '#f0f9ff';
            productElement.style.boxShadow = '0 0 0 0.25rem rgba(25, 135, 84, 0.25)';
            
            // Scroll to selected product with smooth animation
            setTimeout(() => {
                productElement.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 100);
        }
        
        if (badgeElement) {
            badgeElement.style.display = 'block';
        }
        
        if (buttonElement) {
            buttonElement.classList.remove('btn-primary');
            buttonElement.classList.add('btn-success');
            buttonElement.innerHTML = '<i class="bi bi-check-circle me-1"></i> Selected as Reference';
            buttonElement.disabled = true;
        }
    }
}

// Function to set Amazon reference and auto-fill form fields
function setAmazonReference(product) {
    if (!product) return;
    
    // Clear previous reference's imported content so autofill reflects the new selection only
    document.getElementById('imported_image_urls').value = '';
    const imgContainer = document.getElementById('importedImagesContainer');
    if (imgContainer) imgContainer.innerHTML = '';
    document.getElementById('importedImagesPreview').style.display = 'none';
    
    document.getElementById('imported_video_urls').value = '';
    const vidContainer = document.getElementById('importedVideoContainer');
    if (vidContainer) vidContainer.innerHTML = '';
    document.getElementById('product_video_url').value = '';
    document.getElementById('importedVideoPreview').style.display = 'none';
    
    
    const price = product.price_php || product.price || 0;
    const priceFloat = parseFloat(price);
    const priceDisplay = '₱' + priceFloat.toFixed(2);
    
    // Highlight the selected product
    if (product.asin) {
        highlightSelectedProduct(product.asin);
    }
    
    // Auto-fill Product Name
    if (product.title) {
        document.getElementById('product_name').value = product.title;
    }
    
    // Auto-fill Description
    if (product.description) {
        document.getElementById('product_description').value = product.description;
    } else if (product.title) {
        // Use title as fallback description
        document.getElementById('product_description').value = product.title + '\n\nProduct details imported from Amazon reference.';
    }
    
    // Auto-fill Brand (normalize "Visit the X Store" to "X" e.g. LEGO, Hot Wheels)
    if (product.brand) {
        document.getElementById('product_brand').value = normalizeAmazonBrand(product.brand);
    }
    
    // Auto-fill SKU (generate from ASIN if available)
    if (product.sku) {
        document.getElementById('product_sku').value = product.sku;
    } else if (product.asin) {
        document.getElementById('product_sku').value = 'SKU-' + product.asin;
    }
    
    // Update category selection count
    updateCategorySelectionCount();
    
    // Add event listeners to category checkboxes
    document.querySelectorAll('.category-selection-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateCategorySelectionCount);
    });
    
    // Category selection validation - ensure at least one is selected
    function updateCategorySelectionCount() {
        const selectedCount = document.querySelectorAll('.category-selection-checkbox:checked').length;
        const countElement = document.getElementById('selectedCategoriesCount');
        if (countElement) {
            countElement.textContent = selectedCount;
            if (selectedCount > 0) {
                countElement.classList.add('text-success', 'fw-bold');
            } else {
                countElement.classList.remove('text-success', 'fw-bold');
            }
        }
    }
    
    // Set the price in the main price input field
    // IMPORTANT: When Amazon reference is selected, use final_price (with platform fees and tax)
    if (priceFloat > 0) {
        // Mark that price is from Amazon reference
        isPriceFromAmazon = true;
        
        // Calculate final price with platform fees and tax
        const platformFeePercent = parseFloat(document.getElementById('platform_fee_percentage').value) || 5.00;
        const taxPercent = 12.00; // Philippine VAT
        
        const platformFee = (priceFloat * platformFeePercent) / 100;
        const subtotal = priceFloat + platformFee;
        const tax = (subtotal * taxPercent) / 100;
        const finalPrice = subtotal + tax;
        
        // Set the final price as the product price (what customers will pay in toyshop)
        document.getElementById('product_price').value = finalPrice.toFixed(2);
        
        // Set hidden final_price field
        document.getElementById('final_price_hidden').value = finalPrice.toFixed(2);
        
        // Calculate and display price breakdown (showing base price, fees, tax, and final)
        calculatePriceBreakdown(priceFloat); // Show breakdown based on base price from Amazon
    }
    
    // Set the hidden input values (use first image from images array = HD/HDR quality from API)
    document.getElementById('amazon_reference_price_hidden').value = priceFloat.toFixed(2);
    document.getElementById('amazon_reference_image_hidden').value = (product.images && product.images[0]) ? product.images[0] : (product.image || '');
    document.getElementById('amazon_reference_url_hidden').value = product.url || '';
    
    // Display the reference
    document.getElementById('amazonRefProductTitle').textContent = product.title || 'Product';
    document.getElementById('amazonRefPrice').textContent = priceDisplay;
    document.getElementById('amazonReferenceDisplay').style.display = 'block';
    
    // Automatically import ALL images from the reference
    if (product.images && Array.isArray(product.images) && product.images.length > 0) {
        // Import all images automatically
        product.images.forEach(imageUrl => {
            if (imageUrl) {
                importImageFromUrl(imageUrl);
            }
        });
    } else if (product.image) {
        // Fallback to single image if images array is not available
        importImageFromUrl(product.image);
    }
    
    // Automatically import ALL videos from the reference
    if (product.videos && Array.isArray(product.videos) && product.videos.length > 0) {
        // Import all videos automatically
        product.videos.forEach((videoUrl, index) => {
            if (videoUrl) {
                importVideoFromUrl(videoUrl);
                // Set the first video URL in the main input field
                if (index === 0) {
                    document.getElementById('product_video_url').value = videoUrl;
                }
            }
        });
    }
    
    // Scroll to the top of the form to show filled fields
    document.querySelector('.card').scrollIntoView({ behavior: 'smooth', block: 'start' });
    
    // Show success message
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show';
    alertDiv.innerHTML = `
        <i class="bi bi-check-circle me-2"></i><strong>Product information auto-filled!</strong> Please review and select the appropriate category, then adjust any fields as needed.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    const firstCard = document.querySelector('.card .card-body');
    if (firstCard) {
        firstCard.insertBefore(alertDiv, firstCard.firstChild);
    }
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Function to import image from URL
function importImageFromUrl(imageUrl) {
    if (!imageUrl) return;
    
    // Add to imported images array
    const importedUrls = document.getElementById('imported_image_urls').value;
    const urlArray = importedUrls ? importedUrls.split(',') : [];
    
    if (!urlArray.includes(imageUrl)) {
        urlArray.push(imageUrl);
        document.getElementById('imported_image_urls').value = urlArray.join(',');
        
        // Show preview
        const previewContainer = document.getElementById('importedImagesContainer');
        const imageDiv = document.createElement('div');
        imageDiv.className = 'position-relative';
        imageDiv.style.cssText = 'width: 120px; height: 120px;';
        imageDiv.innerHTML = `
            <img src="${imageUrl}" alt="Imported" class="img-thumbnail" style="width: 100%; height: 100%; object-fit: cover;">
            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" onclick="removeImportedImage('${imageUrl}')" style="padding: 2px 6px;">
                <i class="bi bi-x"></i>
            </button>
        `;
        previewContainer.appendChild(imageDiv);
        
        document.getElementById('importedImagesPreview').style.display = 'block';
        
        // Update required indicator
        updateImagesRequiredIndicator();
    }
}

// Function to remove imported image
function removeImportedImage(imageUrl) {
    const importedUrls = document.getElementById('imported_image_urls').value;
    const urlArray = importedUrls ? importedUrls.split(',') : [];
    const filtered = urlArray.filter(url => url !== imageUrl);
    document.getElementById('imported_image_urls').value = filtered.join(',');
    
    // Remove from preview
    const previewContainer = document.getElementById('importedImagesContainer');
    const images = previewContainer.querySelectorAll('div');
    images.forEach(div => {
        if (div.innerHTML.includes(imageUrl)) {
            div.remove();
        }
    });
    
    if (filtered.length === 0) {
        document.getElementById('importedImagesPreview').style.display = 'none';
    }
    
    // Update required indicator
    updateImagesRequiredIndicator();
}

// Function to import video from URL
function importVideoFromUrl(videoUrl) {
    if (!videoUrl) return;
    
    const videoInput = document.getElementById('product_video_url');
    const videoContainer = document.getElementById('importedVideoContainer');
    const importedVideoUrlsInput = document.getElementById('imported_video_urls');
    
    // Add to imported videos array
    const importedUrls = importedVideoUrlsInput.value;
    const urlArray = importedUrls ? importedUrls.split(',') : [];
    
    if (!urlArray.includes(videoUrl)) {
        urlArray.push(videoUrl);
        importedVideoUrlsInput.value = urlArray.join(',');
    }
    
    // Show preview container
    document.getElementById('importedVideoPreview').style.display = 'block';
    
    // Create video preview element with unique ID
    const videoId = 'video-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
    const videoDiv = document.createElement('div');
    videoDiv.id = videoId;
    videoDiv.className = 'position-relative mb-2 p-2 border rounded';
    videoDiv.style.cssText = 'background-color: #f8f9fa;';
    videoDiv.setAttribute('data-video-url', videoUrl);
    
    // Check if it's a YouTube or Vimeo URL for embedding
    let embedHtml = '';
    if (videoUrl.includes('youtube.com/watch') || videoUrl.includes('youtu.be/')) {
        const youtubeId = extractYouTubeId(videoUrl);
        if (youtubeId) {
            embedHtml = '<iframe width="100%" height="200" src="https://www.youtube.com/embed/' + youtubeId + '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
        }
    } else if (videoUrl.includes('vimeo.com/')) {
        const vimeoId = extractVimeoId(videoUrl);
        if (vimeoId) {
            embedHtml = '<iframe width="100%" height="200" src="https://player.vimeo.com/video/' + vimeoId + '" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
        }
    }
    
    if (embedHtml) {
        videoDiv.innerHTML = embedHtml +
            '<div class="mt-2">' +
                '<small class="text-muted d-block mb-1">' + videoUrl + '</small>' +
                '<button type="button" class="btn btn-sm btn-danger" onclick="removeImportedVideo(\'' + videoId + '\', \'' + videoUrl.replace(/'/g, "\\'") + '\')">' +
                    '<i class="bi bi-x me-1"></i> Remove' +
                '</button>' +
            '</div>';
    } else {
        // For other video URLs, show a link
        videoDiv.innerHTML = 
            '<div class="d-flex align-items-center justify-content-between">' +
                '<div>' +
                    '<i class="bi bi-play-circle me-2"></i>' +
                    '<a href="' + videoUrl + '" target="_blank" class="text-break">' + videoUrl + '</a>' +
                '</div>' +
                '<button type="button" class="btn btn-sm btn-danger" onclick="removeImportedVideo(\'' + videoId + '\', \'' + videoUrl.replace(/'/g, "\\'") + '\')">' +
                    '<i class="bi bi-x me-1"></i> Remove' +
                '</button>' +
            '</div>';
    }
    
    // Check if this video URL is already displayed
    const existingVideos = videoContainer.querySelectorAll('div[data-video-url]');
    let alreadyExists = false;
    existingVideos.forEach(div => {
        if (div.getAttribute('data-video-url') === videoUrl) {
            alreadyExists = true;
        }
    });
    
    if (!alreadyExists) {
        videoContainer.appendChild(videoDiv);
    }
}

// Helper function to extract YouTube video ID
function extractYouTubeId(url) {
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);
    return (match && match[2].length === 11) ? match[2] : null;
}

// Helper function to extract Vimeo video ID
function extractVimeoId(url) {
    const regExp = /(?:vimeo\.com\/)(?:.*\/)?(\d+)/;
    const match = url.match(regExp);
    return match ? match[1] : null;
}

// Function to remove imported video
function removeImportedVideo(videoId, videoUrl) {
    const videoInput = document.getElementById('product_video_url');
    const videoContainer = document.getElementById('importedVideoContainer');
    const importedVideoUrlsInput = document.getElementById('imported_video_urls');
    
    // Remove from imported URLs array
    const importedUrls = importedVideoUrlsInput.value;
    const urlArray = importedUrls ? importedUrls.split(',') : [];
    const filtered = urlArray.filter(url => url !== videoUrl);
    importedVideoUrlsInput.value = filtered.join(',');
    
    // Remove from preview by ID
    const videoElement = document.getElementById(videoId);
    if (videoElement) {
        videoElement.remove();
    }
    
    // If removed video was the one in the input field, clear it or set to next available
    if (videoInput.value === videoUrl) {
        // Find the first remaining video URL
        const remainingVideos = videoContainer.querySelectorAll('div[data-video-url]');
        if (remainingVideos.length > 0) {
            videoInput.value = remainingVideos[0].getAttribute('data-video-url');
        } else {
            videoInput.value = '';
        }
    }
    
    // Hide preview if no videos left
    if (videoContainer.children.length === 0) {
        document.getElementById('importedVideoPreview').style.display = 'none';
    }
}

// Function to update images required indicator
function updateImagesRequiredIndicator() {
    const importedUrls = document.getElementById('imported_image_urls').value;
    const fileInput = document.getElementById('product_images');
    const hasImported = importedUrls && importedUrls.trim().length > 0;
    const hasFiles = fileInput.files && fileInput.files.length > 0;
    const requiredIndicator = document.getElementById('imagesRequired');
    
    if (hasImported || hasFiles) {
        requiredIndicator.style.display = 'none';
        fileInput.removeAttribute('required');
    } else {
        requiredIndicator.style.display = 'inline';
        fileInput.setAttribute('required', 'required');
    }
}

// Watch file input changes
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('product_images');
    if (fileInput) {
        fileInput.addEventListener('change', updateImagesRequiredIndicator);
    }
    updateImagesRequiredIndicator();
    
    // Handle video file upload
    const videoFileInput = document.getElementById('product_video_file');
    if (videoFileInput) {
        videoFileInput.addEventListener('change', function(e) {
            handleVideoFileUpload(e.target.files[0]);
        });
    }
    
    // Function to update category selection count
    function updateCategorySelectionCount() {
        const selectedCount = document.querySelectorAll('.category-selection-checkbox:checked').length;
        const countElement = document.getElementById('selectedCategoriesCount');
        if (countElement) {
            countElement.textContent = selectedCount;
            if (selectedCount > 0) {
                countElement.classList.add('text-success', 'fw-bold');
            } else {
                countElement.classList.remove('text-success', 'fw-bold');
            }
        }
    }
    
    // Add event listeners to category checkboxes
    document.querySelectorAll('.category-selection-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateCategorySelectionCount);
    });
    
    // Update category count on load
    updateCategorySelectionCount();
    
    // Show price breakdown if price exists
    const priceInput = document.getElementById('product_price');
    if (priceInput && priceInput.value) {
        const price = parseFloat(priceInput.value) || 0;
        if (price > 0) {
            calculatePriceBreakdown(price);
        }
    }
});

// Function to handle video file upload
function handleVideoFileUpload(file) {
    if (!file) return;
    
    // Validate file type
    const validTypes = ['video/mp4', 'video/mov', 'video/avi', 'video/wmv', 'video/flv', 'video/webm'];
    if (!validTypes.includes(file.type)) {
        alert('Please upload a valid video file (MP4, MOV, AVI, WMV, FLV, or WebM)');
        document.getElementById('product_video_file').value = '';
        return;
    }
    
    // Validate file size (50MB)
    const maxSize = 50 * 1024 * 1024; // 50MB in bytes
    if (file.size > maxSize) {
        alert('Video file size must be less than 50MB');
        document.getElementById('product_video_file').value = '';
        return;
    }
    
    // Show preview
    const previewDiv = document.getElementById('uploadedVideoPreview');
    const nameSpan = document.getElementById('uploadedVideoName');
    
    if (nameSpan) {
        nameSpan.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
    }
    
    previewDiv.style.display = 'block';
    
    // Clear video URL if file is uploaded
    document.getElementById('product_video_url').value = '';
}

// Function to remove uploaded video
function removeUploadedVideo() {
    document.getElementById('product_video_file').value = '';
    document.getElementById('uploadedVideoPreview').style.display = 'none';
}

function clearVideoUrl() {
    document.getElementById('product_video_url').value = '';
    document.getElementById('imported_video_urls').value = '';
    const container = document.getElementById('importedVideoContainer');
    if (container) container.innerHTML = '';
    document.getElementById('importedVideoPreview').style.display = 'none';
}

// Helper function to format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Note: Images and videos are now automatically imported when a reference is selected
// Users can still upload additional images/videos manually

// Price Breakdown Calculation (matches backend PriceCalculationService)
// Fair split: Customer pays item + platform fee + VAT; Seller receives item − platform fee; Platform receives fee.
// skipInputUpdate: when true (user typing), don't overwrite the price input so manual edit isn't broken
function calculatePriceBreakdown(basePrice, skipInputUpdate) {
    const platformFeePercent = parseFloat(document.getElementById('platform_fee_percentage').value) || 5.00;
    const taxPercent = 12.00; // Philippine VAT
    
    const basePriceFloat = parseFloat(basePrice) || 0;
    const platformFee = (basePriceFloat * platformFeePercent) / 100;
    const subtotalBeforeTax = basePriceFloat + platformFee;
    const taxAmount = (subtotalBeforeTax * taxPercent) / 100;
    const finalPrice = subtotalBeforeTax + taxAmount; // What customer pays
    const sellerReceives = basePriceFloat - platformFee; // What seller gets (platform fee deducted)
    
    const fmt = (n) => '₱' + (typeof n === 'number' ? n.toFixed(2) : parseFloat(n).toFixed(2));
    
    // What the customer pays
    document.getElementById('breakdown_base_price').textContent = fmt(basePriceFloat);
    document.getElementById('breakdown_platform_fee').textContent = fmt(platformFee);
    document.getElementById('breakdown_subtotal').textContent = fmt(subtotalBeforeTax);
    document.getElementById('breakdown_tax').textContent = fmt(taxAmount);
    document.getElementById('breakdown_final_price').textContent = fmt(finalPrice);
    document.getElementById('breakdown_fee_percent').textContent = platformFeePercent.toFixed(2);
    document.getElementById('breakdown_tax_percent').textContent = taxPercent.toFixed(2);
    
    // Your earnings & platform
    document.getElementById('breakdown_listing_display').textContent = fmt(basePriceFloat);
    document.getElementById('breakdown_platform_fee_deduction').textContent = '−' + fmt(platformFee);
    document.getElementById('breakdown_seller_receives').textContent = fmt(sellerReceives);
    document.getElementById('breakdown_platform_receives').textContent = fmt(platformFee);
    document.getElementById('breakdown_fee_pct_2').textContent = platformFeePercent.toFixed(2);
    
    // Hidden inputs for form
    document.getElementById('final_price_hidden').value = finalPrice.toFixed(2);
    document.getElementById('base_price_hidden').value = basePriceFloat.toFixed(2);
    
    if (!skipInputUpdate) {
        document.getElementById('product_price').value = basePriceFloat.toFixed(2);
    }
    
    document.getElementById('priceBreakdown').style.display = 'block';
}

// Track if price is from Amazon reference
let isPriceFromAmazon = false;

// Watch for price changes
document.addEventListener('DOMContentLoaded', function() {
    const priceInput = document.getElementById('product_price');
    if (priceInput) {
        // When user manually enters price, treat it as base price and calculate final
        priceInput.addEventListener('input', function() {
            const enteredPrice = parseFloat(this.value) || 0;
            
            if (enteredPrice > 0) {
                // Always treat manually entered/adjusted price as base price
                document.getElementById('base_price_hidden').value = enteredPrice.toFixed(2);
                
                // Update breakdown only; don't overwrite input (skipInputUpdate=true) so typing e.g. "12.5" works
                calculatePriceBreakdown(enteredPrice, true);
                if (typeof updateVariationFinalPriceCells === 'function') updateVariationFinalPriceCells();
            } else {
                document.getElementById('priceBreakdown').style.display = 'none';
                document.getElementById('final_price_hidden').value = '';
                document.getElementById('base_price_hidden').value = '';
            }
            
            // Reset flag after user edits
            isPriceFromAmazon = false;
        });
        
        // Also trigger on page load if price exists
        if (priceInput.value) {
            const price = parseFloat(priceInput.value) || 0;
            if (price > 0) {
                // Use base_price if available, otherwise use the displayed price
                const basePriceHidden = document.getElementById('base_price_hidden');
                const basePrice = basePriceHidden.value ? parseFloat(basePriceHidden.value) : price;
                basePriceHidden.value = basePrice.toFixed(2);
                calculatePriceBreakdown(basePrice);
            }
        }
    }
});

// Function to clear Amazon reference and reset all auto-filled fields
function clearAmazonReference() {
    // Clear reference hidden fields and UI
    document.getElementById('amazon_reference_price_hidden').value = '';
    document.getElementById('amazon_reference_image_hidden').value = '';
    document.getElementById('amazon_reference_url_hidden').value = '';
    document.getElementById('amazonReferenceDisplay').style.display = 'none';
    document.getElementById('amazon_url_input').value = '';
    document.getElementById('amazon_name_input').value = '';
    document.getElementById('urlSearchResult').innerHTML = '';
    document.getElementById('nameSearchResults').innerHTML = '';
    
    // Clear auto-filled product fields (so form reflects "no reference selected")
    document.getElementById('product_name').value = '';
    document.getElementById('product_description').value = '';
    document.getElementById('product_brand').value = '';
    // SKU is readonly on edit - do not clear
    
    // Clear price and breakdown
    document.getElementById('product_price').value = '';
    document.getElementById('base_price_hidden').value = '';
    document.getElementById('final_price_hidden').value = '';
    document.getElementById('priceBreakdown').style.display = 'none';
    isPriceFromAmazon = false;
    
    // Restore default price help text
    const priceInput = document.getElementById('product_price');
    if (priceInput) {
        const helpEl = priceInput.closest('.col-md-6').querySelector('small.text-muted, small.text-success');
        if (helpEl) {
            helpEl.className = 'text-muted';
            helpEl.innerHTML = 'Your base selling price for this product. You can type directly or use buttons to adjust.';
        }
    }
    
    // Clear imported images
    document.getElementById('imported_image_urls').value = '';
    document.getElementById('importedImagesContainer').innerHTML = '';
    document.getElementById('importedImagesPreview').style.display = 'none';
    
    // Clear imported videos
    document.getElementById('product_video_url').value = '';
    document.getElementById('imported_video_urls').value = '';
    const videoContainer = document.getElementById('importedVideoContainer');
    if (videoContainer) videoContainer.innerHTML = '';
    document.getElementById('importedVideoPreview').style.display = 'none';
    
    // Remove product highlighting
    clearProductHighlights();
    
    if (typeof updateImagesRequiredIndicator === 'function') updateImagesRequiredIndicator();
}

// On edit page load: show Amazon reference block if product already has reference data
document.addEventListener('DOMContentLoaded', function() {
    const hasRefPrice = document.getElementById('amazon_reference_price_hidden').value;
    const hasRefUrl = document.getElementById('amazon_reference_url_hidden').value;
    if (hasRefPrice || hasRefUrl) {
        document.getElementById('amazonRefProductTitle').textContent = 'Amazon reference (saved)';
        document.getElementById('amazonRefPrice').textContent = hasRefPrice ? '₱' + parseFloat(hasRefPrice).toFixed(2) : '—';
        document.getElementById('amazonReferenceDisplay').style.display = 'block';
    }
});
</script>
@endpush
@endsection
