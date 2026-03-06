@extends('layouts.toyshop')

@section('title', 'Auction History')

@section('content')
<div class="container py-5">
    <h2 class="mb-4"><i class="bi bi-clock-history me-2"></i>Auction History</h2>

    @if($payments->isEmpty())
        <p class="text-muted">You haven't won any auctions yet.</p>
        <a href="{{ route('auctions.index') }}" class="btn btn-primary">Browse Auctions</a>
    @else
        {{ $payments->links() }}
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Auction</th><th>Amount</th><th>Status</th><th>Date</th><th></th></tr></thead>
                <tbody>
                    @foreach($payments as $p)
                        <tr>
                            <td>{{ $p->auction->title }}</td>
                            <td>₱{{ number_format($p->amount, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $p->isPaid() ? 'success' : 'warning' }}">
                                    {{ $p->isPaid() ? 'Paid' : 'Pending' }}
                                </span>
                            </td>
                            <td>{{ $p->created_at->format('M j, Y') }}</td>
                            <td>
                                @if(!$p->isPaid())
                                    <a href="{{ route('auctions.payment.index', $p) }}" class="btn btn-sm btn-primary">Pay</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $payments->links() }}
    @endif
</div>
@endsection
