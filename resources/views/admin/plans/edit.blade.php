@extends('layouts.admin-new')

@section('title', 'Edit Plan: ' . $plan->name)

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Edit Plan: {{ $plan->name }}</h1>
    <form action="{{ route('admin.plans.update', $plan) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card mb-4">
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
                <div class="mb-3">
                    <label class="form-label">Features</label>
                    <textarea name="features" class="form-control" rows="6" placeholder="One feature per line">{{ old('features', $plan->features ? implode("\n", $plan->features) : '') }}</textarea>
                    <small class="text-muted">Enter one feature per line.</small>
                    @error('features')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">Capabilities</label>
                    <div class="form-check">
                        <input type="hidden" name="can_register_individual_seller" value="0">
                        <input type="checkbox" name="can_register_individual_seller" id="can_individual" value="1" class="form-check-input" {{ old('can_register_individual_seller', $plan->can_register_individual_seller) ? 'checked' : '' }}>
                        <label class="form-check-label" for="can_individual">Can register as Individual seller</label>
                    </div>
                    <div class="form-check">
                        <input type="hidden" name="can_register_business_seller" value="0">
                        <input type="checkbox" name="can_register_business_seller" id="can_business" value="1" class="form-check-input" {{ old('can_register_business_seller', $plan->can_register_business_seller) ? 'checked' : '' }}>
                        <label class="form-check-label" for="can_business">Can register as Business store seller</label>
                    </div>
                    <div class="form-check">
                        <input type="hidden" name="has_analytics_dashboard" value="0">
                        <input type="checkbox" name="has_analytics_dashboard" id="has_analytics" value="1" class="form-check-input" {{ old('has_analytics_dashboard', $plan->has_analytics_dashboard) ? 'checked' : '' }}>
                        <label class="form-check-label" for="has_analytics">Has analytics dashboard</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Update Plan</button>
                <a href="{{ route('admin.plans.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
