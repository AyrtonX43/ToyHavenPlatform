@extends('layouts.toyshop')

@section('title', 'Verification Status - ToyHaven')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auctions.index') }}">Auctions</a></li>
            <li class="breadcrumb-item"><a href="{{ route('auctions.verification.index') }}">Seller Verification</a></li>
            <li class="breadcrumb-item active">Status</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white p-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0 fw-bold">Verification Status</h3>
                        <span class="badge bg-{{ $verification->status === 'approved' ? 'success' : ($verification->status === 'pending' ? 'warning' : 'danger') }} fs-6">
                            {{ ucfirst(str_replace('_', ' ', $verification->status)) }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="mb-1 text-muted small">Seller Type</p>
                            <p class="fw-semibold">{{ ucfirst($verification->seller_type) }} Seller</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1 text-muted small">Submitted</p>
                            <p class="fw-semibold">{{ $verification->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1 text-muted small">Phone</p>
                            <p class="fw-semibold">{{ $verification->phone }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1 text-muted small">Address</p>
                            <p class="fw-semibold">{{ $verification->address }}</p>
                        </div>
                    </div>

                    @if($verification->rejection_reason)
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <strong>Feedback:</strong> {{ $verification->rejection_reason }}
                        </div>
                    @endif

                    @if($verification->verified_at)
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-1"></i>
                            Verified on {{ $verification->verified_at->format('M d, Y H:i') }}
                        </div>
                    @endif

                    <h5 class="fw-bold mb-3">Submitted Documents</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Document</th>
                                    <th>Status</th>
                                    <th>Uploaded</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Selfie</td>
                                    <td><span class="badge bg-{{ $verification->status === 'approved' ? 'success' : 'warning' }}">{{ $verification->status === 'approved' ? 'Approved' : 'Pending' }}</span></td>
                                    <td>{{ $verification->created_at->format('M d, Y') }}</td>
                                </tr>
                                @foreach($verification->documents as $doc)
                                    <tr>
                                        <td>{{ \App\Models\AuctionSellerVerification::documentLabel($doc->document_type) }}</td>
                                        <td><span class="badge bg-{{ $doc->status === 'approved' ? 'success' : ($doc->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($doc->status) }}</span></td>
                                        <td>{{ $doc->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(in_array($verification->status, ['rejected', 'requires_resubmission']))
                        <div class="text-center mt-4">
                            <a href="{{ route('auctions.verification.create', ['type' => $verification->seller_type]) }}" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-arrow-repeat me-1"></i>Resubmit Verification
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
