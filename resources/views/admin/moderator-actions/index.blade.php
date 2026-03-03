@extends('layouts.admin-new')

@section('title', 'Moderator Actions - Admin')
@section('page-title', 'Moderator Actions Audit')

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.moderator-actions.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Moderator</label>
                <select name="moderator_id" class="form-select">
                    <option value="">All</option>
                    @foreach($moderators as $m)
                    <option value="{{ $m->id }}" {{ request('moderator_id') == $m->id ? 'selected' : '' }}>{{ $m->name }} ({{ $m->role }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Action Type</label>
                <select name="action_type" class="form-select">
                    <option value="">All</option>
                    @foreach($actionTypes as $type)
                    <option value="{{ $type }}" {{ request('action_type') == $type ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($type)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Filter</button>
                <a href="{{ route('admin.moderator-actions.index') }}" class="btn btn-outline-secondary ms-2">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Moderator Actions ({{ $actions->total() }})</h5>
        <small class="text-muted">Audit log of moderator actions on trades and reports. Admin can override any decision.</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Moderator</th>
                        <th>Action</th>
                        <th>Target</th>
                        <th>Description</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($actions as $action)
                    <tr>
                        <td class="align-middle"><small>{{ $action->created_at->format('M d, Y H:i') }}</small></td>
                        <td class="align-middle">{{ $action->moderator->name ?? '—' }}</td>
                        <td class="align-middle">
                            <span class="badge bg-info">{{ str_replace('_', ' ', ucfirst($action->action_type)) }}</span>
                        </td>
                        <td class="align-middle">
                            {{ class_basename($action->actionable_type) }} #{{ $action->actionable_id }}
                        </td>
                        <td class="align-middle">{{ Str::limit($action->description ?? '—', 50) }}</td>
                        <td class="align-middle"><small class="text-muted">{{ $action->ip_address ?? '—' }}</small></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">No moderator actions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $actions->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
