@extends('layouts.admin-new')
@section('title', 'Trade Listings - Moderator')
@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Trade Listings</h1>
    <form class="row g-2 mb-4" method="GET">
        <div class="col-auto"><input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}"></div>
        <div class="col-auto"><select name="status" class="form-select"><option value="">All</option><option value="pending_approval" {{ request('status') === 'pending_approval' ? 'selected' : '' }}>Pending</option></select></div>
        <div class="col-auto"><button type="submit" class="btn btn-primary">Filter</button></div>
    </form>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>ID</th><th>Title</th><th>User</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach($listings as $l)
                <tr>
                    <td>{{ $l->id }}</td>
                    <td>{{ Str::limit($l->title, 40) }}</td>
                    <td>{{ $l->user->name ?? '-' }}</td>
                    <td><span class="badge bg-{{ $l->status === 'pending_approval' ? 'warning' : 'secondary' }}">{{ $l->getStatusLabel() }}</span></td>
                    <td><a href="{{ route('moderator.trade-listings.show', $l->id) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $listings->links() }}
</div>
@endsection
