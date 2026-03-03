@extends('layouts.seller-new')

@section('title', 'Team / Moderators - ToyHaven')

@section('page-title', 'Team & Moderators')

@section('content')
<x-seller.page-header
    title="Team & Moderators"
    subtitle="Add team members who can log in and access your seller dashboard with specific permissions"
>
    <x-slot:actions>
        <a href="{{ route('seller.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </x-slot:actions>
</x-seller.page-header>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Add Moderator</h5>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">Moderators must have an existing account. Enter their email to invite them. They will log in through the website and access your seller dashboard based on the permissions you assign.</p>
        <form action="{{ route('seller.moderators.store') }}" method="POST" class="row g-3">
            @csrf
            <div class="col-md-4">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                       placeholder="moderator@example.com" value="{{ old('email') }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-5">
                <label class="form-label">Permissions <span class="text-danger">*</span></label>
                <div class="d-flex flex-wrap gap-3">
                    @foreach(\App\Models\SellerModerator::validPermissions() as $perm)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $perm }}" 
                                   id="perm_{{ $perm }}" {{ in_array($perm, old('permissions', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="perm_{{ $perm }}">
                                @if($perm === 'products') Products
                                @elseif($perm === 'orders') Orders
                                @else Business Page
                                @endif
                            </label>
                        </div>
                    @endforeach
                </div>
                <small class="text-muted">Select what they can access</small>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Add Moderator
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Current Moderators</h5>
    </div>
    <div class="card-body">
        @if($moderators->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Permissions</th>
                            <th>Added</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($moderators as $mod)
                            <tr>
                                <td>{{ $mod->user->name }}</td>
                                <td>{{ $mod->user->email }}</td>
                                <td>
                                    @foreach($mod->permissions ?? [] as $p)
                                        <span class="badge bg-primary me-1">
                                            @if($p === 'products') Products
                                            @elseif($p === 'orders') Orders
                                            @else Business Page
                                            @endif
                                        </span>
                                    @endforeach
                                </td>
                                <td>{{ $mod->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="#" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $mod->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('seller.moderators.destroy', $mod) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this moderator?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal{{ $mod->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('seller.moderators.update', $mod) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Permissions - {{ $mod->user->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-0">
                                                    @foreach(\App\Models\SellerModerator::validPermissions() as $perm)
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $perm }}" 
                                                                   id="edit_perm_{{ $mod->id }}_{{ $perm }}" {{ $mod->hasPermission($perm) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="edit_perm_{{ $mod->id }}_{{ $perm }}">
                                                                @if($perm === 'products') Products
                                                                @elseif($perm === 'orders') Orders
                                                                @else Business Page
                                                                @endif
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Save</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4 text-muted">
                <i class="bi bi-people" style="font-size: 3rem;"></i>
                <p class="mb-0 mt-2">No moderators yet. Add one above.</p>
            </div>
        @endif
    </div>
</div>
@endsection
