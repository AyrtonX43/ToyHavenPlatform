@extends('layouts.admin-new')

@section('title', 'Moderator Details - ToyHaven')
@section('page-title', 'Moderator Details')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Moderator Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">ID:</th>
                        <td>#{{ $moderator->id }}</td>
                    </tr>
                    <tr>
                        <th>Name:</th>
                        <td>{{ $moderator->name }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $moderator->email }}</td>
                    </tr>
                    <tr>
                        <th>Role:</th>
                        <td><span class="badge bg-info">Trade Moderator</span></td>
                    </tr>
                    <tr>
                        <th>Login URL:</th>
                        <td><code>{{ url('/login') }}</code></td>
                    </tr>
                    <tr>
                        <th>Moderator Panel:</th>
                        <td><a href="{{ url('/moderator/dashboard') }}" target="_blank">{{ url('/moderator/dashboard') }}</a></td>
                    </tr>
                    <tr>
                        <th>Created:</th>
                        <td>{{ $moderator->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Last Updated:</th>
                        <td>{{ $moderator->updated_at->format('M d, Y H:i') }}</td>
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
                    <a href="{{ route('admin.moderators.edit', $moderator->id) }}" class="btn btn-warning">
                        <i class="bi bi-pencil me-2"></i>Edit Account
                    </a>
                    <form action="{{ route('admin.moderators.destroy', $moderator->id) }}" method="POST" onsubmit="return confirm('Remove this moderator? They will lose access to the moderator panel.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash me-2"></i>Remove Moderator
                        </button>
                    </form>
                    <a href="{{ route('admin.moderators.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
