@extends('layouts.toyshop')

@section('title', 'Pay for ' . $auction->title)

@section('content')
<div class="container py-5">
    <h2 class="mb-4">Complete Payment</h2>
    <div class="card">
        <div class="card-body">
            <h4>{{ $auction->title }}</h4>
            <p class="fs-4 text-success fw-bold">Amount: ₱{{ number_format($payment->amount, 2) }}</p>
            <p class="text-muted">Pay within 24 hours.</p>

            @if(isset($client_key))
                <div id="paymongo-mount" class="my-4"></div>
                <script src="https://js.paymongo.com/v1"></script>
                <script>
                    PayMongoCard();
                </script>
            @else
                <form action="{{ route('auctions.payment.paymongo', $payment) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary">Pay with QR Ph</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
