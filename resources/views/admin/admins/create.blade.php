@extends('layouts.admin-new')

@section('title', 'Create Admin Account - ToyHaven')
@section('page-title', 'Create Admin Account')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Create New Admin Account</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.admins.store') }}">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            name="name" 
                            id="name"
                            class="form-control @error('name') is-invalid @enderror" 
                            value="{{ old('name') }}" 
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
                            value="{{ old('email') }}" 
                            required
                            placeholder="Enter email address">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input 
                            type="password" 
                            name="password" 
                            id="password"
                            class="form-control @error('password') is-invalid @enderror" 
                            required
                            placeholder="Create a password">
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
                        <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input 
                            type="password" 
                            name="password_confirmation" 
                            id="password_confirmation"
                            class="form-control @error('password_confirmation') is-invalid @enderror" 
                            required
                            placeholder="Confirm password">
                        @error('password_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Note:</strong> The admin account will be created with full administrative privileges. 
                Make sure to securely share the credentials with the new admin.
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.admins.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-person-plus me-2"></i>Create Admin Account
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
