@extends('layouts.admin')

@section('title', 'Auctions - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <div class="d-flex justify-content-between align-items-center mb-6">
                    <h1 class="text-3xl font-bold mb-0">Auctions</h1>
                    <a href="{{ route('admin.auctions.create') }}" class="btn btn-primary"><i class="bi bi-plus me-1"></i>Create Auction</a>
                </div>

                <form method="GET" action="{{ route('admin.auctions.index') }}" class="mb-6 row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="live" {{ request('status') == 'live' ? 'selected' : '' }}>Live</option>
                            <option value="ended" {{ request('status') == 'ended' ? 'selected' : '' }}>Ended</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by title...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-secondary me-2">Filter</button>
                        <a href="{{ route('admin.auctions.index') }}" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Current Price</th>
                                <th>Bids</th>
                                <th>Status</th>
                                <th>Ends</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($auctions as $auction)
                                <tr>
                                    <td>{{ $auction->id }}</td>
                                    <td>{{ Str::limit($auction->title, 40) }}</td>
                                    <td>₱{{ number_format($auction->getCurrentPrice(), 2) }}</td>
                                    <td>{{ $auction->bids_count }}</td>
                                    <td><span class="badge bg-{{ $auction->status === 'live' ? 'success' : ($auction->status === 'ended' ? 'secondary' : 'warning') }}">{{ $auction->status }}</span></td>
                                    <td>{{ $auction->end_at?->format('M d, Y H:i') ?? 'TBD' }}</td>
                                    <td>
                                        <a href="{{ route('admin.auctions.show', $auction) }}" class="btn btn-sm btn-outline-primary">View</a>
                                        <a href="{{ route('admin.auctions.edit', $auction) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No auctions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $auctions->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
