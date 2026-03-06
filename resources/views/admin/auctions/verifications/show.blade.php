@extends('layouts.admin-new')

@section('title', 'Verification: ' . $verification->user->name)

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Auction Seller Verification</h1>
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Applicant</div>
                <div class="card-body">
                    <p><strong>User:</strong> {{ $verification->user->name }} ({{ $verification->user->email }})</p>
                    <p><strong>Type:</strong> {{ ucfirst($verification->type) }}</p>
                    <p><strong>Status:</strong> {{ $verification->verification_status }}</p>
                    @if($verification->seller)
                        <p><strong>Linked Toyshop:</strong> {{ $verification->seller->business_name }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            @if($verification->type === 'individual' && $verification->documents->isNotEmpty())
                <div class="card mb-4">
                    <div class="card-header">Documents</div>
                    <div class="card-body">
                        @foreach($verification->documents as $doc)
                            <p>
                                <strong>{{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}:</strong>
                                <a href="{{ asset('storage/' . $doc->document_path) }}" target="_blank">View</a>
                            </p>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($verification->verification_status === 'pending' || $verification->verification_status === 'requires_resubmission')
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.auction-verifications.approve', $verification) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">Approve</button>
                </form>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>
            </div>
        </div>
    @endif
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.auction-verifications.reject', $verification) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Verification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reason (required)</label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required></textarea>
                    </div>
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
