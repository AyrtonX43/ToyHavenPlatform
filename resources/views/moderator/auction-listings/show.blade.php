@extends('layouts.admin-new')
@section('title', 'Auction Listing #' . $listing->id . ' - Moderator')
@section('content')
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('moderator.auction-listings.index') }}">Auction Listings</a></li>
            <li class="breadcrumb-item active">#{{ $listing->id }}</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Auction Listing: {{ $listing->title }}</h1>
        <a href="{{ route('moderator.auction-listings.index') }}" class="btn btn-outline-secondary">Back to List</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="card-title">Details</h5>
                    <p class="mb-1"><strong>Title:</strong> {{ $listing->title }}</p>
                    <p class="mb-1"><strong>Description:</strong></p>
                    <div class="bg-light p-3 rounded mb-3">{{ $listing->description ?: '(none)' }}</div>
                    <p class="mb-1"><strong>Condition:</strong> {{ \App\Models\Auction::CONDITIONS[$listing->condition ?? 'good'] ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Starting Bid:</strong> ₱{{ number_format($listing->starting_bid, 2) }}</p>
                    @if($listing->reserve_price)
                        <p class="mb-1"><strong>Reserve Price:</strong> ₱{{ number_format($listing->reserve_price, 2) }}</p>
                    @endif
                    <p class="mb-1"><strong>Bid Increment:</strong> ₱{{ number_format($listing->bid_increment, 2) }}</p>
                    <p class="mb-1"><strong>Duration:</strong> {{ $listing->duration_hours ?? 'N/A' }} hours</p>
                    <p class="mb-1"><strong>Categories:</strong> {{ $listing->categories()->pluck('name')->join(', ') ?: ($listing->category?->name ?? 'None') }}</p>
                    @if($listing->start_at)
                        <p class="mb-1"><strong>Ends:</strong> {{ $listing->end_at?->format('M d, Y H:i') }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="card-title">Seller</h5>
                    <p class="mb-1"><strong>{{ $listing->user?->name }}</strong></p>
                    <p class="mb-1 text-muted">{{ $listing->user?->email }}</p>
                </div>
            </div>

            @php $watcherCount = \Illuminate\Support\Facades\Schema::hasTable('saved_auctions') ? $listing->watchersCount() : 0; @endphp
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="card-title">Watchers</h5>
                    <p class="mb-2"><strong>Current:</strong> {{ $watcherCount }}</p>
                    @if($listing->min_watchers_to_approve)
                        <p class="mb-0 small text-muted">Min required: {{ $listing->min_watchers_to_approve }}</p>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="card-title">Status</h5>
                    @php
                        $badge = match($listing->status) {
                            'pending_approval' => 'warning',
                            'active' => 'success',
                            'ended' => 'info',
                            default => 'secondary'
                        };
                    @endphp
                    <p class="mb-3"><span class="badge bg-{{ $badge }} fs-6">{{ str_replace('_', ' ', ucfirst($listing->status)) }}</span></p>
                    @if($listing->status === 'pending_approval')
                        @php
                            $minReq = $listing->min_watchers_to_approve ?? 0;
                            $needsOverride = $minReq > 0 && $watcherCount < $minReq;
                        @endphp
                        <form action="{{ route('moderator.auction-listings.approve', $listing) }}" method="POST" class="mb-2">
                            @csrf
                            @if($needsOverride)
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="override_min_watchers" value="1" id="overrideWatchers" class="form-check-input">
                                    <label for="overrideWatchers" class="form-check-label small">Override: approve anyway ({{ $watcherCount }}/{{ $minReq }} watchers)</label>
                                </div>
                            @endif
                            <button type="submit" class="btn btn-success w-100">Approve & Go Live</button>
                        </form>
                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($listing->status === 'pending_approval')
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('moderator.auction-listings.reject', $listing) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Listing</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Feedback (required)</label>
                        <textarea name="feedback" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
