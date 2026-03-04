@extends('layouts.toyshop')

@section('title', 'Trade History - ToyHaven Trading')

@push('styles')
<style>
    .history-header {
        background: white;
        border-radius: 14px;
        padding: 1.5rem 2rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        margin-bottom: 2rem;
    }
    .history-header h1 { font-size: 1.375rem; font-weight: 700; color: #1e293b; margin-bottom: 0.25rem; }
    .history-card {
        background: white;
        border-radius: 14px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        margin-bottom: 1.25rem;
    }
    .history-card-title { font-size: 1.1rem; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem; }
    .history-type-badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }
    .history-type-badge.barter { background: #dbeafe; color: #1e40af; }
    .history-type-badge.barter_with_cash { background: #fef3c7; color: #92400e; }
    .history-type-badge.cash { background: #d1fae5; color: #065f46; }
    .trade-side { margin-bottom: 1rem; }
    .trade-side-label { font-size: 0.8125rem; font-weight: 600; color: #64748b; margin-bottom: 0.35rem; }
    .item-thumb { width: 56px; height: 56px; object-fit: cover; border-radius: 8px; }
    .proof-thumb { max-width: 120px; max-height: 120px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; }
    .proof-label { font-size: 0.75rem; color: #64748b; margin-top: 0.25rem; }
    .empty-history { text-align: center; padding: 4rem 2rem; background: #f8fafc; border-radius: 14px; border: 2px dashed #e2e8f0; }
</style>
@endpush

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trading.index') }}">Trading</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trading.listings.my') }}">My Listings</a></li>
            <li class="breadcrumb-item active">Trade History</li>
        </ol>
    </nav>

    <div class="history-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1><i class="bi bi-clock-history me-2"></i>Trade History</h1>
            <p class="text-muted mb-0">Completed trades and product proof</p>
        </div>
        <a href="{{ route('trading.listings.my') }}" class="btn btn-outline-primary">
            <i class="bi bi-list-ul me-1"></i>My Listings
        </a>
    </div>

    @if($trades->count() > 0)
        @foreach($trades as $trade)
            @php
                $listing = $trade->tradeListing;
                $tradeType = $listing->trade_type ?? 'barter';
                $otherParty = $trade->getOtherParty(Auth::id());
                $isBarterOrBarterCash = in_array($tradeType, ['barter', 'barter_with_cash']);
            @endphp
            <div class="history-card">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <span class="history-type-badge {{ $tradeType === 'cash' ? 'cash' : ($tradeType === 'barter_with_cash' ? 'barter_with_cash' : 'barter') }}">
                            {{ $tradeType === 'cash' ? 'Cash' : ($tradeType === 'barter_with_cash' ? 'Barter + Cash' : 'Barter') }}
                        </span>
                        <h2 class="history-card-title mb-0">{{ $listing->title }}</h2>
                        <p class="text-muted small mb-0">With {{ $otherParty->name }} · {{ $trade->completed_at?->format('M d, Y') }}</p>
                    </div>
                    <a href="{{ route('trading.trades.show', $trade->id) }}" class="btn btn-sm btn-outline-primary">View trade</a>
                </div>

                @if($isBarterOrBarterCash)
                    {{-- Barter & Barter+Cash: Trade 1 & Trade 2 + both proofs --}}
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="trade-side">
                                <div class="trade-side-label">Trade 1 (listing)</div>
                                <div class="d-flex gap-2 flex-wrap align-items-center">
                                    @foreach($trade->initiatorItems as $item)
                                        @if(!empty($item->product_images))
                                            <img src="{{ asset('storage/' . $item->product_images[0]) }}" alt="" class="item-thumb">
                                        @else
                                            <div class="item-thumb bg-light d-flex align-items-center justify-content-center text-muted"><i class="bi bi-box"></i></div>
                                        @endif
                                        <span class="fw-semibold small">{{ $item->product_name }}</span>
                                    @endforeach
                                    @if($trade->initiatorItems->isEmpty())
                                        <span class="text-muted small">—</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="trade-side">
                                <div class="trade-side-label">Trade 2 (offered)</div>
                                <div class="d-flex gap-2 flex-wrap align-items-center">
                                    @foreach($trade->participantItems as $item)
                                        @if(!empty($item->product_images))
                                            <img src="{{ asset('storage/' . $item->product_images[0]) }}" alt="" class="item-thumb">
                                        @else
                                            <div class="item-thumb bg-light d-flex align-items-center justify-content-center text-muted"><i class="bi bi-box"></i></div>
                                        @endif
                                        <span class="fw-semibold small">{{ $item->product_name }}</span>
                                    @endforeach
                                    @if($trade->participantItems->isEmpty())
                                        <span class="text-muted small">—</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <div class="trade-side-label">Product proof ({{ $trade->initiator_id === Auth::id() ? 'you' : $trade->initiator->name }})</div>
                            @if($trade->initiator_received_proof_path)
                                <img src="{{ asset('storage/' . $trade->initiator_received_proof_path) }}" alt="Proof" class="proof-thumb">
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="trade-side-label">Product proof ({{ $trade->participant_id === Auth::id() ? 'you' : $trade->participant->name }})</div>
                            @if($trade->participant_received_proof_path)
                                <img src="{{ asset('storage/' . $trade->participant_received_proof_path) }}" alt="Proof" class="proof-thumb">
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </div>
                    </div>
                @else
                    {{-- Cash: listing product + proof(s) --}}
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="trade-side">
                                <div class="trade-side-label">Trade product (listing)</div>
                                @php
                                    $listingImg = $listing->images->first();
                                    if (!$listingImg && $listing->getItem() && method_exists($listing->getItem(), 'images')) {
                                        $listingImg = $listing->getItem()->images->first();
                                    }
                                    $imgPath = $listingImg ? ($listingImg->image_path ?? $listingImg->path ?? '') : null;
                                @endphp
                                <div class="d-flex gap-2 align-items-center">
                                    @if($imgPath)
                                        <img src="{{ asset('storage/' . $imgPath) }}" alt="" class="item-thumb">
                                    @else
                                        <div class="item-thumb bg-light d-flex align-items-center justify-content-center text-muted"><i class="bi bi-box"></i></div>
                                    @endif
                                    <span class="fw-semibold small">{{ $listing->title }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="trade-side-label">Proof received</div>
                            <div class="d-flex gap-2 flex-wrap">
                                @if($trade->initiator_received_proof_path)
                                    <div>
                                        <img src="{{ asset('storage/' . $trade->initiator_received_proof_path) }}" alt="Proof" class="proof-thumb">
                                        <div class="proof-label">{{ $trade->initiator_id === Auth::id() ? 'You' : $trade->initiator->name }}</div>
                                    </div>
                                @endif
                                @if($trade->participant_received_proof_path)
                                    <div>
                                        <img src="{{ asset('storage/' . $trade->participant_received_proof_path) }}" alt="Proof" class="proof-thumb">
                                        <div class="proof-label">{{ $trade->participant_id === Auth::id() ? 'You' : $trade->participant->name }}</div>
                                    </div>
                                @endif
                                @if(!$trade->initiator_received_proof_path && !$trade->participant_received_proof_path)
                                    <span class="text-muted small">—</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach

        <div class="d-flex justify-content-center mt-4">
            {{ $trades->links() }}
        </div>
    @else
        <div class="empty-history">
            <i class="bi bi-clock-history text-muted" style="font-size: 3rem;"></i>
            <h5 class="mt-3">No trade history yet</h5>
            <p class="text-muted mb-4">Completed trades will appear here with product proof.</p>
            <a href="{{ route('trading.index') }}" class="btn btn-primary">Browse listings</a>
        </div>
    @endif
</div>
@endsection
