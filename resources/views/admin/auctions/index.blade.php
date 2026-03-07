@extends('layouts.admin-new')

@section('title', 'Auctions - Admin')
@section('page-title', 'Auctions')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Seller</th>
                        <th>Status</th>
                        <th>Start</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auctions as $a)
                        <tr>
                            <td>{{ $a->title }}</td>
                            <td>{{ $a->auctionSellerProfile?->user?->name ?? '-' }}</td>
                            <td><span class="badge bg-{{ match($a->status) { 'pending_approval' => 'warning', 'approved' => 'info', 'live' => 'success', 'ended' => 'secondary', 'cancelled' => 'danger', default => 'secondary' } }}">{{ $a->status }}</span></td>
                            <td>{{ $a->start_at?->format('M d, Y H:i') ?? '-' }}</td>
                            <td>
                                <a href="{{ ($context ?? null) === 'moderator' ? route('moderator.auctions.show', $a) : route('admin.auctions.show', $a) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">No auctions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $auctions->links() }}
    </div>
</div>
@endsection
