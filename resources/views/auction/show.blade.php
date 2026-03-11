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
    .toast-notification { position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 420px; }
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

    {{-- Toast notification --}}
    <div x-show="toast.show" x-transition.opacity.duration.300ms class="toast-notification" x-cloak>
        <div :class="'alert alert-' + toast.type + ' alert-dismissible shadow-lg mb-0'">
            <i :class="toast.type === 'success' ? 'bi bi-trophy-fill me-2' : 'bi bi-exclamation-triangle-fill me-2'"></i>
            <span x-text="toast.message"></span>
            <button type="button" class="btn-close" @click="toast.show = false"></button>
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
                                <span x-show="canBid" class="auction-live-badge text-danger" x-cloak>
                                    <span class="auction-live-dot"></span> LIVE
                                </span>
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
                        <template x-for="(bid, index) in bidHistory" :key="'bid-' + index">
                            <li class="list-group-item d-flex justify-content-between px-4 py-2 bid-entry-animate">
                                <span>
                                    <span x-text="bid.alias"></span>
                                    <span x-show="bid.isNew" class="badge bg-success ms-1" style="font-size:.65rem;">NEW</span>
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

                {{-- Current bid info (always visible) --}}
                <div x-show="!isEnded">
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
                                :disabled="bidLoading || !canBid"
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
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg w-100 rounded-pill">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login to Bid
                        </a>
                    @endauth
                </div>

                {{-- Ended Auction State --}}
                <div x-show="isEnded" x-cloak>
                    <p class="mb-2"><strong>Final price:</strong> <span class="amount d-block" x-text="currentBidFormatted"></span></p>

                    <div x-show="auctionOutcome === 'reserve_not_met'" class="mb-2">
                        <p class="text-warning mb-0"><i class="bi bi-exclamation-triangle me-1"></i>Reserve not met. No winner.</p>
                    </div>
                    <div x-show="auctionOutcome === 'no_bids'" class="mb-2">
                        <p class="text-muted mb-0">No bids were placed.</p>
                    </div>

                    @auth
                        @php
                            $bladePaymentLink = null;
                            $bladePaymentIsPending = false;
                            $bladeIsWinner = $auction->status === 'ended' && $auction->winner_id === auth()->id();
                            if ($bladeIsWinner && $auction->payment) {
                                $bladePaymentIsPending = $auction->payment->isPending();
                                $bladePaymentLink = $bladePaymentIsPending
                                    ? route('auction.payment.show', $auction->payment)
                                    : route('auction.payment.success', $auction->payment);
                            }
                        @endphp

                        {{-- Current user is the winner (uses Alpine winnerId for real-time accuracy) --}}
                        <div x-show="winnerId === {{ (int) auth()->id() }} && auctionOutcome === 'sold'" x-cloak class="mt-3 p-4 rounded-3 text-center winner-reveal" style="background:#ccfbf1;border:1px solid #0d9488;">
                            <h5 class="text-success mb-2"><i class="bi bi-trophy-fill me-2"></i>Congratulations! You won this auction.</h5>

                            {{-- Payment link from real-time event --}}
                            <template x-if="paymentLink">
                                <div>
                                    <p class="mb-3 small">Please complete your payment to proceed with the order.</p>
                                    <a :href="paymentLink" class="btn btn-success w-100 fw-bold">
                                        <i class="bi bi-credit-card me-1"></i> Proceed to Payment
                                    </a>
                                </div>
                            </template>

                            {{-- Server-rendered payment link (when paymentLink is not set from WebSocket) --}}
                            <template x-if="!paymentLink">
                                <div>
                                    @if($bladePaymentLink)
                                        @if($bladePaymentIsPending)
                                            <p class="mb-3 small">Please complete your payment to proceed with the order.</p>
                                            <a href="{{ $bladePaymentLink }}" class="btn btn-success w-100 fw-bold">
                                                <i class="bi bi-credit-card me-1"></i> Proceed to Payment
                                            </a>
                                        @else
                                            <a href="{{ $bladePaymentLink }}" class="btn btn-outline-success w-100 fw-bold mt-2">
                                                <i class="bi bi-check-circle me-1"></i> View Order Status
                                            </a>
                                        @endif
                                    @else
                                        <p class="mb-2 small text-muted">Preparing payment details...</p>
                                        <button class="btn btn-outline-primary w-100 rounded-pill mt-1" @click="window.location.reload()">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Refresh Page
                                        </button>
                                    @endif
                                </div>
                            </template>
                        </div>

                        {{-- Other user won (non-seller view) --}}
                        @if($auction->user_id !== auth()->id())
                            <div x-show="winnerId && winnerId !== {{ (int) auth()->id() }} && auctionOutcome === 'sold'" class="mt-3 p-3 rounded-3 text-center bg-light" x-cloak>
                                <p class="mb-1 fw-semibold"><i class="bi bi-trophy me-1"></i>Won by <span x-text="winnerAlias || 'a bidder'"></span></p>
                                <p class="mb-0 text-muted small">at <span x-text="currentBidFormatted"></span></p>
                            </div>
                        @endif

                        {{-- Seller's view of their ended auction --}}
                        @if($auction->user_id === auth()->id())
                            <div x-show="auctionOutcome === 'sold'" class="mt-3 p-3 rounded-3" style="background:#eff6ff;border:1px solid #3b82f6;" x-cloak>
                                <p class="mb-1 fw-semibold text-primary"><i class="bi bi-cash-coin me-1"></i>Your Auction Sold!</p>
                                <p class="mb-1 small">Won by <strong x-text="winnerAlias || 'a bidder'"></strong> at <span x-text="currentBidFormatted"></span></p>
                                @if($auction->payment)
                                    @if($auction->payment->isPending())
                                        <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Awaiting buyer payment</span>
                                    @elseif(in_array($auction->payment->status, ['paid', 'held']))
                                        @if(!in_array($auction->payment->delivery_status, ['shipped', 'delivered', 'confirmed']))
                                            <span class="badge bg-danger"><i class="bi bi-truck me-1"></i>Ready to ship</span>
                                        @elseif($auction->payment->delivery_status === 'shipped')
                                            <span class="badge bg-info"><i class="bi bi-truck me-1"></i>Shipped</span>
                                        @else
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Delivered</span>
                                        @endif
                                    @elseif($auction->payment->status === 'released')
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Payment released</span>
                                    @endif
                                @endif
                                <div class="mt-2">
                                    <a href="{{ route('auction.seller.dashboard') }}" class="btn btn-sm btn-primary rounded-pill">
                                        <i class="bi bi-speedometer2 me-1"></i>Seller Dashboard
                                    </a>
                                </div>
                            </div>
                        @endif
                    @endauth

                    @guest
                        <div x-show="winnerId && auctionOutcome === 'sold'" class="mt-3 p-3 rounded-3 text-center bg-light" x-cloak>
                            <p class="mb-1 fw-semibold"><i class="bi bi-trophy me-1"></i>Won by <span x-text="winnerAlias || 'a bidder'"></span></p>
                            <p class="mb-0 text-muted small">at <span x-text="currentBidFormatted"></span></p>
                        </div>
                    @endguest

                    <p class="mb-0 text-muted mt-2"><i class="bi bi-flag me-1"></i>This auction has ended.</p>
                </div>

            </div>
        </div>
    </div>

    <a href="{{ route('auction.index') }}" class="btn btn-outline-secondary rounded-pill mt-3">
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
        'alias' => auth()->check() && $b->user_id === auth()->id() ? 'You' : ($b->bidder_alias ?: 'Anonymous'),
        'amount_formatted' => '₱' . number_format($b->amount, 2),
        'isNew' => false,
    ])->values()->toArray();

    $serverIsActive = $auction->status === 'active' && $auction->end_at && $auction->end_at->isFuture();
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
        canBid: @json($serverIsActive),
        isEnded: @json($auction->status === 'ended'),
        currentBid: {{ (float) $auction->current_bid }},
        currentBidFormatted: @json('₱' . number_format($auction->current_bid, 2)),
        nextMinBid: {{ (float) $auction->next_min_bid }},
        nextMinBidFormatted: @json('₱' . number_format($auction->next_min_bid, 2)),
        bidCount: {{ (int) ($auction->bids_count ?? 0) }},
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
        paymentLink: null,
        toast: { show: false, message: '', type: 'warning' },
        _countdownInterval: null,

        init() {
            this.startCountdown();

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
            if (this._countdownInterval) clearInterval(this._countdownInterval);
            this.updateCountdown();
            this._countdownInterval = setInterval(() => this.updateCountdown(), 1000);
        },

        updateCountdown() {
            if (!this.endAt) {
                this.countdownText = 'No end time set';
                return;
            }
            const end = new Date(this.endAt).getTime();
            const diff = end - Date.now();

            if (diff <= 0) {
                this.countdownText = 'Auction ended';
                this.canBid = false;
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

        showToast(message, type = 'warning', duration = 8000) {
            if (!message) return;
            this.toast = { show: true, message, type };
            setTimeout(() => { this.toast.show = false; }, duration);
        },

        async placeBid() {
            if (this.bidLoading || !this.canBid) return;
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
                    let errMsg = data.error || data.message || 'Bid failed.';
                    if (data.errors) {
                        const firstErr = Object.values(data.errors)[0];
                        errMsg = Array.isArray(firstErr) ? firstErr[0] : firstErr;
                    }
                    this.bidMessage = String(errMsg);
                    this.bidSuccess = false;
                    return;
                }

                this.bidMessage = data.message || 'Bid placed!';
                this.bidSuccess = true;

                if (data.bid) {
                    this.currentBid = parseFloat(data.bid.amount);
                    this.currentBidFormatted = data.bid.amount_formatted;
                    this.nextMinBid = parseFloat(data.bid.next_min_bid);
                    this.nextMinBidFormatted = data.bid.next_min_bid_formatted;
                    this.bidCount = data.bid.bid_count;
                    if (data.bid.end_at) {
                        this.endAt = data.bid.end_at;
                        this.startCountdown();
                    }

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
            this.currentBid = parseFloat(e.amount);
            this.currentBidFormatted = e.amount_formatted;
            this.nextMinBid = parseFloat(e.next_min_bid);
            this.nextMinBidFormatted = e.next_min_bid_formatted;
            this.bidCount = e.bid_count;
            if (e.end_at) {
                this.endAt = e.end_at;
                this.startCountdown();
            }

            this.bidHistory.unshift({
                alias: e.bidder_alias,
                amount_formatted: e.amount_formatted,
                isNew: true,
            });

            this.triggerBidFlash();
        },

        handleExtended(e) {
            if (e.end_at) {
                this.endAt = e.end_at;
                this.canBid = true;
                this.startCountdown();
            }
            this.snipeAlert = true;
            setTimeout(() => { this.snipeAlert = false; }, 5000);
        },

        handleEnded(e) {
            this.canBid = false;
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
            this.canBid = true;
            this.isEnded = false;
            if (e.end_at) {
                this.endAt = e.end_at;
            }
            this.startCountdown();
        },

        handleUserOutbid(e) {
            this.showToast(e.message || "You've been outbid on this auction!", 'warning');
        },

        handleUserWon(e) {
            this.showToast(e.message || 'Congratulations! You won this auction!', 'success', 15000);
            if (e.payment_link) {
                this.paymentLink = e.payment_link;
            }
        },
    };
}
</script>
@endpush
