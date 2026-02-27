@extends('layouts.toyshop')

@section('title', 'My Auction Listings - ToyHaven')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auctions.index') }}">Auctions</a></li>
            <li class="breadcrumb-item active">My Listings</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0"><i class="bi bi-hammer me-2"></i>My Auction Listings</h2>
        <a href="{{ route('auctions.seller.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Create Auction
        </a>
    </div>

    @if($auctions->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-hammer text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3 text-muted">No auction listings yet</h4>
            <p class="text-muted">Create your first auction listing to start selling.</p>
            <a href="{{ route('auctions.seller.create') }}" class="btn btn-primary btn-lg mt-2">
                <i class="bi bi-plus-circle me-1"></i>Create Your First Auction
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover bg-white rounded shadow-sm">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Starting Bid</th>
                        <th>Bids</th>
                        <th>Status</th>
                        <th>Ends</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($auctions as $auction)
                        <tr>
                            <td>
                                @if($img = $auction->images->first())
                                    <img src="{{ asset('storage/' . $img->path) }}" alt="" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ Str::limit($auction->title, 40) }}</td>
                            <td><span class="badge bg-{{ $auction->auction_type === 'live_event' ? 'danger' : 'info' }}">{{ $auction->auction_type === 'live_event' ? 'Live Event' : 'Timed' }}</span></td>
                            <td>₱{{ number_format($auction->starting_bid, 2) }}</td>
                            <td>{{ $auction->bids_count }}</td>
                            <td>
                                @php
                                    $statusColor = match($auction->status) {
                                        'live' => 'success',
                                        'ended' => 'secondary',
                                        'pending_approval' => 'warning',
                                        'draft' => 'info',
                                        'cancelled' => 'danger',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">{{ ucfirst(str_replace('_', ' ', $auction->status)) }}</span>
                            </td>
                            <td>{{ $auction->end_at?->format('M d, Y H:i') ?? 'TBD' }}</td>
                            <td>
                                <a href="{{ route('auctions.show', $auction) }}" class="btn btn-sm btn-outline-primary">View</a>
                                @if(in_array($auction->status, ['draft', 'pending_approval', 'cancelled']))
                                    <a href="{{ route('auctions.seller.edit', $auction) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $auctions->links() }}</div>
    @endif
</div>
@endsection
