@extends('layouts.toyshop')

@section('title', 'Shopping Cart - ToyHaven')

@push('styles')
<style>
    .cart-header {
        background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
        border-radius: 16px;
        padding: 1.5rem 2rem;
        margin-bottom: 2rem;
        color: white;
        box-shadow: 0 4px 20px rgba(8, 145, 178, 0.25);
    }
    .cart-header h2 { color: white; margin: 0; font-weight: 700; }

    .cart-item-card {
        background: white;
        border-radius: 14px;
        padding: 1.5rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        margin-bottom: 1.5rem;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .cart-item-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        border-color: #cbd5e1;
    }

    .cart-item-image {
        width: 110px;
        height: 110px;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }

    .cart-item-title {
        font-size: 1.05rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }

    .cart-item-price {
        font-size: 1.2rem;
        font-weight: 700;
        color: #0891b2;
    }

    .quantity-control {
        display: flex;
        align-items: center;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        width: fit-content;
    }

    .quantity-btn {
        background: #f8fafc;
        border: none;
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        transition: all 0.2s ease;
        color: #475569;
    }

    .quantity-btn:hover:not(:disabled) {
        background: #e2e8f0;
        color: #0891b2;
    }
    
    .quantity-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .quantity-input {
        border: none;
        width: 50px;
        text-align: center;
        font-weight: 600;
        padding: 0.5rem;
        font-size: 0.9375rem;
    }

    .summary-card {
        background: white;
        border-radius: 16px;
        padding: 1.75rem 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        position: sticky;
        top: 100px;
        border: 1px solid #e2e8f0;
    }

    .summary-card h5 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 0.6rem 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9375rem;
    }

    .summary-row:last-of-type { border-bottom: none; }

    .summary-total {
        font-size: 1.35rem;
        font-weight: 700;
        color: #0891b2;
    }

    .empty-cart {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
    }

    .empty-cart-icon {
        font-size: 4rem;
        color: #cbd5e1;
        margin-bottom: 1.25rem;
    }

    .empty-cart h4 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
    }

    .btn-primary {
        background: linear-gradient(135deg, #0891b2, #06b6d4);
        border: none;
        font-weight: 600;
        border-radius: 10px;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #0e7490, #0891b2);
        border: none;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="cart-header reveal">
        <h2 class="fw-bold mb-0"><i class="bi bi-cart3 me-2"></i>Shopping Cart</h2>
    </div>

    @if(isset($message) && $message)
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($cartItems->count() > 0)
        <form id="cart-checkout-form" action="{{ route('checkout.index') }}" method="GET">
        <div class="row">
            <div class="col-lg-8">
                @foreach($cartItems as $item)
                    @php
                        $adj = $item->variation ? (float)$item->variation->price_adjustment : 0;
                        $unitPrice = $item->product->price + $adj;
                        $lineTotal = $unitPrice * $item->quantity;
                    @endphp
                    <div class="cart-item-card reveal" style="animation-delay: {{ min($loop->index, 3) * 0.1 }}s;" data-item-id="{{ $item->id }}" data-line-total="{{ $lineTotal }}">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="form-check mt-2">
                                <input class="form-check-input cart-item-select" type="checkbox" name="cart_items[]" value="{{ $item->id }}" id="cart_item_{{ $item->id }}" checked>
                                <label class="form-check-label visually-hidden" for="cart_item_{{ $item->id }}">Select for checkout</label>
                            </div>
                            @if($item->product->images->first())
                                <img src="{{ asset('storage/' . $item->product->images->first()->image_path) }}" 
                                     class="cart-item-image" 
                                     alt="{{ $item->product->name }}">
                            @else
                                <div class="cart-item-image bg-light d-flex align-items-center justify-content-center">
                                    <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                                </div>
                            @endif
                            
                            <div class="flex-grow-1">
                                <h5 class="cart-item-title">
                                    <a href="{{ route('toyshop.products.show', $item->product->slug) }}" class="text-decoration-none text-dark">
                                        {{ $item->product->name }}
                                    </a>
                                </h5>
                                @if($item->variation)
                                    <p class="text-muted mb-1 small">
                                        <i class="bi bi-tag me-1"></i>{{ $item->variation->variation_type }}: {{ $item->variation->variation_value }}
                                    </p>
                                @endif
                                @php
                                    $unitPrice = $item->product->price;
                                    if ($item->variation) {
                                        $unitPrice = $unitPrice + (float) $item->variation->price_adjustment;
                                    }
                                    $maxQty = $item->variation ? $item->variation->stock_quantity : $item->product->stock_quantity;
                                @endphp
                                <p class="text-muted mb-2">₱{{ number_format($unitPrice, 2) }} each</p>
                                
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mt-3">
                                    <div class="d-flex flex-column gap-1">
                                        <div class="quantity-control" data-item-id="{{ $item->id }}" data-unit-price="{{ $unitPrice }}" data-max-qty="{{ $maxQty }}">
                                            <button type="button" class="quantity-btn decrease-btn" data-item-id="{{ $item->id }}" {{ $item->quantity <= 1 ? 'disabled' : '' }}>
                                                <i class="bi bi-dash"></i>
                                            </button>
                                            <input type="number" 
                                                   class="quantity-input" 
                                                   data-item-id="{{ $item->id }}"
                                                   data-update-url="{{ route('cart.update', $item->id) }}"
                                                   value="{{ $item->quantity }}" 
                                                   min="1" 
                                                   max="{{ $maxQty }}">
                                            <button type="button" class="quantity-btn increase-btn" data-item-id="{{ $item->id }}" {{ $item->quantity >= $maxQty ? 'disabled' : '' }}>
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            <i class="bi bi-box-seam me-1"></i><span class="stock-available" data-item-id="{{ $item->id }}">{{ $maxQty }}</span> available
                                        </small>
                                    </div>
                                    
                                    <div class="text-end">
                                        <div class="cart-item-price item-total" data-item-id="{{ $item->id }}">₱{{ number_format($unitPrice * $item->quantity, 2) }}</div>
                                        <small class="text-muted d-block item-calculation" data-item-id="{{ $item->id }}" style="font-size: 0.75rem;">
                                            ₱{{ number_format($unitPrice, 2) }} × <span class="qty-display">{{ $item->quantity }}</span>
                                        </small>
                                    </div>
                                    
                                    <form action="{{ route('cart.remove', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this item from cart?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-trash me-1"></i>Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="col-lg-4">
                <div class="summary-card reveal" style="animation-delay: 0.2s;">
                    <h5 class="fw-bold mb-4"><i class="bi bi-receipt me-2"></i>Order Summary</h5>
                    <p class="text-muted small mb-3"><i class="bi bi-info-circle me-1"></i>Select items above to include in checkout</p>
                    <div class="summary-row">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-semibold" id="summary-subtotal">₱{{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="text-muted">VAT ({{ $taxRate ?? 12 }}%):</span>
                        <span class="fw-semibold" id="summary-vat">₱{{ number_format($vat ?? 0, 2) }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="text-muted">Shipping:</span>
                        <span class="fw-semibold">₱0.00</span>
                    </div>
                    <hr class="my-3">
                    <div class="summary-row">
                        <span class="fw-bold">Total:</span>
                        <span class="summary-total" id="summary-total">₱{{ number_format($totalWithVat ?? $subtotal, 2) }}</span>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" form="cart-checkout-form" class="btn btn-primary btn-lg">
                            <i class="bi bi-credit-card me-2"></i>Proceed to Checkout
                        </button>
                        <a href="{{ route('toyshop.products.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
        </form>
    @else
        <div class="empty-cart reveal">
            <i class="bi bi-cart-x empty-cart-icon"></i>
            <h4 class="fw-bold mb-2">Your cart is empty</h4>
            <p class="text-muted mb-4">Start shopping to add items to your cart!</p>
            <a href="{{ route('toyshop.products.index') }}" class="btn btn-primary btn-lg">
                <i class="bi bi-bag me-2"></i>Browse Products
            </a>
        </div>
    @endif
</div>

@push('scripts')
<script>
(function() {
    'use strict';
    
    const taxRate = {{ $taxRate ?? 12 }} / 100;
    const commissionRate = {{ $commissionRate ?? 5 }} / 100;
    
    // Update item price display
    function updateItemDisplay(itemId, quantity) {
        const control = document.querySelector(`.quantity-control[data-item-id="${itemId}"]`);
        if (!control) return;
        
        const unitPrice = parseFloat(control.dataset.unitPrice);
        const maxQty = parseInt(control.dataset.maxQty);
        
        // Calculate item total
        const itemTotal = unitPrice * quantity;
        
        // Update item total display
        const totalEl = document.querySelector(`.item-total[data-item-id="${itemId}"]`);
        if (totalEl) {
            totalEl.textContent = '₱' + itemTotal.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
        
        // Update quantity display in calculation
        const calcEl = document.querySelector(`.item-calculation[data-item-id="${itemId}"] .qty-display`);
        if (calcEl) {
            calcEl.textContent = quantity;
        }
        
        // Update button states
        const decreaseBtn = document.querySelector(`.decrease-btn[data-item-id="${itemId}"]`);
        const increaseBtn = document.querySelector(`.increase-btn[data-item-id="${itemId}"]`);
        
        if (decreaseBtn) decreaseBtn.disabled = quantity <= 1;
        if (increaseBtn) increaseBtn.disabled = quantity >= maxQty;
        
        // Update cart summary
        updateCartSummary();
    }
    
    // Update cart summary totals
    function updateCartSummary() {
        let subtotal = 0;
        
        // Calculate subtotal from all items
        document.querySelectorAll('.item-total').forEach(function(el) {
            const priceText = el.textContent.replace(/[₱,]/g, '');
            const price = parseFloat(priceText) || 0;
            subtotal += price;
        });
        
        const commission = subtotal * commissionRate;
        const vat = (subtotal + commission) * taxRate;
        const total = subtotal + commission + vat;
        
        // Update summary display
        const subtotalEl = document.getElementById('summary-subtotal');
        const vatEl = document.getElementById('summary-vat');
        const totalEl = document.getElementById('summary-total');
        
        if (subtotalEl) subtotalEl.textContent = '₱' + subtotal.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (vatEl) vatEl.textContent = '₱' + vat.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (totalEl) totalEl.textContent = '₱' + total.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    
    // Save quantity to server
    function saveQuantity(itemId, quantity, input) {
        const updateUrl = input.dataset.updateUrl;
        
        const formData = new FormData();
        formData.append('quantity', quantity);
        formData.append('_method', 'PUT');
        
        fetch(updateUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Update failed');
            return response.json();
        })
        .then(data => {
            console.log('Quantity updated successfully');
        })
        .catch(error => {
            console.error('Error updating quantity:', error);
            alert('Failed to update quantity. Please refresh the page.');
        });
    }
    
    // Handle quantity input changes
    function handleQuantityChange(input) {
        const itemId = input.dataset.itemId;
        const control = input.closest('.quantity-control');
        const maxQty = parseInt(control.dataset.maxQty);
        
        let quantity = parseInt(input.value) || 1;
        
        // Validate quantity
        if (quantity < 1) quantity = 1;
        if (quantity > maxQty) quantity = maxQty;
        
        input.value = quantity;
        
        // Update display immediately
        updateItemDisplay(itemId, quantity);
        
        return quantity;
    }
    
    // Initialize event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Decrease button click
        document.querySelectorAll('.decrease-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const itemId = this.dataset.itemId;
                const input = document.querySelector(`.quantity-input[data-item-id="${itemId}"]`);
                
                let quantity = parseInt(input.value) || 1;
                if (quantity > 1) {
                    quantity--;
                    input.value = quantity;
                    updateItemDisplay(itemId, quantity);
                    saveQuantity(itemId, quantity, input);
                }
            });
        });
        
        // Increase button click
        document.querySelectorAll('.increase-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const itemId = this.dataset.itemId;
                const input = document.querySelector(`.quantity-input[data-item-id="${itemId}"]`);
                const control = input.closest('.quantity-control');
                const maxQty = parseInt(control.dataset.maxQty);
                
                let quantity = parseInt(input.value) || 1;
                if (quantity < maxQty) {
                    quantity++;
                    input.value = quantity;
                    updateItemDisplay(itemId, quantity);
                    saveQuantity(itemId, quantity, input);
                }
            });
        });
        
        // Input field changes
        document.querySelectorAll('.quantity-input').forEach(function(input) {
            let oldValue = parseInt(input.value);
            
            // Real-time update as user types
            input.addEventListener('input', function() {
                const itemId = this.dataset.itemId;
                handleQuantityChange(this);
            });
            
            // Save to server when user finishes editing
            input.addEventListener('blur', function() {
                const itemId = this.dataset.itemId;
                const newValue = handleQuantityChange(this);
                
                if (oldValue !== newValue) {
                    saveQuantity(itemId, newValue, this);
                    oldValue = newValue;
                }
            });
            
            // Save on Enter key
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.blur();
                }
            });
        });
        
        // Handle cart item selection for checkout
        document.querySelectorAll('.cart-item-select').forEach(function(checkbox) {
            checkbox.addEventListener('change', updateCartSummary);
        });
    });
})();
</script>
@endpush
@endsection
