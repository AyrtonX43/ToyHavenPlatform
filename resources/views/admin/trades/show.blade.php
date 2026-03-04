@extends('layouts.admin-new')

@section('title', 'Trade #' . $trade->id . ' - Admin')
@section('page-title', 'Trade #' . $trade->id . ' Details')

@section('content')
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.analytics.index') }}">Analytics</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.trades.index') }}">Trades</a></li>
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
                        <div class="text-muted small">Initiator</div>
                        <div class="fw-semibold">{{ $trade->initiator->name ?? '—' }} ({{ $trade->initiator->email ?? '—' }})</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Participant</div>
                        <div class="fw-semibold">{{ $trade->participant->name ?? '—' }} ({{ $trade->participant->email ?? '—' }})</div>
                    </div>
                    @if($trade->cash_amount)
                    <div class="col-md-6 mt-2">
                        <div class="text-muted small">Cash Amount</div>
                        <div class="fw-semibold">₱{{ number_format($trade->cash_amount, 2) }}</div>
                    </div>
                    @endif
                    <div class="col-md-6 mt-2">
                        <div class="text-muted small">Created</div>
                        <div>{{ $trade->created_at->format('M d, Y H:i') }}</div>
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

                @if($trade->conversation && $trade->conversation->messages->isNotEmpty())
                <hr class="my-4">
                <h6 class="fw-bold mb-2">Chat transcript</h6>
                <div class="bg-light rounded p-3" style="max-height: 400px; overflow-y: auto;">
                    @foreach($trade->conversation->messages->sortBy('id') as $msg)
                    <div class="mb-2">
                        <small class="text-muted">{{ $msg->sender->name ?? 'User' }} · {{ $msg->created_at->format('M d, H:i') }}</small>
                        <div class="small">{{ $msg->message }}</div>
                    </div>
                    @endforeach
                </div>
                @endif

                @if($trade->dispute)
                <hr class="my-4">
                <h6 class="fw-bold mb-2">Dispute</h6>
                <p><strong>Type:</strong> {{ $trade->dispute->getTypeLabel() }} · <strong>Reporter:</strong> {{ $trade->dispute->reporter->name ?? '—' }}</p>
                <p class="text-muted">{{ $trade->dispute->description }}</p>
                @endif

                @if($trade->status === 'disputed')
                <hr class="my-4">
                <h6 class="fw-bold mb-2">Resolve Dispute</h6>
                <form action="{{ route('admin.trades.resolve-dispute', $trade->id) }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label">Resolution</label>
                        <select name="resolution" class="form-select" required>
                            <option value="">Choose...</option>
                            <option value="completed">Complete trade</option>
                            <option value="cancelled">Cancel trade</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Notes (optional)</label>
                        <input type="text" name="notes" class="form-control" placeholder="Resolution notes..." maxlength="1000">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-success">Resolve</button>
                    </div>
                </form>
                @endif

                @if($trade->status !== 'completed' && $trade->status !== 'cancelled')
                <hr class="my-4">
                <form action="{{ route('admin.trades.cancel', $trade->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this trade?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">Cancel Trade</button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
