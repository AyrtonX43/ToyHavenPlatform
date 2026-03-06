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
                <button type="submit" class="btn btn-primary">Update Plan</button>
                <a href="{{ route('admin.plans.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
