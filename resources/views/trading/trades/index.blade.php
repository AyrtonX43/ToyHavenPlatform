@extends('layouts.toyshop')

@section('title', 'My Trades - ToyHaven Trading')

@push('styles')
<style>
    .trades-header {
        background: white;
        border-radius: 14px;
        padding: 1.5rem 2rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        margin-bottom: 2rem;
    }
    
    .trades-header h1 {
        font-size: 1.375rem;
        font-weight: 700;
        color: #1e293b;
        letter-spacing: -0.02em;
        margin-bottom: 0.25rem;
    }
    
    .trades-header .text-muted {
        font-size: 0.9375rem;
    }
    
    .trade-card {
        background: white;
        border-radius: 14px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        margin-bottom: 1rem;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    
    .trade-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border-color: #e2e8f0;
    }
    
    .trade-card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }
    
    .trade-card-meta {
        font-size: 0.875rem;
        color: #64748b;
        margin-bottom: 0.75rem;
    }
    
    .trade-progress {
        height: 6px;
        border-radius: 3px;
        background: #e2e8f0;
        overflow: hidden;
        margin-top: 0.75rem;
    }
    
    .trade-progress-bar {
        height: 100%;
        border-radius: 3px;
        background: #0891b2;
        transition: width 0.3s ease;
    }
    
    .btn-trade-view {
        border-radius: 10px;
        font-weight: 600;
        padding: 0.5rem 1.25rem;
        font-size: 0.875rem;
        background: #0891b2;
        border: none;
        color: white;
    }
    
    .btn-trade-view:hover {
        background: #0e7490;
        color: white;
    }
    
    .empty-trades {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 14px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
    }
    
    .empty-trades-icon {
        font-size: 3.5rem;
        color: #cbd5e1;
        margin-bottom: 1.25rem;
    }
    
    .empty-trades h4 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
    }
    
    .empty-trades a {
        color: #0891b2;
        font-weight: 600;
    }
    
    .empty-trades a:hover {
        color: #0e7490;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="trades-header">
        <h1><i class="bi bi-arrow-left-right me-2"></i>My Trades</h1>
        <p class="text-muted mb-0">Track and manage your active trades</p>
    </div>

    <div class="mb-4">
        @forelse($trades as $trade)
        <div class="trade-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div class="flex-grow-1 min-w-0">
                <h3 class="trade-card-title">{{ $trade->tradeListing->title }}</h3>
                <p class="trade-card-meta mb-0">
                    Status: <span class="fw-semibold text-dark">{{ $trade->getStatusLabel() }}</span>
                    &nbsp;·&nbsp;
                    With: {{ $trade->getOtherParty(Auth::id())->name }}
                </p>
                <div class="trade-progress mt-2">
                    <div class="trade-progress-bar" style="width: {{ $trade->getProgressPercentage() }}%;"></div>
                </div>
            </div>
            <a href="{{ route('trading.trades.show', $trade->id) }}" class="btn btn-trade-view text-nowrap">
                <i class="bi bi-eye me-1"></i>View Details
            </a>
        </div>
        @empty
        <div class="empty-trades">
            <i class="bi bi-inbox empty-trades-icon"></i>
            <h4>You don't have any trades yet</h4>
            <p class="text-muted mb-4">Browse trade listings to start trading with other collectors.</p>
            <a href="{{ route('trading.index') }}">
                <i class="bi bi-arrow-left-right me-1"></i>Browse trade listings
            </a>
        </div>
        @endforelse
    </div>

    @if($trades->count() > 0)
    <div class="d-flex justify-content-center mt-4">
        {{ $trades->links() }}
    </div>
    @endif
</div>
@endsection
