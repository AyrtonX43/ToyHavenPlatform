@extends('layouts.seller')

@section('title', 'Point of Sale (POS) - ToyHaven')

@section('page-title', 'Point of Sale (POS)')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<!-- POS Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-6 mb-3">
        <div class="stat-card bg-primary">
            <i class="bi bi-currency-dollar stat-icon"></i>
            <div class="stat-label">Today's POS Sales</div>
            <div class="stat-value">₱{{ number_format($stats['today_pos_sales'], 2) }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="stat-card bg-success">
            <i class="bi bi-cart-check stat-icon"></i>
            <div class="stat-label">Today's Transactions</div>
            <div class="stat-value counter-number" data-count="{{ $stats['today_pos_orders'] }}">{{ $stats['today_pos_orders'] }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="stat-card bg-info">
            <i class="bi bi-graph-up stat-icon"></i>
            <div class="stat-label">This Week's Sales</div>
            <div class="stat-value">₱{{ number_format($stats['week_pos_sales'], 2) }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="stat-card bg-warning">
            <i class="bi bi-receipt stat-icon"></i>
            <div class="stat-label">Week Transactions</div>
            <div class="stat-value counter-number" data-count="{{ $stats['week_pos_orders'] }}">{{ $stats['week_pos_orders'] }}</div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2 text-primary"></i>POS Sales Trend</h5>
                <small class="text-muted">Last 7 Days</small>
            </div>
            <div class="card-body">
                <canvas id="posSalesChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-credit-card me-2 text-success"></i>Payment Methods</h5>
            </div>
            <div class="card-body">
                @if($paymentBreakdown->count() > 0)
                    <canvas id="paymentChart" height="180"></canvas>
                    <div class="mt-3">
                        @foreach($paymentBreakdown as $pm)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-capitalize">{{ $pm->payment_method }}</span>
                                <span class="badge bg-primary">{{ $pm->count }} orders · ₱{{ number_format($pm->total, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center py-4 mb-0">No payment data yet</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent POS Transactions -->
@if($recentPosOrders->count() > 0)
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent POS Transactions</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentPosOrders as $order)
                    <tr>
                        <td><strong>#{{ $order->order_number }}</strong></td>
                        <td class="text-success fw-bold">₱{{ number_format($order->total ?? $order->total_amount ?? 0, 2) }}</td>
                        <td><span class="badge bg-secondary text-capitalize">{{ $order->payment_method }}</span></td>
                        <td><small>{{ $order->created_at->format('M d, h:i A') }}</small></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<div class="row">
    <!-- Products List -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-grid me-2"></i>Products</h5>
                <span class="badge bg-primary">{{ $products->count() }} available</span>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <input type="text" id="productSearch" class="form-control" placeholder="Search products...">
                </div>
                <div class="row g-3" id="productsGrid">
                    @foreach($products as $product)
                        <div class="col-md-4 col-sm-6 product-item" data-name="{{ strtolower($product->name) }}">
                            <div class="card h-100 border product-card" data-product-id="{{ $product->id }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock_quantity }}">
                                @if($product->images->first())
                                    <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" 
                                         class="card-img-top" 
                                         style="height: 150px; object-fit: cover;"
                                         alt="{{ $product->name }}">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                    </div>
                                @endif
                                <div class="card-body">
                                    <h6 class="card-title mb-1">{{ Str::limit($product->name, 30) }}</h6>
                                    <p class="text-muted small mb-2">Stock: {{ $product->stock_quantity }}</p>
                                    <p class="text-success mb-2 fw-bold">₱{{ number_format($product->price, 2) }}</p>
                                    <button class="btn btn-primary btn-sm w-100 add-to-cart" 
                                            data-product-id="{{ $product->id }}"
                                            data-product-name="{{ $product->name }}"
                                            data-product-price="{{ $product->price }}"
                                            data-product-stock="{{ $product->stock_quantity }}">
                                        <i class="bi bi-cart-plus me-1"></i> Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($products->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-box-seam text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">No active products available for POS</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Cart & Checkout -->
    <div class="col-lg-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-cart me-2"></i>Shopping Cart</h5>
            </div>
            <div class="card-body">
                <div id="cartItems" class="mb-3">
                    <p class="text-muted text-center">Cart is empty</p>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-3">
                    <strong>Total:</strong>
                    <strong class="text-primary fs-5" id="cartTotal">₱0.00</strong>
                </div>

                <!-- Customer Info -->
                <div class="mb-3">
                    <label class="form-label">Customer Name (Optional)</label>
                    <input type="text" id="customerName" class="form-control" placeholder="Walk-in customer">
                </div>
                <div class="mb-3">
                    <label class="form-label">Customer Phone (Optional)</label>
                    <input type="text" id="customerPhone" class="form-control" placeholder="Phone number">
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                    <select id="paymentMethod" class="form-select" required>
                        <option value="">Select payment method</option>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="digital">Digital Payment</option>
                    </select>
                </div>

                <button id="processOrderBtn" class="btn btn-success w-100" disabled>
                    <i class="bi bi-check-circle me-1"></i> Process Order
                </button>
                <button id="clearCartBtn" class="btn btn-outline-danger w-100 mt-2">
                    <i class="bi bi-trash me-1"></i> Clear Cart
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let cart = [];

// POS Charts
document.addEventListener('DOMContentLoaded', function() {
    // Sales Chart
    if (document.getElementById('posSalesChart')) {
        const salesData = @json($salesChartData);
        new Chart(document.getElementById('posSalesChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: salesData.map(d => d.date),
                datasets: [{
                    label: 'Sales (₱)',
                    data: salesData.map(d => d.sales),
                    borderColor: '#0891b2',
                    backgroundColor: 'rgba(8, 145, 178, 0.15)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: v => '₱' + v.toLocaleString() }
                    }
                }
            }
        });
    }

    // Payment Method Chart
    @if($paymentBreakdown->count() > 0)
    if (document.getElementById('paymentChart')) {
        const paymentData = @json($paymentBreakdown);
        const colors = ['#0891b2', '#10b981', '#f59e0b'];
        new Chart(document.getElementById('paymentChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: paymentData.map(d => d.payment_method.charAt(0).toUpperCase() + d.payment_method.slice(1)),
                datasets: [{
                    data: paymentData.map(d => parseFloat(d.total)),
                    backgroundColor: colors.slice(0, paymentData.length),
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
    @endif
});

document.addEventListener('DOMContentLoaded', function() {
    // Add to cart
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = parseInt(this.dataset.productId);
            const productName = this.dataset.productName;
            const productPrice = parseFloat(this.dataset.productPrice);
            const productStock = parseInt(this.dataset.productStock);

            const existingItem = cart.find(item => item.productId === productId);
            
            if (existingItem) {
                if (existingItem.quantity >= productStock) {
                    alert('Insufficient stock!');
                    return;
                }
                existingItem.quantity++;
            } else {
                cart.push({
                    productId: productId,
                    productName: productName,
                    price: productPrice,
                    stock: productStock,
                    quantity: 1
                });
            }

            updateCart();
        });
    });

    // Product search
    document.getElementById('productSearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        document.querySelectorAll('.product-item').forEach(item => {
            const name = item.dataset.name;
            if (name.includes(searchTerm)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Clear cart
    document.getElementById('clearCartBtn').addEventListener('click', function() {
        if (confirm('Clear cart?')) {
            cart = [];
            updateCart();
        }
    });

    // Process order
    document.getElementById('processOrderBtn').addEventListener('click', function() {
        if (cart.length === 0) {
            alert('Cart is empty!');
            return;
        }

        const paymentMethod = document.getElementById('paymentMethod').value;
        if (!paymentMethod) {
            alert('Please select payment method');
            return;
        }

        if (!confirm('Process this order?')) {
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';

        fetch('{{ route("seller.pos.process") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                items: cart.map(item => ({
                    product_id: item.productId,
                    quantity: item.quantity
                })),
                customer_name: document.getElementById('customerName').value,
                customer_phone: document.getElementById('customerPhone').value,
                payment_method: paymentMethod
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Order processed successfully! Order #' + data.order.order_number);
                cart = [];
                updateCart();
                document.getElementById('customerName').value = '';
                document.getElementById('customerPhone').value = '';
                document.getElementById('paymentMethod').value = '';
                // Optionally reload page to update stock
                window.location.reload();
            } else {
                alert(data.error || 'Error processing order');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error processing order. Please try again.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Process Order';
        });
    });
});

function updateCart() {
    const cartItemsDiv = document.getElementById('cartItems');
    const cartTotal = document.getElementById('cartTotal');
    const processBtn = document.getElementById('processOrderBtn');

    if (cart.length === 0) {
        cartItemsDiv.innerHTML = '<p class="text-muted text-center">Cart is empty</p>';
        cartTotal.textContent = '₱0.00';
        processBtn.disabled = true;
        return;
    }

    let html = '';
    let total = 0;

    cart.forEach((item, index) => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        html += `
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <div class="flex-grow-1">
                    <strong class="d-block small">${item.productName}</strong>
                    <small class="text-muted">₱${item.price.toFixed(2)} × ${item.quantity}</small>
                </div>
                <div class="text-end">
                    <strong class="text-success">₱${itemTotal.toFixed(2)}</strong>
                    <div class="btn-group btn-group-sm mt-1">
                        <button class="btn btn-outline-secondary" onclick="updateQuantity(${index}, -1)">-</button>
                        <span class="btn btn-outline-secondary">${item.quantity}</span>
                        <button class="btn btn-outline-secondary" onclick="updateQuantity(${index}, 1)">+</button>
                        <button class="btn btn-outline-danger" onclick="removeItem(${index})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    cartItemsDiv.innerHTML = html;
    cartTotal.textContent = '₱' + total.toFixed(2);
    processBtn.disabled = false;
}

function updateQuantity(index, change) {
    const item = cart[index];
    const newQuantity = item.quantity + change;
    
    if (newQuantity <= 0) {
        removeItem(index);
        return;
    }
    
    if (newQuantity > item.stock) {
        alert('Insufficient stock!');
        return;
    }
    
    item.quantity = newQuantity;
    updateCart();
}

function removeItem(index) {
    cart.splice(index, 1);
    updateCart();
}
</script>
@endpush
@endsection
