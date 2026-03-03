<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Seller Dashboard - ToyHaven')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts - Quicksand -->
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/seller.css'])
    @stack('styles')
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="bi bi-list fs-5"></i>
    </button>
    
    <!-- Sidebar -->
    <div class="seller-sidebar" id="sellerSidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('images/logo.png') }}" alt="ToyHaven" height="34" class="d-inline-block align-top me-2">
            <h4 class="mb-0 d-inline-block align-middle"><i class="bi bi-shop me-2"></i>Seller Panel</h4>
            <small class="text-white-50 d-block mt-1" style="font-size: 0.78rem;">ToyHaven Platform</small>
        </div>
        <nav>
            <div class="sidebar-section-label">Main</div>
            <a href="{{ route('seller.dashboard') }}" class="sidebar-link {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <div class="sidebar-section-label">Catalog</div>
            <a href="{{ route('seller.products.index') }}" class="sidebar-link {{ request()->routeIs('seller.products.index') || request()->routeIs('seller.products.show') || request()->routeIs('seller.products.edit') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> Products
            </a>
            <a href="{{ route('seller.products.create') }}" class="sidebar-link {{ request()->routeIs('seller.products.create') ? 'active' : '' }}">
                <i class="bi bi-plus-circle"></i> Add Product
            </a>

            <div class="sidebar-section-label">Sales</div>
            <a href="{{ route('seller.orders.index') }}" class="sidebar-link {{ request()->routeIs('seller.orders.*') ? 'active' : '' }}">
                <i class="bi bi-cart-check"></i> Orders
            </a>
            @if(Auth::user()->seller && Auth::user()->seller->verification_status === 'approved')
                <a href="{{ route('seller.pos.index') }}" class="sidebar-link {{ request()->routeIs('seller.pos.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-register"></i> Point of Sale
                </a>
            @endif

            <div class="sidebar-section-label">Store</div>
            <a href="{{ route('seller.business-page.index') }}" class="sidebar-link {{ request()->routeIs('seller.business-page.*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> Business Settings
            </a>
            @if(Auth::user()->seller && Auth::user()->seller->verification_status === 'approved')
                <a href="{{ route('toyshop.business.show', Auth::user()->seller->business_slug) }}" target="_blank" class="sidebar-link">
                    <i class="bi bi-eye"></i> View Store
                </a>
            @endif

            @if(Auth::user()->seller && !Auth::user()->seller->is_verified_shop)
            <div class="sidebar-section-label">Account</div>
                <a href="{{ route('seller.shop-upgrade.index') }}" class="sidebar-link {{ request()->routeIs('seller.shop-upgrade.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-check"></i> Upgrade to Trusted Shop
                </a>
            @endif

            <hr class="text-white-50 my-3 mx-4">
            <a href="{{ route('home') }}" class="sidebar-link" style="background: rgba(14, 165, 233, 0.1); border-left-color: var(--primary-sky-light);">
                <i class="bi bi-house-door"></i> Back to Website
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="seller-content">
        <!-- Navbar -->
        <div class="seller-navbar">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="mb-0">@yield('page-title', 'Seller Dashboard')</h5>
                </div>
                <div class="seller-user-info">
                    <div class="d-flex align-items-center gap-2">
                        <div class="text-end">
                            <div style="font-weight: 700; color: var(--primary-sky);">{{ Auth::user()->name }}</div>
                            <small class="text-muted" style="font-size: 0.8rem;">
                                @if(Auth::user()->seller)
                                    {{ Auth::user()->seller->business_name }}
                                @else
                                    Seller
                                @endif
                            </small>
                        </div>
                        @if(Auth::user()->seller && Auth::user()->seller->verification_status === 'approved')
                            <span class="badge bg-success" style="font-size: 0.75rem;">Verified</span>
                        @elseif(Auth::user()->seller && Auth::user()->seller->verification_status === 'pending')
                            <span class="badge bg-warning" style="font-size: 0.75rem;">Pending</span>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Page Content -->
        <div class="page-content">
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function animateCounter(element, target, isCurrency = false, duration = 2000) {
            const start = 0;
            const increment = target / (duration / 16);
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    if (isCurrency) {
                        element.textContent = '₱' + target.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    } else {
                        element.textContent = Math.floor(target).toLocaleString();
                    }
                    clearInterval(timer);
                } else {
                    if (isCurrency) {
                        element.textContent = '₱' + current.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    } else {
                        element.textContent = Math.floor(current).toLocaleString();
                    }
                }
            }, 16);
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.counter-number').forEach(counter => {
                const count = parseFloat(counter.getAttribute('data-count'));
                if (!isNaN(count) && count > 0) {
                    counter.textContent = '0';
                    setTimeout(() => {
                        animateCounter(counter, count, false);
                    }, 300);
                }
            });

            document.querySelectorAll('.counter-currency').forEach(counter => {
                const count = parseFloat(counter.getAttribute('data-count'));
                if (!isNaN(count) && count >= 0) {
                    counter.textContent = '₱0.00';
                    setTimeout(() => {
                        animateCounter(counter, count, true);
                    }, 500);
                }
            });
        });
        
        function toggleSidebar() {
            const sidebar = document.getElementById('sellerSidebar');
            sidebar.classList.toggle('show');
        }
        
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sellerSidebar');
            const toggle = document.querySelector('.sidebar-toggle');
            
            if (window.innerWidth <= 992) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>
