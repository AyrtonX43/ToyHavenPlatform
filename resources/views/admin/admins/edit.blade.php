@extends('layouts.admin')

@section('title', 'Edit Admin Account - ToyHaven')
@section('page-title', 'Edit Admin Account')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Edit Admin Account: {{ $admin->name }}</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.admins.update', $admin->id) }}">
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
                            value="{{ old('name', $admin->name) }}" 
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
                            value="{{ old('email', $admin->email) }}" 
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

            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Warning:</strong> Changing the password will require the admin to use the new password on their next login.
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.admins.show', $admin->id) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>Update Admin Account
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
