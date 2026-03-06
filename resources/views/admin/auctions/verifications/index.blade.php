@extends('layouts.admin-new')

@section('title', 'Auction Seller Verifications')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Auction Seller Verifications</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="mb-3">
        <a href="{{ route('admin.auction-verifications.index') }}" class="btn btn-outline-secondary btn-sm">All</a>
        <a href="{{ route('admin.auction-verifications.index', ['status' => 'pending']) }}" class="btn btn-outline-warning btn-sm">Pending</a>
        <a href="{{ route('admin.auction-verifications.index', ['status' => 'approved']) }}" class="btn btn-outline-success btn-sm">Approved</a>
        <a href="{{ route('admin.auction-verifications.index', ['status' => 'rejected']) }}" class="btn btn-outline-danger btn-sm">Rejected</a>
    </div>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($verifications as $v)
                    <tr>
                        <td>{{ $v->user->name ?? 'N/A' }}<br><small>{{ $v->user->email }}</small></td>
                        <td>{{ ucfirst($v->type) }}</td>
                        <td>
                            <span class="badge bg-{{ $v->verification_status === 'approved' ? 'success' : ($v->verification_status === 'rejected' ? 'danger' : 'warning') }}">{{ $v->verification_status }}</span>
                        </td>
                        <td>{{ $v->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('admin.auction-verifications.show', $v) }}" class="btn btn-sm btn-primary">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $verifications->links() }}
</div>
@endsection
