@extends('layouts.toyshop')

@section('title', 'Dashboard - ToyHaven')

@push('styles')
<style>
    .dashboard-header {
        background: linear-gradient(135deg, #0891b2 0%, #06b6d4 50%, #fb923c 100%);
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 8px 32px rgba(255, 107, 107, 0.3);
        margin-bottom: 2rem;
        color: white;
        border: none;
    }
    
    .dashboard-welcome {
        font-size: 1.75rem;
        font-weight: 800;
        margin-bottom: 0.375rem;
        letter-spacing: -0.02em;
    }
    
    .dashboard-subtitle {
        font-size: 1rem;
        opacity: 0.95;
        font-weight: 500;
    }
    
    .quick-action-card {
        background: #fff;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        border: 2px solid #e2e8f0;
        transition: box-shadow 0.25s ease, border-color 0.25s ease, transform 0.2s ease;
        height: 100%;
        text-align: center;
    }
    
    .quick-action-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0,0,0,0.08);
        border-color: #a5f3fc;
    }
    
    .action-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2.5rem;
        background: linear-gradient(135deg, #0891b2, #06b6d4);
        color: white;
        transition: all 0.3s ease;
    }
    
    .quick-action-card:hover .action-icon {
        transform: scale(1.05);
    }
    
    .action-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }
    
    .action-description {
        color: #64748b;
        font-size: 0.875rem;
        margin-bottom: 1.5rem;
        font-weight: 500;
    }
    
    .welcome-card {
        background: #fff;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        border: 2px solid #e2e8f0;
        margin-top: 2rem;
    }
    
    .welcome-card:hover {
        border-color: #a5f3fc;
    }
    
    .welcome-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .welcome-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 0.75rem;
    }
    
    .welcome-text {
        color: #64748b;
        line-height: 1.8;
        margin-bottom: 1.5rem;
        font-weight: 500;
    }
    
    .quick-action-card .btn-primary {
        background: linear-gradient(135deg, #0891b2, #06b6d4);
        border: none;
        font-weight: 700;
    }
    .quick-action-card .btn-primary:hover {
        background: linear-gradient(135deg, #ee5a5a, #f57d43);
        color: #fff;
    }
    .quick-action-card .btn-success {
        background: linear-gradient(135deg, #51cf66, #40c057);
        border: none;
        font-weight: 700;
    }
    .quick-action-card .btn-info {
        background: linear-gradient(135deg, #339af0, #228be6);
        border: none;
        color: #fff;
        font-weight: 700;
    }
    .quick-action-card .btn-warning {
        background: linear-gradient(135deg, #06b6d4, #fb923c);
        border: none;
        color: #fff;
        font-weight: 700;
    }
    .welcome-card .btn-outline-primary {
        border: 2px solid #0891b2;
        color: #0891b2;
        font-weight: 700;
    }
    .welcome-card .btn-outline-primary:hover {
        background: #ecfeff;
        color: #0891b2;
        border-color: #0891b2;
    }
    .welcome-card .btn-outline-danger {
        border: 2px solid #0891b2;
        color: #0891b2;
        font-weight: 700;
    }
    .welcome-card .btn-outline-danger:hover {
        background: #ecfeff;
        color: #0891b2;
        border-color: #0891b2;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="dashboard-header reveal">
        <div class="dashboard-welcome">
            <i class="bi bi-hand-thumbs-up me-2"></i>Welcome back, {{ Auth::user()->name }}!
        </div>
        <div class="dashboard-subtitle">Manage your account, browse products, and track your orders</div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="quick-action-card reveal">
                <div class="action-icon">
                    <i class="bi bi-bag-check"></i>
                </div>
                <h5 class="action-title">Shop Products</h5>
                <p class="action-description">Browse our collection of toys and collectibles</p>
                <a href="{{ route('toyshop.products.index') }}" class="btn btn-primary w-100">
                    <i class="bi bi-arrow-right me-2"></i>Go Shopping
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="quick-action-card reveal" style="animation-delay: 0.1s;">
                <div class="action-icon" style="background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);">
                    <i class="bi bi-cart3"></i>
                </div>
                <h5 class="action-title">Shopping Cart</h5>
                <p class="action-description">View and manage your cart items</p>
                <a href="{{ route('cart.index') }}" class="btn btn-success w-100">
                    <i class="bi bi-arrow-right me-2"></i>View Cart
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="quick-action-card reveal" style="animation-delay: 0.2s;">
                <div class="action-icon" style="background: linear-gradient(135deg, #339af0 0%, #228be6 100%);">
                    <i class="bi bi-box-seam"></i>
                </div>
                <h5 class="action-title">My Orders</h5>
                <p class="action-description">Track and manage your orders</p>
                <a href="{{ route('orders.index') }}" class="btn btn-info w-100">
                    <i class="bi bi-arrow-right me-2"></i>View Orders
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="quick-action-card reveal" style="animation-delay: 0.3s;">
                <div class="action-icon" style="background: linear-gradient(135deg, #06b6d4 0%, #fb923c 100%);">
                    <i class="bi bi-person-circle"></i>
                </div>
                <h5 class="action-title">Profile</h5>
                <p class="action-description">Manage your account settings</p>
                <a href="{{ route('profile.edit') }}" class="btn btn-warning w-100">
                    <i class="bi bi-arrow-right me-2"></i>Edit Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Welcome Message -->
    <div class="welcome-card reveal" style="animation-delay: 0.4s;">
        <div class="welcome-icon">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <h3 class="welcome-title">You're all set!</h3>
        <p class="welcome-text">
            Thank you for being part of ToyHaven Platform. You can now browse products, add them to your cart, 
            place orders, and track your purchases. Explore our marketplace to discover amazing toys and collectibles 
            from verified sellers.
        </p>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('suggested-products') }}" class="btn btn-primary">
                <i class="bi bi-stars me-2"></i>View Suggested Products
            </a>
            @if(Auth::user()->isSeller())
                <a href="{{ route('seller.dashboard') }}" class="btn btn-outline-primary">
                    <i class="bi bi-shop me-2"></i>Go to Seller Dashboard
                </a>
            @else
                <a href="{{ route('seller.register') }}" class="btn btn-outline-primary">
                    <i class="bi bi-person-badge me-2"></i>Become a Seller
                </a>
            @endif
            <a href="{{ route('wishlist.index') }}" class="btn btn-outline-danger">
                <i class="bi bi-heart me-2"></i>My Wishlist
            </a>
        </div>
    </div>
</div>
@endsection
