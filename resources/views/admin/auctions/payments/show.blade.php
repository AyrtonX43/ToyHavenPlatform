@extends('layouts.admin')

@section('title', 'Auction Payment #' . $auctionPayment->id . ' - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="{{ route('admin.auction-payments.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Payments
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <h1 class="text-2xl font-bold mb-6">Payment #{{ $auctionPayment->id }}</h1>

                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header fw-bold">Payment Details</div>
                            <div class="card-body">
                                <p><strong>Auction:</strong> {{ $auctionPayment->auction->title ?? 'N/A' }}</p>
                                <p><strong>Winner:</strong> {{ $auctionPayment->winner->name ?? 'N/A' }} ({{ $auctionPayment->winner->email ?? '' }})</p>
                                <p><strong>Seller:</strong> {{ $auctionPayment->seller->name ?? 'N/A' }}</p>
                                <p><strong>Bid Amount:</strong> ₱{{ number_format($auctionPayment->bid_amount, 2) }}</p>
                                <p><strong>Buyer Premium:</strong> ₱{{ number_format($auctionPayment->buyer_premium, 2) }}</p>
                                <p><strong>Total:</strong> ₱{{ number_format($auctionPayment->total_amount, 2) }}</p>
                                <p><strong>Platform Fee:</strong> ₱{{ number_format($auctionPayment->platform_fee, 2) }}</p>
                                <p><strong>Seller Payout:</strong> ₱{{ number_format($auctionPayment->seller_payout, 2) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header fw-bold">Status</div>
                            <div class="card-body">
                                <p><strong>Payment:</strong> <span class="badge bg-{{ $auctionPayment->payment_status === 'paid' ? 'success' : 'warning' }}">{{ ucfirst($auctionPayment->payment_status) }}</span></p>
                                <p><strong>Escrow:</strong> <span class="badge bg-{{ $auctionPayment->escrow_status === 'released' ? 'success' : 'info' }}">{{ ucfirst(str_replace('_', ' ', $auctionPayment->escrow_status)) }}</span></p>
                                <p><strong>Delivery:</strong> {{ $auctionPayment->delivery_status ? ucfirst(str_replace('_', ' ', $auctionPayment->delivery_status)) : 'N/A' }}</p>
                                <p><strong>Tracking:</strong> {{ $auctionPayment->tracking_number ?? 'N/A' }}</p>
                                <p><strong>Deadline:</strong> {{ $auctionPayment->payment_deadline?->format('M d, Y H:i') ?? 'N/A' }}</p>
                                <p><strong>Paid At:</strong> {{ $auctionPayment->paid_at?->format('M d, Y H:i') ?? 'N/A' }}</p>
                                <p><strong>Confirmed At:</strong> {{ $auctionPayment->confirmed_at?->format('M d, Y H:i') ?? 'N/A' }}</p>
                                <p class="mb-0"><strong>Released At:</strong> {{ $auctionPayment->released_at?->format('M d, Y H:i') ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($auctionPayment->escrow_status === 'held')
                    <div class="d-flex gap-3">
                        <form action="{{ route('admin.auction-payments.release-escrow', $auctionPayment) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Release escrow to seller?')">
                                <i class="bi bi-check-circle me-1"></i>Release Escrow
                            </button>
                        </form>
                        <form action="{{ route('admin.auction-payments.refund', $auctionPayment) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-lg" onclick="return confirm('Refund payment to buyer?')">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Refund Buyer
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
