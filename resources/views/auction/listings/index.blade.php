@extends('layouts.toyshop')

@section('title', 'My Auction Listings - ToyHaven')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auction.index') }}">Auction</a></li>
            <li class="breadcrumb-item"><a href="{{ route('auction.seller.dashboard') }}">Seller Dashboard</a></li>
            <li class="breadcrumb-item active">My Listings</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-list-ul me-2"></i>My Auction Listings</h4>
        <a href="{{ route('auction.listings.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Add Listing
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Starting Bid</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($listings as $l)
                        <tr>
                            <td>
                                <strong>{{ $l->title }}</strong>
                                @if($l->rejection_reason)
                                    <br><small class="text-danger">{{ Str::limit($l->rejection_reason, 60) }}</small>
                                @endif
                            </td>
                            <td>₱{{ number_format($l->starting_bid, 2) }}</td>
                            <td>
                                @php
                                    $badge = match($l->status) {
                                        'draft' => 'secondary',
                                        'pending_approval' => 'warning',
                                        'active' => 'success',
                                        'ended' => 'info',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $badge }}">{{ str_replace('_', ' ', ucfirst($l->status)) }}</span>
                            </td>
                            <td>{{ $l->updated_at->format('M d, Y') }}</td>
                            <td>
                                @if($l->isDraft())
                                    <a href="{{ route('auction.listings.edit', $l) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="{{ route('auction.listings.submit-for-approval', $l) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Submit for Approval</button>
                                    </form>
                                @elseif($l->isActive() || $l->isEnded())
                                    <a href="{{ route('auction.show', $l) }}" class="btn btn-sm btn-outline-primary" target="_blank">View</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No auction listings yet. <a href="{{ route('auction.listings.create') }}">Create your first listing</a>.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $listings->links() }}</div>

    <a href="{{ route('auction.seller.dashboard') }}" class="btn btn-outline-secondary mt-3">
        <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
    </a>
</div>
@endsection
