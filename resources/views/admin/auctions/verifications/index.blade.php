@extends('layouts.admin')

@section('title', 'Auction Seller Verifications - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <div class="d-flex justify-content-between align-items-center mb-6">
                    <h1 class="text-3xl font-bold mb-0">Auction Seller Verifications</h1>
                </div>

                <form method="GET" action="{{ route('admin.auction-verifications.index') }}" class="mb-6 row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="pending" {{ request('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="requires_resubmission" {{ request('status') == 'requires_resubmission' ? 'selected' : '' }}>Requires Resubmission</option>
                            <option value="" {{ request('status') === '' ? 'selected' : '' }}>All</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by user name or email...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-secondary me-2">Filter</button>
                        <a href="{{ route('admin.auction-verifications.index') }}" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Documents</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($verifications as $v)
                                <tr>
                                    <td>{{ $v->id }}</td>
                                    <td>
                                        <div>{{ $v->user->name }}</div>
                                        <small class="text-muted">{{ $v->user->email }}</small>
                                    </td>
                                    <td><span class="badge bg-{{ $v->seller_type === 'business' ? 'success' : 'primary' }}">{{ ucfirst($v->seller_type) }}</span></td>
                                    <td><span class="badge bg-{{ $v->status === 'approved' ? 'success' : ($v->status === 'pending' ? 'warning' : 'danger') }}">{{ ucfirst(str_replace('_', ' ', $v->status)) }}</span></td>
                                    <td>{{ $v->documents()->count() }}</td>
                                    <td>{{ $v->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.auction-verifications.show', $v) }}" class="btn btn-sm btn-outline-primary">Review</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No verifications found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $verifications->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
