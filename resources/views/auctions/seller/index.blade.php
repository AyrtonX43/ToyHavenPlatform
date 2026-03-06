@extends('layouts.toyshop')

@section('title', 'Auction Seller Dashboard')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-hammer me-2"></i>Auction Listings</h2>
        <a href="{{ route('auctions.seller.create') }}" class="btn btn-primary"><i class="bi bi-plus me-1"></i>Create Auction</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <form action="{{ route('auctions.seller.index') }}" method="GET" class="mb-3">
        <select name="status" class="form-select w-auto d-inline-block" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="pending_approval" {{ request('status') === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
            <option value="live" {{ request('status') === 'live' ? 'selected' : '' }}>Live</option>
            <option value="ended" {{ request('status') === 'ended' ? 'selected' : '' }}>Ended</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
    </form>

    @if($auctions->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-inbox display-4 text-muted mb-3"></i>
            <p class="text-muted">You have no auction listings.</p>
            <a href="{{ route('auctions.seller.create') }}" class="btn btn-primary">Create Your First Auction</a>
        </div>
    @else
        <div class="row g-4">
            @foreach($auctions as $auction)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        @if($auction->primaryImage())
                            <img src="{{ Storage::url($auction->primaryImage()->image_path) }}" class="card-img-top" style="height:180px;object-fit:cover" alt="{{ $auction->title }}">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height:180px"><i class="bi bi-image text-muted display-4"></i></div>
                        @endif
                        <div class="card-body">
                            <h5 class="card-title text-truncate">{{ $auction->title }}</h5>
                            <span class="badge
                                @if($auction->status === 'draft') bg-secondary
                                @elseif($auction->status === 'pending_approval') bg-warning text-dark
                                @elseif($auction->status === 'active') bg-success
                                @elseif($auction->status === 'ended') bg-info
                                @else bg-danger
                                @endif
                            ">{{ ucfirst(str_replace('_',' ',$auction->status)) }}</span>
                            <p class="mb-2 mt-2">Current: ₱{{ number_format($auction->currentPrice(), 0) }} · {{ $auction->bids_count }} bids</p>
                            <div class="d-flex gap-1 flex-wrap">
                                @if($auction->isDraft())
                                    <a href="{{ route('auctions.seller.edit', $auction) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="{{ route('auctions.seller.submit', $auction) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Submit</button>
                                    </form>
                                    <form action="{{ route('auctions.seller.destroy', $auction) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this auction?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @else
                                    <a href="{{ route('auctions.show', $auction) }}" class="btn btn-sm btn-outline-primary">View</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        {{ $auctions->links() }}
    @endif
</div>
@endsection
