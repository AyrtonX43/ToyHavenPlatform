@extends('layouts.toyshop')
@section('title', 'Open Dispute - ToyHaven')
@section('content')
<div class="container py-4">
    <h1 class="h3 mb-4">Open Dispute</h1>
    <form action="{{ route('trading.trades.dispute', $trade->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="form-label">Type</label>
            <select name="type" class="form-select" required>
                <option value="no_show">No Show</option>
                <option value="wrong_item">Wrong Item</option>
                <option value="damaged">Damaged</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Evidence (images, optional)</label>
            <input type="file" name="evidence_images[]" class="form-control" accept="image/*" multiple>
        </div>
        <button type="submit" class="btn btn-danger">Submit Dispute</button>
        <a href="{{ route('trading.trades.show', $trade->id) }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
@endsection
