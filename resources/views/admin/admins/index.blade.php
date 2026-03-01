@extends('layouts.admin-new')

@section('title', 'Admin Account Management - ToyHaven')
@section('page-title', 'Admin Account Management')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-shield-check me-2"></i>Admin Accounts</h5>
        <a href="{{ route('admin.admins.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-2"></i>Create New Admin
        </a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.admins.index') }}" class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Name, email...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>All Admin Accounts <span class="badge bg-primary">{{ $admins->total() }}</span></h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admins as $admin)
                        <tr>
                            <td>#{{ $admin->id }}</td>
                            <td>
                                <strong>{{ $admin->name }}</strong>
                                @if($admin->id === auth()->id())
                                    <span class="badge bg-info ms-2">You</span>
                                @endif
                            </td>
                            <td>{{ $admin->email }}</td>
                            <td>
                                @if($admin->is_banned ?? false)
                                    <span class="badge bg-danger">Banned</span>
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            </td>
                            <td>{{ $admin->created_at->format('M d, Y') }}</td>
                            <td>{{ $admin->updated_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.admins.show', $admin->id) }}" class="btn btn-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($admin->id !== auth()->id())
                                        <a href="{{ route('admin.admins.edit', $admin->id) }}" class="btn btn-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.admins.destroy', $admin->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this admin account?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="Remove">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No admin accounts found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $admins->links() }}</div>
    </div>
</div>
@endsection
