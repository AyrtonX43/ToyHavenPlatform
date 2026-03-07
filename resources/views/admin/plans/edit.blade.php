@extends('layouts.admin-new')

@section('title', 'Edit Plan: ' . $plan->name)

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Edit Plan: {{ $plan->name }}</h1>
    <form action="{{ route('admin.plans.update', $plan) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-header"><strong>Basic Details</strong></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $plan->name) }}" required>
                    @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Price (₱)</label>
                    <input type="number" name="price" class="form-control" step="0.01" min="0" value="{{ old('price', $plan->price) }}" required>
                    @error('price')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description', $plan->description) }}</textarea>
                    @error('description')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><strong>Features</strong></div>
            <div class="card-body">
                <p class="text-muted small mb-3">One feature per line. Empty lines are ignored.</p>
                <div id="features-container">
                    @php $features = old('features', $plan->features ?? []); @endphp
                    @if(!empty($features) && is_array($features))
                        @foreach($features as $idx => $f)
                            <div class="input-group mb-2 feature-row">
                                <input type="text" name="features[]" class="form-control" value="{{ $f }}" placeholder="Feature {{ $idx + 1 }}">
                                <button type="button" class="btn btn-outline-danger remove-feature" title="Remove"><i class="bi bi-dash-lg"></i></button>
                            </div>
                        @endforeach
                    @else
                        <div class="input-group mb-2 feature-row">
                            <input type="text" name="features[]" class="form-control" placeholder="Feature 1">
                            <button type="button" class="btn btn-outline-danger remove-feature" title="Remove"><i class="bi bi-dash-lg"></i></button>
                        </div>
                    @endif
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="add-feature">
                    <i class="bi bi-plus-lg me-1"></i> Add Feature
                </button>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><strong>Capabilities</strong></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="can_register_individual_seller" value="1" id="can_individual" {{ old('can_register_individual_seller', $plan->can_register_individual_seller ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="can_individual">Can register Individual Toyshop Seller</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="can_register_business_seller" value="1" id="can_business" {{ old('can_register_business_seller', $plan->can_register_business_seller ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="can_business">Can register Business Toyshop Seller</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="has_analytics_dashboard" value="1" id="has_analytics" {{ old('has_analytics_dashboard', $plan->has_analytics_dashboard ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="has_analytics">Has Analytics Dashboard</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="can_register_individual_auction_seller" value="1" id="can_individual_auction" {{ old('can_register_individual_auction_seller', $plan->can_register_individual_auction_seller ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="can_individual_auction">Can register Individual Auction Seller</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="can_register_business_auction_seller" value="1" id="can_business_auction" {{ old('can_register_business_auction_seller', $plan->can_register_business_auction_seller ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="can_business_auction">Can register Business Auction Seller</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><strong>Terms &amp; Conditions</strong></div>
            <div class="card-body">
                <p class="text-muted small mb-3">Add or update terms. Saving creates a new version with today's date. Users will see the latest version before payment.</p>
                <textarea name="terms_content" class="form-control font-monospace" rows="16" placeholder="Enter terms content (HTML supported)...">{{ old('terms_content', $plan->latestTerms?->content) }}</textarea>
                @error('terms_content')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Update Plan</button>
            <a href="{{ route('admin.plans.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var container = document.getElementById('features-container');
    var addBtn = document.getElementById('add-feature');

    addBtn.addEventListener('click', function() {
        var count = container.querySelectorAll('.feature-row').length + 1;
        var html = '<div class="input-group mb-2 feature-row">' +
            '<input type="text" name="features[]" class="form-control" placeholder="Feature ' + count + '">' +
            '<button type="button" class="btn btn-outline-danger remove-feature" title="Remove"><i class="bi bi-dash-lg"></i></button>' +
            '</div>';
        container.insertAdjacentHTML('beforeend', html);
    });

    container.addEventListener('click', function(e) {
        if (e.target.closest('.remove-feature')) {
            var row = e.target.closest('.feature-row');
            if (container.querySelectorAll('.feature-row').length > 1) {
                row.remove();
            }
        }
    });
});
</script>
@endpush
@endsection
