@extends('layouts.toyshop')

@section('title', 'Promote Auction - ' . $auction->title)

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auctions.index') }}">Auctions</a></li>
            <li class="breadcrumb-item"><a href="{{ route('auctions.seller.index') }}">My Listings</a></li>
            <li class="breadcrumb-item active">Promote</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white p-4 border-bottom">
                    <h3 class="mb-1 fw-bold"><i class="bi bi-megaphone text-warning me-2"></i>Promote Your Auction</h3>
                    <p class="text-muted mb-0">Boost visibility for <strong>{{ $auction->title }}</strong></p>
                </div>
                <div class="card-body p-4">
                    @if($auction->isCurrentlyPromoted())
                        <div class="alert alert-success">
                            <i class="bi bi-star-fill me-1"></i>
                            This auction is currently promoted until {{ $auction->promoted_until->format('M d, Y h:i A') }}.
                            You can extend the promotion below.
                        </div>
                    @endif

                    <div class="row g-4">
                        @foreach($tiers as $key => $tier)
                            <div class="col-md-4">
                                <div class="card h-100 border-2 {{ $key === '3d' ? 'border-primary' : '' }}">
                                    @if($key === '3d')
                                        <div class="text-center">
                                            <span class="badge bg-primary" style="position: relative; top: -10px;">Popular</span>
                                        </div>
                                    @endif
                                    <div class="card-body text-center p-4">
                                        <h5 class="fw-bold">{{ $tier['label'] }}</h5>
                                        <div class="my-3" style="font-size: 2rem; font-weight: 800; color: #0891b2;">
                                            ₱{{ number_format($tier['price'], 0) }}
                                        </div>
                                        <ul class="list-unstyled text-muted small mb-3">
                                            <li><i class="bi bi-check text-success me-1"></i>Featured carousel placement</li>
                                            <li><i class="bi bi-check text-success me-1"></i>Priority in search results</li>
                                            <li><i class="bi bi-check text-success me-1"></i>Highlighted listing badge</li>
                                        </ul>
                                        <form action="{{ route('auctions.seller.promote.store', $auction) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="tier" value="{{ $key }}">
                                            <button type="submit" class="btn {{ $key === '3d' ? 'btn-primary' : 'btn-outline-primary' }} w-100">
                                                @if(auth()->user()->wallet && auth()->user()->wallet->balance >= $tier['price'])
                                                    Pay from Wallet
                                                @else
                                                    Promote Now
                                                @endif
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(auth()->user()->wallet)
                        <div class="mt-4 text-center text-muted small">
                            <i class="bi bi-wallet2 me-1"></i>Wallet balance: ₱{{ number_format(auth()->user()->wallet->balance, 2) }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
