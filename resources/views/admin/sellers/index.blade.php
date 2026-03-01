@extends('layouts.admin-new')

@section('title', 'Sellers Management - ToyHaven')
@section('page-title', 'Sellers Management')

@section('content')
<div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0 fw-bold">
            <i class="bi bi-funnel text-primary me-2"></i>Filter Sellers
        </h5>
    </div>
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.sellers.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold">
                    <i class="bi bi-search me-1"></i>Search
                </label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Business name, email, owner...">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="bi bi-check-circle me-1"></i>Status
                </label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="bi bi-toggle-on me-1"></i>Active Status
                </label>
                <select name="active" class="form-select">
                    <option value="">All</option>
                    <option value="1" {{ request('active') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('active') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="bi bi-calendar me-1"></i>Date From
                </label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="bi bi-calendar me-1"></i>Date To
                </label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100 fw-semibold">
                    <i class="bi bi-funnel-fill me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-shop text-primary me-2"></i>All Sellers
            </h5>
            <span class="badge bg-primary px-3 py-2">
                <i class="bi bi-people me-1"></i>{{ $sellers->total() }} Total
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 fw-bold">
                            <i class="bi bi-shop me-1"></i>Business Name
                        </th>
                        <th class="py-3 fw-bold">
                            <i class="bi bi-person me-1"></i>Owner
                        </th>
                        <th class="py-3 fw-bold">
                            <i class="bi bi-telephone me-1"></i>Contact
                        </th>
                        <th class="py-3 fw-bold">
                            <i class="bi bi-check-circle me-1"></i>Status
                        </th>
                        <th class="py-3 fw-bold text-center">
                            <i class="bi bi-box-seam me-1"></i>Products
                        </th>
                        <th class="py-3 fw-bold text-center">
                            <i class="bi bi-cart me-1"></i>Orders
                        </th>
                        <th class="py-3 fw-bold">
                            <i class="bi bi-star me-1"></i>Rating
                        </th>
                        <th class="py-3 fw-bold">
                            <i class="bi bi-calendar me-1"></i>Joined
                        </th>
                        <th class="py-3 fw-bold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sellers as $seller)
                        <tr class="border-bottom">
                            <td class="py-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px; min-width: 40px;">
                                        <i class="bi bi-shop text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ Str::limit($seller->business_name, 30) }}</div>
                                        @if($seller->is_verified_shop)
                                            <small class="badge bg-info-subtle text-info border border-info">
                                                <i class="bi bi-shield-check me-1"></i>Verified
                                            </small>
                                        @endif
                                        @if(!$seller->is_active)
                                            <small class="badge bg-secondary">
                                                <i class="bi bi-pause-circle me-1"></i>Suspended
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3">{{ $seller->user->name }}</td>
                            <td class="py-3">
                                <div class="small">
                                    <i class="bi bi-envelope me-1 text-muted"></i>{{ $seller->email ?? 'N/A' }}
                                </div>
                                <div class="small text-muted">
                                    <i class="bi bi-telephone me-1"></i>{{ $seller->phone ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="py-3">
                                <span class="badge bg-{{ $seller->verification_status === 'approved' ? 'success' : ($seller->verification_status === 'rejected' ? 'danger' : 'warning') }}-subtle text-{{ $seller->verification_status === 'approved' ? 'success' : ($seller->verification_status === 'rejected' ? 'danger' : 'warning') }} border border-{{ $seller->verification_status === 'approved' ? 'success' : ($seller->verification_status === 'rejected' ? 'danger' : 'warning') }} px-3 py-2">
                                    <i class="bi bi-{{ $seller->verification_status === 'approved' ? 'check-circle-fill' : ($seller->verification_status === 'rejected' ? 'x-circle-fill' : 'clock-fill') }} me-1"></i>
                                    {{ ucfirst($seller->verification_status) }}
                                </span>
                            </td>
                            <td class="py-3 text-center">
                                <span class="badge bg-info-subtle text-info border border-info px-3 py-2">
                                    <i class="bi bi-box-seam me-1"></i>{{ $seller->products_count ?? $seller->products->count() }}
                                </span>
                            </td>
                            <td class="py-3 text-center">
                                <span class="badge bg-primary-subtle text-primary border border-primary px-3 py-2">
                                    <i class="bi bi-cart-check me-1"></i>{{ $seller->orders_count ?? $seller->orders->count() }}
                                </span>
                            </td>
                            <td class="py-3">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-star-fill text-warning me-1"></i>
                                    <span class="fw-semibold">{{ number_format($seller->rating, 1) }}</span>
                                    <small class="text-muted ms-1">({{ $seller->total_reviews }})</small>
                                </div>
                            </td>
                            <td class="py-3 text-muted small">{{ $seller->created_at->format('M d, Y') }}</td>
                            <td class="py-3 text-center">
                                <a href="{{ route('admin.sellers.show', $seller->id) }}" class="btn btn-sm btn-primary" title="View Details">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                <p class="mb-0 mt-2">No sellers found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sellers->hasPages())
            <div class="mt-4 px-3">{{ $sellers->links() }}</div>
        @endif
    </div>
</div>

<style>
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.table-hover tbody tr:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.05);
}

.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.badge {
    font-weight: 500;
    letter-spacing: 0.3px;
}
</style>
@endsection
