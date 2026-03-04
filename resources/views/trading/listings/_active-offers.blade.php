@if($listing->activeOffers->count() > 0)
<div class="offers-section" id="active-offers-section">
    <h5 class="mb-3">Active Offers ({{ $listing->activeOffers->count() }})</h5>
    @foreach($listing->activeOffers as $offer)
    @php
        $offeredItem = $offer->getOfferedItem();
        $offererListing = ($offererListingsByOffer ?? collect())[$offer->id] ?? null;
        $displayListing = $offererListing ?? null;
        $offerModalId = 'offerProductModal' . $offer->id;
    @endphp
    <div class="offer-card">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="flex-grow-1">
                <strong>{{ $offer->offerer->name }}</strong>
                @if($offeredItem)
                <div class="text-muted small">{{ $offeredItem->name }}</div>
                <button type="button" class="btn btn-link btn-sm p-0 mt-1" data-bs-toggle="modal" data-bs-target="#{{ $offerModalId }}" title="View full product">
                    <i class="bi bi-eye me-1"></i>View Product
                </button>
                @endif
                @if($offer->cash_amount)
                <div class="text-success small">+ ₱{{ number_format($offer->cash_amount, 2) }}</div>
                @endif
            </div>
            @if(Auth::check() && Auth::id() === $listing->user_id)
            <div class="btn-group btn-group-sm">
                <form method="POST" action="{{ route('trading.offers.accept', $offer->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">Accept</button>
                </form>
                <form method="POST" action="{{ route('trading.offers.reject', $offer->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                </form>
            </div>
            @endif
        </div>
        @if($offer->message)
        <p class="small text-muted mb-0">{{ $offer->message }}</p>
        @endif
        <small class="text-muted">{{ $offer->created_at->diffForHumans() }}</small>
    </div>

    <!-- Full Product Listing View Modal -->
    <div class="modal fade" id="{{ $offerModalId }}" tabindex="-1" aria-labelledby="{{ $offerModalId }}Label" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="{{ $offerModalId }}Label">
                        @if($displayListing)
                            {{ $displayListing->title }}
                            <span class="badge ms-2" style="background: linear-gradient(135deg, #0ea5e9, #38bdf8);">{{ ucfirst(str_replace('_', ' ', $displayListing->trade_type ?? 'Trade')) }}</span>
                        @else
                            Offered: {{ $offeredItem ? $offeredItem->name : 'Product' }}
                        @endif
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($displayListing || $offeredItem)
                    <div class="row g-4">
                        <div class="col-lg-6">
                            @php
                                $modalImages = $displayListing
                                    ? ($displayListing->images->isNotEmpty() ? $displayListing->images : ($displayListing->getItem() ? $displayListing->getItem()->images : collect()))
                                    : ($offeredItem ? $offeredItem->images : collect());
                                $modalFirstImg = $modalImages->first();
                            @endphp
                            @if($modalFirstImg)
                            <div class="rounded overflow-hidden bg-light mb-2" style="min-height: 280px;">
                                <img src="{{ asset('storage/' . $modalFirstImg->image_path) }}" alt="{{ $displayListing ? $displayListing->title : ($offeredItem ? $offeredItem->name : '') }}" class="w-100 offer-modal-main-img" style="object-fit: contain; max-height: 360px; cursor: zoom-in;">
                            </div>
                            @if($modalImages->count() > 1)
                            <div class="d-flex gap-2 flex-wrap">
                                @foreach($modalImages as $img)
                                <img src="{{ asset('storage/' . $img->image_path) }}" alt="" class="rounded border offer-modal-thumb" style="width: 64px; height: 64px; object-fit: cover; cursor: pointer;" data-src="{{ asset('storage/' . $img->image_path) }}">
                                @endforeach
                            </div>
                            @endif
                            @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="min-height: 200px;">
                                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                            </div>
                            @endif
                        </div>
                        <div class="col-lg-6">
                            <h5 class="fw-bold mb-3">{{ $displayListing ? $displayListing->title : ($offeredItem ? $offeredItem->name : 'Product') }}</h5>
                            @if($displayListing && $displayListing->description)
                            <div class="mb-3">
                                <h6 class="text-muted small text-uppercase mb-1">Description</h6>
                                <p class="mb-0">{{ $displayListing->description }}</p>
                            </div>
                            @elseif($offeredItem && $offeredItem->description)
                            <div class="mb-3">
                                <h6 class="text-muted small text-uppercase mb-1">Description</h6>
                                <p class="mb-0">{{ $offeredItem->description }}</p>
                            </div>
                            @endif
                            <div class="row g-2 small">
                                @php $dItem = $displayListing ? $displayListing->getItem() : $offeredItem; @endphp
                                @if($dItem)
                                @if(!empty($dItem->condition))
                                <div class="col-6"><strong>Condition:</strong> {{ ucfirst($dItem->condition) }}</div>
                                @endif
                                @if(!empty($dItem->brand))
                                <div class="col-6"><strong>Brand:</strong> {{ $dItem->brand }}</div>
                                @endif
                                @if($displayListing && $displayListing->category)
                                <div class="col-6"><strong>Category:</strong> {{ $displayListing->category->name }}</div>
                                @endif
                                @if($dItem instanceof \App\Models\Product && $dItem->price)
                                <div class="col-6"><strong>Price:</strong> ₱{{ number_format($dItem->price, 2) }}</div>
                                @elseif($dItem instanceof \App\Models\UserProduct && $dItem->estimated_value)
                                <div class="col-6"><strong>Est. Value:</strong> ₱{{ number_format($dItem->estimated_value, 2) }}</div>
                                @endif
                                @endif
                                @if($displayListing && $displayListing->cash_difference)
                                <div class="col-6"><strong>Cash difference:</strong> ₱{{ number_format($displayListing->cash_difference, 2) }}</div>
                                @endif
                            </div>
                            @if($displayListing && ($displayListing->location || $displayListing->meet_up_references))
                            <div class="mt-3 pt-3 border-top">
                                <h6 class="text-muted small text-uppercase mb-1">Meet-up</h6>
                                @if($displayListing->location)
                                <p class="mb-1 small"><i class="bi bi-geo-alt me-1"></i>{{ $displayListing->location }}</p>
                                @endif
                                @if($displayListing->meet_up_references)
                                <p class="mb-0 small">{{ $displayListing->meet_up_references }}</p>
                                @endif
                            </div>
                            @endif
                            @if($displayListing && in_array($displayListing->status, ['active', 'pending_approval']))
                            <a href="{{ route('trading.listings.show', $displayListing->id) }}" class="btn btn-outline-primary btn-sm mt-3" target="_blank">
                                <i class="bi bi-box-arrow-up-right me-1"></i>View Full Listing
                            </a>
                            @endif
                        </div>
                    </div>
                    <script>
                    (function() {
                        var modal = document.getElementById('{{ $offerModalId }}');
                        if (!modal) return;
                        modal.addEventListener('shown.bs.modal', function() {
                            modal.querySelectorAll('.offer-modal-thumb').forEach(function(t) {
                                t.onclick = function() {
                                    var main = modal.querySelector('.offer-modal-main-img');
                                    if (main && this.dataset.src) main.src = this.dataset.src;
                                };
                            });
                        });
                    })();
                    </script>
                    @else
                    <p class="text-muted mb-0">Product details not available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
