@extends('layouts.admin-new')

@section('title', 'Auction Seller Profile - Admin')
@section('page-title', 'Auction Seller Profile')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <h5>User</h5>
        <p class="mb-0">{{ $auctionSellerProfile->user->name }} ({{ $auctionSellerProfile->user->email }})</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h5>Profile Details</h5>
        <p><strong>Type:</strong> {{ ucfirst($auctionSellerProfile->seller_type) }}</p>
        @if($auctionSellerProfile->business_name)
            <p><strong>Business Name:</strong> {{ $auctionSellerProfile->business_name }}</p>
        @endif
        <p><strong>PayPal Email:</strong> {{ $auctionSellerProfile->paypal_email }}</p>
        <p><strong>Status:</strong> <span class="badge bg-{{ match($auctionSellerProfile->status) { 'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', default => 'secondary' } }}">{{ $auctionSellerProfile->status }}</span></p>
    </div>
</div>

@if($auctionSellerProfile->documents->isNotEmpty())
<div class="card mb-4">
    <div class="card-body">
        <h5>Documents</h5>
        <ul class="list-group">
            @foreach($auctionSellerProfile->documents as $doc)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    {{ $doc->document_type }}
                    <a href="{{ asset('storage/' . $doc->document_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
@endif

@if($auctionSellerProfile->status === 'pending')
<form method="POST" action="{{ ($context ?? null) === 'moderator' ? route('moderator.auction-seller-profiles.reject', $auctionSellerProfile) : route('admin.auction-seller-profiles.reject', $auctionSellerProfile) }}" class="d-inline">
    @csrf
    <div class="input-group mb-2">
        <input type="text" name="rejection_reason" class="form-control" placeholder="Rejection reason (required)" required>
        <button type="submit" class="btn btn-danger">Reject</button>
    </div>
</form>
<form method="POST" action="{{ ($context ?? null) === 'moderator' ? route('moderator.auction-seller-profiles.approve', $auctionSellerProfile) : route('admin.auction-seller-profiles.approve', $auctionSellerProfile) }}" class="d-inline">
    @csrf
    <button type="submit" class="btn btn-success">Approve</button>
</form>
@endif

@if($auctionSellerProfile->status === 'approved' && ($context ?? null) !== 'moderator')
<form method="POST" action="{{ route('admin.auction-seller-profiles.suspend', $auctionSellerProfile) }}" class="d-inline">
    @csrf
    <button type="submit" class="btn btn-warning">Suspend</button>
</form>
@endif

@if($auctionSellerProfile->status === 'suspended' && ($context ?? null) !== 'moderator')
<form method="POST" action="{{ route('admin.auction-seller-profiles.activate', $auctionSellerProfile) }}" class="d-inline">
    @csrf
    <button type="submit" class="btn btn-success">Activate</button>
</form>
@endif

<a href="{{ ($context ?? null) === 'moderator' ? route('moderator.auction-seller-profiles.index') : route('admin.auction-seller-profiles.index') }}" class="btn btn-secondary">Back</a>
@endsection
