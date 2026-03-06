@extends('layouts.toyshop')

@section('title', 'Sale Details - ' . $auctionPayment->auction->title)

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auctions.seller.sales.index') }}">My Sales</a></li>
            <li class="breadcrumb-item active">{{ $auctionPayment->auction->title }}</li>
        </ol>
    </nav>

    <h2 class="mb-4">Sale: {{ $auctionPayment->auction->title }}</h2>

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Winner:</strong> {{ $auctionPayment->winner->name }}</p>
            <p><strong>Total paid:</strong> ₱{{ number_format($auctionPayment->total_amount, 0) }}</p>
            <p><strong>Paid at:</strong> {{ $auctionPayment->paid_at?->format('F d, Y H:i') }}</p>
        </div>
    </div>

    @if($auctionPayment->trackingUpdates->count() > 0)
    <div class="card mb-4">
        <div class="card-header">Tracking History</div>
        <div class="card-body">
            <ul class="list-unstyled mb-0">
                @foreach($auctionPayment->trackingUpdates as $tu)
                    <li class="mb-2">
                        <strong>{{ $tu->tracking_number }}</strong>
                        @if($tu->carrier) ({{ $tu->carrier }}) @endif
                        @if($tu->notes) – {{ $tu->notes }} @endif
                        <small class="text-muted">{{ $tu->created_at->format('M d, Y') }}</small>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">Add Tracking</div>
        <div class="card-body">
            <form action="{{ route('auctions.payment.tracking.store', $auctionPayment) }}" method="POST">
                @csrf
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label">Tracking Number</label>
                        <input type="text" name="tracking_number" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Carrier (e.g. LBC, J&T)</label>
                        <input type="text" name="carrier" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-2">Add Tracking</button>
            </form>
        </div>
    </div>

    @if(!$auctionPayment->seller_delivery_confirmed_at)
    <div class="card mb-4 border-warning">
        <div class="card-body">
            <p class="mb-2">Once the buyer has received the item and you have confirmed delivery, click below to complete the sale.</p>
            <form action="{{ route('auctions.payment.confirm-seller-delivery', $auctionPayment) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">Confirm I Have Delivered</button>
            </form>
        </div>
    </div>
    @else
    <div class="alert alert-success">
        You confirmed delivery on {{ $auctionPayment->seller_delivery_confirmed_at->format('F d, Y') }}.
    </div>
    @endif

    <a href="{{ route('auctions.seller.sales.index') }}" class="btn btn-secondary">Back to Sales</a>
</div>
@endsection
