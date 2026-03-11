@extends('layouts.toyshop')

@section('title', 'Payment Successful - ToyHaven')

@push('styles')
<link href="{{ asset('css/auction.css') }}" rel="stylesheet">
<style>
    .timeline { position: relative; padding-left: 2rem; }
    .timeline::before { content: ''; position: absolute; left: .65rem; top: .5rem; bottom: .5rem; width: 2px; background: #e2e8f0; }
    .timeline-step { position: relative; margin-bottom: 1.5rem; }
    .timeline-step:last-child { margin-bottom: 0; }
    .timeline-dot { position: absolute; left: -1.6rem; top: .15rem; width: 14px; height: 14px; border-radius: 50%; border: 2px solid #cbd5e1; background: #fff; z-index: 1; }
    .timeline-dot.completed { background: #10b981; border-color: #10b981; }
    .timeline-dot.active { background: #0284c7; border-color: #0284c7; animation: dotPulse 1.5s infinite; }
    @keyframes dotPulse { 0%,100% { box-shadow: 0 0 0 0 rgba(2,132,199,.4); } 50% { box-shadow: 0 0 0 6px rgba(2,132,199,0); } }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="auction-payment-card">
                <div class="card-body p-4 p-lg-5">

                    {{-- Header --}}
                    <div class="text-center mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size:3.5rem;"></i>
                        <h3 class="fw-bold mt-2 mb-1">Payment Successful</h3>
                        <p class="text-muted">{{ $payment->auction->title }}</p>
                        <p class="fs-4 fw-bold" style="color:#0d9488;">₱{{ number_format($payment->amount, 2) }}</p>
                    </div>

                    {{-- Order Info --}}
                    <div class="row mb-4 g-3">
                        <div class="col-sm-6">
                            <div class="p-3 rounded-3 bg-light">
                                <span class="text-muted small d-block">Payment method</span>
                                <span class="fw-semibold text-capitalize">{{ $payment->payment_method ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 rounded-3 bg-light">
                                <span class="text-muted small d-block">Paid on</span>
                                <span class="fw-semibold">{{ $payment->paid_at?->format('M d, Y g:i A') ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 rounded-3 bg-light">
                                <span class="text-muted small d-block">Seller</span>
                                <span class="fw-semibold">{{ $payment->auction->user?->name ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 rounded-3 bg-light">
                                <span class="text-muted small d-block">Reference</span>
                                <span class="fw-semibold small">{{ $payment->payment_reference ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Tracking Timeline --}}
                    <h6 class="fw-semibold mb-3"><i class="bi bi-truck me-2"></i>Order Tracking</h6>
                    @php
                        $steps = [
                            ['key' => 'paid', 'label' => 'Payment received', 'desc' => 'Funds held in escrow', 'done' => $payment->isPaid()],
                            ['key' => 'shipped', 'label' => 'Shipped', 'desc' => $payment->tracking_number ? 'Tracking: ' . $payment->tracking_number : 'Seller will ship your item', 'done' => in_array($payment->delivery_status, ['shipped', 'delivered', 'confirmed'])],
                            ['key' => 'delivered', 'label' => 'Delivered', 'desc' => 'Buyer confirmed delivery', 'done' => in_array($payment->delivery_status, ['delivered', 'confirmed'])],
                            ['key' => 'released', 'label' => 'Escrow released', 'desc' => 'Payment released to seller', 'done' => $payment->status === 'released'],
                        ];
                        $activeIndex = collect($steps)->search(fn($s) => !$s['done']);
                        if ($activeIndex === false) $activeIndex = count($steps);
                    @endphp

                    <div class="timeline mb-4">
                        @foreach($steps as $i => $step)
                            <div class="timeline-step">
                                <div class="timeline-dot {{ $step['done'] ? 'completed' : ($i === $activeIndex ? 'active' : '') }}"></div>
                                <div>
                                    <p class="mb-0 fw-semibold {{ $step['done'] ? 'text-dark' : 'text-muted' }}">{{ $step['label'] }}</p>
                                    <p class="mb-0 small text-muted">{{ $step['desc'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Actions --}}
                    @if($payment->delivery_status === 'shipped')
                        <div class="p-3 rounded-3 mb-4" style="background:#f0fdfa;border:1px solid #0d9488;">
                            <p class="mb-2 fw-semibold"><i class="bi bi-box-seam me-1"></i>Your item has been shipped!</p>
                            @if($payment->tracking_number)
                                <p class="small mb-2"><strong>Tracking:</strong> {{ $payment->tracking_number }}</p>
                            @endif
                            <form action="{{ route('auction.payment.confirm-delivery', $payment) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success rounded-pill px-4" onclick="return confirm('Confirm that you have received the item?')">
                                    <i class="bi bi-check-lg me-1"></i>I have received the item
                                </button>
                            </form>
                        </div>
                    @elseif(in_array($payment->delivery_status, ['delivered', 'confirmed']))
                        <div class="alert alert-success border-0 mb-4">
                            <i class="bi bi-check-circle-fill me-1"></i>Delivery confirmed. Thank you!
                        </div>
                    @else
                        <div class="alert alert-info border-0 mb-4">
                            <i class="bi bi-info-circle me-1"></i>The seller will ship your item soon. You'll be notified when it ships.
                        </div>
                    @endif

                    {{-- Review --}}
                    @php $canReview = in_array($payment->delivery_status, ['delivered', 'confirmed']); @endphp
                    @if($canReview && !\App\Models\AuctionReview::where('auction_payment_id', $payment->id)->exists())
                        <hr class="my-4">
                        <h6 class="fw-semibold mb-3"><i class="bi bi-star me-2"></i>Leave a Review</h6>
                        <form action="{{ route('auction.review.store', $payment) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Rating</label>
                                <select name="rating" class="form-select" required>
                                    @for($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}">{{ str_repeat('★', $i) . str_repeat('☆', 5 - $i) }} ({{ $i }})</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Feedback (optional)</label>
                                <textarea name="feedback" class="form-control" rows="3" maxlength="2000" placeholder="Share your experience..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">Submit Review</button>
                        </form>
                    @endif

                    {{-- Buttons --}}
                    <div class="d-flex gap-2 mt-4">
                        <a href="{{ route('auction.payment.receipt', $payment) }}" class="btn btn-outline-primary rounded-pill px-4">
                            <i class="bi bi-download me-1"></i>Download Receipt
                        </a>
                        <a href="{{ route('auction.index') }}" class="btn btn-primary rounded-pill px-4">Back to Auctions</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
