@extends('layouts.toyshop')

@section('title', 'Saved Auctions - ToyHaven')

@push('styles')
<link href="{{ asset('css/auction.css') }}" rel="stylesheet">
<style>
    .auction-saved-card:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(2, 132, 199, 0.15); }
</style>
@endpush

@section('content')
<div class="container py-4 pb-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('auction.index') }}">Auction Hub</a></li>
            <li class="breadcrumb-item active">Saved Auctions</li>
        </ol>
    </nav>

    <h2 class="mb-4 fw-bold"><i class="bi bi-bookmark-star me-2"></i>Saved Auctions</h2>

    @if($savedAuctions->count() > 0)
        <div class="row g-4">
            @foreach($savedAuctions as $auction)
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('auction.show', $auction) }}" class="text-decoration-none text-dark">
                        <div class="card h-100 auction-card auction-saved-card border-0 position-relative">
                            @php $primaryImg = $auction->images->firstWhere('is_primary', true) ?? $auction->images->first(); @endphp
                            @if($primaryImg)
                                <img src="{{ asset('storage/' . $primaryImg->image_path) }}" alt="" class="card-img-top" style="height:180px;object-fit:cover;">
                            @endif
                            <div class="card-body">
                                <h6 class="card-title">{{ Str::limit($auction->title, 50) }}</h6>
                                <p class="mb-1"><strong>Current Bid:</strong> ₱{{ number_format($auction->winning_amount ?? $auction->starting_bid, 2) }}</p>
                                
                                @if($auction->isActive())
                                    <p class="mb-0 small text-primary">Ends {{ $auction->end_at?->diffForHumans() }}</p>
                                @elseif($auction->isPendingApproval())
                                    <p class="mb-0 small text-warning">Pending Approval</p>
                                @else
                                    <p class="mb-0 small text-muted">Ended</p>
                                @endif
                            </div>
                            
                            <form action="{{ route('auction.unsave', $auction) }}" method="POST" class="position-absolute top-0 end-0 m-2">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-light rounded-circle shadow-sm" style="width:36px;height:36px;" title="Remove from saved">
                                    <i class="bi bi-bookmark-fill text-primary"></i>
                                </button>
                            </form>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $savedAuctions->links() }}</div>
    @else
        <div class="auction-empty">
            <i class="bi bi-bookmark fs-1 text-muted mb-3 d-block"></i>
            <p class="text-muted mb-0">You haven't saved any auctions yet.</p>
            <a href="{{ route('auction.index') }}" class="btn btn-primary mt-3 rounded-pill px-4">Browse Auctions</a>
        </div>
    @endif
</div>
@endsection
