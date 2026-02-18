@extends('layouts.toyshop')

@section('title', $listing->title . ' - ToyHaven Trading')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<style>
    .trading-listing-page { max-width: 1440px; margin-left: auto; margin-right: auto; padding-left: 1rem; padding-right: 1rem; }
    @media (min-width: 576px) { .trading-listing-page { padding-left: 1.5rem; padding-right: 1.5rem; } }
    @media (min-width: 992px) { .trading-listing-page { padding-left: 2rem; padding-right: 2rem; } }
    
    .product-image-gallery {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
        border: 1px solid rgba(240,230,220,0.8);
        position: relative;
    }
    
    .trade-type-badge {
        position: absolute;
        top: 1rem;
        left: 1rem;
        z-index: 2;
        padding: 0.35rem 1rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        background: linear-gradient(135deg, #0891b2, #ff8e53);
        color: white;
        box-shadow: 0 2px 8px rgba(255,107,107,0.35);
    }
    
    .main-image {
        width: 100%;
        min-height: 420px;
        height: clamp(420px, 55vh, 600px);
        object-fit: cover;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    }
    
    .thumbnail-images {
        display: flex;
        gap: 0.5rem;
        padding: 1rem 1.25rem;
        overflow-x: auto;
        background: #fafbfc;
        border-top: 1px solid #e2e8f0;
        scrollbar-width: thin;
    }
    
    .thumbnail-images::-webkit-scrollbar { height: 6px; }
    .thumbnail-images::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    
    .thumbnail {
        width: 72px;
        height: 72px;
        min-width: 72px;
        object-fit: cover;
        border-radius: 10px;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s ease;
        background: #fff;
    }
    
    .thumbnail:hover, .thumbnail.active {
        border-color: #0891b2;
        box-shadow: 0 0 0 2px rgba(255,107,107,0.25);
    }
    
    .product-info-card {
        background: white;
        border-radius: 16px;
        padding: 1.75rem 2rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
        border: 1px solid rgba(240,230,220,0.8);
        position: sticky;
        top: 100px;
    }
    
    .product-title-large {
        font-size: 1.625rem;
        font-weight: 800;
        color: #1e1b18;
        margin-bottom: 1rem;
        letter-spacing: -0.025em;
        line-height: 1.35;
    }
    
    .product-price-large {
        font-size: 1.875rem;
        font-weight: 800;
        color: #0891b2;
        margin-bottom: 1.25rem;
        letter-spacing: -0.02em;
    }
    
    .product-price-large .currency { font-size: 1rem; font-weight: 600; opacity: 0.9; }
    
    .seller-info {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.25rem;
        background: linear-gradient(135deg, #fafbfc 0%, #f8fafc 100%);
        border-radius: 12px;
        margin-bottom: 1.5rem;
        border: 1px solid #e2e8f0;
    }
    
    .seller-avatar {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0891b2, #ff8e53);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    
    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .btn-action {
        border-radius: 12px;
        font-weight: 600;
        padding: 0.875rem 1.5rem;
        font-size: 1rem;
        transition: all 0.2s ease;
    }
    
    .btn-message {
        background: linear-gradient(135deg, #0891b2, #ff8e53);
        border: none;
        color: white;
    }
    
    .btn-message:hover {
        background: linear-gradient(135deg, #0e7490, #06b6d4);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(8,145,178,0.35);
    }
    
    .btn-offer {
        background: #0f172a;
        border: none;
        color: white;
    }
    
    .btn-offer:hover {
        background: #1e293b;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(15,23,42,0.25);
    }
    
    .product-details-card {
        background: white;
        border-radius: 16px;
        padding: 2rem 2.25rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
        border: 1px solid rgba(240,230,220,0.8);
        margin-bottom: 1.5rem;
    }
    
    .product-details-card h4 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e1b18;
        margin-bottom: 1rem;
        letter-spacing: -0.02em;
    }
    
    .product-details-card h5 {
        font-size: 1rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: 0.75rem;
        letter-spacing: -0.01em;
    }
    
    .detail-row {
        display: flex;
        padding: 0.875rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .detail-row:last-child {
        border-bottom: none;
    }
    
    .detail-label {
        font-weight: 600;
        color: #64748b;
        width: 160px;
        min-width: 140px;
        font-size: 0.875rem;
    }
    
    .detail-value {
        color: #1e1b18;
        flex: 1;
        font-size: 0.9375rem;
        font-weight: 500;
    }
    
    .offers-section {
        background: white;
        border-radius: 16px;
        padding: 1.75rem 2rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
        border: 1px solid rgba(240,230,220,0.8);
    }
    
    .offers-section h5 {
        font-size: 1.125rem;
        font-weight: 700;
        color: #1e1b18;
        margin-bottom: 1.25rem;
    }
    
    .offer-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 0.75rem;
        background: linear-gradient(135deg, #fafbfc 0%, #f8fafc 100%);
        transition: border-color 0.2s ease;
    }
    
    .offer-card:hover { border-color: #e2e8f0; }
    .offer-card:last-child { margin-bottom: 0; }
    
    .stats-row {
        display: flex;
        gap: 1rem;
        padding: 1.25rem 0;
        border-top: 1px solid #f1f5f9;
        margin-top: 1rem;
    }
    
    .stat-item {
        flex: 1;
        text-align: center;
        padding: 0.5rem;
    }
    
    .stat-item .stat-value {
        font-size: 1.25rem;
        font-weight: 800;
        color: #1e1b18;
    }
    
    .stat-item .stat-label {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    
    .breadcrumb-trading {
        background: transparent;
        padding: 0.75rem 0;
        font-size: 0.875rem;
    }
    
    .breadcrumb-trading .breadcrumb-item a {
        color: #64748b;
        text-decoration: none;
        font-weight: 500;
    }
    
    .breadcrumb-trading .breadcrumb-item a:hover {
        color: #0891b2;
    }
    
    .breadcrumb-trading .breadcrumb-item.active { color: #475569; font-weight: 600; }

    #listing-show-map {
        height: 300px;
        width: 100%;
        border-radius: 12px;
        overflow: hidden;
    }
    
    .suggested-listing-card {
        background: white;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
        transition: all 0.25s ease;
        height: 100%;
    }
    
    .suggested-listing-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        transform: translateY(-4px);
        border-color: #0891b2;
    }
    
    .make-offer-section {
        background: linear-gradient(135deg, #fafbfc 0%, #f8fafc 100%);
        border-radius: 16px;
        padding: 2rem;
        border: 1px dashed #e2e8f0;
    }
    
    .form-control, .form-select {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #0891b2;
        box-shadow: 0 0 0 3px rgba(255,107,107,0.15);
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
<div class="trading-listing-page">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb breadcrumb-trading mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trading.index') }}">Trading</a></li>
            <li class="breadcrumb-item active">{{ Str::limit($listing->title, 40) }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Product Images -->
        <div class="col-xl-8 col-lg-7 mb-lg-0 mb-4">
            <div class="product-image-gallery">
                <span class="trade-type-badge">{{ ucfirst(str_replace('_', ' ', $listing->trade_type ?? 'Trade')) }}</span>
                @php
                    $listingImages = $listing->images;
                    $item = $listing->getItem();
                    $productImages = $item ? $item->images : collect();
                    $useListingImages = $listingImages->isNotEmpty();
                    $mainSrc = $useListingImages
                        ? asset('storage/' . $listingImages->first()->image_path)
                        : ($listing->image_path ? asset('storage/' . $listing->image_path) : ($productImages->count() > 0 ? asset('storage/' . $productImages->first()->image_path) : null));
                @endphp
                @if($mainSrc)
                <img src="{{ $mainSrc }}" alt="{{ $listing->title }}" class="main-image" id="mainImage">
                @if($useListingImages && $listingImages->count() > 1)
                <div class="thumbnail-images">
                    @foreach($listingImages as $image)
                    <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $listing->title }}" class="thumbnail {{ $loop->first ? 'active' : '' }}" onclick="changeMainImage('{{ asset('storage/' . $image->image_path) }}', this)">
                    @endforeach
                </div>
                @elseif(!$useListingImages && $listing->image_path && $productImages->count() > 0)
                <div class="thumbnail-images">
                    <img src="{{ asset('storage/' . $listing->image_path) }}" alt="{{ $listing->title }}" class="thumbnail active" onclick="changeMainImage('{{ asset('storage/' . $listing->image_path) }}', this)">
                    @foreach($productImages as $image)
                    <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $listing->title }}" class="thumbnail" onclick="changeMainImage('{{ asset('storage/' . $image->image_path) }}', this)">
                    @endforeach
                </div>
                @elseif(!$useListingImages && $productImages->count() > 1)
                <div class="thumbnail-images">
                    @foreach($productImages as $image)
                    <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $listing->title }}" class="thumbnail {{ $loop->first ? 'active' : '' }}" onclick="changeMainImage('{{ asset('storage/' . $image->image_path) }}', this)">
                    @endforeach
                </div>
                @endif
                @else
                <div class="d-flex align-items-center justify-content-center" style="min-height: 420px;">
                    <i class="bi bi-image text-muted" style="font-size: 5rem;"></i>
                </div>
                @endif
            </div>
        </div>

        <!-- Product Info & Actions -->
        <div class="col-xl-4 col-lg-5 mb-lg-0 mb-4">
            <div class="product-info-card">
                @if($listing->status === 'pending_approval')
                    <div class="alert alert-warning py-2 mb-3 mb-md-3" role="alert">
                        <i class="bi bi-hourglass-split me-2"></i><strong>Pending approval.</strong> Your listing is under review. You can still view and edit it until an admin approves it.
                    </div>
                @endif
                <h1 class="product-title-large">{{ $listing->title }}</h1>

                <div class="mb-3">
                    <span class="badge rounded-pill px-3 py-2" style="font-size: 0.8rem; font-weight: 600; background: linear-gradient(135deg, #0891b2, #ff8e53); color: white;">
                        <i class="bi bi-arrow-left-right me-1"></i>{{ ucfirst(str_replace('_', ' ', $listing->trade_type ?? 'Trade')) }}
                    </span>
                </div>
                
                @if($item)
                <div class="product-price-large">
                    @if($item instanceof \App\Models\Product)
                        <span class="currency">₱</span>{{ number_format($item->price, 2) }}
                    @elseif($item instanceof \App\Models\UserProduct && $item->estimated_value)
                        <span class="currency">₱</span>{{ number_format($item->estimated_value, 2) }}
                    @else
                        Trade Only
                    @endif
                </div>
                @elseif($listing->cash_difference)
                <div class="product-price-large"><span class="currency">₱</span>{{ number_format($listing->cash_difference, 2) }}</div>
                @endif

                <div class="seller-info">
                    <div class="seller-avatar">
                        {{ strtoupper(substr($listing->user->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-bold">{{ $listing->user->name }}</div>
                        <small class="text-muted">
                            <i class="bi bi-geo-alt me-1"></i>{{ $listing->user->city ?? 'Philippines' }}
                        </small>
                    </div>
                </div>

                <div class="action-buttons">
                    @auth
                        @if($listing->user_id !== Auth::id())
                            <a href="#make-offer" class="btn btn-action btn-offer">
                                <i class="bi bi-hand-thumbs-up me-2"></i>Make an Offer
                            </a>
                            <a href="{{ route('trading.conversations.store-from-listing', $listing->id) }}" class="btn btn-action btn-message">
                                <i class="bi bi-chat-dots me-2"></i>Message Seller
                            </a>
                        @else
                            <a href="{{ route('trading.listings.edit', $listing->id) }}" class="btn btn-action btn-outline-primary">
                                <i class="bi bi-pencil me-2"></i>Edit Listing
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-action btn-message">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login to Trade
                        </a>
                    @endauth
                </div>

                <div class="stats-row">
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($listing->views_count) }}</div>
                        <div class="stat-label">Views</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($listing->offers_count) }}</div>
                        <div class="stat-label">Offers</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $listing->created_at->diffForHumans() }}</div>
                        <div class="stat-label">Listed</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Details -->
    <div class="row g-4">
        <div class="col-xl-8 col-lg-7">
            <div class="product-details-card">
                <h4 class="mb-3">Description</h4>
                <p class="text-muted">{{ $listing->description }}</p>

                <h5 class="mt-4 mb-3">Listing Details</h5>
                @if($listing->condition)
                <div class="detail-row">
                    <div class="detail-label">Condition</div>
                    <div class="detail-value">{{ ucfirst($listing->condition) }}</div>
                </div>
                @endif
                @if($listing->brand)
                <div class="detail-row">
                    <div class="detail-label">Brand</div>
                    <div class="detail-value">{{ $listing->brand }}</div>
                </div>
                @endif
                @if($listing->location || $listing->meet_up_references || ($listing->location_lat && $listing->location_lng))
                <h5 class="mt-4 mb-3">Preferred meet-up location</h5>
                @if($listing->location)
                <div class="detail-row">
                    <div class="detail-label">Address</div>
                    <div class="detail-value">{{ $listing->location }}</div>
                </div>
                @endif
                @if($listing->meet_up_references)
                <div class="detail-row">
                    <div class="detail-label">References</div>
                    <div class="detail-value">{{ $listing->meet_up_references }}</div>
                </div>
                @endif
                @if($listing->location || ($listing->location_lat && $listing->location_lng))
                <div class="mt-3">
                    <div class="detail-label mb-2"><i class="bi bi-map me-1"></i>Map</div>
                    <div id="listing-show-map" class="border rounded-bottom"></div>
                    <small class="text-muted d-block mt-2"><i class="bi bi-geo-alt me-1"></i>Meet-up spot</small>
                </div>
                @endif
                @endif

                @if($item)
                <h5 class="mt-4 mb-3">Product Details</h5>
                <div class="detail-row">
                    <div class="detail-label">Name</div>
                    <div class="detail-value">{{ $item->name }}</div>
                </div>
                @if($item instanceof \App\Models\Product)
                <div class="detail-row">
                    <div class="detail-label">Price</div>
                    <div class="detail-value">₱{{ number_format($item->price, 2) }}</div>
                </div>
                @elseif($item instanceof \App\Models\UserProduct && $item->estimated_value)
                <div class="detail-row">
                    <div class="detail-label">Estimated Value</div>
                    <div class="detail-value">₱{{ number_format($item->estimated_value, 2) }}</div>
                </div>
                @endif
                @endif

                @if($listing->cash_difference)
                <div class="alert alert-info mt-3">
                    <strong>Cash Difference / Asking:</strong> ₱{{ number_format($listing->cash_difference, 2) }}
                </div>
                @endif
            </div>
        </div>

        <!-- Active Offers -->
        <div class="col-xl-4 col-lg-5">
            @if($listing->activeOffers->count() > 0)
            <div class="offers-section">
                <h5 class="mb-3">Active Offers ({{ $listing->activeOffers->count() }})</h5>
                @foreach($listing->activeOffers as $offer)
                <div class="offer-card">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <strong>{{ $offer->offerer->name }}</strong>
                            @if($offer->getOfferedItem())
                            <div class="text-muted small">{{ $offer->getOfferedItem()->name }}</div>
                            @endif
                            @if($offer->cash_amount)
                            <div class="text-success small">+ ₱{{ number_format($offer->cash_amount, 2) }}</div>
                            @endif
                        </div>
                        @if(Auth::id() === $listing->user_id)
                        <div class="btn-group btn-group-sm">
                            <form method="POST" action="{{ route('trading.offers.accept', $offer->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">Accept</button>
                            </form>
                            <form method="POST" action="{{ route('trading.offers.reject', $offer->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        </div>
                        @endif
                    </div>
                    @if($offer->message)
                    <p class="small text-muted mb-0">{{ $offer->message }}</p>
                    @endif
                    <small class="text-muted">{{ $offer->created_at->diffForHumans() }}</small>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    @if(isset($suggestedListings) && $suggestedListings->isNotEmpty())
    <div class="row mt-5 pt-4">
        <div class="col-12">
            <h5 class="fw-bold mb-4" style="font-size: 1.125rem; color: #1e1b18;">
                <i class="bi bi-arrow-repeat me-2 text-primary"></i>Listings you might match
            </h5>
            <div class="row g-3">
                @foreach($suggestedListings as $suggested)
                <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                    <a href="{{ route('trading.listings.show', $suggested->id) }}" class="text-decoration-none">
                        <div class="suggested-listing-card">
                            <div style="height: 180px; overflow: hidden; background: #f8fafc;">
                                @php
                                    $sItem = $suggested->getItem();
                                    $sImg = $suggested->images->first() ?? ($sItem && $sItem->images->isNotEmpty() ? $sItem->images->first() : null);
                                    if (!$sImg && $suggested->image_path) {
                                        $sImg = (object)['image_path' => $suggested->image_path];
                                    }
                                @endphp
                                @if($sImg)
                                <img src="{{ asset('storage/' . $sImg->image_path) }}" alt="{{ $suggested->title }}" style="width:100%;height:100%;object-fit:cover;">
                                @else
                                <div class="d-flex align-items-center justify-content-center h-100">
                                    <i class="bi bi-image text-muted" style="font-size: 2.5rem;"></i>
                                </div>
                                @endif
                            </div>
                            <div class="p-3">
                                <div class="fw-semibold small text-dark" style="line-height: 1.4; min-height: 2.8em; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ Str::limit($suggested->title, 45) }}</div>
                                <small class="text-muted">{{ $suggested->user->name ?? '' }}</small>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Make Offer Form -->
    @auth
    @if($listing->user_id !== Auth::id() && $listing->canAcceptOffers())
    <div id="make-offer" class="row mt-5 pt-2">
        <div class="col-xl-8 col-lg-7">
            <div class="make-offer-section">
                <h4 class="mb-4" style="font-size: 1.25rem; font-weight: 700; color: #1e1b18;">
                    <i class="bi bi-hand-thumbs-up me-2 text-primary"></i>Make an Offer
                </h4>
                <form method="POST" action="{{ route('trading.offers.store', $listing->id) }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Your Product</label>
                            <select name="product_type" class="form-select form-select-lg" required>
                                <option value="">Select Product Type</option>
                                <option value="user_product">My Personal Product</option>
                                @if(Auth::user()->isSeller())
                                <option value="seller_product">Business Product</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Cash Amount (Optional)</label>
                            <input type="number" name="cash_amount" step="0.01" min="0" class="form-control form-control-lg" placeholder="0.00">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Message (Optional)</label>
                            <textarea name="message" rows="3" class="form-control" placeholder="Add a message to your offer..."></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-offer btn-action">
                                <i class="bi bi-send me-2"></i>Submit Offer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    @endauth
</div>
</div>

<script>
function changeMainImage(src, element) {
    document.getElementById('mainImage').src = src;
    document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
    element.classList.add('active');
}
</script>
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
(function() {
    var mapEl = document.getElementById('listing-show-map');
    if (!mapEl) return;
    var hasCoords = {{ ($listing->location_lat !== null && $listing->location_lng !== null) ? 'true' : 'false' }};
    var lat = {{ $listing->location_lat ?? 'null' }};
    var lng = {{ $listing->location_lng ?? 'null' }};
    var locationText = @json($listing->location ?? '');
    var defaultCenter = [14.5995, 120.9842];

    var map = L.map('listing-show-map').setView(defaultCenter, 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    function pinAndCenter(latVal, lngVal) {
        map.setView([latVal, lngVal], 14);
        L.marker([latVal, lngVal]).addTo(map);
    }

    if (hasCoords && lat != null && lng != null) {
        pinAndCenter(parseFloat(lat), parseFloat(lng));
    } else if (locationText) {
        fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(locationText) + '&limit=1', {
            headers: { 'Accept': 'application/json', 'User-Agent': 'ToyHavenPlatform/1.0' }
        }).then(function(r) { return r.json(); })
        .then(function(data) {
            if (data && data[0]) {
                var latVal = parseFloat(data[0].lat);
                var lonVal = parseFloat(data[0].lon);
                pinAndCenter(latVal, lonVal);
            } else {
                L.marker(defaultCenter).addTo(map);
            }
        }).catch(function() {
            L.marker(defaultCenter).addTo(map);
        });
    } else {
        L.marker(defaultCenter).addTo(map);
    }
})();
</script>
@endpush
@endsection
