@extends('layouts.toyshop')

@section('title', 'Trade History - ' . $listing->title . ' - ToyHaven')

@push('styles')
<style>
    .history-page { max-width: 900px; margin: 0 auto; padding: 0 1rem; }
    .history-header { background: white; border-radius: 14px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 1.5rem; }
    .trade-record { background: white; border-radius: 14px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 1rem; }
    .trade-record h5 { font-size: 1rem; font-weight: 700; color: #1e293b; margin-bottom: 1rem; }
    .proof-row { display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; }
    .proof-box { flex: 1; min-width: 200px; background: #f8fafc; border-radius: 10px; padding: 1rem; border: 1px solid #e2e8f0; }
    .proof-box img { max-width: 100%; height: auto; max-height: 200px; object-fit: contain; border-radius: 8px; }
    .proof-label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #64748b; margin-bottom: 0.5rem; }
    .empty-proof { font-size: 0.875rem; color: #94a3b8; font-style: italic; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="history-page">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('trading.index') }}">Trading</a></li>
                <li class="breadcrumb-item"><a href="{{ route('trading.listings.my') }}">My Listings</a></li>
                <li class="breadcrumb-item active">Trade History</li>
            </ol>
        </nav>

        <div class="history-header d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h1 class="h4 mb-1 fw-bold">Trade History</h1>
                <p class="text-muted small mb-0">{{ Str::limit($listing->title, 60) }}</p>
                <span class="badge bg-secondary mt-1">{{ ucfirst(str_replace('_', ' ', $listing->trade_type ?? 'trade')) }}</span>
            </div>
            <a href="{{ route('trading.listings.show', $listing->id) }}" class="btn btn-outline-primary btn-sm">View listing</a>
        </div>

        @if($trades->count() > 0)
            @foreach($trades as $trade)
                <div class="trade-record">
                    <h5><i class="bi bi-check-circle text-success me-1"></i> Completed {{ $trade->completed_at?->format('M j, Y g:i A') ?? $trade->updated_at->format('M j, Y') }}</h5>
                    <p class="small text-muted mb-3">
                        With {{ $trade->initiator_id === auth()->id() ? $trade->participant->name : $trade->initiator->name }}
                    </p>

                    @if(in_array($listing->trade_type, ['barter', 'barter_with_cash']))
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="proof-box">
                                    <div class="proof-label">Trade 1 – Listing (your product)</div>
                                    @if($listing->images->isNotEmpty())
                                        <img src="{{ asset('storage/' . $listing->images->first()->image_path) }}" alt="Listing">
                                    @else
                                        <span class="empty-proof">Listing image</span>
                                    @endif
                                </div>
                                <div class="proof-box mt-2">
                                    <div class="proof-label">Proof from {{ $trade->initiator_id === auth()->id() ? 'you' : $trade->initiator->name }}</div>
                                    @if($trade->initiator_received_proof_path)
                                        <img src="{{ asset('storage/' . $trade->initiator_received_proof_path) }}" alt="Proof">
                                    @else
                                        <span class="empty-proof">No proof uploaded</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="proof-box">
                                    <div class="proof-label">Trade 2 – Other party</div>
                                    <span class="empty-proof">{{ $trade->participant_id === auth()->id() ? 'You' : $trade->participant->name }}</span>
                                </div>
                                <div class="proof-box mt-2">
                                    <div class="proof-label">Proof from {{ $trade->participant_id === auth()->id() ? 'you' : $trade->participant->name }}</div>
                                    @if($trade->participant_received_proof_path)
                                        <img src="{{ asset('storage/' . $trade->participant_received_proof_path) }}" alt="Proof">
                                    @else
                                        <span class="empty-proof">No proof uploaded</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Cash: single trade product (listing) + proof received --}}
                        <div class="proof-row">
                            <div class="proof-box" style="max-width: 280px;">
                                <div class="proof-label">Trade product (listing)</div>
                                @if($listing->images->isNotEmpty())
                                    <img src="{{ asset('storage/' . $listing->images->first()->image_path) }}" alt="Listing">
                                @else
                                    <span class="empty-proof">Listing image</span>
                                @endif
                            </div>
                            <div class="proof-box" style="max-width: 280px;">
                                <div class="proof-label">Proof received (listing)</div>
                                @if($trade->initiator_received_proof_path || $trade->participant_received_proof_path)
                                    @if($trade->initiator_received_proof_path)
                                        <img src="{{ asset('storage/' . $trade->initiator_received_proof_path) }}" alt="Proof">
                                    @endif
                                    @if($trade->participant_received_proof_path)
                                        @if($trade->initiator_received_proof_path)<br class="my-2">@endif
                                        <img src="{{ asset('storage/' . $trade->participant_received_proof_path) }}" alt="Proof">
                                    @endif
                                @else
                                    <span class="empty-proof">No proof uploaded</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <div class="text-center py-5 bg-white rounded-3 border">
                <i class="bi bi-clock-history text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3 fw-bold">No completed trades yet</h5>
                <p class="text-muted mb-0">When you complete a deal from this listing, it will appear here with proof.</p>
            </div>
        @endif
    </div>
</div>
@endsection
