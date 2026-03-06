@extends('layouts.toyshop')

@section('title', 'Verification Status')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">Auction Seller Verification Status</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <p><strong>Type:</strong> {{ ucfirst($verification->seller_type) }}</p>
            <p><strong>Status:</strong>
                @if($verification->status === 'approved')
                    <span class="badge bg-success">Approved</span>
                @elseif($verification->status === 'rejected')
                    <span class="badge bg-danger">Rejected</span>
                @else
                    <span class="badge bg-warning">Pending</span>
                @endif
            </p>
            @if($verification->rejection_reason)
                <p><strong>Feedback:</strong> {{ $verification->rejection_reason }}</p>
            @endif
            @if($verification->status === 'approved')
                <a href="{{ route('auctions.seller.index') }}" class="btn btn-primary">Go to Seller Dashboard</a>
            @elseif($verification->status === 'rejected')
                <a href="{{ route('auctions.verification.create') }}" class="btn btn-primary">Apply Again</a>
            @endif
        </div>
    </div>
</div>
@endsection
