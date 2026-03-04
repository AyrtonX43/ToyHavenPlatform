@extends('layouts.seller-new')

@section('title', 'Seller Dashboard - ToyHaven')

@section('page-title', 'Dashboard Overview')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
{{-- Status Alerts --}}
@if(session('pending_approval') || $seller->verification_status === 'pending')
    <x-seller.alert-banner type="warning" heading="Registration Pending Admin Approval">
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
    </x-seller.alert-banner>
@elseif($seller->verification_status === 'rejected')
    <x-seller.alert-banner type="danger" heading="Account Verification Rejected">
        <p class="mb-0">{{ $seller->rejection_reason ?? 'Your account verification was rejected. Please contact support for more information.' }}</p>
    </x-seller.alert-banner>
@endif

{{-- Upgrade to Trusted Shop Alert --}}
@if($seller->verification_status === 'approved' && !$seller->is_verified_shop)
    <x-seller.alert-banner type="info" heading="Upgrade to Trusted Shop">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <i class="bi bi-shield-check" style="font-size: 2rem;"></i>
            </div>
            <div class="flex-grow-1 ms-3">
                <p class="mb-2">
                    Unlock premium features and gain customer trust by upgrading to a verified trusted shop. Get priority listing, trusted badge, and enhanced credibility.
                </p>
                <a href="{{ route('seller.shop-upgrade.index') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-arrow-up-circle me-1"></i> Upgrade Now
                </a>
            </div>
        </div>
    </x-seller.alert-banner>
@endif

{{-- Hero Stats Row --}}
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <x-seller.stat-card
            icon="bi-box-seam"
            :label="__('Total Products')"
            :value="$stats['total_products']"
            variant="primary"
            :animate="true"
        />
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <x-seller.stat-card
            icon="bi-check-circle"
            :label="__('Active Products')"
            :value="$stats['active_products']"
            variant="success"
            :animate="true"
        />
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <x-seller.stat-card
            icon="bi-cart-check"
            :label="__('Total Orders')"
            :value="$stats['total_orders']"
            variant="info"
            :animate="true"
        />
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <x-seller.stat-card
            icon="bi-currency-exchange"
            :label="__('Total Sales')"
            :value="$stats['total_sales']"
            variant="warning"
            :animate="true"
            :currency="true"
        />
    </div>
</div>

{{-- Two-column: Recent Orders (left) | Sidebar (right) --}}
<div class="row">
    {{-- Recent Orders - Left 2/3 --}}
    <div class="col-lg-8 mb-4">
        <x-seller.data-table title="Recent Orders">
            <x-slot:actions>
                @if($seller->verification_status === 'approved')
                    <a href="{{ route('seller.orders.index') }}" class="btn btn-sm btn-outline-primary">
                        View All <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                @endif
            </x-slot:actions>
            @if($recentOrders->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
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
                                    <td><strong>#{{ $order->order_number }}</strong></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-person-circle me-2 text-muted"></i>
                                            {{ $order->user?->name ?? 'Walk-in Customer' }}
                                        </div>
                                    </td>
                                    <td><strong class="text-success">₱{{ number_format($order->total, 2) }}</strong></td>
                                    <td>
                                        <x-seller.status-badge :status="$order->status" />
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
                <x-seller.empty-state
                    icon="bi-cart-x"
                    message="No orders yet"
                    :action="$seller->verification_status === 'approved' ? route('seller.products.create') : null"
                    :actionLabel="$seller->verification_status === 'approved' ? 'Add Your First Product' : null"
                />
            @endif
        </x-seller.data-table>
    </div>

    {{-- Sidebar: Quick Actions, Low Stock, Pending Products - Right 1/3 --}}
    <div class="col-lg-4 mb-4">
        @if($seller->verification_status === 'approved')
            {{-- Quick Actions - Condensed --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <x-seller.quick-action
                                href="{{ route('seller.products.create') }}"
                                icon="bi-plus-circle-fill"
                                title="Add Product"
                                description="New listing"
                            />
                        </div>
                        <div class="col-6">
                            <x-seller.quick-action
                                href="{{ route('seller.products.index') }}"
                                icon="bi-box-seam"
                                title="Products"
                                description="Manage all"
                            />
                        </div>
                        <div class="col-6">
                            <x-seller.quick-action
                                href="{{ route('seller.orders.index') }}"
                                icon="bi-cart-check"
                                title="Orders"
                                description="View orders"
                            />
                        </div>
                        <div class="col-6">
                            <x-seller.quick-action
                                href="{{ route('seller.business-page.index') }}"
                                icon="bi-gear"
                                title="Settings"
                                description="Store config"
                            />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Become an Auction Seller CTA --}}
            <div class="card mb-4 border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-hammer text-warning" style="font-size: 2rem;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 fw-bold">Become an Auction Seller</h6>
                            <p class="text-muted small mb-2">List items on ToyHaven Auctions. Link your Toyshop business or register as individual.</p>
                            <a href="{{ route('auctions.become-seller') }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-hammer me-1"></i>Get Started
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Low Stock --}}
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
                        <a href="{{ route('seller.products.index') }}?stock=low_stock" class="btn btn-sm btn-outline-warning w-100 mt-3">
                            View All Low Stock <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    @endif
                @else
                    <div class="text-center py-3">
                        <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2 mb-0">All products have sufficient stock</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Pending Products --}}
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

{{-- Charts --}}
@if($seller->verification_status === 'approved')
<div class="row mb-4">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2 text-primary"></i>Revenue Trend</h5>
                <small class="text-muted">Last 7 Days</small>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-cart-check me-2 text-info"></i>Orders Trend</h5>
                <small class="text-muted">Last 7 Days</small>
            </div>
            <div class="card-body">
                <canvas id="ordersChart" height="120"></canvas>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Secondary Stats (Pending, Completed, Today) - compact row --}}
<div class="row mb-4">
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100 border-start border-warning border-4">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                    <i class="bi bi-clock-history text-warning" style="font-size: 2.5rem;"></i>
                </div>
                <div>
                    <h3 class="mb-1 counter-number" data-count="{{ $stats['pending_orders'] }}">{{ $stats['pending_orders'] }}</h3>
                    <p class="text-muted mb-0 small">Pending Orders</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100 border-start border-success border-4">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                    <i class="bi bi-check2-circle text-success" style="font-size: 2.5rem;"></i>
                </div>
                <div>
                    <h3 class="mb-1 counter-number" data-count="{{ $stats['completed_orders'] }}">{{ $stats['completed_orders'] }}</h3>
                    <p class="text-muted mb-0 small">Completed Orders</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100 border-start border-info border-4">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                    <i class="bi bi-calendar-day text-info" style="font-size: 2.5rem;"></i>
                </div>
                <div>
                    <h3 class="mb-1 counter-number" data-count="{{ $stats['today_orders'] }}">{{ $stats['today_orders'] }}</h3>
                    <p class="text-muted mb-0 small">Today's Orders</p>
                    <small class="text-success fw-bold">₱{{ number_format($stats['today_sales'], 2) }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

@if($seller->verification_status === 'approved' && isset($revenueData) && isset($ordersData))
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('revenueChart')) {
        new Chart(document.getElementById('revenueChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($revenueData, 'date')) !!},
                datasets: [{
                    label: 'Revenue (₱)',
                    data: {!! json_encode(array_column($revenueData, 'revenue')) !!},
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
    if (document.getElementById('ordersChart')) {
        new Chart(document.getElementById('ordersChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_column($ordersData, 'date')) !!},
                datasets: [{
                    label: 'Orders',
                    data: {!! json_encode(array_column($ordersData, 'count')) !!},
                    backgroundColor: 'rgba(14, 165, 233, 0.6)',
                    borderColor: '#0ea5e9',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }
});
</script>
@endpush
@endif
@endsection
