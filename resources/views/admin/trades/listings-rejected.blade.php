@extends('layouts.admin')

@section('title', 'Rejected Trade Listings - Admin')
@section('page-title', 'Rejected Trade Listings by Category')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.trades.listings') }}" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left me-1"></i> Back to Trade Listings
    </a>
    <a href="{{ route('admin.trades.listings', ['status' => 'pending_approval']) }}" class="btn btn-outline-warning ms-2">
        <i class="bi bi-hourglass-split me-1"></i> Pending Review
    </a>
    <a href="{{ route('admin.trades.listings.approved') }}" class="btn btn-outline-success ms-2">
        <i class="bi bi-check2-square me-1"></i> Approved Listings
    </a>
</div>

<p class="text-muted mb-4">Rejected trade listings are grouped by category. These listings were not approved for the marketplace.</p>

@forelse($organized as $group)
<div class="card mb-4">
    <div class="card-header bg-danger bg-opacity-10 border-danger border-start border-4">
        <h5 class="mb-0">
            <i class="bi bi-tag-fill text-danger me-2"></i>
            {{ $group->category->name }}
            <span class="badge bg-danger ms-2">{{ count($group->listings) }} rejected listing(s)</span>
        </h5>
    </div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            @foreach($group->listings as $listing)
            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center flex-grow-1">
                    @php $firstImage = $listing->images->first(); @endphp
                    @if($firstImage)
                        <img src="{{ asset('storage/' . $firstImage->image_path) }}" alt="" class="rounded me-3" style="width: 48px; height: 48px; object-fit: cover;">
                    @else
                        <div class="rounded bg-light d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                            <i class="bi bi-image text-muted"></i>
                        </div>
                    @endif
                    <div class="min-w-0">
                        <strong>{{ Str::limit($listing->title, 50) }}</strong>
                        <br><small class="text-muted">{{ $listing->user->name ?? '—' }} · {{ str_replace('_', ' ', ucfirst($listing->trade_type)) }} · {{ $listing->updated_at->format('M d, Y') }}</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <a href="{{ route('admin.trades.listings.show', $listing->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                        <i class="bi bi-eye"></i>
                    </a>
                    <form action="{{ route('admin.trades.delete-listing', $listing->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this listing?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </li>
            @endforeach
        </ul>
    </div>
</div>
@empty
<div class="card">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-inbox display-4"></i>
        <p class="mb-0 mt-2">No rejected trade listings.</p>
        <a href="{{ route('admin.trades.listings') }}" class="btn btn-outline-primary mt-3">View all listings</a>
    </div>
</div>
@endforelse
@endsection
