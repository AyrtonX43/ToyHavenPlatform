@extends('layouts.admin-new')

@section('title', 'Edit Terms - ' . $plan->name)

@section('content')
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.plans.index') }}">Plans</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.plans.terms.index', $plan) }}">{{ $plan->name }} - Terms</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
    <h1 class="h3 mb-4">Edit Terms & Conditions: {{ $plan->name }}</h1>

    <form action="{{ route('admin.plans.terms.update', $plan) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Content</label>
                    <textarea name="content" class="form-control font-monospace" rows="16" required>{{ old('content', $terms?->content ?? '') }}</textarea>
                    <div class="form-text">Users must accept these terms before proceeding to payment. HTML is not rendered; plain text only.</div>
                    @error('content')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Version</label>
                        <input type="text" name="version" class="form-control" value="{{ old('version', $terms?->version ?? '1.0') }}" placeholder="e.g. 1.0">
                        @error('version')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Effective Date</label>
                        <input type="date" name="effective_at" class="form-control" value="{{ old('effective_at', $terms?->effective_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
                        @error('effective_at')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Save Terms</button>
                <a href="{{ route('admin.plans.terms.index', $plan) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
