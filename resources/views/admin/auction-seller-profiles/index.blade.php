@extends('layouts.admin-new')

@section('title', 'Auction Seller Profiles - Admin')
@section('page-title', 'Auction Seller Profiles')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Type</th>
                        <th>Business Name</th>
                        <th>Status</th>
                        <th>Applied</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($profiles as $p)
                        <tr>
                            <td>{{ $p->user->name }}<br><small>{{ $p->user->email }}</small></td>
                            <td>{{ ucfirst($p->seller_type) }}</td>
                            <td>{{ $p->business_name ?? '-' }}</td>
                            <td><span class="badge bg-{{ match($p->status) { 'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'suspended' => 'secondary', default => 'secondary' } }}">{{ $p->status }}</span></td>
                            <td>{{ $p->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ ($context ?? null) === 'moderator' ? route('moderator.auction-seller-profiles.show', $p) : route('admin.auction-seller-profiles.show', $p) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">No applications yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $profiles->links() }}
    </div>
</div>
@endsection
