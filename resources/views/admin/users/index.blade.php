@extends('layouts.admin-new')

@section('title', 'User Management - ToyHaven')
@section('page-title', 'User Management')

@section('content')
{{-- Role Organizer: quick filter by role with counts --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Organize by Role</h5>
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            @php
                $currentRole = request('role');
                $queryWithoutRole = request()->except(['role', 'page']);
            @endphp
            <a href="{{ route('admin.users.index', $queryWithoutRole) }}"
               class="btn btn-{{ $currentRole === null || $currentRole === '' ? 'primary' : 'outline-primary' }} btn-sm">
                <i class="bi bi-people me-1"></i> All <span class="badge bg-{{ $currentRole === null || $currentRole === '' ? 'light text-dark' : 'primary' }} ms-1">{{ $roleCounts['all'] ?? 0 }}</span>
            </a>
            <a href="{{ route('admin.users.index', array_merge($queryWithoutRole, ['role' => 'customer'])) }}"
               class="btn btn-{{ $currentRole === 'customer' ? 'secondary' : 'outline-secondary' }} btn-sm">
                <i class="bi bi-person me-1"></i> Customer <span class="badge bg-{{ $currentRole === 'customer' ? 'light text-dark' : 'secondary' }} ms-1">{{ $roleCounts['customer'] ?? 0 }}</span>
            </a>
            <a href="{{ route('admin.users.index', array_merge($queryWithoutRole, ['role' => 'seller'])) }}"
               class="btn btn-{{ $currentRole === 'seller' ? 'info' : 'outline-info' }} btn-sm">
                <i class="bi bi-shop me-1"></i> Seller <span class="badge bg-{{ $currentRole === 'seller' ? 'light text-dark' : 'info' }} ms-1">{{ $roleCounts['seller'] ?? 0 }}</span>
            </a>
            <a href="{{ route('admin.users.index', array_merge($queryWithoutRole, ['role' => 'premium'])) }}"
               class="btn btn-{{ $currentRole === 'premium' ? 'warning' : 'outline-warning' }} btn-sm">
                <i class="bi bi-star me-1"></i> Premium <span class="badge bg-{{ $currentRole === 'premium' ? 'light text-dark' : 'warning' }} ms-1">{{ $roleCounts['premium'] ?? 0 }}</span>
            </a>
            <a href="{{ route('admin.users.index', array_merge($queryWithoutRole, ['role' => 'admin'])) }}"
               class="btn btn-{{ $currentRole === 'admin' ? 'danger' : 'outline-danger' }} btn-sm">
                <i class="bi bi-shield-check me-1"></i> Admin <span class="badge bg-{{ $currentRole === 'admin' ? 'light text-dark' : 'danger' }} ms-1">{{ $roleCounts['admin'] ?? 0 }}</span>
            </a>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter Users</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Name, email...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                    <option value="">All Roles</option>
                    <option value="customer" {{ request('role') == 'customer' ? 'selected' : '' }}>Customer</option>
                    <option value="seller" {{ request('role') == 'seller' ? 'selected' : '' }}>Seller</option>
                    <option value="premium" {{ request('role') == 'premium' ? 'selected' : '' }}>Premium</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="banned" class="form-select">
                    <option value="">All</option>
                    <option value="0" {{ request('banned') == '0' ? 'selected' : '' }}>Active</option>
                    <option value="1" {{ request('banned') == '1' ? 'selected' : '' }}>Banned</option>
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
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">
            <i class="bi bi-people me-2"></i>All Users
            @if(request('role'))
                <span class="text-muted fw-normal fs-6">({{ ucfirst(request('role')) }})</span>
            @endif
            <span class="badge bg-primary ms-2">{{ $users->total() }}</span>
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>#{{ $user->id }}</td>
                            <td>
                                <strong>{{ $user->name }}</strong>
                                @if($user->seller)
                                    <br><small class="text-muted">Seller: {{ $user->seller->business_name }}</small>
                                @endif
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @php
                                    $roleBadge = match($user->role ?? 'customer') {
                                        'admin' => 'danger',
                                        'seller' => 'primary',
                                        'premium' => 'warning',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $roleBadge }}">{{ ucfirst($user->role ?? 'customer') }}</span>
                            </td>
                            <td>
                                @if($user->is_banned ?? false)
                                    <span class="badge bg-danger">Banned</span>
                                    @if($user->banned_at)
                                        <br><small class="text-muted">{{ $user->banned_at->format('M d, Y') }}</small>
                                    @endif
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No users found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $users->links() }}</div>
    </div>
</div>
@endsection
