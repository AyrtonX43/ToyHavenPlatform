@extends('layouts.admin-new')

@section('title', 'Auction Payment #' . $auctionPayment->id . ' - Admin')
@section('page-title', 'Auction Payment')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <p><strong>Auction:</strong> {{ $auctionPayment->auction->title }}</p>
        <p><strong>Winner:</strong> {{ $auctionPayment->winner->name }} ({{ $auctionPayment->winner->email }})</p>
        <p><strong>Seller:</strong> {{ $auctionPayment->seller->name }}</p>
        <p><strong>Total:</strong> ₱{{ number_format($auctionPayment->total_amount, 0) }}</p>
        <p><strong>Escrow:</strong> {{ $auctionPayment->escrow_status }}</p>
        @if($auctionPayment->escrow_status === 'held' && $auctionPayment->canRelease())
            <form method="POST" action="{{ ($context ?? null) === 'moderator' ? route('moderator.auction-payments.release-escrow', $auctionPayment) : route('admin.auction-payments.release-escrow', $auctionPayment) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">Release Escrow</button>
            </form>
        @endif
    </div>
</div>
<a href="{{ ($context ?? null) === 'moderator' ? route('moderator.auction-payments.index') : route('admin.auction-payments.index') }}" class="btn btn-secondary">Back</a>
@endsection
