@extends('layouts.toyshop')

@section('title', 'Welcome to ToyHaven')

@push('styles')
<style>
    /* Toys and Joy style - clean, modern toy store (Template Monster 471052 inspired) */
    .hero-section {
        background: linear-gradient(135deg, #ecfeff 0%, #f0fdfa 40%, #fef3c7 100%);
        color: #1e293b;
        padding: 4rem 0 5rem;
        position: relative;
        overflow: hidden;
    }
    .hero-section::before {
        content: '';
        position: absolute;
        top: -50%; right: -20%;
        width: 60%;
        height: 200%;
        background: radial-gradient(ellipse, rgba(8,145,178,0.12) 0%, transparent 70%);
        pointer-events: none;
    }
    .hero-content { position: relative; z-index: 2; }
    .hero-badge {
        display: inline-block;
        background: linear-gradient(135deg, #0891b2, #06b6d4);
        color: #fff;
        font-weight: 700;
        font-size: 0.875rem;
        padding: 0.35rem 1rem;
        border-radius: 50px;
        margin-bottom: 1rem;
        letter-spacing: 0.02em;
    }
    .hero-title {
        font-size: clamp(2.25rem, 5vw, 3.5rem);
        font-weight: 700;
        margin-bottom: 0.75rem;
        line-height: 1.15;
        color: #1e293b;
        letter-spacing: -0.03em;
    }
    .hero-subtitle {
        font-size: 1.25rem;
        margin-bottom: 2rem;
        color: #64748b;
        font-weight: 600;
        max-width: 520px;
        margin-left: auto;
        margin-right: auto;
        line-height: 1.6;
    }
    .hero-cta .btn {
        border-radius: 50px;
        padding: 1rem 2.25rem;
        font-weight: 700;
        font-size: 1.0625rem;
        background: linear-gradient(135deg, #f97316, #fb923c);
        border: none;
        color: #fff;
        box-shadow: 0 4px 20px rgba(249,115,22,0.35);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .hero-cta .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(249,115,22,0.45);
        color: #fff;
    }

    .section-label {
        font-size: 0.8125rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: #0891b2;
        margin-bottom: 0.5rem;
    }
    .section-heading {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }
    .section-subheading {
        color: #64748b;
        font-size: 1rem;
        margin-bottom: 2rem;
        font-weight: 500;
    }

    .category-block {
        background: #fff;
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 3rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    }
    .category-block h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .category-block h3 a {
        font-size: 0.9375rem;
        font-weight: 700;
        color: #0891b2;
        text-decoration: none;
    }
    .category-block h3 a:hover { color: #0e7490; }

    .toy-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        height: 100%;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        text-decoration: none;
        color: inherit;
        display: block;
    }
    .toy-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0,0,0,0.08);
        border-color: #22d3ee;
        color: inherit;
    }
    .toy-card-img-wrap {
        aspect-ratio: 1;
        background: linear-gradient(145deg, #ecfeff, #f0fdfa);
        overflow: hidden;
    }
    .toy-card-img-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .toy-card-body {
        padding: 1.25rem;
    }
    .toy-card-title {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.35rem;
        line-height: 1.3;
    }
    .toy-card-price {
        font-size: 1.125rem;
        font-weight: 700;
        color: #0891b2;
    }

    .about-section {
        background: linear-gradient(180deg, #fff 0%, #f8fafc 50%, #ecfeff 100%);
        padding: 4rem 0;
        border-radius: 24px;
        margin: 3rem 0;
        border: 1px solid #e2e8f0;
    }
    .about-section h2 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1rem;
    }
    .about-section p {
        color: #64748b;
        font-size: 1.0625rem;
        line-height: 1.7;
        max-width: 640px;
        margin: 0 auto;
    }

    .cta-block {
        background: linear-gradient(135deg, #0891b2 0%, #06b6d4 50%, #22d3ee 100%);
        color: #fff;
        padding: 3rem 2rem;
        border-radius: 20px;
        text-align: center;
        margin: 3rem 0;
        box-shadow: 0 8px 32px rgba(8,145,178,0.35);
    }
    .cta-block h2 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    .cta-block p {
        opacity: 0.95;
        margin-bottom: 1.5rem;
        font-weight: 500;
    }
    .cta-block .btn {
        background: #fff;
        color: #0891b2;
        font-weight: 700;
        border-radius: 50px;
        padding: 0.85rem 2rem;
        border: none;
    }
    .cta-block .btn:hover {
        background: #ecfeff;
        color: #0e7490;
    }

    .newsletter-section {
        background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
        color: #fff;
        padding: 3.5rem 2rem;
        border-radius: 20px;
        margin: 3rem 0;
        border-top: 3px solid #0891b2;
    }
    .newsletter-section h3 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    .newsletter-section p {
        opacity: 0.9;
        margin-bottom: 1.5rem;
        font-weight: 500;
    }
    .newsletter-section .form-control {
        border-radius: 50px;
        padding: 0.9rem 1.5rem;
        border: 2px solid rgba(255,255,255,0.2);
        background: rgba(255,255,255,0.08);
        color: #fff;
    }
    .newsletter-section .form-control::placeholder {
        color: rgba(255,255,255,0.6);
    }
    .newsletter-section .btn {
        border-radius: 50px;
        padding: 0.9rem 1.75rem;
        font-weight: 700;
        background: linear-gradient(135deg, #f97316, #fb923c);
        border: none;
        color: #fff;
    }
    .newsletter-section .btn:hover { color: #fff; }

    .how-it-row .card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 2rem;
        height: 100%;
        transition: transform 0.2s, border-color 0.2s;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .how-it-row .card:hover {
        transform: translateY(-2px);
        border-color: #22d3ee;
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    }
    .how-it-row .card .icon-wrap {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        margin-bottom: 1.25rem;
        background: linear-gradient(135deg, #ecfeff, #f0fdfa);
        color: #0891b2;
    }
    .how-it-row .card h3 {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }
    .how-it-row .card p {
        color: #64748b;
        font-size: 0.9375rem;
        margin-bottom: 1rem;
    }
    .how-it-row .card .btn-primary {
        border-radius: 50px;
        font-weight: 700;
        background: linear-gradient(135deg, #0891b2, #06b6d4);
        border: none;
        color: #fff;
    }
    .how-it-row .card .btn:hover { color: #fff; }
    .how-it-row .card .btn-secondary {
        background: #e2e8f0;
        color: #64748b;
    }
    .how-it-row .card a.card:hover { border-color: #22d3ee; }
    .how-it-row .card a.card .btn { pointer-events: none; }

    /* Featured Auctions - match auction page styling */
    .auction-card-home {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 16px rgba(0,0,0,0.06);
        border: 2px solid #e2e8f0;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        display: flex;
        flex-direction: column;
        text-decoration: none;
        color: inherit;
    }
    .auction-card-home:hover {
        border-color: #0891b2;
        box-shadow: 0 12px 40px rgba(8, 145, 178, 0.15);
        transform: translateY(-6px);
        color: inherit;
    }
    .auction-card-home .auction-image-wrap {
        height: 180px;
        background: #f8fafc;
        overflow: hidden;
        position: relative;
    }
    .auction-card-home .auction-image-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .auction-card-home:hover .auction-image-wrap img {
        transform: scale(1.06);
    }
    .auction-card-home .auction-price { font-size: 1.25rem; font-weight: 800; color: #0891b2; }

    @media (max-width: 768px) {
        .hero-section { padding: 3rem 0 4rem; }
        .hero-title { font-size: 2rem; }
        .category-block { padding: 1.5rem; }
    }
</style>
@endpush

@section('content')
<!-- Hero (ToyStore-style) -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content text-center">
            <span class="hero-badge">Free to shop • Verified sellers</span>
            <h1 class="hero-title">Say Hello to ToyHaven!</h1>
            <p class="hero-subtitle">Your trusted marketplace for toys and collectibles in the Philippines. Shop from verified sellers with secure checkout.</p>
            <div class="hero-cta">
                <a class="btn btn-lg" href="{{ route('toyshop.products.index') }}" role="button">
                    <i class="bi bi-bag-check me-2"></i>Shop Now
                </a>
            </div>
        </div>
    </div>
</section>

<div class="container my-5 py-2">
    <!-- How it works -->
    <div class="row mb-2">
        <div class="col-12 mb-4">
            <p class="section-label text-center reveal">How it works</p>
            <h2 class="section-heading text-center reveal">Choose how you want to discover toys</h2>
            <p class="section-subheading text-center reveal">Shop, trade, or explore—all in one place.</p>
        </div>
    </div>
    <div class="row g-4 mb-5 how-it-row">
        <div class="col-md-4">
            <div class="card reveal">
                <div class="icon-wrap"><i class="bi bi-shop"></i></div>
                <h3>Toyshop</h3>
                <p>Browse and buy from verified sellers. Secure payment and order tracking.</p>
                <a href="{{ route('toyshop.products.index') }}" class="btn btn-primary w-100">Shop Now</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card reveal">
                <div class="icon-wrap"><i class="bi bi-arrow-left-right"></i></div>
                <h3>Trading</h3>
                <p>Trade items with other collectors. List what you have, find what you want.</p>
                <a href="{{ route('trading.index') }}" class="btn btn-primary w-100">Trade Now</a>
            </div>
        </div>
        <div class="col-md-4">
            <a href="{{ route('auctions.index') }}" class="card reveal text-decoration-none" style="color: inherit; display: block;">
                <div class="icon-wrap"><i class="bi bi-hammer"></i></div>
                <h3>Auction</h3>
                <p>Bid on rare and collectible items. Join membership to place bids on exclusive auctions.</p>
                <span class="btn btn-primary w-100">View Auctions</span>
            </a>
        </div>
    </div>

    <!-- Featured Auctions - clickable direct to auction -->
    @if($featuredAuctions->isNotEmpty())
        <div class="category-block reveal mb-5">
            <h3>
                Live Auctions
                <a href="{{ route('auctions.index') }}">View All Auctions <i class="bi bi-arrow-right"></i></a>
            </h3>
            <div class="row g-4">
                @foreach($featuredAuctions as $index => $auction)
                    <div class="col-6 col-md-4">
                        <a href="{{ route('auctions.show', $auction) }}" class="auction-card-home">
                            <div class="auction-image-wrap">
                                @if($imgUrl = $auction->getPrimaryImageUrl())
                                    <img src="{{ $imgUrl }}" alt="{{ $auction->title }}">
                                @else
                                    <div class="d-flex align-items-center justify-content-center h-100"><i class="bi bi-image text-secondary" style="font-size: 2.5rem;"></i></div>
                                @endif
                                @if($auction->is_members_only)
                                    <span class="position-absolute top-0 end-0 m-2 badge" style="background: rgba(245,158,11,0.95); color: white; font-size: 0.7rem;">Members Only</span>
                                @endif
                            </div>
                            <div class="p-3 flex-grow-1 d-flex flex-column">
                                <h5 class="fw-bold mb-2 text-truncate" title="{{ $auction->title }}">{{ Str::limit($auction->title, 35) }}</h5>
                                <div class="auction-price mb-2">₱{{ number_format($auction->getCurrentPrice(), 0) }}</div>
                                <div class="mt-auto text-muted small"><i class="bi bi-clock me-1"></i>Ends {{ $auction->end_at?->diffForHumans() ?? 'TBD' }}</div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- All products (minimum 5) for guest homepage -->
    @if($featuredProducts->count() >= 5)
        <div class="category-block reveal">
            <h3>
                All Products
                <a href="{{ route('toyshop.products.index') }}">See All Toys <i class="bi bi-arrow-right"></i></a>
            </h3>
            <div class="row g-4">
                @foreach($featuredProducts as $product)
                    <div class="col-6 col-md-3">
                        <a href="{{ route('toyshop.products.show', $product->slug) }}" class="toy-card">
                            <div class="toy-card-img-wrap">
                                @if($product->images->first())
                                    <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" alt="{{ $product->name }}">
                                @else
                                    <div class="d-flex align-items-center justify-content-center h-100"><i class="bi bi-image text-secondary" style="font-size: 3rem;"></i></div>
                                @endif
                            </div>
                            <div class="toy-card-body">
                                <div class="toy-card-title">{{ Str::limit($product->name, 40) }}</div>
                                <div class="toy-card-price">₱ {{ number_format($product->price, 2) }}</div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- About (ToyStore: Watch Our Story) -->
    <section class="about-section text-center" id="about">
        <p class="section-label reveal">About the shop</p>
        <h2 class="reveal">Watch Our Story</h2>
        <p class="reveal">ToyHaven is built for collectors and families who want a trusted place to buy and trade toys in the Philippines. We verify every seller so you can shop with confidence—and our trading feature lets you swap items with other enthusiasts. No magic formula—just a simple, colorful marketplace made for you.</p>
    </section>

    <!-- CTA block (ToyStore: Simple & Colorful / GET IT NOW) -->
    <div class="cta-block reveal">
        <h2>Simple & Colorful Marketplace for Your Business</h2>
        <p>Browse verified sellers, track orders, and trade with the community. All in one place.</p>
        <a href="{{ route('toyshop.products.index') }}" class="btn btn-lg">Get Started Now</a>
    </div>

    <!-- Newsletter (ToyStore: Subscribe & get 10% off) -->
    <section class="newsletter-section text-center" id="contact">
        <h3>Subscribe to our newsletter & get 10% discount!</h3>
        <p>Get updates on new arrivals, promos, and trading tips. No spam.</p>
        <form action="#" method="post" class="row g-2 justify-content-center mx-auto" style="max-width: 420px;">
            @csrf
            <div class="col-12 col-sm-auto" style="min-width: 260px;">
                <input type="email" name="email" class="form-control" placeholder="Your email address" required>
            </div>
            <div class="col-12 col-sm-auto">
                <button type="submit" class="btn w-100 w-sm-auto">Subscribe</button>
            </div>
        </form>
    </section>
</div>
@endsection
