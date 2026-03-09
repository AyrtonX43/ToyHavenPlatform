@extends('layouts.toyshop')

@section('title', 'Auction History - ToyHaven')

@section('content')
<div class="container py-4 pb-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auction.index') }}">Auction Hub</a></li>
            <li class="breadcrumb-item active">Auction History</li>
        </ol>
    </nav>

    <h2 class="mb-4"><i class="bi bi-clock-history me-2"></i>Auction History (Won)</h2>

    @if($payments->count() > 0)
        <div class="row g-4">
            @foreach($payments as $payment)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0">
                        @php $primaryImg = $payment->auction->images->firstWhere('is_primary', true) ?? $payment->auction->images->first(); @endphp
                        @if($primaryImg)
                            <img src="{{ asset('storage/' . $primaryImg->image_path) }}" alt="" class="card-img-top" style="height:180px;object-fit:cover;">
                        @endif
                        <div class="card-body">
                            <h6 class="card-title">{{ Str::limit($payment->auction->title, 50) }}</h6>
                            <p class="mb-1"><strong>Winning Bid:</strong> ₱{{ number_format($payment->amount, 2) }}</p>
                            
                            <p class="mb-2">
                                <strong>Status:</strong> 
                                @if($payment->status === 'pending')
                                    <span class="badge bg-warning text-dark">Awaiting Payment</span>
                                @elseif($payment->status === 'paid' || $payment->status === 'held' || $payment->status === 'released')
                                    <span class="badge bg-success">Paid</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($payment->status) }}</span>
                                @endif
                            </p>

                            @if($payment->isPending())
                                <a href="{{ route('auction.payment.show', $payment) }}" class="btn btn-sm btn-primary w-100">Proceed to Payment</a>
                            @else
                                <a href="{{ route('auction.payment.success', $payment) }}" class="btn btn-sm btn-outline-success w-100">View Details</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $payments->links() }}</div>
    @else
        <div class="text-center py-5 bg-light rounded border border-dashed">
            <i class="bi bi-trophy fs-1 text-muted mb-3 d-block"></i>
            <p class="text-muted mb-0">You haven't won any auctions yet.</p>
            <a href="{{ route('auction.index') }}" class="btn btn-primary mt-3">Browse Auctions</a>
        </div>
    @endif
</div>
@endsection
