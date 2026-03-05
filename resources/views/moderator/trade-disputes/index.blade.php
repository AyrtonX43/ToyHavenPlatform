@extends('layouts.admin-new')
@section('title', 'Trade Disputes - Moderator')
@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Trade Disputes</h1>
    <form class="row g-2 mb-4" method="GET">
        <div class="col-auto"><select name="status" class="form-select"><option value="">All</option><option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option></select></div>
        <div class="col-auto"><button type="submit" class="btn btn-primary">Filter</button></div>
    </form>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>ID</th><th>Trade</th><th>Reporter</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach($disputes as $d)
                <tr>
                    <td>{{ $d->id }}</td>
                    <td>Trade #{{ $d->trade_id }}</td>
                    <td>{{ $d->reporter->name ?? '-' }}</td>
                    <td><span class="badge bg-{{ $d->status === 'open' ? 'warning' : 'secondary' }}">{{ ucfirst($d->status) }}</span></td>
                    <td><a href="{{ route('moderator.trade-disputes.show', $d->id) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $disputes->links() }}
</div>
@endsection
