@extends('layouts.toyshop')
@section('title', 'My Listings - ToyHaven Trade')
@section('content')
<div class="container py-4">
    <h1 class="h3 mb-4">My Listings</h1>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <a href="{{ route('trading.listings.create') }}" class="btn btn-primary mb-4"><i class="bi bi-plus-lg me-1"></i>Create Listing</a>

    @if($listings->count() > 0)
    <div class="row g-4">
        @foreach($listings as $listing)
        <div class="col-md-4">
            <div class="card h-100">
                @php $thumb = $listing->getThumbnailImage(); @endphp
                @if($thumb)
                <img src="{{ asset('storage/' . $thumb->image_path) }}" class="card-img-top" style="height: 180px; object-fit: cover;" alt="">
                @else
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px;"><i class="bi bi-image text-muted"></i></div>
                @endif
                <div class="card-body">
                    <span class="badge bg-{{ $listing->status === 'active' ? 'success' : ($listing->status === 'pending_approval' ? 'warning' : 'secondary') }}">{{ $listing->getStatusLabel() }}</span>
                    <h5 class="card-title mt-2">{{ Str::limit($listing->title, 40) }}</h5>
                    <p class="card-text small text-muted">{{ ucfirst(str_replace('_', ' ', $listing->trade_type)) }}</p>
                    <div class="d-flex gap-2">
                        <a href="{{ route('trading.listings.show', $listing->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                        @if(in_array($listing->status, ['pending_approval', 'active']) && $listing->status !== 'pending_deal')
                        <a href="{{ route('trading.listings.edit', $listing->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        @endif
                        @if($listing->status === 'active')
                        <form action="{{ route('trading.listings.mark-sold', $listing->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Mark as sold?');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-success">Mark Sold</button>
                        </form>
                        @endif
                        @if(!in_array($listing->status, ['pending_deal', 'completed']))
                        <form action="{{ route('trading.listings.destroy', $listing->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this listing?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $listings->links() }}</div>
    @else
    <div class="text-center py-5 bg-light rounded-3">
        <p class="text-muted">You have no listings yet.</p>
        <a href="{{ route('trading.listings.create') }}" class="btn btn-primary">Create Listing</a>
    </div>
    @endif
</div>
@endsection
