@extends('layouts.toyshop')

@section('title', 'Payment Successful - ToyHaven')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body p-5">
                    <i class="bi bi-check-circle text-success display-4 mb-3"></i>
                    <h3>Payment Successful</h3>
                    <p class="text-muted mb-4">You have paid for "{{ $payment->auction->title }}" (₱{{ number_format($payment->amount, 2) }}).</p>
                    @if(in_array($payment->delivery_status, ['shipped']))
                        <p class="small text-muted mb-2">The seller has marked your item as shipped. @if($payment->tracking_number) Tracking: {{ $payment->tracking_number }} @endif</p>
                        <form action="{{ route('auction.payment.confirm-delivery', $payment) }}" method="POST" class="mb-3">
                            @csrf
                            <button type="submit" class="btn btn-success">I have received the item</button>
                        </form>
                    @elseif(in_array($payment->delivery_status, ['delivered', 'confirmed']))
                        <p class="small text-success mb-2"><i class="bi bi-check-circle me-1"></i>You have confirmed delivery. Thank you!</p>
                    @else
                        <p class="small text-muted">The seller will ship your item. You can confirm delivery once you receive it.</p>
                    @endif

                    @php $canReview = $payment->delivery_status === 'delivered' || $payment->delivery_status === 'confirmed'; @endphp
                    @if($canReview && !\App\Models\AuctionReview::where('auction_payment_id', $payment->id)->exists())
                        <hr class="my-4">
                        <h6>Leave a Review</h6>
                        <form action="{{ route('auction.review.store', $payment) }}" method="POST" class="text-start">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Rating (1-5 stars)</label>
                                <select name="rating" class="form-select" required>
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}">{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Feedback (optional)</label>
                                <textarea name="feedback" class="form-control" rows="3" maxlength="2000"></textarea>
                            </div>
                            <button type="submit" class="btn btn-outline-primary">Submit Review</button>
                        </form>
                    @endif

                    <a href="{{ route('auction.index') }}" class="btn btn-primary mt-3">Back to Auctions</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
