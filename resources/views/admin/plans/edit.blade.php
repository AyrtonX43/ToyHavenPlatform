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
                    <div class="form-text">Enter one feature per line. These appear on the membership plan cards.</div>
                    @error('features')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Capabilities</label>
                    <div class="form-check">
                        <input type="checkbox" name="has_analytics_dashboard" value="1" class="form-check-input" id="cap_analytics" {{ old('has_analytics_dashboard', $plan->has_analytics_dashboard) ? 'checked' : '' }}>
                        <label class="form-check-label" for="cap_analytics">Analytics dashboard</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="can_register_individual_seller" value="1" class="form-check-input" id="cap_individual" {{ old('can_register_individual_seller', $plan->can_register_individual_seller) ? 'checked' : '' }}>
                        <label class="form-check-label" for="cap_individual">Can register as Individual seller</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="can_register_business_seller" value="1" class="form-check-input" id="cap_business" {{ old('can_register_business_seller', $plan->can_register_business_seller) ? 'checked' : '' }}>
                        <label class="form-check-label" for="cap_business">Can register as Business store seller</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Update Plan</button>
                <a href="{{ route('admin.plans.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
