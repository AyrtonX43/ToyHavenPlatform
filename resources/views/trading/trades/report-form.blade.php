@extends('layouts.toyshop')

@section('title', 'Report Trade - Trade #' . $trade->id)

@section('content')
<div class="container my-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trading.trades.index') }}">My Trades</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trading.trades.show', $trade->id) }}">Trade #{{ $trade->id }}</a></li>
            <li class="breadcrumb-item active">Report Trade</li>
        </ol>
    </nav>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-danger bg-opacity-10">
            <h4 class="mb-0"><i class="bi bi-flag me-2"></i>Report Product &amp; Seller</h4>
            <p class="text-muted small mb-0 mt-1">Trade #{{ $trade->id }} with {{ $trade->getOtherParty(Auth::id())->name }}</p>
            <p class="text-muted small mb-0">Use this if you did not receive the product or payment. A moderator will review and take action.</p>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('trading.trades.report', $trade->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="report_type" class="form-label">Issue type <span class="text-danger">*</span></label>
                    <select name="report_type" id="report_type" class="form-select" required>
                        <option value="">Select type...</option>
                        <option value="product_not_received" {{ old('report_type') == 'product_not_received' ? 'selected' : '' }}>Product not received</option>
                        <option value="payment_not_received" {{ old('report_type') == 'payment_not_received' ? 'selected' : '' }}>Payment not received</option>
                        <option value="wrong_item" {{ old('report_type') == 'wrong_item' ? 'selected' : '' }}>Wrong item received</option>
                        <option value="damaged" {{ old('report_type') == 'damaged' ? 'selected' : '' }}>Item damaged</option>
                        <option value="seller_issue" {{ old('report_type') == 'seller_issue' ? 'selected' : '' }}>Seller / other party issue</option>
                        <option value="other" {{ old('report_type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="reason" class="form-label">Reason <span class="text-danger">*</span></label>
                    <input type="text" name="reason" id="reason" class="form-control" required maxlength="500" placeholder="Short summary of the issue" value="{{ old('reason') }}">
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description (optional)</label>
                    <textarea name="description" id="description" class="form-control" rows="4" maxlength="2000" placeholder="Add details to help the moderator...">{{ old('description') }}</textarea>
                </div>
                <div class="mb-4">
                    <label for="evidence" class="form-label">Evidence (optional)</label>
                    <input type="file" name="evidence[]" id="evidence" class="form-control" multiple accept="image/jpeg,image/png,image/jpg,image/webp">
                    <small class="text-muted">Up to 5 images. Max 5 MB each.</small>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-flag me-1"></i> Submit Report
                    </button>
                    <a href="{{ route('trading.trades.show', $trade->id) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
