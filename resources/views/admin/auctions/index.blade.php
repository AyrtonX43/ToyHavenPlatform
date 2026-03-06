@extends('layouts.admin-new')

@section('title', 'Auctions')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Auctions</h1>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="mb-3">
        <a href="{{ route('admin.auctions.index') }}" class="btn btn-outline-secondary btn-sm">All</a>
        <a href="{{ route('admin.auctions.index', ['status' => 'pending_approval']) }}" class="btn btn-outline-warning btn-sm">Pending Approval</a>
        <a href="{{ route('admin.auctions.index', ['status' => 'active']) }}" class="btn btn-outline-success btn-sm">Active</a>
        <a href="{{ route('admin.auctions.index', ['status' => 'ended']) }}" class="btn btn-outline-info btn-sm">Ended</a>
        <a href="{{ route('admin.auctions.index', ['status' => 'draft']) }}" class="btn btn-outline-secondary btn-sm">Draft</a>
        <a href="{{ route('admin.auctions.index', ['status' => 'cancelled']) }}" class="btn btn-outline-danger btn-sm">Cancelled</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Seller</th>
                    <th>Category</th>
                    <th>Current</th>
                    <th>Status</th>
                    <th>Ends</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($auctions as $a)
                    <tr>
                        <td>{{ Str::limit($a->title, 40) }}</td>
                        <td>{{ $a->user->name ?? 'N/A' }}</td>
                        <td>{{ $a->category->name ?? '-' }}</td>
                        <td>₱{{ number_format($a->currentPrice(), 0) }}</td>
                        <td>
                            <span class="badge
                                @if($a->status === 'draft') bg-secondary
                                @elseif($a->status === 'pending_approval') bg-warning text-dark
                                @elseif($a->status === 'active') bg-success
                                @elseif($a->status === 'ended') bg-info
                                @else bg-danger
                                @endif
                            ">{{ ucfirst(str_replace('_',' ',$a->status)) }}</span>
                        </td>
                        <td>{{ $a->end_at?->format('M j, g:i A') }}</td>
                        <td>
                            <a href="{{ route('admin.auctions.show', $a) }}" class="btn btn-sm btn-primary">View</a>
                            @if($a->status === 'pending_approval')
                                <form action="{{ route('admin.auctions.approve', $a) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                </form>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $a->id }}">Reject</button>
                            @endif
                        </td>
                    </tr>
                    @if($a->status === 'pending_approval')
                    <div class="modal fade" id="rejectModal{{ $a->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('admin.auctions.reject', $a) }}" method="POST">
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
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $auctions->links() }}
</div>
@endsection
