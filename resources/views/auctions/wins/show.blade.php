@extends('layouts.toyshop')

@section('title', 'Win Details - ' . $auctionPayment->auction->title)

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auctions.wins.index') }}">My Wins</a></li>
            <li class="breadcrumb-item active">{{ $auctionPayment->auction->title }}</li>
        </ol>
    </nav>

    <h2 class="mb-4">{{ $auctionPayment->auction->title }}</h2>

    <div class="row">
        <div class="col-lg-8">
            @if($auctionPayment->auction->images->first())
                <img src="{{ asset('storage/' . $auctionPayment->auction->images->first()->path) }}" class="img-fluid rounded mb-3" alt="{{ $auctionPayment->auction->title }}">
            @endif

            <div class="card mb-4">
                <div class="card-body">
                    <p><strong>Amount paid:</strong> ₱{{ number_format($auctionPayment->total_amount, 0) }}</p>
                    <p><strong>Paid at:</strong> {{ $auctionPayment->paid_at?->format('F d, Y H:i') }}</p>
                    @if($auctionPayment->receipt_path)
                        <a href="{{ asset('storage/' . $auctionPayment->receipt_path) }}" target="_blank" class="btn btn-outline-secondary btn-sm">Download Receipt</a>
                    @endif
                </div>
            </div>

            @if($auctionPayment->trackingUpdates->count() > 0)
            <div class="card mb-4">
                <div class="card-header">Tracking</div>
                <div class="card-body">
                    @foreach($auctionPayment->trackingUpdates as $tu)
                        <p class="mb-1"><strong>{{ $tu->tracking_number }}</strong> @if($tu->carrier)({{ $tu->carrier }})@endif</p>
                        @if($tu->notes)<p class="text-muted small mb-2">{{ $tu->notes }}</p>@endif
                    @endforeach
                </div>
            </div>
            @endif

            @if(!$auctionPayment->winner_received_confirmed_at)
            <div class="card mb-4 border-primary">
                <div class="card-header">Confirm Receipt</div>
                <div class="card-body">
                    <p>Have you received the item? Upload a proof photo and confirm.</p>
                    <form action="{{ route('auctions.payment.confirm-received', $auctionPayment) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Proof photo (required)</label>
                            <input type="file" name="proof" class="form-control" accept="image/*" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Confirm I Received</button>
                    </form>
                </div>
            </div>
            @else
            <div class="alert alert-success mb-4">
                You confirmed receipt on {{ $auctionPayment->winner_received_confirmed_at->format('F d, Y') }}.
            </div>
            @endif

            @php $hasListingReview = $auctionPayment->reviews->where('for_listing', true)->first(); $hasSellerReview = $auctionPayment->reviews->where('for_listing', false)->first(); @endphp
            @if($auctionPayment->winner_received_confirmed_at && (!$hasListingReview || !$hasSellerReview))
            <div class="card mb-4">
                <div class="card-header">Rate & Review</div>
                <div class="card-body">
                    <form action="{{ route('auctions.review.store', $auctionPayment) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Rating for listing (1–5)</label>
                            <select name="rating_listing" class="form-select" required>
                                <option value="">Select</option>
                                @for($i=1;$i<=5;$i++)<option value="{{ $i }}">{{ $i }} star{{ $i>1?'s':'' }}</option>@endfor
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Feedback for listing (optional)</label>
                            <textarea name="feedback_listing" class="form-control" rows="2" maxlength="1000"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rating for seller (1–5)</label>
                            <select name="rating_seller" class="form-select" required>
                                <option value="">Select</option>
                                @for($i=1;$i<=5;$i++)<option value="{{ $i }}">{{ $i }} star{{ $i>1?'s':'' }}</option>@endfor
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Feedback for seller (optional)</label>
                            <textarea name="feedback_seller" class="form-control" rows="2" maxlength="1000"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                    </form>
                </div>
            </div>
            @elseif($hasListingReview && $hasSellerReview)
            <div class="alert alert-success">
                Thank you for your review!
            </div>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <a href="{{ route('auctions.wins.index') }}" class="btn btn-secondary">Back to My Wins</a>
</div>
@endsection
