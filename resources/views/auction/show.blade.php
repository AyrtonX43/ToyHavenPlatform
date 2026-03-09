@extends('layouts.toyshop')

@section('title', $auction->title . ' - ToyHaven Auction')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auction.index') }}">Auction</a></li>
            <li class="breadcrumb-item active">{{ Str::limit($auction->title, 40) }}</li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            @if($auction->isPendingApproval())
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-hourglass-split me-2"></i>This listing is pending approval. Save it to get notified when it goes live.
                </div>
            @endif

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h1 class="h4 mb-0">{{ $auction->title }}</h1>
                        @auth
                            @if($auction->user_id !== auth()->id())
                                @if($isSaved ?? false)
                                    <form action="{{ route('auction.unsave', $auction) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-bookmark-fill me-1"></i>Saved</button>
                                    </form>
                                @else
                                    <form action="{{ route('auction.save', $auction) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-bookmark me-1"></i>Save</button>
                                    </form>
                                @endif
                            @endif
                        @endauth
                    </div>

                    @if($auction->images->count() > 0)
                        <div class="mb-4">
                            @php $primary = $auction->images->firstWhere('is_primary', true) ?? $auction->images->first(); @endphp
                            <img src="{{ asset('storage/' . $primary->image_path) }}" alt="{{ $auction->title }}" class="img-fluid rounded mb-2" style="max-height:400px;object-fit:contain;">
                            @if($auction->images->count() > 1)
                                <div class="d-flex gap-2 flex-wrap">
                                    @foreach($auction->images->take(5) as $img)
                                        <img src="{{ asset('storage/' . $img->image_path) }}" alt="" class="rounded" style="width:80px;height:80px;object-fit:cover;cursor:pointer;" onclick="document.querySelector('.img-fluid.rounded').src=this.src">
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    <p class="text-muted small mb-2">
                        <span class="badge bg-light text-dark">{{ \App\Models\Auction::CONDITIONS[$auction->condition ?? 'good'] ?? 'Good' }}</span>
                        Listed by {{ $auction->user?->name }}
                        @php $catNames = $auction->categories()->pluck('name'); @endphp
                        · {{ $catNames->isNotEmpty() ? $catNames->join(', ') : ($auction->category?->name ?? 'Uncategorized') }}
                    </p>
                    @if($auction->description)
                        <div class="mb-0">{!! nl2br(e($auction->description)) !!}</div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Bid History</h5>
                </div>
                <div class="card-body">
                    @if($auction->bids->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($auction->bids as $bid)
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>{{ auth()->check() && $bid->user_id === auth()->id() ? 'You' : 'Bidder #' . ($bid->rank_at_bid ?? '—') }}</span>
                                    <span>₱{{ number_format($bid->amount, 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">No bids yet. Be the first to bid!</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4 sticky-top">
                <div class="card-body">
                    @if($auction->isActive())
                        <p class="mb-2">
                            <strong>Current bid:</strong>
                            <span class="fs-4 text-primary">₱{{ number_format($auction->winning_amount ?? $auction->starting_bid, 2) }}</span>
                        </p>
                        <p class="mb-2 text-muted small">Minimum next bid: ₱{{ number_format($auction->next_min_bid, 2) }}</p>
                        <p class="mb-3">
                            <i class="bi bi-clock me-1"></i>
                            Ends {{ $auction->end_at?->format('M d, Y H:i') }}
                            ({{ $auction->end_at?->diffForHumans() }})
                        </p>

                        @if($auction->user_id !== auth()->id())
                            <form action="{{ route('auction.bid.store', $auction) }}" method="POST" class="mb-0">
                                @csrf
                                <input type="hidden" name="amount" value="{{ $auction->next_min_bid }}">
                                <p class="mb-3 text-muted small">Click below to place a bid at the minimum amount. No custom amount to prevent errors.</p>
                                @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="bi bi-hammer me-1"></i>Place bid at ₱{{ number_format($auction->next_min_bid, 2) }}
                                </button>
                            </form>
                        @else
                            <p class="text-muted mb-0">This is your listing. You cannot bid on it.</p>
                        @endif
                    @else
                        <p class="mb-2"><strong>Final price:</strong> ₱{{ number_format($auction->winning_amount ?? $auction->starting_bid, 2) }}</p>
                        @if($auction->auction_outcome === 'reserve_not_met')
                            <p class="mb-2 text-warning">Reserve not met. No winner.</p>
                        @elseif($auction->auction_outcome === 'no_bids')
                            <p class="mb-2 text-muted">No bids were placed.</p>
                        @elseif($auction->winner_id === auth()->id())
                            <div class="mt-3 p-3 bg-success bg-opacity-10 border border-success rounded text-center">
                                <h5 class="text-success mb-2"><i class="bi bi-trophy-fill me-2"></i>Congratulations! You won this auction.</h5>
                                @if($auction->payment)
                                    @if($auction->payment->isPending())
                                        <p class="mb-3 small">Please complete your payment to proceed with the order.</p>
                                        <a href="{{ route('auction.payment.show', $auction->payment) }}" class="btn btn-success w-100 fw-bold">
                                            <i class="bi bi-credit-card me-1"></i> Proceed to Payment
                                        </a>
                                    @else
                                        <a href="{{ route('auction.payment.success', $auction->payment) }}" class="btn btn-outline-success w-100 fw-bold mt-2">
                                            <i class="bi bi-check-circle me-1"></i> View Order Status
                                        </a>
                                    @endif
                                @else
                                    <p class="mb-0 small text-muted">Processing payment details, please wait...</p>
                                @endif
                            </div>
                        @endif
                        <p class="mb-0 text-muted">This auction has ended.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('auction.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Auctions
    </a>
</div>
@endsection
