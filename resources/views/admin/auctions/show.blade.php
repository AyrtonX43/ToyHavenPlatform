@extends('layouts.admin-new')

@section('title', 'Auction: ' . $auction->title)

@section('content')
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.auctions.index') }}">Auctions</a></li>
            <li class="breadcrumb-item active">{{ Str::limit($auction->title, 30) }}</li>
        </ol>
    </nav>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h2>{{ $auction->title }}</h2>
                    <span class="badge
                        @if($auction->status === 'draft') bg-secondary
                        @elseif($auction->status === 'pending_approval') bg-warning text-dark
                        @elseif($auction->status === 'active') bg-success
                        @elseif($auction->status === 'ended') bg-info
                        @else bg-danger
                        @endif
                    ">{{ ucfirst(str_replace('_',' ',$auction->status)) }}</span>
                    <hr>
                    <p>{{ $auction->description }}</p>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header">Images</div>
                <div class="card-body">
                    @forelse($auction->images as $img)
                        <img src="{{ Storage::url($img->image_path) }}" alt="" class="img-thumbnail me-2 mb-2" style="max-height:120px">
                    @empty
                        <p class="text-muted mb-0">No images</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body">
                    <p><strong>Seller:</strong> {{ $auction->user->name }} ({{ $auction->user->email }})</p>
                    <p><strong>Category:</strong> {{ $auction->category->name ?? '-' }}</p>
                    <p><strong>Starting bid:</strong> ₱{{ number_format($auction->starting_bid, 2) }}</p>
                    <p><strong>Reserve:</strong> {{ $auction->reserve_price ? '₱' . number_format($auction->reserve_price, 2) : '-' }}</p>
                    <p><strong>Current:</strong> ₱{{ number_format($auction->currentPrice(), 2) }}</p>
                    <p><strong>Bids:</strong> {{ $auction->bids_count }}</p>
                    <p><strong>Ends:</strong> {{ $auction->end_at?->format('M j, Y g:i A') }}</p>
                    @if($auction->status === 'pending_approval')
                        <hr>
                        <form action="{{ route('admin.auctions.approve', $auction) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">Approve</button>
                        </form>
                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Bid History</div>
        <div class="card-body">
            <table class="table table-sm">
                <thead><tr><th>Bidder (alias)</th><th>Amount</th><th>Time</th></tr></thead>
                <tbody>
                    @forelse($auction->bids as $bid)
                        <tr>
                            <td>{{ $bid->displayName() }}</td>
                            <td>₱{{ number_format($bid->amount, 2) }}</td>
                            <td>{{ $bid->created_at->format('M j, g:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted">No bids</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.auctions.reject', $auction) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Auction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Feedback (required)</label>
                    <textarea name="feedback" class="form-control" rows="3" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
