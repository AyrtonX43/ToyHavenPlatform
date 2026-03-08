@extends('layouts.admin-new')

@section('title', 'Auction Seller Verifications')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Auction Seller Verifications</h1>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-3">
        <a href="{{ route('admin.auction-seller-verifications.index', ['status' => 'pending']) }}" class="btn btn-sm {{ !request('status') || request('status') === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">Pending</a>
        <a href="{{ route('admin.auction-seller-verifications.index', ['status' => 'approved']) }}" class="btn btn-sm {{ request('status') === 'approved' ? 'btn-success' : 'btn-outline-success' }}">Approved</a>
        <a href="{{ route('admin.auction-seller-verifications.index', ['status' => 'rejected']) }}" class="btn btn-sm {{ request('status') === 'rejected' ? 'btn-danger' : 'btn-outline-danger' }}">Rejected</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($verifications as $v)
                        <tr>
                            <td>#{{ $v->id }}</td>
                            <td>
                                <strong>{{ $v->user?->name }}</strong><br>
                                <small class="text-muted">{{ $v->user?->email }}</small>
                                @if($v->type === 'business' && $v->business_info)
                                    <br><small class="text-primary">{{ $v->business_info['business_name'] ?? '' }}</small>
                                @endif
                            </td>
                            <td><span class="badge bg-{{ $v->type === 'business' ? 'info' : 'secondary' }}">{{ ucfirst($v->type) }}</span></td>
                            <td>
                                <span class="badge bg-{{ $v->verification_status === 'approved' ? 'success' : ($v->verification_status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($v->verification_status) }}
                                </span>
                            </td>
                            <td>{{ $v->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.auction-seller-verifications.show', $v) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No auction seller verifications found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $verifications->withQueryString()->links() }}</div>
</div>
@endsection
