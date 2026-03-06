@extends('layouts.admin-new')

@section('title', 'Edit Trade Moderator - ToyHaven')
@section('page-title', 'Edit Trade Moderator')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Edit Moderator: {{ $moderator->name }}</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.moderators.update', $moderator->id) }}">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            name="name" 
                            id="name"
                            class="form-control @error('name') is-invalid @enderror" 
                            value="{{ old('name', $moderator->name) }}" 
                            required 
                            autofocus
                            placeholder="Enter full name"
                            pattern="[A-Za-z\s]+"
                            oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Letters and spaces only</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input 
                            type="email" 
                            name="email" 
                            id="email"
                            class="form-control @error('email') is-invalid @enderror" 
                            value="{{ old('email', $moderator->email) }}" 
                            required
                            placeholder="Enter email address (login)">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password <span class="text-muted">(Leave blank to keep current)</span></label>
                        <input 
                            type="password" 
                            name="password" 
                            id="password"
                            class="form-control @error('password') is-invalid @enderror" 
                            placeholder="Enter new password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            Must contain: 8+ characters, uppercase, lowercase, number, special character
                        </small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input 
                            type="password" 
                            name="password_confirmation" 
                            id="password_confirmation"
                            class="form-control @error('password_confirmation') is-invalid @enderror" 
                            placeholder="Confirm new password">
                        @error('password_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Platform permissions</label>
                <p class="text-muted small mb-2">Grant access to auction and plan management (optional).</p>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="moderator_permissions[]" value="auctions_view" id="perm_auctions_view" {{ in_array('auctions_view', old('moderator_permissions', $moderator->moderator_permissions ?? [])) ? 'checked' : '' }}>
                    <label class="form-check-label" for="perm_auctions_view">View auctions</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="moderator_permissions[]" value="auctions_moderate" id="perm_auctions_moderate" {{ in_array('auctions_moderate', old('moderator_permissions', $moderator->moderator_permissions ?? [])) ? 'checked' : '' }}>
                    <label class="form-check-label" for="perm_auctions_moderate">Moderate auctions (approve/reject listings)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="moderator_permissions[]" value="auction_payments_moderate" id="perm_auction_payments" {{ in_array('auction_payments_moderate', old('moderator_permissions', $moderator->moderator_permissions ?? [])) ? 'checked' : '' }}>
                    <label class="form-check-label" for="perm_auction_payments">Moderate auction payments (release escrow)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="moderator_permissions[]" value="auction_sellers_moderate" id="perm_auction_sellers" {{ in_array('auction_sellers_moderate', old('moderator_permissions', $moderator->moderator_permissions ?? [])) ? 'checked' : '' }}>
                    <label class="form-check-label" for="perm_auction_sellers">Moderate auction sellers</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="moderator_permissions[]" value="plans_manage" id="perm_plans_manage" {{ in_array('plans_manage', old('moderator_permissions', $moderator->moderator_permissions ?? [])) ? 'checked' : '' }}>
                    <label class="form-check-label" for="perm_plans_manage">Manage membership plans (edit price & description)</label>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.moderators.show', $moderator->id) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>Update Moderator
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
