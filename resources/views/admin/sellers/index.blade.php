@extends('layouts.admin')

@section('title', 'Sellers Management - ToyHaven')
@section('page-title', 'Sellers Management')

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filter Sellers</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.sellers.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Business name, email, owner...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Active Status</label>
                <select name="active" class="form-select">
                    <option value="">All</option>
                    <option value="1" {{ request('active') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('active') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All Sellers ({{ $sellers->total() }})</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Business Name</th>
                        <th>Owner</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Products</th>
                        <th>Orders</th>
                        <th>Rating</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sellers as $seller)
                        <tr>
                            <td>
                                <strong>{{ $seller->business_name }}</strong>
                                @if(!$seller->is_active)
                                    <span class="badge bg-secondary">Suspended</span>
                                @endif
                            </td>
                            <td>{{ $seller->user->name }}</td>
                            <td>
                                <small>{{ $seller->email ?? 'N/A' }}</small><br>
                                <small class="text-muted">{{ $seller->phone ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $seller->verification_status === 'approved' ? 'success' : ($seller->verification_status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($seller->verification_status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $seller->products_count ?? $seller->products->count() }}</span>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $seller->orders_count ?? $seller->orders->count() }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-star-fill text-warning me-1"></i>
                                    {{ number_format($seller->rating, 1) }}
                                    <small class="text-muted ms-1">({{ $seller->total_reviews }})</small>
                                </div>
                            </td>
                            <td>{{ $seller->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.sellers.show', $seller->id) }}" class="btn btn-primary" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No sellers found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $sellers->links() }}</div>
    </div>
</div>
@endsection
