@extends('layouts.toyshop')

@section('title', 'My Auction Listings - ToyHaven')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">My Auction Listings</h2>

    <p class="mb-4">
        <a href="{{ route('auctions.seller.sales.index') }}" class="btn btn-outline-primary">My Auction Sales</a>
    </p>

    @if($auctions && $auctions->count() > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Starting Bid</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($auctions as $a)
                        <tr>
                            <td>{{ $a->title }}</td>
                            <td><span class="badge bg-secondary">{{ $a->status }}</span></td>
                            <td>₱{{ number_format($a->starting_bid, 0) }}</td>
                            <td>{{ $a->created_at->format('M d, Y') }}</td>
                            <td>
                                @if(in_array($a->status, ['draft', 'pending_approval']))
                                    <a href="{{ route('auctions.seller.edit', $a) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $auctions->links() }}
    @else
        <p class="text-muted">You have no auction listings yet.</p>
        <a href="{{ route('auctions.seller.create') }}" class="btn btn-primary">Create Auction</a>
    @endif
</div>
@endsection
