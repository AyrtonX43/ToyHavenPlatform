@extends('layouts.admin')

@section('title', 'Review Business Page Change - ToyHaven')
@section('page-title', 'Review Business Page Change')

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
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Seller</h5>
        <a href="{{ route('admin.sellers.show', $revision->seller->id) }}" class="btn btn-sm btn-outline-primary">View Seller</a>
    </div>
    <div class="card-body">
        <p class="mb-1"><strong>Business:</strong> {{ $revision->seller->business_name }}</p>
        <p class="mb-1"><strong>Owner:</strong> {{ $revision->seller->user->name }}</p>
        <p class="mb-0"><strong>Submitted:</strong> {{ $revision->created_at->format('M d, Y H:i') }}</p>
        <p class="mb-0"><strong>Type:</strong>
            @if($revision->type === 'general')
                <span class="badge bg-primary">General Settings</span>
            @elseif($revision->type === 'contact')
                <span class="badge bg-info">Contact Information</span>
            @else
                <span class="badge bg-secondary">Social Links</span>
            @endif
        </p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Requested Changes</h5>
    </div>
    <div class="card-body">
        @if($revision->type === 'general')
            <dl class="row mb-0">
                @if(isset($revision->payload['page_name']))
                    <dt class="col-sm-3">Page name</dt>
                    <dd class="col-sm-9">{{ $revision->payload['page_name'] }}</dd>
                @endif
                @if(isset($revision->payload['business_description']))
                    <dt class="col-sm-3">Description</dt>
                    <dd class="col-sm-9">{{ Str::limit($revision->payload['business_description'], 300) }}</dd>
                @endif
                @if(!empty($revision->payload['logo_path']))
                    <dt class="col-sm-3">New logo</dt>
                    <dd class="col-sm-9">
                        @if(\Illuminate\Support\Facades\Storage::disk('public')->exists($revision->payload['logo_path']))
                            <img src="{{ asset('storage/' . $revision->payload['logo_path']) }}" alt="New logo" class="img-thumbnail" style="max-height: 100px;">
                        @else
                            <span class="text-muted">File not found</span>
                        @endif
                    </dd>
                @endif
                @if(!empty($revision->payload['banner_path']))
                    <dt class="col-sm-3">New banner</dt>
                    <dd class="col-sm-9">
                        @if(\Illuminate\Support\Facades\Storage::disk('public')->exists($revision->payload['banner_path']))
                            <img src="{{ asset('storage/' . $revision->payload['banner_path']) }}" alt="New banner" class="img-thumbnail" style="max-height: 120px;">
                        @else
                            <span class="text-muted">File not found</span>
                        @endif
                    </dd>
                @endif
                @if(isset($revision->payload['primary_color']))
                    <dt class="col-sm-3">Primary color</dt>
                    <dd class="col-sm-9"><span class="d-inline-block rounded" style="width:24px;height:24px;background:{{ $revision->payload['primary_color'] }}"></span> {{ $revision->payload['primary_color'] }}</dd>
                @endif
                @if(isset($revision->payload['layout_type']))
                    <dt class="col-sm-3">Layout</dt>
                    <dd class="col-sm-9">{{ $revision->payload['layout_type'] }}</dd>
                @endif
                @if(isset($revision->payload['is_published']))
                    <dt class="col-sm-3">Publish</dt>
                    <dd class="col-sm-9">{{ $revision->payload['is_published'] ? 'Yes' : 'No' }}</dd>
                @endif
            </dl>
        @elseif($revision->type === 'contact')
            <dl class="row mb-0">
                <dt class="col-sm-3">Email</dt>
                <dd class="col-sm-9">{{ $revision->payload['email'] ?? '—' }}</dd>
                <dt class="col-sm-3">Phone</dt>
                <dd class="col-sm-9">{{ $revision->payload['phone'] ?? '—' }}</dd>
            </dl>
        @else
            <ul class="list-unstyled mb-0">
                @foreach($revision->payload['social_links'] ?? [] as $link)
                    <li class="mb-2">
                        <strong>{{ ucfirst($link['platform']) }}</strong>
                        @if(!empty($link['display_name'])) ({{ $link['display_name'] }}) @endif
                        <br><a href="{{ $link['url'] }}" target="_blank" rel="noopener">{{ Str::limit($link['url'], 50) }}</a>
                        @if(!empty($link['is_active'])) <span class="badge bg-success">Active</span> @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

<div class="d-flex gap-2">
    <form action="{{ route('admin.business-page-revisions.approve', $revision) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-success">
            <i class="bi bi-check-lg me-1"></i> Approve & Apply
        </button>
    </form>
    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
        <i class="bi bi-x-lg me-1"></i> Reject
    </button>
    <a href="{{ route('admin.business-page-revisions.index') }}" class="btn btn-outline-secondary">Back to list</a>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.business-page-revisions.reject', $revision) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Changes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Reason (optional)</label>
                    <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Optional reason for rejection"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
