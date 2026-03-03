@extends('layouts.admin-new')

@section('title', 'Trade #' . $trade->id . ' - Moderator')
@section('page-title', 'Trade #' . $trade->id . ' Details')

@section('content')
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('moderator.dashboard') }}">Moderator</a></li>
        <li class="breadcrumb-item"><a href="{{ route('moderator.trades.index') }}">Trades</a></li>
        <li class="breadcrumb-item active">Trade #{{ $trade->id }}</li>
    </ol>
</nav>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row g-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">{{ $trade->tradeListing->title ?? 'Trade #' . $trade->id }}</h5>
                @php
                    $statusClass = match($trade->status) {
                        'completed' => 'bg-success',
                        'disputed' => 'bg-danger',
                        'cancelled' => 'bg-secondary',
                        default => 'bg-warning text-dark',
                    };
                @endphp
                <span class="badge {{ $statusClass }} fs-6">{{ $trade->getStatusLabel() }}</span>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="info-label mb-1">Initiator</div>
                        <div class="info-value">{{ $trade->initiator->name ?? '—' }} ({{ $trade->initiator->email ?? '—' }})</div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-label mb-1">Participant</div>
                        <div class="info-value">{{ $trade->participant->name ?? '—' }} ({{ $trade->participant->email ?? '—' }})</div>
                    </div>
                    @if($trade->cash_amount)
                    <div class="col-md-6 mt-2">
                        <div class="info-label mb-1">Cash Amount</div>
                        <div class="info-value fw-semibold">₱{{ number_format($trade->cash_amount, 2) }}</div>
                    </div>
                    @endif
                    <div class="col-md-6 mt-2">
                        <div class="info-label mb-1">Created</div>
                        <div class="info-value">{{ $trade->created_at->format('M d, Y H:i') }}</div>
                    </div>
                </div>

                <h6 class="fw-bold mb-2">Items in Trade</h6>
                <div class="row g-3">
                    @foreach($trade->initiatorItems as $item)
                    <div class="col-md-6">
                        <div class="d-flex gap-2 p-3 rounded bg-light">
                            @if(!empty($item->product_images))
                                <img src="{{ asset('storage/' . $item->product_images[0]) }}" alt="" style="width: 48px; height: 48px; object-fit: cover; border-radius: 8px;">
                            @else
                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center text-white" style="width: 48px; height: 48px;"><i class="bi bi-box"></i></div>
                            @endif
                            <div>
                                <div class="fw-semibold">{{ $item->product_name ?? 'Item' }}</div>
                                <small class="text-muted">Initiator's item</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @foreach($trade->participantItems as $item)
                    <div class="col-md-6">
                        <div class="d-flex gap-2 p-3 rounded bg-light">
                            @if(!empty($item->product_images))
                                <img src="{{ asset('storage/' . $item->product_images[0]) }}" alt="" style="width: 48px; height: 48px; object-fit: cover; border-radius: 8px;">
                            @else
                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center text-white" style="width: 48px; height: 48px;"><i class="bi bi-box"></i></div>
                            @endif
                            <div>
                                <div class="fw-semibold">{{ $item->product_name ?? 'Item' }}</div>
                                <small class="text-muted">Participant's item</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($trade->status === 'disputed')
                <hr class="my-4">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    This trade is in dispute. Resolution is handled by Admin. 
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.trades.show', $trade->id) }}" class="alert-link">Resolve in Admin Panel</a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
