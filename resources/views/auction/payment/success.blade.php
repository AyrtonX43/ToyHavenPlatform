@extends('layouts.toyshop')

@section('title', 'Payment Successful - ToyHaven')

@push('styles')
<link href="{{ asset('css/auction.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="auction-payment-card text-center">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size:4rem;"></i>
                    </div>
                    <h3 class="fw-bold mb-2">Payment Successful</h3>
                    <p class="text-muted mb-4">You have paid for "{{ Str::limit($payment->auction->title, 50) }}" (₱{{ number_format($payment->amount, 2) }}).</p>
                    @if(in_array($payment->delivery_status, ['shipped']))
                        <div class="p-3 rounded-3 mb-4 text-start" style="background:#f0fdfa;border:1px solid #0d9488;">
                            <p class="small mb-2">The seller has marked your item as shipped. @if($payment->tracking_number) <strong>Tracking:</strong> {{ $payment->tracking_number }} @endif</p>
                            <form action="{{ route('auction.payment.confirm-delivery', $payment) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success rounded-pill px-4">I have received the item</button>
                            </form>
                        </div>
                    @elseif(in_array($payment->delivery_status, ['delivered', 'confirmed']))
                        <p class="small text-success mb-4"><i class="bi bi-check-circle-fill me-1"></i>You have confirmed delivery. Thank you!</p>
                    @else
                        <p class="small text-muted mb-4">The seller will ship your item. You can confirm delivery once you receive it.</p>
                    @endif

                    @php $canReview = $payment->delivery_status === 'delivered' || $payment->delivery_status === 'confirmed'; @endphp
                    @if($canReview && !\App\Models\AuctionReview::where('auction_payment_id', $payment->id)->exists())
                        <hr class="my-4">
                        <h6 class="fw-semibold mb-3">Leave a Review</h6>
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
                            <button type="submit" class="btn btn-primary rounded-pill px-4">Submit Review</button>
                        </form>
                    @endif

                    <a href="{{ route('auction.index') }}" class="btn btn-primary mt-4 rounded-pill px-4">Back to Auctions</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
