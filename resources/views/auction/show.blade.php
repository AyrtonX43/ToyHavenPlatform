@extends('layouts.toyshop')

@section('title', $auction->title . ' - ToyHaven Auction')

@push('styles')
<link href="{{ asset('css/auction.css') }}" rel="stylesheet">
<style>
    .auction-live-badge { display: inline-flex; align-items: center; gap: 5px; font-size: .75rem; font-weight: 600; }
    .auction-live-dot { width: 8px; height: 8px; border-radius: 50%; background: #ef4444; animation: auctionPulse 1.5s infinite; }
    @keyframes auctionPulse { 0%,100% { opacity: 1; transform: scale(1); } 50% { opacity: .5; transform: scale(1.3); } }
    .bid-flash { animation: bidFlash .6s ease; }
    @keyframes bidFlash { 0% { background: #fef9c3; } 100% { background: transparent; } }
    .countdown-urgent { color: #ef4444 !important; font-weight: 700; }
    .snipe-alert { animation: snipeSlide .4s ease; }
    @keyframes snipeSlide { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    .viewer-badge { font-size: .8rem; color: #64748b; }
    .bid-entry-animate { animation: bidSlideIn .3s ease; }
    @keyframes bidSlideIn { from { opacity: 0; transform: translateX(-12px); } to { opacity: 1; transform: translateX(0); } }
    .winner-reveal { animation: winnerPop .5s ease; }
    @keyframes winnerPop { 0% { opacity: 0; transform: scale(.8); } 60% { transform: scale(1.05); } 100% { opacity: 1; transform: scale(1); } }
    .toast-outbid { position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; }
</style>
@endpush

@section('content')
<div class="container py-4" x-data="auctionLive()" x-init="init()">
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

    {{-- Outbid toast --}}
    <div x-show="outbidToast.show" x-transition class="toast-outbid" x-cloak>
        <div class="alert alert-warning alert-dismissible shadow-lg mb-0">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <span x-text="outbidToast.message"></span>
            <button type="button" class="btn-close" @click="outbidToast.show = false"></button>
        </div>
    </div>

    {{-- Snipe extension alert --}}
    <div x-show="snipeAlert" x-transition class="alert alert-info alert-dismissible snipe-alert mb-3" x-cloak>
        <i class="bi bi-clock-history me-2"></i>
        <strong>Time Extended!</strong> Anti-snipe protection activated. The auction has been extended.
        <button type="button" class="btn-close" @click="snipeAlert = false"></button>
    </div>

    <div class="row">
        {{-- Left: Item Details --}}
        <div class="col-lg-8">
            @if($auction->isPendingApproval())
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-hourglass-split me-2"></i>This listing is pending approval. Save it to get notified when it goes live.
                </div>
            @endif

            <div class="card auction-card border-0 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h1 class="h4 mb-1">{{ $auction->title }}</h1>
                            <div class="d-flex align-items-center gap-3">
                                <template x-if="isLive">
                                    <span class="auction-live-badge text-danger">
                                        <span class="auction-live-dot"></span> LIVE
                                    </span>
                                </template>
                                <span class="viewer-badge" x-show="viewerCount > 0" x-cloak>
                                    <i class="bi bi-eye me-1"></i><span x-text="viewerCount"></span> watching
                                </span>
                            </div>
                        </div>
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
                            <img src="{{ asset('storage/' . $primary->image_path) }}" alt="{{ $auction->title }}" class="img-fluid rounded-3 mb-3" id="auction-main-img" style="max-height:420px;object-fit:contain;background:#f8fafc;">
                            @if($auction->images->count() > 1)
                                <div class="d-flex gap-2 flex-wrap">
                                    @foreach($auction->images->take(5) as $img)
                                        <img src="{{ asset('storage/' . $img->image_path) }}" alt="" class="rounded-2" style="width:72px;height:72px;object-fit:cover;cursor:pointer;border:2px solid transparent;transition:border-color .2s;" onmouseover="this.style.borderColor='#0284c7'" onmouseout="this.style.borderColor='transparent'" onclick="document.getElementById('auction-main-img').src=this.src">
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    <p class="text-muted small mb-2">
                        <span class="badge bg-light text-dark">{{ \App\Models\Auction::CONDITIONS[$auction->condition ?? 'good'] ?? 'Good' }}</span>
                        Listed by {{ $auction->user?->name }}
                        @php $catNames = $auction->categories()->pluck('name'); @endphp
                        &middot; {{ $catNames->isNotEmpty() ? $catNames->join(', ') : ($auction->category?->name ?? 'Uncategorized') }}
                    </p>
                    @if($auction->description)
                        <div class="mb-0">{!! nl2br(e($auction->description)) !!}</div>
                    @endif
                </div>
            </div>

            {{-- Bid History --}}
            <div class="card auction-card border-0 mb-4">
                <div class="card-header bg-light border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">Bid History</h5>
                    <span class="badge bg-primary rounded-pill" x-text="bidCount + ' bid' + (bidCount !== 1 ? 's' : '')"></span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush" id="bid-history-list">
                        <template x-for="(bid, index) in bidHistory" :key="index">
                            <li class="list-group-item d-flex justify-content-between px-4 py-2 bid-entry-animate">
                                <span>
                                    <span x-text="bid.alias"></span>
                                    <template x-if="bid.isNew">
                                        <span class="badge bg-success ms-1" style="font-size:.65rem;">NEW</span>
                                    </template>
                                </span>
                                <span class="fw-semibold" x-text="bid.amount_formatted"></span>
                            </li>
                        </template>
                    </ul>
                    <div x-show="bidHistory.length === 0" class="p-4">
                        <p class="text-muted mb-0">No bids yet. Be the first to bid!</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Bid Panel --}}
        <div class="col-lg-4">
            <div class="auction-bid-panel sticky-top">

                {{-- Active Auction State --}}
                <template x-if="isLive">
                    <div>
                        <p class="mb-2">
                            <strong>Current bid</strong>
                            <span class="amount d-block" :class="{ 'bid-flash': bidFlash }" x-text="currentBidFormatted"></span>
                        </p>
                        <p class="mb-2 text-muted small">
                            Minimum next bid: <span x-text="nextMinBidFormatted"></span>
                        </p>
                        <p class="mb-3" :class="{ 'countdown-urgent': isUrgent }">
                            <i class="bi bi-clock me-1"></i>
                            <span x-text="countdownText"></span>
                        </p>

                        @auth
                            @if($auction->user_id !== auth()->id())
                                <div x-show="bidMessage" x-transition class="mb-2" x-cloak>
                                    <div :class="'alert alert-' + (bidSuccess ? 'success' : 'danger') + ' py-2 px-3 small mb-0'">
                                        <span x-text="bidMessage"></span>
                                    </div>
                                </div>
                                <button
                                    @click="placeBid()"
                                    :disabled="bidLoading || !isLive"
                                    class="btn btn-primary btn-lg w-100 auction-btn-primary rounded-pill"
                                >
                                    <span x-show="!bidLoading">
                                        <i class="bi bi-hammer me-1"></i>Place bid at <span x-text="nextMinBidFormatted"></span>
                                    </span>
                                    <span x-show="bidLoading" x-cloak>
                                        <span class="spinner-border spinner-border-sm me-1"></span> Placing bid...
                                    </span>
                                </button>
                            @else
                                <p class="text-muted mb-0">This is your listing. You cannot bid on it.</p>
                            @endif
                        @endauth
                    </div>
                </template>

                {{-- Ended Auction State --}}
                <template x-if="isEnded">
                    <div>
                        <p class="mb-2"><strong>Final price:</strong> <span x-text="currentBidFormatted"></span></p>

                        <template x-if="auctionOutcome === 'reserve_not_met'">
                            <p class="mb-2 text-warning">Reserve not met. No winner.</p>
                        </template>
                        <template x-if="auctionOutcome === 'no_bids'">
                            <p class="mb-2 text-muted">No bids were placed.</p>
                        </template>

                        @auth
                            <template x-if="winnerId === {{ (int) auth()->id() }}">
                                <div class="mt-3 p-4 rounded-3 text-center winner-reveal" style="background:#ccfbf1;border:1px solid #0d9488;">
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
                            </template>
                        @endauth

                        <template x-if="winnerAlias && winnerId !== {{ auth()->id() ? (int) auth()->id() : 'null' }}">
                            <div class="mt-3 p-3 rounded-3 text-center bg-light">
                                <p class="mb-1 fw-semibold"><i class="bi bi-trophy me-1"></i>Won by <span x-text="winnerAlias"></span></p>
                                <p class="mb-0 text-muted small">at <span x-text="currentBidFormatted"></span></p>
                            </div>
                        </template>

                        <p class="mb-0 text-muted mt-2">This auction has ended.</p>
                    </div>
                </template>

            </div>
        </div>
    </div>

    <a href="{{ route('auction.index') }}" class="btn btn-outline-secondary rounded-pill">
        <i class="bi bi-arrow-left me-1"></i>Back to Auctions
    </a>
</div>
@endsection

@push('scripts')
@php
    $broadcastEnabled = config('broadcasting.default') !== 'null' && config('broadcasting.default') !== 'log';
    $reverbKey = config('reverb.apps.apps.0.key', config('broadcasting.connections.reverb.key', ''));
    $reverbHost = config('reverb.apps.apps.0.options.host', config('broadcasting.connections.reverb.options.host', ''));
    $reverbPort = config('reverb.servers.reverb.port', 8080);
    $reverbScheme = config('reverb.apps.apps.0.options.scheme', config('broadcasting.connections.reverb.options.scheme', 'https'));

    $initialBids = $auction->bids->map(fn($b) => [
        'alias' => auth()->check() && $b->user_id === auth()->id() ? 'You' : 'Bidder #' . ($b->rank_at_bid ?? '-'),
        'amount_formatted' => '₱' . number_format($b->amount, 2),
        'isNew' => false,
    ])->values()->toArray();
@endphp

<script>
    window.AUCTION_CONFIG = {
        auctionId: {{ $auction->id }},
        userId: {{ auth()->id() ?? 'null' }},
        echoKey: @json($reverbKey),
        wsHost: @json($reverbHost ?: request()->getHost()),
        wsPort: {{ $reverbPort }},
        wssPort: 443,
        scheme: @json($reverbScheme),
        authEndpoint: '/broadcasting/auth',
        broadcastEnabled: @json($broadcastEnabled),
    };
</script>

@if($broadcastEnabled)
    @vite('resources/js/echo-auction.js')
@endif

<script>
function auctionLive() {
    return {
        isLive: @json($auction->isActive()),
        isEnded: @json($auction->isEnded()),
        currentBid: {{ (float)($auction->winning_amount ?? $auction->starting_bid) }},
        currentBidFormatted: '₱{{ number_format($auction->winning_amount ?? $auction->starting_bid, 2) }}',
        nextMinBid: {{ (float)$auction->next_min_bid }},
        nextMinBidFormatted: '₱{{ number_format($auction->next_min_bid, 2) }}',
        bidCount: {{ (int)$auction->bids_count }},
        endAt: @json($auction->end_at?->toIso8601String()),
        countdownText: '',
        isUrgent: false,
        bidHistory: @json($initialBids),
        viewerCount: 0,
        bidLoading: false,
        bidMessage: '',
        bidSuccess: false,
        bidFlash: false,
        snipeAlert: false,
        auctionOutcome: @json($auction->auction_outcome),
        winnerId: {{ $auction->winner_id ?? 'null' }},
        winnerAlias: null,
        outbidToast: { show: false, message: '' },
        _countdownInterval: null,

        init() {
            if (this.isLive) {
                this.startCountdown();
            }

            // Wire up Echo callbacks
            window.auctionOnBidPlaced = (e) => this.handleBidPlaced(e);
            window.auctionOnExtended = (e) => this.handleExtended(e);
            window.auctionOnEnded = (e) => this.handleEnded(e);
            window.auctionOnStarted = (e) => this.handleStarted(e);
            window.auctionOnUserOutbid = (e) => this.handleUserOutbid(e);
            window.auctionOnUserWon = (e) => this.handleUserWon(e);
            window.auctionOnViewersUpdate = (count) => { this.viewerCount = count; };
            window.auctionOnViewerJoined = () => { this.viewerCount++; };
            window.auctionOnViewerLeft = () => { this.viewerCount = Math.max(0, this.viewerCount - 1); };
        },

        startCountdown() {
            this.updateCountdown();
            this._countdownInterval = setInterval(() => this.updateCountdown(), 1000);
        },

        updateCountdown() {
            if (!this.endAt) {
                this.countdownText = 'No end time set';
                return;
            }
            const end = new Date(this.endAt).getTime();
            const now = Date.now();
            const diff = end - now;

            if (diff <= 0) {
                this.countdownText = 'Auction ended';
                this.isUrgent = false;
                if (this._countdownInterval) clearInterval(this._countdownInterval);
                return;
            }

            this.isUrgent = diff <= 120000;

            const days = Math.floor(diff / 86400000);
            const hours = Math.floor((diff % 86400000) / 3600000);
            const mins = Math.floor((diff % 3600000) / 60000);
            const secs = Math.floor((diff % 60000) / 1000);

            let parts = [];
            if (days > 0) parts.push(days + 'd');
            if (hours > 0) parts.push(hours + 'h');
            parts.push(mins + 'm');
            parts.push(secs + 's');
            this.countdownText = 'Ends in ' + parts.join(' ');
        },

        formatCurrency(val) {
            return '₱' + parseFloat(val).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        async placeBid() {
            if (this.bidLoading) return;
            this.bidLoading = true;
            this.bidMessage = '';

            try {
                const resp = await fetch('{{ route("auction.bid.store", $auction) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ amount: this.nextMinBid }),
                });

                const data = await resp.json();

                if (!resp.ok) {
                    this.bidMessage = data.error || data.message || 'Bid failed.';
                    this.bidSuccess = false;
                    return;
                }

                this.bidMessage = data.message || 'Bid placed!';
                this.bidSuccess = true;

                // Update local state from our own bid response
                if (data.bid) {
                    this.currentBid = data.bid.amount;
                    this.currentBidFormatted = data.bid.amount_formatted;
                    this.nextMinBid = data.bid.next_min_bid;
                    this.nextMinBidFormatted = data.bid.next_min_bid_formatted;
                    this.bidCount = data.bid.bid_count;
                    this.endAt = data.bid.end_at;

                    this.bidHistory.unshift({
                        alias: 'You',
                        amount_formatted: data.bid.amount_formatted,
                        isNew: true,
                    });

                    this.triggerBidFlash();

                    if (data.bid.anti_snipe) {
                        this.snipeAlert = true;
                        setTimeout(() => { this.snipeAlert = false; }, 5000);
                    }
                }

                setTimeout(() => { this.bidMessage = ''; }, 4000);
            } catch (err) {
                this.bidMessage = 'Network error. Please try again.';
                this.bidSuccess = false;
            } finally {
                this.bidLoading = false;
            }
        },

        triggerBidFlash() {
            this.bidFlash = true;
            setTimeout(() => { this.bidFlash = false; }, 600);
        },

        handleBidPlaced(e) {
            this.currentBid = e.amount;
            this.currentBidFormatted = e.amount_formatted;
            this.nextMinBid = e.next_min_bid;
            this.nextMinBidFormatted = e.next_min_bid_formatted;
            this.bidCount = e.bid_count;
            this.endAt = e.end_at;

            this.bidHistory.unshift({
                alias: e.bidder_alias,
                amount_formatted: e.amount_formatted,
                isNew: true,
            });

            this.triggerBidFlash();
        },

        handleExtended(e) {
            this.endAt = e.end_at;
            this.snipeAlert = true;
            setTimeout(() => { this.snipeAlert = false; }, 5000);
        },

        handleEnded(e) {
            this.isLive = false;
            this.isEnded = true;
            this.auctionOutcome = e.outcome;
            this.winnerId = e.winner_id;
            this.winnerAlias = e.winner_alias;
            if (e.final_price_formatted) {
                this.currentBidFormatted = e.final_price_formatted;
            }
            if (this._countdownInterval) clearInterval(this._countdownInterval);
            this.countdownText = 'Auction ended';
        },

        handleStarted(e) {
            this.isLive = true;
            this.isEnded = false;
            this.endAt = e.end_at;
            this.startCountdown();
        },

        handleUserOutbid(e) {
            this.outbidToast.message = e.message;
            this.outbidToast.show = true;
            setTimeout(() => { this.outbidToast.show = false; }, 8000);
        },

        handleUserWon(e) {
            this.outbidToast.message = e.message;
            this.outbidToast.show = true;
            if (e.payment_link) {
                setTimeout(() => { window.location.href = e.payment_link; }, 3000);
            }
        },
    };
}
</script>
@endpush
