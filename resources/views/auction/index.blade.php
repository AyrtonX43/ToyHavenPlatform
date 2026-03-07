@extends('layouts.toyshop')

@section('title', 'Live Auctions - ToyHaven')

@push('styles')
<style>
    .auction-header { background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 2px 12px rgba(0,0,0,0.06); margin-bottom: 2rem; }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="auction-header">
        <h1 class="h3 fw-bold mb-2"><i class="bi bi-hammer me-2"></i>Live Auctions</h1>
        <p class="text-muted mb-0">Browse active auctions, place bids, and discover collectibles.</p>
    </div>

    @auth
    <div class="mb-4">
        <a href="{{ route('auction.become-seller') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i>Become a Seller
        </a>
    </div>
    @endauth

    {{-- Placeholder until auction listings are implemented --}}
    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="card-body text-center py-5">
            <i class="bi bi-hammer text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3 mb-2">No live auctions right now</h4>
            <p class="text-muted mb-0">Check back soon for new auction listings, or become a seller to list your own.</p>
        </div>
    </div>
</div>
@endsection
