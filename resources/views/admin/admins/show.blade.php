@extends('layouts.admin')

@section('title', 'Admin Account Details - ToyHaven')
@section('page-title', 'Admin Account Details')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Admin Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">ID:</th>
                        <td>#{{ $admin->id }}</td>
                    </tr>
                    <tr>
                        <th>Name:</th>
                        <td>{{ $admin->name }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $admin->email }}</td>
                    </tr>
                    <tr>
                        <th>Role:</th>
                        <td><span class="badge bg-danger">Admin</span></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            @if($admin->is_banned ?? false)
                                <span class="badge bg-danger">Banned</span>
                            @else
                                <span class="badge bg-success">Active</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Email Verified:</th>
                        <td>
                            @if($admin->email_verified_at)
                                <span class="badge bg-success">Yes</span>
                                <small class="text-muted">({{ $admin->email_verified_at->format('M d, Y H:i') }})</small>
                            @else
                                <span class="badge bg-warning">No</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Created:</th>
                        <td>{{ $admin->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Last Updated:</th>
                        <td>{{ $admin->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($admin->id !== auth()->id())
                        <a href="{{ route('admin.admins.edit', $admin->id) }}" class="btn btn-warning">
                            <i class="bi bi-pencil me-2"></i>Edit Account
                        </a>
                        <form action="{{ route('admin.admins.destroy', $admin->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this admin account?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-trash me-2"></i>Remove Admin
                            </button>
                        </form>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>This is your account. You can edit it from your profile page.
                        </div>
                    @endif
                    <a href="{{ route('admin.admins.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Statistics</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <th>Account Created:</th>
                        <td>{{ $stats['created_at']->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <th>Days Active:</th>
                        <td>{{ $stats['created_at']->diffInDays(now()) }} days</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
