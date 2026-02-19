@extends('layouts.admin')

@section('title', 'Auction - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="text-2xl font-bold mb-0">{{ $auction->title }}</h1>
                    <div>
                        @if($auction->status === 'pending_approval')
                            <form action="{{ route('admin.auctions.approve', $auction) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">Approve</button>
                            </form>
                            <form action="{{ route('admin.auctions.reject', $auction) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger">Reject</button>
                            </form>
                        @elseif(!$auction->isEnded())
                            <form action="{{ route('admin.auctions.cancel', $auction) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this auction?');">
                                @csrf
                                <button type="submit" class="btn btn-warning">Cancel</button>
                            </form>
                        @endif
                        <a href="{{ route('admin.auctions.edit', $auction) }}" class="btn btn-outline-primary">Edit</a>
                    </div>
                </div>

                <p class="text-muted mb-4">{{ $auction->description }}</p>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <strong>Status:</strong>
                        <span class="badge bg-{{ $auction->status === 'live' ? 'success' : ($auction->status === 'ended' ? 'secondary' : 'warning') }}">{{ $auction->status }}</span>
                    </div>
                    <div class="col-md-3"><strong>Current Price:</strong> ₱{{ number_format($auction->getCurrentPrice(), 2) }}</div>
                    <div class="col-md-3"><strong>Bids:</strong> {{ $auction->bids_count }}</div>
                    <div class="col-md-3"><strong>Ends:</strong> {{ $auction->end_at?->format('M d, Y H:i') ?? 'TBD' }}</div>
                </div>

                @if($auction->winner)
                    <div class="alert alert-success">
                        <strong>Winner:</strong> {{ $auction->winner->name }} (₱{{ number_format($auction->winning_amount ?? $auction->getCurrentPrice(), 2) }})
                    </div>
                @endif

                <h5 class="mt-4">Bid History</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>User</th><th>Amount</th><th>Date</th></tr></thead>
                        <tbody>
                            @forelse($auction->bids as $bid)
                                <tr>
                                    <td>{{ $bid->user->name ?? 'N/A' }}</td>
                                    <td>₱{{ number_format($bid->amount, 2) }}</td>
                                    <td>{{ $bid->created_at->format('M d, H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted">No bids yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
