@extends('layouts.admin-new')

@section('title', 'Auction Payments')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4 fw-bold">Auction Payments</h1>
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

    <form action="{{ url()->current() }}" method="GET" class="mb-3">
        <select name="status" class="form-select form-select-sm w-auto d-inline-block" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
            <option value="held" {{ request('status') === 'held' ? 'selected' : '' }}>Held</option>
            <option value="released" {{ request('status') === 'released' ? 'selected' : '' }}>Released</option>
            <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
        </select>
    </form>

    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Auction</th>
                        <th>Winner</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Delivery</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $p)
                        <tr>
                            <td>#{{ $p->id }}</td>
                            <td>{{ Str::limit($p->auction?->title, 30) }}</td>
                            <td>{{ $p->winner?->name }}</td>
                            <td>₱{{ number_format($p->amount, 2) }}</td>
                            <td><span class="badge rounded-pill bg-{{ $p->status === 'released' ? 'success' : ($p->status === 'pending' ? 'warning' : 'info') }}">{{ ucfirst($p->status) }}</span></td>
                            <td>{{ $p->delivery_status ?? '-' }}{{ $p->tracking_number ? ' (' . $p->tracking_number . ')' : '' }}</td>
                            <td>
                                @if(in_array($p->status, ['paid', 'held']) && in_array($p->delivery_status, ['delivered', 'confirmed']))
                                    <form action="{{ route('admin.auction-payments.release', $p) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">Release Escrow</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No auction payments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $payments->withQueryString()->links() }}</div>
</div>
@endsection
