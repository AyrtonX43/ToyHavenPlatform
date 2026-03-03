@extends('layouts.admin-new')

@section('title', 'Trade Dispute #' . $tradeDispute->id . ' - Moderator')
@section('page-title', 'Trade Dispute #' . $tradeDispute->id)

@section('content')
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('moderator.dashboard') }}">Moderator</a></li>
        <li class="breadcrumb-item"><a href="{{ route('moderator.trade-disputes.index') }}">Trade Disputes</a></li>
        <li class="breadcrumb-item active">Dispute #{{ $tradeDispute->id }}</li>
    </ol>
</nav>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@php $trade = $tradeDispute->trade; @endphp

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Dispute Details</h5>
                @php
                    $statusClass = match($tradeDispute->status) {
                        'open' => 'bg-warning text-dark',
                        'investigating' => 'bg-info',
                        'resolved', 'closed' => 'bg-success',
                        default => 'bg-secondary',
                    };
                @endphp
                <span class="badge {{ $statusClass }} fs-6">{{ $tradeDispute->getStatusLabel() }}</span>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <div class="text-muted small">Type</div>
                        <div class="fw-semibold">{{ $tradeDispute->getTypeLabel() }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">Reporter</div>
                        <div class="fw-semibold">{{ $tradeDispute->reporter->name ?? '—' }} ({{ $tradeDispute->reporter->email ?? '—' }})</div>
                    </div>
                    @if($tradeDispute->assignedTo)
                    <div class="col-sm-6 mt-2">
                        <div class="text-muted small">Assigned To</div>
                        <div class="fw-semibold">{{ $tradeDispute->assignedTo->name }}</div>
                    </div>
                    @endif
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Description</div>
                    <p class="mb-0">{{ $tradeDispute->description }}</p>
                </div>
                @if($tradeDispute->evidence_images && count($tradeDispute->evidence_images) > 0)
                <div>
                    <div class="text-muted small mb-2">Evidence</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($tradeDispute->evidence_images as $path)
                        <a href="{{ asset('storage/' . $path) }}" target="_blank" class="d-inline-block">
                            <img src="{{ asset('storage/' . $path) }}" alt="Evidence" style="max-width: 120px; max-height: 120px; object-fit: cover; border-radius: 8px; border: 1px solid #dee2e6;">
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Trade #{{ $trade->id }} – {{ $trade->tradeListing->title ?? 'Trade' }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Initiator</div>
                        <div>{{ $trade->initiator->name ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Participant</div>
                        <div>{{ $trade->participant->name ?? '—' }}</div>
                    </div>
                </div>
                <a href="{{ route('moderator.trades.show', $trade->id) }}" class="btn btn-outline-primary btn-sm">View full trade</a>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Actions</h5>
            </div>
            <div class="card-body">
                @if(!in_array($tradeDispute->status, ['resolved', 'closed']))
                    @if(!$tradeDispute->assigned_to || $tradeDispute->assigned_to === auth()->id())
                        @if($tradeDispute->status === 'open')
                        <form action="{{ route('moderator.trade-disputes.assign', $tradeDispute) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-person-check me-1"></i> Assign to Me
                            </button>
                        </form>
                        @endif

                        <hr>
                        <h6 class="mb-2">Resolve Dispute</h6>
                        <form action="{{ route('moderator.trade-disputes.resolve', $tradeDispute) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Resolution</label>
                                <select name="resolution" class="form-select" required>
                                    <option value="">Choose...</option>
                                    <option value="completed">Complete trade (both parties keep items)</option>
                                    <option value="cancelled">Cancel trade (return items to available)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes (optional)</label>
                                <textarea name="notes" class="form-control" rows="2" maxlength="1000" placeholder="Internal notes..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-circle me-1"></i> Resolve Dispute
                            </button>
                        </form>
                    @else
                        <p class="text-muted small mb-0">This dispute is assigned to {{ $tradeDispute->assignedTo->name }}.</p>
                        @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.trades.show', $trade->id) }}" class="btn btn-outline-primary btn-sm mt-2">Resolve in Admin Panel</a>
                        @endif
                    @endif
                @else
                    <div class="text-muted">
                        <p class="mb-1">Resolved: {{ $tradeDispute->resolution_type ?? '—' }}</p>
                        @if($tradeDispute->resolvedBy)
                        <p class="mb-0 small">By {{ $tradeDispute->resolvedBy->name }} on {{ $tradeDispute->resolved_at?->format('M d, Y H:i') }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
