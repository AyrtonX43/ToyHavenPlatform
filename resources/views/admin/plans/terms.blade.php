@extends('layouts.admin-new')

@section('title', 'Edit Terms: ' . $plan->name)

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Edit Terms & Conditions: {{ $plan->name }}</h1>
    <form action="{{ route('admin.plans.terms.update', $plan) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Terms Content</label>
                    <textarea name="content" class="form-control font-monospace" rows="20" required>{{ old('content', $terms?->content ?? '') }}</textarea>
                    <small class="text-muted">HTML is allowed. Use headings (&lt;h5&gt;), lists (&lt;ul&gt;, &lt;li&gt;), paragraphs (&lt;p&gt;), and &lt;strong&gt; for formatting.</small>
                    @error('content')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                @if($terms)
                    <p class="text-muted small mb-3">Current version: {{ $terms->version }} (effective {{ $terms->effective_at?->format('M j, Y') }})</p>
                @endif
                <button type="submit" class="btn btn-primary">Save Terms</button>
                <a href="{{ route('admin.plans.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
