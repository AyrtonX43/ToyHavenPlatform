@extends('layouts.admin-new')

@section('title', 'Terms & Conditions - ' . $plan->name)

@section('content')
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.plans.index') }}">Plans</a></li>
            <li class="breadcrumb-item active">{{ $plan->name }} - Terms</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Terms & Conditions: {{ $plan->name }}</h1>
        <a href="{{ route('admin.plans.terms.edit', $plan) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit Terms
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($terms->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <p class="text-muted mb-3">No terms have been set for this plan yet.</p>
                <a href="{{ route('admin.plans.terms.edit', $plan) }}" class="btn btn-primary">Add Terms</a>
            </div>
        </div>
    @else
        <div class="card mb-4">
            <div class="card-header">
                <strong>Current Terms</strong>
                @if($terms->first())
                    <span class="badge bg-secondary ms-2">Version {{ $terms->first()->version }}</span>
                    @if($terms->first()->effective_at)
                        <span class="text-muted ms-2">Effective {{ $terms->first()->effective_at->format('M j, Y') }}</span>
                    @endif
                @endif
            </div>
            <div class="card-body">
                @if($terms->first())
                    <div class="terms-preview" style="white-space: pre-wrap;">{{ $terms->first()->content }}</div>
                @endif
            </div>
        </div>

        @if($terms->count() > 1)
            <div class="card">
                <div class="card-header">Version History</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Version</th>
                                <th>Effective</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($terms as $t)
                                <tr>
                                    <td>{{ $t->version }}</td>
                                    <td>{{ $t->effective_at?->format('M j, Y') ?? '-' }}</td>
                                    <td>{{ $t->updated_at->format('M j, Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
</div>
@endsection
