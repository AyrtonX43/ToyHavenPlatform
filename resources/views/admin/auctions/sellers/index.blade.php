@extends('layouts.admin-new')

@section('title', 'Auction Seller Management - ToyHaven')
@section('page-title', 'Auction Seller Management')

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel me-1"></i>Filter Auction Sellers</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.auction-sellers.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Name, email, business...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Seller Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="individual" {{ request('type') == 'individual' ? 'selected' : '' }}>Individual</option>
                    <option value="business" {{ request('type') == 'business' ? 'selected' : '' }}>Business</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Sync Status</label>
                <select name="sync" class="form-select">
                    <option value="">All</option>
                    <option value="synced" {{ request('sync') == 'synced' ? 'selected' : '' }}>Synced with ToyShop</option>
                    <option value="auction_only" {{ request('sync') == 'auction_only' ? 'selected' : '' }}>Auction Only</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="suspended" class="form-select">
                    <option value="">All</option>
                    <option value="0" {{ request('suspended') === '0' ? 'selected' : '' }}>Active</option>
                    <option value="1" {{ request('suspended') === '1' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end gap-1">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <a href="{{ route('admin.auction-sellers.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Approved Auction Sellers ({{ $sellers->total() }})</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Seller Name</th>
                        <th>Owner</th>
                        <th>Type</th>
                        <th>ToyShop Sync</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Verified</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sellers as $seller)
                        <tr>
                            <td>
                                <strong>{{ $seller->getDisplayName() }}</strong>
                                @if($seller->is_suspended)
                                    <span class="badge bg-secondary ms-1">Suspended</span>
                                @endif
                            </td>
                            <td>{{ $seller->user->name }}</td>
                            <td>
                                <span class="badge bg-{{ $seller->seller_type === 'business' ? 'success' : 'primary' }}">
                                    <i class="bi bi-{{ $seller->seller_type === 'business' ? 'building' : 'person' }} me-1"></i>{{ ucfirst($seller->seller_type) }}
                                </span>
                            </td>
                            <td>
                                @if($seller->isSynced())
                                    <span class="badge bg-info">
                                        <i class="bi bi-link-45deg me-1"></i>Synced
                                    </span>
                                    <br><small class="text-muted">{{ $seller->seller->business_name ?? 'N/A' }}</small>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-hammer me-1"></i>Auction Only
                                    </span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $seller->user->email }}</small><br>
                                <small class="text-muted">{{ $seller->phone ?? 'N/A' }}</small>
                            </td>
                            <td>
                                @if($seller->is_suspended)
                                    <span class="badge bg-danger">Suspended</span>
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            </td>
                            <td>{{ $seller->verified_at ? $seller->verified_at->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                <a href="{{ route('admin.auction-sellers.show', $seller) }}" class="btn btn-sm btn-primary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No approved auction sellers found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $sellers->links() }}</div>
    </div>
</div>
@endsection
