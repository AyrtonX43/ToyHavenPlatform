@extends('layouts.toyshop')

@section('title', 'My Auction Sales - ToyHaven')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">My Auction Sales</h2>

    <p class="mb-4">
        <a href="{{ route('auctions.seller.index') }}" class="btn btn-outline-secondary">Back to Listings</a>
    </p>

    @if($sales->count() > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Auction</th>
                        <th>Winner</th>
                        <th>Amount</th>
                        <th>Paid</th>
                        <th>Delivery</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $sale)
                        <tr>
                            <td>{{ $sale->auction->title }}</td>
                            <td>{{ $sale->winner->name }}</td>
                            <td>₱{{ number_format($sale->total_amount, 0) }}</td>
                            <td>{{ $sale->paid_at?->format('M d, Y') }}</td>
                            <td>
                                @if($sale->seller_delivery_confirmed_at)
                                    <span class="badge bg-success">Confirmed</span>
                                @elseif($sale->winner_received_confirmed_at)
                                    <span class="badge bg-info">Awaiting your confirmation</span>
                                @else
                                    <span class="badge bg-secondary">{{ $sale->delivery_status ?? 'Pending' }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('auctions.seller.sales.show', $sale) }}" class="btn btn-sm btn-outline-primary">Manage</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $sales->links() }}
    @else
        <p class="text-muted">You have no completed auction sales yet.</p>
    @endif
</div>
@endsection
