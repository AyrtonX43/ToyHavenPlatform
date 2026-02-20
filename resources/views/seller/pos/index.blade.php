@extends('layouts.seller')

@section('title', 'Point of Sale - ToyHaven')

@section('page-title', 'Point of Sale')

@push('styles')
<style>
    .pos-container { background: #f1f5f9; border-radius: 12px; }
    .pos-products-header { background: #fff; border-radius: 12px 12px 0 0; padding: 1rem 1.5rem; border-bottom: 1px solid #e2e8f0; }
    .pos-products-header .search-wrapper { position: relative; max-width: 400px; }
    .pos-products-header .search-wrapper i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
    .pos-products-header .search-wrapper input { padding-left: 42px; border-radius: 10px; }
    .pos-products-body { padding: 1.25rem; max-height: calc(100vh - 320px); overflow-y: auto; }
    .pos-product-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.2s ease;
        cursor: pointer;
        height: 100%;
    }
    .pos-product-card:hover {
        border-color: #0891b2;
        box-shadow: 0 4px 12px rgba(8, 145, 178, 0.15);
    }
    .pos-product-card .pos-product-img {
        height: 120px;
        object-fit: cover;
        background: #f8fafc;
    }
    .pos-product-card .pos-product-body { padding: 0.75rem 1rem; }
    .pos-product-card .pos-product-name { font-weight: 600; font-size: 0.9rem; line-height: 1.3; min-height: 2.6em; }
    .pos-product-card .pos-product-price { font-size: 1.1rem; font-weight: 700; color: #059669; }
    .pos-product-card .pos-product-stock { font-size: 0.75rem; color: #64748b; }
    .pos-cart-panel {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .pos-cart-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #fff;
        padding: 1rem 1.25rem;
        font-weight: 600;
    }
    .pos-cart-header .cart-count { background: #0891b2; color: #fff; font-size: 0.75rem; padding: 2px 8px; border-radius: 20px; }
    .pos-cart-body { padding: 1.25rem; }
    .pos-cart-table { font-size: 0.9rem; }
    .pos-cart-table th { font-weight: 600; color: #64748b; font-size: 0.75rem; text-transform: uppercase; }
    .pos-cart-table td { vertical-align: middle; }
    .pos-cart-qty { width: 90px; }
    .pos-cart-qty .btn { padding: 2px 10px; }
    .pos-summary-row { border-top: 2px solid #e2e8f0; padding-top: 0.75rem; margin-top: 0.5rem; }
    .pos-total-row { font-size: 1.25rem; font-weight: 700; color: #0f172a; }
    .pos-btn-process { padding: 12px 24px; font-weight: 600; font-size: 1rem; }
    .pos-receipt-modal .receipt-content { font-family: 'Courier New', monospace; font-size: 0.9rem; }
    .pos-receipt-modal .receipt-line { border-bottom: 1px dashed #cbd5e1; padding: 4px 0; }
    .pos-receipt-modal .receipt-total { border-top: 2px solid #0f172a; padding-top: 8px; margin-top: 8px; font-weight: 700; }
</style>
@endpush

@section('content')
<!-- POS Header -->
<div class="pos-header mb-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h4 class="mb-0 fw-bold text-dark">
            <i class="bi bi-cash-register me-2 text-success"></i>Point of Sale
        </h4>
        <p class="text-muted small mb-0">{{ $seller->business_name }} · <span id="posDateTime"></span></p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('seller.products.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-box-seam me-1"></i> Manage Products
        </a>
        <a href="{{ route('seller.products.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Add Product
        </a>
    </div>
</div>

<div class="row g-0">
    <!-- Products Panel -->
    <div class="col-lg-8 pe-lg-3 mb-4 mb-lg-0">
        <div class="pos-container">
            <div class="pos-products-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h6 class="mb-0 fw-semibold text-dark">
                    <i class="bi bi-grid-3x3-gap me-2"></i>Products
                    @if($products->isNotEmpty())
                        <span class="badge bg-light text-dark ms-2">{{ $products->count() }} items</span>
                    @endif
                </h6>
                @if($products->isNotEmpty())
                <div class="search-wrapper">
                    <i class="bi bi-search"></i>
                    <input type="text" id="productSearch" class="form-control form-control-sm" placeholder="Search by name...">
                </div>
                @endif
            </div>
            <div class="pos-products-body">
                @if($products->isNotEmpty())
                <div class="row g-3" id="productsGrid">
                    @foreach($products as $product)
                    <div class="col-md-3 col-sm-4 col-6 product-item" data-name="{{ strtolower($product->name) }}">
                        <div class="pos-product-card add-to-cart" 
                             data-product-id="{{ $product->id }}"
                             data-product-name="{{ $product->name }}"
                             data-product-price="{{ $product->price }}"
                             data-product-stock="{{ $product->stock_quantity }}">
                            @if($product->images->first())
                                <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" 
                                     class="pos-product-img w-100" alt="{{ $product->name }}">
                            @else
                                <div class="pos-product-img w-100 bg-light d-flex align-items-center justify-content-center">
                                    <i class="bi bi-image text-muted" style="font-size: 2.5rem;"></i>
                                </div>
                            @endif
                            <div class="pos-product-body">
                                <div class="pos-product-name mb-1">{{ Str::limit($product->name, 40) }}</div>
                                <div class="pos-product-price">₱{{ number_format($product->price, 2) }}</div>
                                <div class="pos-product-stock">Stock: {{ $product->stock_quantity }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-box-seam text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 mb-2">No products available</h5>
                    <p class="text-muted mb-4">Add active products with stock to sell at the point of sale.</p>
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <a href="{{ route('seller.products.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Add Product
                        </a>
                        <a href="{{ route('seller.products.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-box-seam me-1"></i> Manage Products
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Cart Panel -->
    <div class="col-lg-4">
        <div class="pos-cart-panel sticky-top" style="top: 100px;">
            <div class="pos-cart-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-cart3 me-2"></i>Current Sale</span>
                <span class="cart-count" id="cartCount" style="display: none;">0</span>
            </div>
            <div class="pos-cart-body">
                <div id="cartItems" class="mb-3">
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-cart-x" style="font-size: 3rem;"></i>
                        <p class="mt-2 mb-0 small">Cart is empty</p>
                        <p class="mb-0 small">Click a product to add</p>
                    </div>
                </div>

                <div id="cartSummary" style="display: none;">
                    <table class="pos-cart-table table table-sm">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody id="cartTableBody"></tbody>
                    </table>
                    <div class="pos-summary-row pos-total-row d-flex justify-content-between">
                        <span>Total</span>
                        <span id="cartTotal">₱0.00</span>
                    </div>

                    <div class="mt-4">
                        <label class="form-label small fw-semibold">Customer (optional)</label>
                        <input type="text" id="customerName" class="form-control form-control-sm mb-2" placeholder="Walk-in customer">
                        <input type="text" id="customerPhone" class="form-control form-control-sm mb-3" placeholder="Phone number">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Payment Method <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2 flex-wrap">
                            <label class="flex-grow-1 btn btn-outline-secondary btn-sm py-2 payment-option">
                                <input type="radio" name="paymentMethod" value="cash" class="d-none">
                                <i class="bi bi-cash-coin d-block mb-1"></i> Cash
                            </label>
                            <label class="flex-grow-1 btn btn-outline-secondary btn-sm py-2 payment-option">
                                <input type="radio" name="paymentMethod" value="card" class="d-none">
                                <i class="bi bi-credit-card d-block mb-1"></i> Card
                            </label>
                            <label class="flex-grow-1 btn btn-outline-secondary btn-sm py-2 payment-option">
                                <input type="radio" name="paymentMethod" value="digital" class="d-none">
                                <i class="bi bi-phone d-block mb-1"></i> Digital
                            </label>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button id="processOrderBtn" class="btn btn-success pos-btn-process" disabled>
                            <i class="bi bi-check-circle me-1"></i> Complete Sale
                        </button>
                        <button id="clearCartBtn" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-trash me-1"></i> Clear Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success / Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Sale Complete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="receiptContent" class="receipt-content"></div>
                <div class="mt-4 d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary flex-grow-1" id="printReceiptBtn">
                        <i class="bi bi-printer me-1"></i> Print Receipt
                    </button>
                    <button type="button" class="btn btn-primary flex-grow-1" data-bs-dismiss="modal">
                        <i class="bi bi-plus-circle me-1"></i> New Sale
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer"></div>
@endsection

@push('scripts')
<script>
(function() {
    let cart = [];
    const receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
    let lastReceiptData = null;

    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const toastId = 'toast-' + Date.now();
        const bgClass = type === 'error' ? 'bg-danger' : (type === 'warning' ? 'bg-warning text-dark' : 'bg-success');
        const html = `
            <div id="${toastId}" class="toast align-items-center ${bgClass} text-white border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        const toastEl = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    }

    function updateCart() {
        const cartItemsDiv = document.getElementById('cartItems');
        const cartSummary = document.getElementById('cartSummary');
        const cartCount = document.getElementById('cartCount');
        const cartTotal = document.getElementById('cartTotal');
        const processBtn = document.getElementById('processOrderBtn');
        const tableBody = document.getElementById('cartTableBody');

        if (cart.length === 0) {
            cartSummary.style.display = 'none';
            cartCount.style.display = 'none';
            cartItemsDiv.style.display = 'block';
            cartItemsDiv.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-cart-x" style="font-size: 3rem;"></i>
                    <p class="mt-2 mb-0 small">Cart is empty</p>
                    <p class="mb-0 small">Click a product to add</p>
                </div>
            `;
            processBtn.disabled = true;
            return;
        }

        cartItemsDiv.style.display = 'none';
        cartSummary.style.display = 'block';
        cartCount.style.display = 'inline';
        cartCount.textContent = cart.reduce((s, i) => s + i.quantity, 0);

        let total = 0;
        let rows = '';
        cart.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            rows += `
                <tr>
                    <td>
                        <div class="fw-semibold small">${escapeHtml(item.productName)}</div>
                        <small class="text-muted">₱${item.price.toFixed(2)} each</small>
                    </td>
                    <td class="text-center">
                        <div class="pos-cart-qty btn-group btn-group-sm d-inline-flex">
                            <button class="btn btn-outline-secondary" onclick="window.posUpdateQty(${index}, -1)">−</button>
                            <span class="btn btn-outline-secondary disabled px-2" style="min-width: 36px;">${item.quantity}</span>
                            <button class="btn btn-outline-secondary" onclick="window.posUpdateQty(${index}, 1)">+</button>
                            <button class="btn btn-outline-danger" onclick="window.posRemoveItem(${index})" title="Remove"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                    <td class="text-end fw-semibold text-success">₱${itemTotal.toFixed(2)}</td>
                </tr>
            `;
        });
        tableBody.innerHTML = rows;
        cartTotal.textContent = '₱' + total.toLocaleString('en-US', { minimumFractionDigits: 2 });
        processBtn.disabled = false;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    window.posUpdateQty = function(index, change) {
        const item = cart[index];
        const newQty = item.quantity + change;
        if (newQty <= 0) {
            cart.splice(index, 1);
        } else if (newQty > item.stock) {
            showToast('Insufficient stock. Max: ' + item.stock, 'warning');
            return;
        } else {
            item.quantity = newQty;
        }
        updateCart();
    };

    window.posRemoveItem = function(index) {
        cart.splice(index, 1);
        updateCart();
    };

    document.addEventListener('DOMContentLoaded', function() {
        // Live date/time
        function updateDateTime() {
            const el = document.getElementById('posDateTime');
            if (el) el.textContent = new Date().toLocaleString('en-PH', { dateStyle: 'medium', timeStyle: 'short' });
        }
        updateDateTime();
        setInterval(updateDateTime, 60000);

        // Add to cart - click on product card
        document.querySelectorAll('.add-to-cart').forEach(el => {
            el.addEventListener('click', function() {
                const productId = parseInt(this.dataset.productId);
                const productName = this.dataset.productName;
                const productPrice = parseFloat(this.dataset.productPrice);
                const productStock = parseInt(this.dataset.productStock);

                const existing = cart.find(i => i.productId === productId);
                if (existing) {
                    if (existing.quantity >= productStock) {
                        showToast('Insufficient stock', 'warning');
                        return;
                    }
                    existing.quantity++;
                } else {
                    cart.push({ productId, productName, price: productPrice, stock: productStock, quantity: 1 });
                }
                updateCart();
                showToast('Added to cart', 'success');
            });
        });

        // Payment method selection
        document.querySelectorAll('.payment-option').forEach(label => {
            label.addEventListener('click', function() {
                document.querySelectorAll('.payment-option').forEach(l => l.classList.remove('active', 'border-primary'));
                this.classList.add('active', 'border-primary');
                this.querySelector('input').checked = true;
            });
        });

        // Product search
        const searchEl = document.getElementById('productSearch');
        if (searchEl) {
            searchEl.addEventListener('input', function() {
                const term = this.value.toLowerCase();
                document.querySelectorAll('.product-item').forEach(el => {
                    el.style.display = el.dataset.name.includes(term) ? '' : 'none';
                });
            });
        }

        // Clear cart
        document.getElementById('clearCartBtn').addEventListener('click', function() {
            if (cart.length === 0) return;
            if (confirm('Clear all items from the cart?')) {
                cart = [];
                updateCart();
                showToast('Cart cleared', 'success');
            }
        });

        // Process order
        document.getElementById('processOrderBtn').addEventListener('click', function() {
            if (cart.length === 0) {
                showToast('Cart is empty', 'warning');
                return;
            }
            const paymentRadio = document.querySelector('input[name="paymentMethod"]:checked');
            if (!paymentRadio) {
                showToast('Please select a payment method', 'warning');
                return;
            }

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';

            fetch('{{ route("seller.pos.process") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    items: cart.map(i => ({ product_id: i.productId, quantity: i.quantity })),
                    customer_name: document.getElementById('customerName').value.trim() || null,
                    customer_phone: document.getElementById('customerPhone').value.trim() || null,
                    payment_method: paymentRadio.value
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    lastReceiptData = {
                        orderNumber: data.order.order_number,
                        items: cart.map(i => ({ name: i.productName, qty: i.quantity, price: i.price })),
                        total: cart.reduce((s, i) => s + i.price * i.quantity, 0),
                        customer: document.getElementById('customerName').value.trim() || 'Walk-in',
                        payment: paymentRadio.value,
                        date: new Date()
                    };
                    renderReceipt(lastReceiptData);
                    cart = [];
                    updateCart();
                    document.getElementById('customerName').value = '';
                    document.getElementById('customerPhone').value = '';
                    document.querySelectorAll('.payment-option').forEach(l => l.classList.remove('active', 'border-primary'));
                    document.querySelectorAll('input[name="paymentMethod"]').forEach(r => r.checked = false);
                    receiptModal.show();
                } else {
                    showToast(data.error || 'Error processing order', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Connection error. Please try again.', 'error');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Complete Sale';
            });
        });

        // Print receipt
        document.getElementById('printReceiptBtn').addEventListener('click', function() {
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <!DOCTYPE html>
                <html><head><title>Receipt - ${lastReceiptData?.orderNumber || 'POS'}</title>
                <style>body{font-family:monospace;padding:20px;font-size:14px;} .line{border-bottom:1px dashed #ccc;padding:4px 0;} .total{border-top:2px solid #000;margin-top:8px;padding-top:8px;font-weight:bold;} table{width:100%;}</style>
                </head><body>
                <h3>{{ $seller->business_name }}</h3>
                <p>Order: ${lastReceiptData?.orderNumber || ''}<br>Date: ${lastReceiptData?.date?.toLocaleString() || ''}</p>
                <table><tbody>
                ${(lastReceiptData?.items || []).map(i => `<tr><td>${i.name} x${i.qty}</td><td style="text-align:right">₱${(i.price*i.qty).toFixed(2)}</td></tr>`).join('')}
                </tbody></table>
                <p class="total">TOTAL: ₱${(lastReceiptData?.total || 0).toFixed(2)}</p>
                <p>Payment: ${(lastReceiptData?.payment || '').toUpperCase()} | Customer: ${lastReceiptData?.customer || 'Walk-in'}</p>
                <p style="margin-top:24px">Thank you for your purchase!</p>
                </body></html>
            `);
            printWindow.document.close();
            printWindow.print();
            printWindow.close();
        });

        function renderReceipt(data) {
            let html = `
                <p class="mb-1"><strong>{{ $seller->business_name }}</strong></p>
                <p class="mb-2 text-muted small">Order #${data.orderNumber}</p>
                <p class="mb-3 small">${data.date.toLocaleString()}</p>
            `;
            data.items.forEach(i => {
                html += `<div class="receipt-line d-flex justify-content-between"><span>${escapeHtml(i.name)} x${i.qty}</span><span>₱${(i.price*i.qty).toFixed(2)}</span></div>`;
            });
            html += `<div class="receipt-total receipt-line d-flex justify-content-between"><span>TOTAL</span><span>₱${data.total.toFixed(2)}</span></div>`;
            html += `<p class="mt-2 small">Payment: ${data.payment.toUpperCase()} | Customer: ${escapeHtml(data.customer)}</p>`;
            document.getElementById('receiptContent').innerHTML = html;
        }
    });
})();
</script>
@endpush
