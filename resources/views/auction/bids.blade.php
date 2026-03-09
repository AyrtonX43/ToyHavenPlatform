@extends('layouts.toyshop')

@section('title', 'My Bids - ToyHaven')

@section('content')
<div class="container py-4 pb-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auction.index') }}">Auction Hub</a></li>
            <li class="breadcrumb-item active">My Bids</li>
        </ol>
    </nav>

    <h2 class="mb-4"><i class="bi bi-tag me-2"></i>My Bids</h2>

    @if($auctions->count() > 0)
        <div class="row g-4">
            @foreach($auctions as $auction)
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('auction.show', $auction) }}" class="text-decoration-none text-dark">
                        <div class="card h-100 shadow-sm border-0 hover-shadow">
                            @php $primaryImg = $auction->images->firstWhere('is_primary', true) ?? $auction->images->first(); @endphp
                            @if($primaryImg)
                                <img src="{{ asset('storage/' . $primaryImg->image_path) }}" alt="" class="card-img-top" style="height:180px;object-fit:cover;">
                            @endif
                            <div class="card-body">
                                <h6 class="card-title">{{ Str::limit($auction->title, 50) }}</h6>
                                
                                @php 
                                    $myHighestBid = $auction->bids->first(); 
                                    $isWinning = $auction->winner_id === auth()->id() || ($auction->isActive() && $auction->winning_amount == $myHighestBid->amount);
                                @endphp
                                
                                <p class="mb-1"><strong>Your Highest Bid:</strong> ₱{{ number_format($myHighestBid->amount ?? 0, 2) }}</p>
                                <p class="mb-1 small text-muted"><strong>Current Bid:</strong> ₱{{ number_format($auction->winning_amount ?? $auction->starting_bid, 2) }}</p>
                                
                                @if($auction->isActive())
                                    <p class="mb-0 small text-primary">Ends {{ $auction->end_at?->diffForHumans() }}</p>
                                    @if($isWinning)
                                        <span class="badge bg-success mt-2">Currently Winning</span>
                                    @else
                                        <span class="badge bg-warning text-dark mt-2">Outbid</span>
                                    @endif
                                @else
                                    <p class="mb-0 small text-muted">Ended</p>
                                    @if($auction->winner_id === auth()->id())
                                        <span class="badge bg-success mt-2">Won</span>
                                    @else
                                        <span class="badge bg-secondary mt-2">Did Not Win</span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $auctions->links() }}</div>
    @else
        <div class="text-center py-5 bg-light rounded border border-dashed">
            <i class="bi bi-tag fs-1 text-muted mb-3 d-block"></i>
            <p class="text-muted mb-0">You haven't placed any bids yet.</p>
            <a href="{{ route('auction.index') }}" class="btn btn-primary mt-3">Browse Auctions</a>
        </div>
    @endif
</div>
@endsection
