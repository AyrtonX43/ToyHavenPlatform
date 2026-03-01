@extends('layouts.admin-new')

@section('title', 'Auction Payments - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <h1 class="text-3xl font-bold mb-6">Auction Payments & Escrow</h1>

                <form method="GET" class="mb-6 row g-3">
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            @foreach(['awaiting_payment', 'held', 'released', 'disputed', 'refunded'] as $s)
                                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-secondary">Filter</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Auction</th>
                                <th>Winner</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Escrow</th>
                                <th>Delivery</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $p)
                                <tr>
                                    <td>{{ $p->id }}</td>
                                    <td>{{ Str::limit($p->auction->title ?? 'N/A', 30) }}</td>
                                    <td>{{ $p->winner->name ?? 'N/A' }}</td>
                                    <td>₱{{ number_format($p->total_amount, 2) }}</td>
                                    <td><span class="badge bg-{{ $p->payment_status === 'paid' ? 'success' : ($p->payment_status === 'pending' ? 'warning' : 'danger') }}">{{ ucfirst($p->payment_status) }}</span></td>
                                    <td><span class="badge bg-{{ $p->escrow_status === 'held' ? 'info' : ($p->escrow_status === 'released' ? 'success' : 'warning') }}">{{ ucfirst(str_replace('_', ' ', $p->escrow_status)) }}</span></td>
                                    <td>{{ $p->delivery_status ? ucfirst(str_replace('_', ' ', $p->delivery_status)) : '-' }}</td>
                                    <td>
                                        <a href="{{ route('admin.auction-payments.show', $p) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted">No payments found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $payments->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
