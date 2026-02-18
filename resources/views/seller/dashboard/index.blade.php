@extends('layouts.seller')

@section('title', 'Seller Dashboard - ToyHaven')

@section('page-title', 'Dashboard Overview')

@section('content')
<!-- Status Alert -->
@if(session('pending_approval') || $seller->verification_status === 'pending')
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <h5 class="alert-heading">
            <i class="bi bi-hourglass-split me-2"></i>Registration Pending Admin Approval
        </h5>
        <p class="mb-2">
            <strong>Your business account registration has been submitted successfully!</strong>
        </p>
        <p class="mb-2">
            You can browse and shop online, but the following business features will be available after admin approval:
        </p>
        <ul class="mb-2">
            <li>Upload Products (Toyshop, Trading, Auction)</li>
            <li>View Business Page</li>
            <li>Product Tracking</li>
            <li>Chat / Messages</li>
            <li>Order Management</li>
            <li>Analytics & Reports</li>
        </ul>
        <p class="mb-0 small">
            <i class="bi bi-info-circle me-1"></i>
            Our admin team will review your registration and notify you once your account is approved. Thank you for your patience!
        </p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@elseif($seller->verification_status === 'rejected')
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h5 class="alert-heading">
            <i class="bi bi-x-circle me-2"></i>Account Verification Rejected
        </h5>
        <p class="mb-0">{{ $seller->rejection_reason ?? 'Your account verification was rejected. Please contact support for more information.' }}</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Upgrade to Trusted Shop Alert -->
@if($seller->verification_status === 'approved' && !$seller->is_verified_shop)
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <i class="bi bi-shield-check" style="font-size: 2rem;"></i>
            </div>
            <div class="flex-grow-1 ms-3">
                <h5 class="alert-heading mb-2">
                    <i class="bi bi-star-fill me-2"></i>Upgrade to Trusted Shop
                </h5>
                <p class="mb-2">
                    Unlock premium features and gain customer trust by upgrading to a verified trusted shop. Get priority listing, trusted badge, and enhanced credibility.
                </p>
                <a href="{{ route('seller.shop-upgrade.index') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-arrow-up-circle me-1"></i> Upgrade Now
                </a>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Quick Actions -->
@if($seller->verification_status === 'approved')
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('seller.products.create') }}" class="quick-action-card card border">
                            <i class="bi bi-plus-circle-fill"></i>
                            <h6>Add New Product</h6>
                            <small class="text-muted">Create a new listing</small>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('seller.products.index') }}" class="quick-action-card card border">
                            <i class="bi bi-box-seam"></i>
                            <h6>Manage Products</h6>
                            <small class="text-muted">View all products</small>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('seller.orders.index') }}" class="quick-action-card card border">
                            <i class="bi bi-cart-check"></i>
                            <h6>View Orders</h6>
                            <small class="text-muted">Manage orders</small>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('seller.business-page.index') }}" class="quick-action-card card border">
                            <i class="bi bi-gear"></i>
                            <h6>Business Settings</h6>
                            <small class="text-muted">Configure your store</small>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('seller.pos.index') }}" class="quick-action-card card border">
                            <i class="bi bi-cash-register"></i>
                            <h6>Point of Sale</h6>
                            <small class="text-muted">Process walk-in orders</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card bg-primary">
            <i class="bi bi-box-seam stat-icon"></i>
            <div class="stat-label">Total Products</div>
            <div class="stat-value counter-number" data-count="{{ $stats['total_products'] }}">{{ $stats['total_products'] }}</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card bg-success">
            <i class="bi bi-check-circle stat-icon"></i>
            <div class="stat-label">Active Products</div>
            <div class="stat-value counter-number" data-count="{{ $stats['active_products'] }}">{{ $stats['active_products'] }}</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card bg-info">
            <i class="bi bi-cart-check stat-icon"></i>
            <div class="stat-label">Total Orders</div>
            <div class="stat-value counter-number" data-count="{{ $stats['total_orders'] }}">{{ $stats['total_orders'] }}</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card bg-warning">
            <i class="bi bi-currency-exchange stat-icon"></i>
            <div class="stat-label">Total Sales</div>
            <div class="stat-value counter-currency" data-count="{{ $stats['total_sales'] }}">₱{{ number_format($stats['total_sales'], 2) }}</div>
        </div>
    </div>
</div>

<!-- Additional Statistics Row -->
<div class="row mb-4">
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-clock-history text-warning" style="font-size: 2.5rem;"></i>
                <h3 class="mt-3 mb-1">{{ $stats['pending_orders'] }}</h3>
                <p class="text-muted mb-0">Pending Orders</p>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-check2-circle text-success" style="font-size: 2.5rem;"></i>
                <h3 class="mt-3 mb-1">{{ $stats['completed_orders'] }}</h3>
                <p class="text-muted mb-0">Completed Orders</p>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-calendar-day text-info" style="font-size: 2.5rem;"></i>
                <h3 class="mt-3 mb-1">{{ $stats['today_orders'] }}</h3>
                <p class="text-muted mb-0">Today's Orders</p>
                <small class="text-success">₱{{ number_format($stats['today_sales'], 2) }} sales</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Orders -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-cart-check me-2"></i>Recent Orders</h5>
                @if($seller->verification_status === 'approved')
                    <a href="{{ route('seller.orders.index') }}" class="btn btn-sm btn-outline-primary">
                        View All <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                @endif
            </div>
            <div class="card-body">
                @if($recentOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <strong>#{{ $order->order_number }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-person-circle me-2 text-muted"></i>
                                                {{ $order->user->name }}
                                            </div>
                                        </td>
                                        <td>
                                            <strong class="text-success">₱{{ number_format($order->total, 2) }}</strong>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'processing' => 'info',
                                                    'shipped' => 'primary',
                                                    'delivered' => 'success',
                                                    'cancelled' => 'danger'
                                                ];
                                                $color = $statusColors[$order->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $order->created_at->format('M d, Y') }}<br>
                                                {{ $order->created_at->format('h:i A') }}
                                            </small>
                                        </td>
                                        <td>
                                            @if($seller->verification_status === 'approved')
                                                <a href="{{ route('seller.orders.show', $order->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-eye me-1"></i>View
                                                </a>
                                            @else
                                                <span class="text-muted small">Pending Approval</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-cart-x text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">No orders yet</p>
                        @if($seller->verification_status === 'approved')
                            <a href="{{ route('seller.products.create') }}" class="btn btn-primary mt-2">
                                <i class="bi bi-plus-circle me-1"></i>Add Your First Product
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Low Stock Products & Pending Products -->
    <div class="col-lg-4 mb-4">
        <!-- Low Stock Products -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>Low Stock</h5>
                @if($lowStockProducts->count() > 0)
                    <span class="badge bg-warning">{{ $lowStockProducts->count() }}</span>
                @endif
            </div>
            <div class="card-body">
                @if($lowStockProducts->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($lowStockProducts as $product)
                            <div class="list-group-item px-0 border-0 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ Str::limit($product->name, 30) }}</h6>
                                        <small class="text-danger">
                                            <i class="bi bi-box me-1"></i>Only {{ $product->stock_quantity }} left
                                        </small>
                                    </div>
                                    @if($seller->verification_status === 'approved')
                                        <a href="{{ route('seller.products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($seller->verification_status === 'approved')
                        <div class="mt-3">
                            <a href="{{ route('seller.products.index') }}?filter=low_stock" class="btn btn-sm btn-outline-warning w-100">
                                View All Low Stock <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center py-3">
                        <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2 mb-0">All products have sufficient stock</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Pending Products -->
        @if($stats['pending_products'] > 0)
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2 text-info"></i>Pending Approval</h5>
                <span class="badge bg-info">{{ $stats['pending_products'] }}</span>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    You have {{ $stats['pending_products'] }} product(s) waiting for admin approval.
                </p>
                @if($seller->verification_status === 'approved')
                    <a href="{{ route('seller.products.index') }}?status=pending" class="btn btn-sm btn-outline-info w-100">
                        View Pending Products <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Sales Summary Card -->
@if($seller->verification_status === 'approved' && $stats['total_sales'] > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Sales Summary</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="p-3">
                            <i class="bi bi-calendar-day text-primary" style="font-size: 2rem;"></i>
                            <h4 class="mt-2 mb-1">₱{{ number_format($stats['today_sales'], 2) }}</h4>
                            <p class="text-muted mb-0">Today's Sales</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3">
                            <i class="bi bi-calendar-month text-success" style="font-size: 2rem;"></i>
                            <h4 class="mt-2 mb-1">₱{{ number_format($stats['month_sales'], 2) }}</h4>
                            <p class="text-muted mb-0">This Month</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3">
                            <i class="bi bi-graph-up-arrow text-info" style="font-size: 2rem;"></i>
                            <h4 class="mt-2 mb-1">₱{{ number_format($stats['total_sales'], 2) }}</h4>
                            <p class="text-muted mb-0">All Time Sales</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
