@extends('layouts.toyshop')

@section('title', 'Report Trade Dispute - Trade #' . $trade->id)

@section('content')
<div class="container my-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trading.trades.index') }}">My Trades</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trading.trades.show', $trade->id) }}">Trade #{{ $trade->id }}</a></li>
            <li class="breadcrumb-item active">Report Dispute</li>
        </ol>
    </nav>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-warning bg-opacity-10">
            <h4 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Report a Trade Dispute</h4>
            <p class="text-muted small mb-0 mt-1">Trade #{{ $trade->id }} with {{ $trade->getOtherParty(Auth::id())->name }}</p>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('trading.trades.dispute', $trade->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="type" class="form-label">Dispute Type <span class="text-danger">*</span></label>
                    <select name="type" id="type" class="form-select" required>
                        <option value="">Select type...</option>
                        <option value="not_received" {{ old('type') == 'not_received' ? 'selected' : '' }}>Item Not Received</option>
                        <option value="damaged" {{ old('type') == 'damaged' ? 'selected' : '' }}>Item Damaged</option>
                        <option value="wrong_item" {{ old('type') == 'wrong_item' ? 'selected' : '' }}>Wrong Item Received</option>
                        <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other Issue</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea name="description" id="description" class="form-control" rows="4" required maxlength="2000" placeholder="Please describe the issue in detail...">{{ old('description') }}</textarea>
                    <small class="text-muted">Include relevant details to help us resolve the dispute quickly.</small>
                </div>
                <div class="mb-4">
                    <label for="evidence_images" class="form-label">Evidence (optional)</label>
                    <input type="file" name="evidence_images[]" id="evidence_images" class="form-control" multiple accept="image/jpeg,image/png,image/jpg,image/webp">
                    <small class="text-muted">You can upload multiple images. Max 5 MB per image.</small>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i> Submit Dispute
                    </button>
                    <a href="{{ route('trading.trades.show', $trade->id) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
