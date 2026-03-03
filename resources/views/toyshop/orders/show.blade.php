@extends('layouts.toyshop')

@section('title', 'Order Details - ToyHaven')

@push('styles')
<style>
    :root {
        --order-sky-blue: #0ea5e9;
        --order-sky-blue-light: #38bdf8;
        --order-sky-blue-gradient: linear-gradient(135deg, #0ea5e9 0%, #38bdf8 100%);
    }
    .order-detail-header {
        background: white;
        border-radius: 16px;
        padding: 1.5rem 2rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
    }
    
    .detail-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
    }
    
    .detail-card-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .detail-card-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--order-sky-blue-gradient);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .detail-card-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2d2a26;
        margin: 0;
    }
    
    .order-item-detail {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .order-item-detail:last-child {
        border-bottom: none;
    }
    
    .order-item-image {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 12px;
        border: 2px solid #e0f2fe;
    }
    
    .order-item-detail .text-primary {
        color: var(--order-sky-blue) !important;
    }
    
    .summary-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        position: sticky;
        top: 100px;
        border-top: 3px solid var(--order-sky-blue);
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .summary-total {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--order-sky-blue);
    }
    
    .info-row {
        display: flex;
        padding: 0.75rem 0;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: 600;
        color: #5c554d;
        width: 140px;
    }
    
    .info-value {
        color: #2d2a26;
        font-weight: 500;
    }
    
    .order-details-page .btn-outline-primary,
    .order-details-page .btn-primary {
        border-color: var(--order-sky-blue);
        color: var(--order-sky-blue);
    }
    .order-details-page .btn-primary {
        background: var(--order-sky-blue-gradient);
        border-color: var(--order-sky-blue);
        color: white;
    }
    .order-details-page .btn-outline-primary:hover,
    .order-details-page .btn-primary:hover {
        background: #0284c7;
        border-color: #0284c7;
        color: white;
    }
    
    .rate-item-row {
        flex-wrap: nowrap;
    }
    .rate-item-row .flex-grow-1 {
        min-width: 0;
        overflow: hidden;
    }
    .rate-item-row .text-truncate {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    @media (max-width: 576px) {
        .rate-item-row {
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .rate-item-row .flex-grow-1 {
            flex-basis: 100%;
        }
        .rate-item-row .flex-shrink-0 {
            margin-left: auto;
        }
    }
</style>
@endpush

@section('content')
<div class="container py-4 order-details-page">
    <div class="order-detail-header reveal">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="fw-bold mb-1">Order Details</h2>
                <p class="text-muted mb-0">Order #{{ $order->order_number }}</p>
            </div>
            <div>
                <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }} px-3 py-2" style="font-size: 0.875rem; font-weight: 600;">
                    {{ $order->getStatusLabel() }}
                </span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Order Items -->
            <div class="detail-card reveal">
                <div class="detail-card-header">
                    <div class="detail-card-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <h3 class="detail-card-title">Order Items</h3>
                </div>
                
                @foreach($order->items as $item)
                    <div class="order-item-detail">
                        @if($item->product && $item->product->images->first())
                            <img src="{{ asset('storage/' . $item->product->images->first()->image_path) }}" 
                                 class="order-item-image" 
                                 alt="{{ $item->product_name }}">
                        @else
                            <div class="order-item-image bg-light d-flex align-items-center justify-content-center">
                                <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                            </div>
                        @endif
                        <div class="flex-grow-1">
                            <h6 class="fw-semibold mb-1">{{ $item->product_name }}</h6>
                            <p class="text-muted mb-0">Quantity: {{ $item->quantity }} × ₱{{ number_format($item->price, 2) }}</p>
                        </div>
                        <div class="text-end">
                            <strong class="text-primary" style="font-size: 1.1rem;">₱{{ number_format($item->subtotal, 2) }}</strong>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Shipping Information -->
            <div class="detail-card reveal" style="animation-delay: 0.1s;">
                <div class="detail-card-header">
                    <div class="detail-card-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h3 class="detail-card-title">Shipping Information</h3>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Address:</div>
                    <div class="info-value">{{ $order->shipping_address }}</div>
                </div>
                @if($order->shipping_barangay ?? null)
                <div class="info-row">
                    <div class="info-label">Barangay:</div>
                    <div class="info-value">{{ $order->shipping_barangay }}</div>
                </div>
                @endif
                <div class="info-row">
                    <div class="info-label">City/Municipality:</div>
                    <div class="info-value">{{ $order->shipping_city }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Province:</div>
                    <div class="info-value">{{ $order->shipping_province }}</div>
                </div>
                @if($order->shipping_region ?? null)
                <div class="info-row">
                    <div class="info-label">Region:</div>
                    <div class="info-value">{{ $order->shipping_region }}</div>
                </div>
                @endif
                <div class="info-row">
                    <div class="info-label">Postal Code:</div>
                    <div class="info-value">{{ $order->shipping_postal_code }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Phone:</div>
                    <div class="info-value">{{ $order->shipping_phone }}</div>
                </div>
                @if($order->shipping_notes)
                    <div class="info-row">
                        <div class="info-label">Notes:</div>
                        <div class="info-value">{{ $order->shipping_notes }}</div>
                    </div>
                @endif
            </div>

            <!-- Receipt Download -->
            @if($order->hasReceipt())
            <div class="detail-card reveal" style="animation-delay: 0.15s;">
                <div class="detail-card-header">
                    <div class="detail-card-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <h3 class="detail-card-title">Receipt</h3>
                </div>
                <p class="text-muted mb-3">Download your official receipt for this order.</p>
                <a href="{{ route('orders.receipt', $order->id) }}" class="btn btn-outline-primary">
                    <i class="bi bi-download me-2"></i>Download Receipt PDF
                </a>
            </div>
            @endif

            <!-- Delivery Confirmation -->
            @if($order->isDelivered())
                @if($order->isDeliveryConfirmed())
                    <div class="detail-card reveal" style="animation-delay: 0.16s;">
                        <div class="detail-card-header">
                            <div class="detail-card-icon" style="background: #10b981;">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <h3 class="detail-card-title">Delivery Confirmed</h3>
                        </div>
                        <div class="alert alert-success mb-3">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            You confirmed delivery on {{ $order->deliveryConfirmation->confirmed_at->format('F d, Y') }}
                            @if($order->deliveryConfirmation->auto_confirmed)
                                <span class="badge bg-info ms-2">Auto-confirmed</span>
                            @endif
                        </div>

                        @if($order->canBeReviewed())
                        <div class="border-top pt-3">
                            <h6 class="fw-semibold mb-2"><i class="bi bi-star me-1"></i>Rate Your Purchase</h6>
                            <p class="text-muted small mb-3">Share your experience to help other customers.</p>
                            @foreach($order->items as $item)
                                @if($item->product)
                                    @php
                                        $hasReviewed = \App\Models\ProductReview::where('product_id', $item->product_id)->where('user_id', auth()->id())->exists();
                                    @endphp
                                    <div class="d-flex align-items-center justify-content-between gap-3 py-2 border-bottom rate-item-row">
                                        <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0 overflow-hidden">
                                            @if($item->product->images->first())
                                                <img src="{{ asset('storage/' . $item->product->images->first()->image_path) }}" alt="" class="rounded flex-shrink-0" style="width: 48px; height: 48px; object-fit: cover;">
                                            @endif
                                            <span class="text-truncate">{{ $item->product_name }}</span>
                                        </div>
                                        <div class="flex-shrink-0">
                                            @if($hasReviewed)
                                                <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Reviewed</span>
                                            @else
                                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#reviewModal" data-product-id="{{ $item->product_id }}" data-product-name="{{ $item->product_name }}" data-order-id="{{ $order->id }}">
                                                    <i class="bi bi-star me-1"></i>Rate
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        @endif
                    </div>
                @elseif(!$order->hasActiveDispute())
                    <div class="detail-card reveal" style="animation-delay: 0.16s;">
                        <div class="detail-card-header">
                            <div class="detail-card-icon" style="background: #f59e0b;">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                            <h3 class="detail-card-title">Action Required</h3>
                        </div>
                        <div class="alert alert-warning mb-3">
                            <strong>Please confirm delivery!</strong><br>
                            Upload a photo as proof that you received this order.
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('orders.confirm-delivery', $order->id) }}" class="btn btn-success flex-fill">
                                <i class="bi bi-camera me-2"></i>Confirm Delivery
                            </a>
                            <a href="{{ route('orders.report-issue', $order->id) }}" class="btn btn-danger flex-fill">
                                <i class="bi bi-flag me-2"></i>Report Issue
                            </a>
                        </div>
                    </div>
                @endif
            @endif

            <!-- Active Dispute -->
            @if($order->hasActiveDispute())
            <div class="detail-card reveal" style="animation-delay: 0.17s;">
                <div class="detail-card-header">
                    <div class="detail-card-icon" style="background: #ef4444;">
                        <i class="bi bi-exclamation-octagon"></i>
                    </div>
                    <h3 class="detail-card-title">Dispute Active</h3>
                </div>
                <div class="alert alert-danger mb-3">
                    <strong>You have reported an issue with this order.</strong><br>
                    Status: <span class="badge bg-danger">{{ $order->activeDispute->getStatusLabel() }}</span>
                    <br>
                    Type: {{ $order->activeDispute->getTypeLabel() }}
                </div>
                <a href="{{ route('disputes.show', $order->activeDispute->id) }}" class="btn btn-outline-danger">
                    <i class="bi bi-eye me-2"></i>View Dispute Details
                </a>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Order Summary -->
            <div class="summary-card reveal" style="animation-delay: 0.2s;">
                <h5 class="fw-bold mb-4"><i class="bi bi-receipt me-2"></i>Order Summary</h5>
                
                <div class="summary-row">
                    <span class="text-muted">Subtotal:</span>
                    <span class="fw-semibold">₱{{ number_format($order->total_amount, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span class="text-muted">Shipping:</span>
                    <span class="fw-semibold">₱{{ number_format($order->shipping_fee, 2) }}</span>
                </div>
                <hr class="my-3">
                <div class="summary-row">
                    <span class="fw-bold">Total:</span>
                    <span class="summary-total">₱{{ number_format($order->total, 2) }}</span>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Status:</span>
                            <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">
                                {{ $order->getStatusLabel() }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Payment:</span>
                            <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </div>
                    </div>

                    @if($order->tracking_number)
                        <div class="mb-3 p-3 bg-light rounded">
                            <div class="text-muted small mb-1">Tracking Number:</div>
                            <code class="fw-bold" style="font-size: 0.875rem;">{{ $order->tracking_number }}</code>
                        </div>
                    @endif

                    <a href="{{ route('orders.tracking', $order->id) }}" class="btn btn-primary w-100">
                        <i class="bi bi-truck me-2"></i>Track Order
                    </a>
                </div>
            </div>

            <!-- Seller Information -->
            <div class="detail-card reveal" style="animation-delay: 0.3s;">
                <div class="detail-card-header">
                    <div class="detail-card-icon">
                        <i class="bi bi-shop"></i>
                    </div>
                    <h3 class="detail-card-title">Seller</h3>
                </div>
                <h6 class="fw-bold mb-2">{{ $order->seller->business_name }}</h6>
                <p class="text-muted mb-0">
                    <i class="bi bi-geo-alt me-1"></i>{{ $order->seller->city }}, {{ $order->seller->province }}
                </p>
            </div>
        </div>
    </div>
</div>

@if($order->isDeliveryConfirmed() && $order->canBeReviewed())
<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewModalLabel"><i class="bi bi-star me-2"></i>Rate Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reviewForm">
                @csrf
                <input type="hidden" name="order_id" id="reviewOrderId" value="">
                <div class="modal-body">
                    <p class="text-muted small mb-3" id="reviewProductName"></p>
                    <div class="mb-3">
                        <label class="form-label">Rating <span class="text-danger">*</span></label>
                        <div class="d-flex gap-1" id="starRating">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" class="btn btn-outline-warning p-2 star-btn" data-rating="{{ $i }}" aria-label="{{ $i }} star">
                                    <i class="bi bi-star"></i>
                                </button>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" id="reviewRating" value="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Product Images (Optional, 1–10)</label>
                        <p class="small text-muted mb-2">Upload photos of the product (max 2MB each)</p>
                        <input type="file" id="reviewFileInput" accept="image/jpeg,image/png,image/jpg" multiple class="d-none">
                        <div id="reviewUploadArea" class="review-upload-area" onclick="document.getElementById('reviewFileInput').click()">
                            <i class="bi bi-cloud-arrow-up text-muted"></i>
                            <span class="small text-muted ms-2">Click or drag to add photos</span>
                        </div>
                        <div id="reviewPreviewGrid" class="review-preview-grid"></div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Review (Optional)</label>
                        <textarea name="review_text" class="form-control" rows="3" placeholder="Share your experience with this product..." maxlength="1000"></textarea>
                        <small class="text-muted">Max 1000 characters</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="reviewSubmitBtn"><i class="bi bi-send me-1"></i>Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="reviewFullscreen" class="review-fullscreen" onclick="if(event.target===this) window.closeReviewFullscreen()">
    <button type="button" class="btn-close-fs" onclick="window.closeReviewFullscreen()">&times;</button>
    <img id="reviewFullscreenImg" src="" alt="">
</div>

@push('styles')
<style>
.review-upload-area { border: 2px dashed #dee2e6; border-radius: 10px; padding: 1rem; text-align: center; cursor: pointer; transition: border-color 0.2s, background 0.2s; }
.review-upload-area:hover, .review-upload-area.dragover { border-color: #0ea5e9; background: #f0f9ff; }
.review-preview-grid { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 0.75rem; }
.review-preview-item { position: relative; width: 80px; height: 80px; border-radius: 8px; overflow: hidden; border: 1px solid #dee2e6; flex-shrink: 0; }
.review-preview-item img { width: 100%; height: 100%; object-fit: cover; cursor: pointer; }
.review-preview-item .btn-del { position: absolute; top: 2px; right: 2px; width: 24px; height: 24px; padding: 0; border-radius: 50%; background: rgba(220,53,69,0.9); color: white; border: none; cursor: pointer; font-size: 0.75rem; display: flex; align-items: center; justify-content: center; }
.review-preview-item .btn-view { position: absolute; bottom: 2px; left: 2px; width: 24px; height: 24px; padding: 0; border-radius: 50%; background: rgba(0,0,0,0.6); color: white; border: none; cursor: pointer; font-size: 0.65rem; display: flex; align-items: center; justify-content: center; }
.review-fullscreen { position: fixed; inset: 0; background: rgba(0,0,0,0.9); z-index: 9999; display: none; align-items: center; justify-content: center; padding: 2rem; }
.review-fullscreen.active { display: flex; }
.review-fullscreen img { max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 8px; }
.review-fullscreen .btn-close-fs { position: absolute; top: 1rem; right: 1rem; background: rgba(255,255,255,0.2); color: white; border: none; width: 40px; height: 40px; border-radius: 50%; font-size: 1.5rem; cursor: pointer; }
</style>
@endpush

@push('scripts')
<script>
(function() {
    var reviewFiles = [];
    var MAX_FILES = 10;
    var MAX_SIZE = 2 * 1024 * 1024;
    var modal = document.getElementById('reviewModal');
    if (!modal) return;

    function renderReviewPreviews() {
        var grid = document.getElementById('reviewPreviewGrid');
        var area = document.getElementById('reviewUploadArea');
        if (!grid) return;
        grid.innerHTML = '';
        reviewFiles.forEach(function(f, i) {
            var reader = new FileReader();
            reader.onload = (function(idx, src) {
                return function(e) {
                    var s = e.target.result;
                    var div = document.createElement('div');
                    div.className = 'review-preview-item';
                    div.innerHTML = '<img src="' + s + '" alt="Preview">' +
                        '<button type="button" class="btn-del" data-idx="' + idx + '" aria-label="Delete"><i class="bi bi-trash"></i></button>' +
                        '<button type="button" class="btn-view" data-src="' + s.replace(/"/g, '&quot;') + '" aria-label="View"><i class="bi bi-zoom-in"></i></button>';
                    grid.appendChild(div);
                    div.querySelector('.btn-del').onclick = function() {
                        reviewFiles.splice(parseInt(this.getAttribute('data-idx')), 1);
                        renderReviewPreviews();
                        if (reviewFiles.length < MAX_FILES) area.style.display = '';
                    };
                    div.querySelector('.btn-view').onclick = function() {
                        document.getElementById('reviewFullscreenImg').src = s;
                        document.getElementById('reviewFullscreen').classList.add('active');
                        document.body.style.overflow = 'hidden';
                    };
                    div.querySelector('img').onclick = function() {
                        document.getElementById('reviewFullscreenImg').src = s;
                        document.getElementById('reviewFullscreen').classList.add('active');
                        document.body.style.overflow = 'hidden';
                    };
                };
            })(i, '');
            reader.readAsDataURL(f);
        });
        if (reviewFiles.length >= MAX_FILES && area) area.style.display = 'none';
    }

    window.closeReviewFullscreen = function() {
        document.getElementById('reviewFullscreen').classList.remove('active');
        document.body.style.overflow = '';
    };

    var reviewInput = document.getElementById('reviewFileInput');
    var reviewArea = document.getElementById('reviewUploadArea');
    if (reviewInput && reviewArea) {
        reviewInput.onchange = function() {
            var added = 0;
            for (var i = 0; i < this.files.length && reviewFiles.length < MAX_FILES; i++) {
                var f = this.files[i];
                if (!f.type.match(/^image\/(jpeg|png|jpg)$/) || f.size > MAX_SIZE) continue;
                reviewFiles.push(f);
                added++;
            }
            renderReviewPreviews();
            this.value = '';
        };
        reviewArea.ondragover = function(e) { e.preventDefault(); reviewArea.classList.add('dragover'); };
        reviewArea.ondragleave = function() { reviewArea.classList.remove('dragover'); };
        reviewArea.ondrop = function(e) {
            e.preventDefault();
            reviewArea.classList.remove('dragover');
            var files = e.dataTransfer.files;
            for (var i = 0; i < files.length && reviewFiles.length < MAX_FILES; i++) {
                var f = files[i];
                if (!f.type.match(/^image\/(jpeg|png|jpg)$/) || f.size > MAX_SIZE) continue;
                reviewFiles.push(f);
            }
            renderReviewPreviews();
        };
    }

    modal.addEventListener('show.bs.modal', function(e) {
        var btn = e.relatedTarget;
        if (!btn) return;
        var productId = btn.getAttribute('data-product-id');
        var productName = btn.getAttribute('data-product-name');
        var orderId = btn.getAttribute('data-order-id');
        document.getElementById('reviewForm').dataset.productId = productId;
        document.getElementById('reviewOrderId').value = orderId;
        document.getElementById('reviewProductName').textContent = productName;
        document.getElementById('reviewRating').value = '0';
        reviewFiles = [];
        renderReviewPreviews();
        if (reviewArea) reviewArea.style.display = '';
        document.querySelectorAll('.star-btn').forEach(function(b) {
            b.querySelector('i').className = 'bi bi-star';
            b.classList.remove('active');
        });
    });

    document.querySelectorAll('.star-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var r = parseInt(this.getAttribute('data-rating'));
            document.getElementById('reviewRating').value = r;
            document.querySelectorAll('.star-btn').forEach(function(b, i) {
                var icon = b.querySelector('i');
                icon.className = (i + 1) <= r ? 'bi bi-star-fill' : 'bi bi-star';
            });
        });
    });

    document.getElementById('reviewForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var r = parseInt(document.getElementById('reviewRating').value || 0);
        if (r < 1 || r > 5) {
            alert('Please select a rating (1-5 stars).');
            return;
        }
        var productId = this.dataset.productId;
        if (!productId) return;
        var fd = new FormData();
        fd.append('_token', document.querySelector('input[name="_token"]').value);
        fd.append('order_id', document.getElementById('reviewOrderId').value);
        fd.append('rating', r);
        fd.append('review_text', this.querySelector('textarea[name="review_text"]').value);
        for (var i = 0; i < reviewFiles.length; i++) fd.append('review_images[]', reviewFiles[i]);
        var btn = document.getElementById('reviewSubmitBtn');
        btn.disabled = true;
        var url = '{{ url("reviews/product") }}/' + productId;
        fetch(url, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(function(res) {
            if (res.redirected) {
                window.location.href = res.url;
                return;
            }
            if (!res.ok) return res.json();
        }).then(function(data) {
            if (data && data.errors) {
                btn.disabled = false;
                var msg = (data.errors.rating || data.errors['review_images'] || ['Please check your input.'])[0];
                alert(msg);
            }
        }).catch(function() { btn.disabled = false; });
    });

    var params = new URLSearchParams(window.location.search);
    var rateProductId = params.get('rate');
    if (rateProductId) {
        var rateBtn = document.querySelector('button[data-bs-target="#reviewModal"][data-product-id="' + rateProductId + '"]');
        if (rateBtn) {
            setTimeout(function() { rateBtn.click(); }, 300);
            history.replaceState(null, '', window.location.pathname);
        }
    }
})();
</script>
@endpush
@endif
@endsection
