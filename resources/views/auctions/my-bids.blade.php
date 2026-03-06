@extends('layouts.toyshop')

@section('title', 'My Bids - ToyHaven Auctions')

@section('content')
<div class="container py-5">
    <h2 class="mb-4"><i class="bi bi-hammer me-2"></i>My Bids</h2>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    @if($bids->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-hand-thumbs-up display-4 text-muted mb-3"></i>
            <p class="text-muted">You haven't placed any bids yet.</p>
            <a href="{{ route('auctions.index') }}" class="btn btn-primary">Browse Auctions</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Auction</th><th>Your Bid</th><th>Status</th><th>Date</th><th></th></tr></thead>
                <tbody>
                    @foreach($bids as $bid)
                        <tr>
                            <td>
                                <a href="{{ route('auctions.show', $bid->auction) }}" class="text-decoration-none text-dark fw-medium">{{ $bid->auction->title }}</a>
                            </td>
                            <td>₱{{ number_format($bid->amount, 2) }}</td>
                            <td>
                                @if($bid->is_winning && $bid->auction->canBid())
                                    <span class="badge bg-success">Winning</span>
                                @elseif($bid->auction->hasEnded())
                                    <span class="badge bg-secondary">Ended</span>
                                @else
                                    <span class="badge bg-warning text-dark">Outbid</span>
                                @endif
                            </td>
                            <td>{{ $bid->created_at->format('M j, g:i A') }}</td>
                            <td><a href="{{ route('auctions.show', $bid->auction) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $bids->links() }}
    @endif
</div>
@endsection
