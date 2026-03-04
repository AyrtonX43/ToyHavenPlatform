@extends('layouts.admin-new')

@section('title', 'Create Trade Moderator - ToyHaven')
@section('page-title', 'Create Trade Moderator')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Create New Trade Moderator</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.moderators.store') }}">
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
                            placeholder="Enter email address (login)">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Auction Permissions</label>
                <div class="card card-body bg-light">
                    @php $checked = old('moderator_permissions', []); @endphp
                    <div class="form-check">
                        <input type="checkbox" name="moderator_permissions[]" value="auctions_view" id="perm_auctions_view"
                               class="form-check-input" {{ in_array('auctions_view', $checked) ? 'checked' : '' }}>
                        <label class="form-check-label" for="perm_auctions_view">View Auctions</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="moderator_permissions[]" value="auctions_moderate" id="perm_auctions_moderate"
                               class="form-check-input" {{ in_array('auctions_moderate', $checked) ? 'checked' : '' }}>
                        <label class="form-check-label" for="perm_auctions_moderate">Approve/Reject Auctions</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="moderator_permissions[]" value="auction_reports_view" id="perm_auction_reports_view"
                               class="form-check-input" {{ in_array('auction_reports_view', $checked) ? 'checked' : '' }}>
                        <label class="form-check-label" for="perm_auction_reports_view">View Auction Reports</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="moderator_permissions[]" value="auction_sellers_view" id="perm_auction_sellers_view"
                               class="form-check-input" {{ in_array('auction_sellers_view', $checked) ? 'checked' : '' }}>
                        <label class="form-check-label" for="perm_auction_sellers_view">View Auction Sellers</label>
                    </div>
                </div>
                <small class="text-muted">Grant auction-related permissions to this moderator.</small>
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
                <strong>Note:</strong> The moderator will be able to access the moderator panel at <code>{{ url('/moderator/dashboard') }}</code> using their email and password. They can review trade listings, approve/reject them, view trades, and handle trade disputes. Share the credentials securely.
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.moderators.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-person-plus me-2"></i>Create Trade Moderator
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
