@extends('layouts.toyshop')

@section('title', 'Business Page Preview - ' . ($pageSettings->page_name ?? $seller->business_name))

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Business Page Preview</h2>
        <div>
            <a href="{{ route('seller.business-page.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Settings
            </a>
            <a href="{{ route('toyshop.business.show', $seller->business_slug) }}" class="btn btn-primary" target="_blank">
                <i class="bi bi-box-arrow-up-right me-1"></i> View Live Page
            </a>
        </div>
    </div>

    <div class="alert alert-info mb-4">
        <i class="bi bi-info-circle me-2"></i>
        This preview matches how your business page appears to customers.
        @if(!($pageSettings->is_published ?? false))
            <strong>Your page is not yet published.</strong> Enable publishing in General Settings to make it visible.
        @else
            <strong>Your page is published and visible to customers.</strong>
        @endif
    </div>

    {{-- Same structure as live page (toyshop/business/show) --}}
    <!-- Business Banner -->
    @if($pageSettings && $pageSettings->banner_path)
        <div class="mb-3 rounded overflow-hidden">
            <img src="{{ asset('storage/' . $pageSettings->banner_path) }}"
                 class="img-fluid w-100"
                 alt="Store banner"
                 style="max-height: 300px; object-fit: cover;">
        </div>
    @endif

    <!-- Business Header (same card as live) -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    @if($pageSettings && $pageSettings->logo_path)
                        <img src="{{ asset('storage/' . $pageSettings->logo_path) }}" class="img-fluid rounded" alt="{{ $pageSettings->page_name ?? $seller->business_name }}" style="max-height: 150px;">
                    @elseif($seller->logo)
                        <img src="{{ asset('storage/' . $seller->logo) }}" class="img-fluid rounded" alt="{{ $seller->business_name }}" style="max-height: 150px;">
                    @else
                        <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 150px; height: 150px; margin: 0 auto;">
                            <i class="bi bi-shop text-white" style="font-size: 4rem;"></i>
                        </div>
                    @endif
                </div>
                <div class="col-md-10">
                    <h2 style="{{ $pageSettings && $pageSettings->primary_color ? 'color: ' . $pageSettings->primary_color : '' }}">{{ $pageSettings->page_name ?? $seller->business_name }}</h2>
                    <div class="mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi bi-star{{ $i <= $seller->rating ? '-fill text-warning' : '' }}"></i>
                        @endfor
                        <span class="ms-2">{{ $seller->rating }} ({{ $seller->total_reviews }} reviews)</span>
                        <span class="badge bg-primary ms-2">{{ $seller->getRankingBadge() }}</span>
                    </div>
                    <p class="mb-0">
                        <i class="bi bi-geo-alt"></i> {{ $seller->city ?? '—' }}, {{ $seller->province ?? '—' }}<br>
                        <i class="bi bi-envelope"></i> {{ $seller->email ?? '—' }}<br>
                        <i class="bi bi-telephone"></i> {{ $seller->phone ?? '—' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- About Us -->
    @if($pageSettings && ($pageSettings->business_description ?? null))
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">About Us</h5>
                <p class="text-muted mb-0">{{ $pageSettings->business_description }}</p>
            </div>
        </div>
    @elseif($seller->description)
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">About Us</h5>
                <p class="text-muted mb-0">{{ $seller->description }}</p>
            </div>
        </div>
    @endif

    <!-- Social Media Links -->
    @if($socialLinks && $socialLinks->count() > 0)
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Follow Us</h5>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($socialLinks as $link)
                        <a href="{{ $link->url }}" target="_blank" class="btn btn-outline-primary btn-sm" rel="noopener noreferrer">
                            <i class="bi {{ $link->getPlatformIcon() }} me-1"></i>
                            {{ $link->display_name ?? ucfirst($link->platform) }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Statistics (same as live) -->
    @php
        $previewStats = [
            'total_products' => $seller->products()->where('status', 'active')->count(),
            'total_sales' => $seller->total_sales,
            'rating' => $seller->rating,
            'total_reviews' => $seller->total_reviews,
        ];
    @endphp
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-primary">{{ $previewStats['total_products'] }}</h4>
                    <p class="text-muted mb-0">Products</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-success">{{ $previewStats['total_sales'] }}</h4>
                    <p class="text-muted mb-0">Total Sales</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-warning">{{ $previewStats['rating'] }}</h4>
                    <p class="text-muted mb-0">Rating</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-info">{{ $previewStats['total_reviews'] }}</h4>
                    <p class="text-muted mb-0">Reviews</p>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-secondary mb-0">
        <i class="bi bi-grid me-2"></i> Products and recent reviews appear in the same layout on the live page.
    </div>
</div>
@endsection
