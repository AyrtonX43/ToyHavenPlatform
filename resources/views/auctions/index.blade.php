@extends('layouts.toyshop')

@section('title', 'Auctions - ToyHaven')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Live Auctions</h1>

    @auth
    <p class="mb-4">
        <a href="{{ route('auctions.wins.index') }}" class="btn btn-outline-primary">My Auction Wins</a>
        <a href="{{ route('auctions.saved.index') }}" class="btn btn-outline-secondary">Saved Auctions</a>
    </p>
    @endauth

    @if($auctions->count() > 0)
        <div class="row g-4">
            @foreach($auctions as $a)
                <div class="col-md-4">
                    <a href="{{ route('auctions.show', $a) }}" class="text-decoration-none text-dark">
                        <div class="card h-100">
                            @if($a->images->first())
                                <img src="{{ asset('storage/' . $a->images->first()->path) }}" class="card-img-top" alt="{{ $a->title }}" style="height: 200px; object-fit: cover;">
                            @else
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                </div>
                            @endif
                            <div class="card-body">
                                <h5 class="card-title">{{ $a->title }}</h5>
                                <p class="mb-1"><strong>₱{{ number_format($a->starting_bid, 0) }}</strong> starting bid</p>
                                <p class="text-muted small mb-0">Ends {{ $a->end_at?->diffForHumans() }}</p>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        {{ $auctions->links() }}
    @else
        <p class="text-muted">No live auctions at the moment. Check back soon!</p>
    @endif
</div>
@endsection
