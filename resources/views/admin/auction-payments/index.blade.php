@extends('layouts.admin-new')

@section('title', 'Auction Payments - Admin')
@section('page-title', 'Auction Payments')

@section('content')
<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Auction</th>
                    <th>Winner</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Escrow</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                    <tr>
                        <td>{{ $p->id }}</td>
                        <td>{{ $p->auction?->title }}</td>
                        <td>{{ $p->winner?->name }}</td>
                        <td>₱{{ number_format($p->total_amount, 0) }}</td>
                        <td><span class="badge bg-{{ $p->payment_status === 'paid' ? 'success' : 'warning' }}">{{ $p->payment_status }}</span></td>
                        <td><span class="badge bg-secondary">{{ $p->escrow_status }}</span></td>
                        <td><a href="{{ ($context ?? null) === 'moderator' ? route('moderator.auction-payments.show', $p) : route('admin.auction-payments.show', $p) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7">No payments.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $payments->links() }}
    </div>
</div>
@endsection
