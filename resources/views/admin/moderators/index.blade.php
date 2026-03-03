@extends('layouts.admin-new')

@section('title', 'Trade Moderators - ToyHaven')
@section('page-title', 'Trade Moderators')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Trade Moderators</h5>
        <a href="{{ route('admin.moderators.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-2"></i>Create Trade Moderator
        </a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.moderators.index') }}" class="row g-3 mb-3">
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
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>All Trade Moderators <span class="badge bg-primary">{{ $moderators->total() }}</span></h5>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">Moderators can access the moderator panel (dashboard, trade listings, trades, trade disputes, reports) by logging in with their email and password at <code>{{ url('/login') }}</code>.</p>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($moderators as $mod)
                        <tr>
                            <td>#{{ $mod->id }}</td>
                            <td><strong>{{ $mod->name }}</strong></td>
                            <td>{{ $mod->email }}</td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>{{ $mod->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.moderators.show', $mod->id) }}" class="btn btn-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.moderators.edit', $mod->id) }}" class="btn btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.moderators.destroy', $mod->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this moderator? They will lose moderator access.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" title="Remove">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No trade moderators yet. Create one to give them access to the moderator panel.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $moderators->links() }}</div>
    </div>
</div>
@endsection
