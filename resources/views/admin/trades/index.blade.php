@extends('layouts.admin-new')
@section('title', 'Trades - Admin')
@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Trades</h1>
    <form class="row g-2 mb-4" method="GET">
        <div class="col-auto"><input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}"></div>
        <div class="col-auto"><select name="status" class="form-select"><option value="">All</option><option value="pending_meetup" {{ request('status') === 'pending_meetup' ? 'selected' : '' }}>Pending Meetup</option><option value="meetup_scheduled" {{ request('status') === 'meetup_scheduled' ? 'selected' : '' }}>Scheduled</option><option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option><option value="disputed" {{ request('status') === 'disputed' ? 'selected' : '' }}>Disputed</option></select></div>
        <div class="col-auto"><button type="submit" class="btn btn-primary">Filter</button></div>
    </form>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>ID</th><th>Listing</th><th>Parties</th><th>Status</th><th>Created</th><th></th></tr></thead>
            <tbody>
                @foreach($trades as $t)
                <tr>
                    <td>{{ $t->id }}</td>
                    <td>{{ Str::limit($t->tradeListing->title ?? '-', 40) }}</td>
                    <td>{{ $t->initiator->name ?? '-' }} / {{ $t->participant->name ?? '-' }}</td>
                    <td><span class="badge bg-secondary">{{ $t->getStatusLabel() }}</span></td>
                    <td>{{ $t->created_at->format('M d, Y') }}</td>
                    <td><a href="{{ route('admin.trades.show', $t->id) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $trades->links() }}
</div>
@endsection
