@extends('layouts.toyshop')

@section('title', 'My Auction Wins - ToyHaven')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">My Auction Wins</h2>

    @if($wins->count() > 0)
        <div class="row g-4">
            @foreach($wins as $win)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        @if($win->auction->images->first())
                            <img src="{{ asset('storage/' . $win->auction->images->first()->path) }}" class="card-img-top" alt="{{ $win->auction->title }}" style="height: 160px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 160px;">
                                <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                            </div>
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">{{ $win->auction->title }}</h5>
                            <p class="mb-2">₱{{ number_format($win->total_amount, 0) }} paid</p>
                            @if($win->winner_received_confirmed_at)
                                <span class="badge bg-success">Received</span>
                            @else
                                <span class="badge bg-warning">Awaiting delivery</span>
                            @endif
                            <a href="{{ route('auctions.wins.show', $win) }}" class="btn btn-sm btn-outline-primary mt-2">View details</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        {{ $wins->links() }}
    @else
        <p class="text-muted">You have not won any auctions yet.</p>
    @endif
</div>
@endsection
