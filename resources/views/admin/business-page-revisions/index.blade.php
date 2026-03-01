@extends('layouts.admin-new')

@section('title', 'Business Page Approvals - ToyHaven')
@section('page-title', 'Business Page Approvals')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.business-page-revisions.index') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="">All types</option>
                    <option value="general" {{ request('type') == 'general' ? 'selected' : '' }}>General Settings</option>
                    <option value="contact" {{ request('type') == 'contact' ? 'selected' : '' }}>Contact Info</option>
                    <option value="social" {{ request('type') == 'social' ? 'selected' : '' }}>Social Links</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Pending Business Page Changes ({{ $revisions->total() }})</h5>
    </div>
    <div class="card-body">
        @if($revisions->isEmpty())
            <p class="text-muted mb-0">No pending business page changes.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Submitted</th>
                            <th>Seller / Business</th>
                            <th>Type</th>
                            <th>Summary</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($revisions as $revision)
                            <tr>
                                <td>{{ $revision->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <strong>{{ $revision->seller->business_name }}</strong><br>
                                    <small class="text-muted">{{ $revision->seller->user->name }}</small>
                                </td>
                                <td>
                                    @if($revision->type === 'general')
                                        <span class="badge bg-primary">General</span>
                                    @elseif($revision->type === 'contact')
                                        <span class="badge bg-info">Contact</span>
                                    @else
                                        <span class="badge bg-secondary">Social</span>
                                    @endif
                                </td>
                                <td>
                                    @if($revision->type === 'general')
                                        @if(!empty($revision->payload['page_name']))
                                            Page: {{ Str::limit($revision->payload['page_name'], 30) }}
                                        @endif
                                        @if(!empty($revision->payload['logo_path'])) <br><small>New logo</small> @endif
                                        @if(!empty($revision->payload['banner_path'])) <br><small>New banner</small> @endif
                                    @elseif($revision->type === 'contact')
                                        @if(!empty($revision->payload['email']))
                                            Email: {{ Str::limit($revision->payload['email'], 25) }}
                                        @endif
                                        @if(!empty($revision->payload['phone']))
                                            <br>Phone: {{ $revision->payload['phone'] }}
                                        @endif
                                    @else
                                        {{ count($revision->payload['social_links'] ?? []) }} link(s)
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.business-page-revisions.show', $revision) }}" class="btn btn-sm btn-outline-primary me-1">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.business-page-revisions.approve', $revision) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success me-1">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $revision->id }}">
                                        <i class="bi bi-x-lg"></i> Reject
                                    </button>

                                    <div class="modal fade" id="rejectModal{{ $revision->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.business-page-revisions.reject', $revision) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Reject Business Page Changes</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Reject changes from <strong>{{ $revision->seller->business_name }}</strong> ({{ $revision->type }})?</p>
                                                        <label class="form-label">Reason (optional)</label>
                                                        <textarea name="rejection_reason" class="form-control" rows="2" placeholder="Optional reason for rejection"></textarea>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Reject</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $revisions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
