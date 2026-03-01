@extends('layouts.admin-new')

@section('title', 'Analytics Dashboard - ToyHaven')
@section('page-title', 'Analytics Dashboard')

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filter Period</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.analytics.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Period (Days)</label>
                <select name="period" class="form-select">
                    <option value="7" {{ $period == '7' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="30" {{ $period == '30' ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="90" {{ $period == '90' ? 'selected' : '' }}>Last 90 Days</option>
                    <option value="180" {{ $period == '180' ? 'selected' : '' }}>Last 6 Months</option>
                    <option value="365" {{ $period == '365' ? 'selected' : '' }}>Last Year</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Group By</label>
                <select name="group_by" class="form-select">
                    <option value="day" {{ $groupBy == 'day' ? 'selected' : '' }}>Day</option>
                    <option value="week" {{ $groupBy == 'week' ? 'selected' : '' }}>Week</option>
                    <option value="month" {{ $groupBy == 'month' ? 'selected' : '' }}>Month</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Apply</button>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h6 class="card-title">Total Users</h6>
                <h2 class="mb-0">{{ number_format($stats['total_users']) }}</h2>
                <small>+{{ $stats['users_growth_30d'] }} this month</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h6 class="card-title">Total Sellers</h6>
                <h2 class="mb-0">{{ number_format($stats['total_sellers']) }}</h2>
                <small>+{{ $stats['sellers_growth_30d'] }} this month</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h6 class="card-title">Total Products</h6>
                <h2 class="mb-0">{{ number_format($stats['total_products']) }}</h2>
                <small>+{{ $stats['products_growth_30d'] }} this month</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h6 class="card-title">Revenue (30d)</h6>
                <h2 class="mb-0">₱{{ number_format($stats['revenue_30d'], 2) }}</h2>
                <small>Last 30 days</small>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">User Growth</h5>
            </div>
            <div class="card-body">
                <canvas id="userGrowthChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Revenue Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Top Selling Products</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Sold</th>
                                <th>Seller</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $product)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.products.show', $product->id) }}">{{ Str::limit($product->name, 30) }}</a>
                                    </td>
                                    <td><span class="badge bg-success">{{ $product->total_sold ?? 0 }}</span></td>
                                    <td>{{ Str::limit($product->seller->business_name, 20) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Top Sellers by Revenue</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Seller</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSellers as $seller)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.sellers.show', $seller->id) }}">{{ Str::limit($seller->business_name, 30) }}</a>
                                    </td>
                                    <td><strong>₱{{ number_format($seller->total_revenue ?? 0, 2) }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // User Growth Chart
    const userCtx = document.getElementById('userGrowthChart').getContext('2d');
    new Chart(userCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($userGrowth, 'label')) !!},
            datasets: [{
                label: 'New Users',
                data: {!! json_encode(array_column($userGrowth, 'count')) !!},
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
                    display: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($revenueData, 'label')) !!},
            datasets: [{
                label: 'Revenue (₱)',
                data: {!! json_encode(array_column($revenueData, 'revenue')) !!},
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true
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
</script>
@endpush
@endsection
