@extends('layouts.admin')

@section('title', 'Admin Dashboard - ToyHaven')
@section('page-title', 'Dashboard')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
    /* Visible divider and card borders for Pending Actions (Platform Overview) */
    .card.border-warning { border-width: 1px !important; border-color: var(--bs-warning) !important; }
    .card.border-info { border-width: 1px !important; border-color: var(--bs-info) !important; }
    .card.border-danger { border-width: 1px !important; border-color: var(--bs-danger) !important; }
    #pending-actions-row .card-body hr { border-width: 1px; opacity: 1; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Platform Overview</h4>
    <div>
        <span class="badge bg-primary">Last Updated: {{ now()->format('M d, Y h:i A') }}</span>
    </div>
</div>

<!-- Key Metrics -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <a href="{{ route('admin.users.index') }}" class="text-decoration-none">
            <div class="card text-white bg-primary" style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Total Users</h6>
                            <h2 class="mb-0 counter-number" data-count="{{ $stats['total_users'] }}">0</h2>
                            <small class="opacity-75">+{{ $stats['users_growth_30d'] }} this month</small>
                        </div>
                        <i class="bi bi-people" style="font-size: 3rem; opacity: 0.3; animation: float 3s ease-in-out infinite;"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="{{ route('admin.sellers.index') }}" class="text-decoration-none">
            <div class="card text-white bg-success" style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Total Sellers</h6>
                            <h2 class="mb-0 counter-number" data-count="{{ $stats['total_sellers'] }}">0</h2>
                            <small class="opacity-75">+{{ $stats['sellers_growth_30d'] }} this month</small>
                        </div>
                        <i class="bi bi-shop" style="font-size: 3rem; opacity: 0.3; animation: float 3s ease-in-out infinite;"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="{{ route('admin.products.index') }}" class="text-decoration-none">
            <div class="card text-white bg-info" style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Total Products</h6>
                            <h2 class="mb-0 counter-number" data-count="{{ $stats['total_products'] }}">0</h2>
                            <small class="opacity-75">+{{ $stats['products_growth_30d'] }} this month</small>
                        </div>
                        <i class="bi bi-box-seam" style="font-size: 3rem; opacity: 0.3; animation: float 3s ease-in-out infinite;"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="{{ route('admin.orders.index') }}" class="text-decoration-none">
            <div class="card text-white bg-warning" style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Total Orders</h6>
                            <h2 class="mb-0 counter-number" data-count="{{ $stats['total_orders'] }}">0</h2>
                            <small class="opacity-75">+{{ $stats['orders_growth_30d'] }} this month</small>
                        </div>
                        <i class="bi bi-cart-check" style="font-size: 3rem; opacity: 0.3; animation: float 3s ease-in-out infinite;"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Revenue Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card text-white" style="background: #0d9488;">
            <div class="card-body">
                <h6 class="card-title mb-2">Total Revenue</h6>
                <h2 class="mb-0 counter-currency" data-count="{{ $stats['total_revenue'] }}">₱0.00</h2>
                <small class="opacity-75">From all paid orders</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body">
                <h6 class="card-title mb-2">Today's Revenue</h6>
                <h2 class="mb-0 counter-currency" data-count="{{ $stats['today_revenue'] }}">₱0.00</h2>
                <small class="opacity-75">{{ $stats['today_orders'] }} orders today</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body">
                <h6 class="card-title mb-2">This Month's Revenue</h6>
                <h2 class="mb-0 counter-currency" data-count="{{ $stats['month_revenue'] }}">₱0.00</h2>
                <small class="opacity-75">Current month</small>
            </div>
        </div>
    </div>
</div>

<!-- Pending Actions -->
<div id="pending-actions-row" class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card border-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title text-danger mb-1">Pending Sellers</h5>
                        <h3 class="mb-0">{{ $stats['pending_sellers'] }}</h3>
                    </div>
                    <i class="bi bi-shop text-danger" style="font-size: 2.5rem; opacity: 0.3;"></i>
                </div>
                <hr class="my-3 border-danger border-opacity-100">
                <a href="{{ route('admin.sellers.index', ['status' => 'pending']) }}" class="btn btn-sm btn-danger text-white w-100">
                    <i class="bi bi-eye me-1"></i> Review Now
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title text-warning mb-1">Pending Products</h5>
                        <h3 class="mb-0">{{ $stats['pending_products'] }}</h3>
                    </div>
                    <i class="bi bi-box-seam text-warning" style="font-size: 2.5rem; opacity: 0.3;"></i>
                </div>
                <hr class="my-3 border-warning border-opacity-100">
                <a href="{{ route('admin.products.index', ['status' => 'pending']) }}" class="btn btn-sm btn-warning text-dark w-100">
                    <i class="bi bi-eye me-1"></i> Review Now
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title text-info mb-1">Pending Reports</h5>
                        <h3 class="mb-0">{{ $stats['pending_reports'] }}</h3>
                    </div>
                    <i class="bi bi-flag text-info" style="font-size: 2.5rem; opacity: 0.3;"></i>
                </div>
                <hr class="my-3 border-info border-opacity-100">
                <a href="{{ route('admin.reports.index', ['status' => 'pending']) }}" class="btn btn-sm btn-info text-white w-100">
                    <i class="bi bi-eye me-1"></i> Review Now
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Revenue Trend (Last 7 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Orders Trend (Last 7 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="ordersChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Orders</h5>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                                <tr>
                                    <td><a href="{{ route('admin.orders.show', $order->id) }}">{{ $order->order_number }}</a></td>
                                    <td>{{ $order->user->name }}</td>
                                    <td>₱{{ number_format($order->total, 2) }}</td>
                                    <td><span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">{{ $order->getStatusLabel() }}</span></td>
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No recent orders</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Activity</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @foreach($recentSellers->take(3) as $seller)
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>New Seller:</strong> {{ $seller->business_name }}
                                    <br><small class="text-muted">{{ $seller->user->name }} • {{ $seller->created_at->diffForHumans() }}</small>
                                </div>
                                <span class="badge bg-{{ $seller->verification_status === 'approved' ? 'success' : 'warning' }}">
                                    {{ ucfirst($seller->verification_status) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                    @foreach($recentProducts->take(2) as $product)
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>New Product:</strong> {{ $product->name }}
                                    <br><small class="text-muted">{{ $product->seller->business_name }} • {{ $product->created_at->diffForHumans() }}</small>
                                </div>
                                <span class="badge bg-{{ $product->status === 'active' ? 'success' : 'warning' }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                    @foreach($recentReports->take(2) as $report)
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>New Report:</strong> {{ $report->report_type }}
                                    <br><small class="text-muted">By {{ $report->reporter->name }} • {{ $report->created_at->diffForHumans() }}</small>
                                </div>
                                <span class="badge bg-{{ $report->status === 'pending' ? 'warning' : 'info' }}">
                                    {{ ucfirst($report->status) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($revenueData, 'date')) !!},
            datasets: [{
                label: 'Revenue (₱)',
                data: {!! json_encode(array_column($revenueData, 'revenue')) !!},
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Orders Chart
    const ordersCtx = document.getElementById('ordersChart').getContext('2d');
    new Chart(ordersCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($ordersData, 'date')) !!},
            datasets: [{
                label: 'Orders',
                data: {!! json_encode(array_column($ordersData, 'count')) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection
