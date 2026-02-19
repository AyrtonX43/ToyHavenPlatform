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
    <!-- Google Fonts - Toys and Joy style -->
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Animate.css for animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    @stack('styles')
    <style>
        body {
            font-family: 'Quicksand', -apple-system, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .seller-sidebar {
            height: 100vh;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            color: white;
            width: 280px;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0,0,0,0.15);
            animation: slideInLeft 0.5s ease-out;
            border-right: 3px solid #0891b2;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .seller-sidebar h4 {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 5px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .seller-sidebar .sidebar-brand {
            padding: 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            flex-shrink: 0;
        }
        
        .seller-sidebar nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 20px 0;
        }
        
        /* Custom Scrollbar for Sidebar */
        .seller-sidebar nav::-webkit-scrollbar {
            width: 6px;
        }
        
        .seller-sidebar nav::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }
        
        .seller-sidebar nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            transition: background 0.3s ease;
        }
        
        .seller-sidebar nav::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        /* Firefox Scrollbar */
        .seller-sidebar nav {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.2) rgba(255, 255, 255, 0.05);
        }

        @keyframes slideInLeft {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .sidebar-link {
            color: rgba(255, 255, 255, 0.85);
            padding: 14px 25px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            border-left: 4px solid transparent;
            font-weight: 500;
            font-size: 0.95rem;
            margin: 2px 0;
        }
        
        .sidebar-link i {
            width: 24px;
            font-size: 1.1rem;
            margin-right: 12px;
        }

        .sidebar-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: linear-gradient(180deg, #0891b2, #06b6d4);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .sidebar-link:hover {
            background: rgba(8, 145, 178, 0.15);
            color: white;
            border-left-color: #0891b2;
        }

        .sidebar-link:hover::before {
            transform: scaleY(1);
        }

        .sidebar-link.active {
            background: rgba(8, 145, 178, 0.2);
            color: white;
            border-left-color: #0891b2;
            font-weight: 700;
        }

        .sidebar-link.active::before {
            transform: scaleY(1);
        }

        .sidebar-link:hover i {
            transform: translateX(4px) scale(1.05);
        }

        .seller-content {
            margin-left: 280px;
            padding: 24px;
            min-height: 100vh;
            background: linear-gradient(180deg, #f8fafc 0%, #f0fdfa 50%, #ecfeff 100%);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Navbar - ToyStore style */
        .seller-navbar {
            background: #fff;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            padding: 18px 25px;
            margin-bottom: 25px;
            border-radius: 16px;
            animation: slideDown 0.5s ease-out;
            border: 1px solid #e2e8f0;
        }
        
        .seller-navbar h5 {
            font-weight: 800;
            color: #0891b2;
            font-size: 1.25rem;
        }
        
        .seller-user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            background: #ecfeff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        
        .seller-user-info span:first-child {
            font-weight: 500;
            color: #495057;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Cards - ToyStore style */
        .card {
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            animation: fadeInUp 0.6s ease-out;
            animation-fill-mode: both;
            background: #fff;
            margin-bottom: 25px;
        }

        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.15s; }
        .card:nth-child(3) { animation-delay: 0.2s; }
        .card:nth-child(4) { animation-delay: 0.25s; }
        .card:nth-child(n+5) { animation-delay: 0.3s; }

        .card:hover {
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            border-color: #22d3ee;
        }

        .card-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 700;
            padding: 20px 25px;
            transition: all 0.3s ease;
            font-size: 1.1rem;
            color: #1e293b;
        }
        
        .card-body {
            padding: 25px;
        }


        /* Stat Cards */
        .stat-card {
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            padding: 25px;
            color: white;
            transition: all 0.3s ease;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            transition: transform 0.6s ease;
        }

        .stat-card:hover::before {
            transform: scale(1.2);
        }

        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
        }

        .stat-card.bg-primary { background: linear-gradient(135deg, #0891b2, #06b6d4); }
        .stat-card.bg-success { background: linear-gradient(135deg, #10b981, #059669); }
        .stat-card.bg-warning { background: linear-gradient(135deg, #f59e0b, #fb923c); }
        .stat-card.bg-info { background: linear-gradient(135deg, #0ea5e9, #06b6d4); }
        .stat-card.bg-danger { background: linear-gradient(135deg, #ef4444, #dc2626); }

        .stat-card .stat-icon {
            font-size: 3rem;
            opacity: 0.3;
            position: absolute;
            right: 20px;
            top: 20px;
        }

        .stat-card .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 10px 0;
        }

        .stat-card .stat-label {
            font-size: 0.95rem;
            opacity: 0.9;
            font-weight: 500;
        }

        /* Buttons - ToyStore style */
        .btn {
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            font-weight: 700;
            padding: 10px 20px;
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #0891b2, #06b6d4);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #0e7490, #0891b2);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(8, 145, 178, 0.35);
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 0.875rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }

        /* Tables */
        .table {
            animation: fadeIn 0.5s ease-in;
            margin-bottom: 0;
        }
        
        .table thead {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .table thead th {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            color: #1e293b;
            padding: 15px;
        }

        .table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .table tbody td {
            padding: 15px;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #ecfeff;
        }

        /* Badges */
        .badge {
            transition: all 0.3s ease;
            padding: 6px 12px;
            font-weight: 500;
            font-size: 0.8rem;
            border-radius: 6px;
        }


        /* Alerts */
        .alert {
            animation: slideInRight 0.5s ease-out;
            border-radius: 10px;
            border-left: 4px solid;
            border-left-width: 5px;
            padding: 15px 20px;
            font-weight: 500;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Responsive */
        @media (max-width: 992px) {
            .seller-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .seller-sidebar.show {
                transform: translateX(0);
            }
            
            .seller-content {
                margin-left: 0;
                padding: 20px 15px;
            }
            
            .seller-navbar {
                padding: 15px;
            }
        }
        
        @media (max-width: 768px) {
            .seller-content {
                padding: 15px 10px;
            }
            
            .card-body {
                padding: 15px;
            }
            
            .seller-user-info {
                flex-direction: column;
                gap: 8px;
                padding: 10px;
            }

            .stat-card .stat-value {
                font-size: 2rem;
            }
        }
        
        /* Mobile Menu Toggle */
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: linear-gradient(135deg, #0891b2, #06b6d4);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(8,145,178,0.4);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .sidebar-toggle:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(8,145,178,0.5);
        }
        
        @media (max-width: 992px) {
            .sidebar-toggle {
                display: block;
            }
        }

        /* Page Content Animation */
        .page-content {
            animation: fadeInUp 0.6s ease-out both;
        }
        
        /* Quick Action Cards */
        .quick-action-card {
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            display: block;
            color: inherit;
        }

        .quick-action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .quick-action-card i {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #0891b2;
        }

        .quick-action-card h6 {
            font-weight: 600;
            margin-top: 10px;
        }

        /* Pulse Animation */
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }

        .pulse {
            animation: pulse 2s infinite;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>
    
    <!-- Sidebar -->
    <div class="seller-sidebar" id="sellerSidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('images/logo.png') }}" alt="ToyHaven" height="32" class="d-inline-block align-top me-2">
            <h4 class="mb-0 d-inline-block align-middle"><i class="bi bi-shop me-2"></i>Seller Panel</h4>
            <small class="text-white-50 d-block mt-1">ToyHaven Platform</small>
        </div>
        <nav>
            <div class="px-3 mb-2">
                <small class="text-white-50 text-uppercase">Dashboard</small>
            </div>
            <a href="{{ route('seller.dashboard') }}" class="sidebar-link {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i> Overview
            </a>
            <hr class="text-white-50 my-3">
            <div class="px-3 mb-2">
                <small class="text-white-50 text-uppercase">Products</small>
            </div>
            <a href="{{ route('seller.products.index') }}" class="sidebar-link {{ request()->routeIs('seller.products.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam me-2"></i> My Products
            </a>
            <a href="{{ route('seller.products.create') }}" class="sidebar-link {{ request()->routeIs('seller.products.create') ? 'active' : '' }}">
                <i class="bi bi-plus-circle me-2"></i> Add Product
            </a>
            <hr class="text-white-50 my-3">
            <div class="px-3 mb-2">
                <small class="text-white-50 text-uppercase">Orders</small>
            </div>
            <a href="{{ route('seller.orders.index') }}" class="sidebar-link {{ request()->routeIs('seller.orders.*') ? 'active' : '' }}">
                <i class="bi bi-cart-check me-2"></i> Order Management
            </a>
            @if(Auth::user()->seller && Auth::user()->seller->verification_status === 'approved')
                <a href="{{ route('seller.pos.index') }}" class="sidebar-link {{ request()->routeIs('seller.pos.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-register me-2"></i> Point of Sale (POS)
                </a>
            @endif
            <hr class="text-white-50 my-3">
            <div class="px-3 mb-2">
                <small class="text-white-50 text-uppercase">Business</small>
            </div>
            <a href="{{ route('seller.business-page.index') }}" class="sidebar-link {{ request()->routeIs('seller.business-page.*') ? 'active' : '' }}">
                <i class="bi bi-gear me-2"></i> Business Settings
            </a>
            @if(Auth::user()->seller && Auth::user()->seller->verification_status === 'approved')
                <a href="{{ route('toyshop.business.show', Auth::user()->seller->business_slug) }}" target="_blank" class="sidebar-link">
                    <i class="bi bi-eye me-2"></i> View Business Page
                </a>
            @endif
            <hr class="text-white-50 my-3">
            <div class="px-3 mb-2">
                <small class="text-white-50 text-uppercase">Account</small>
            </div>
            @if(Auth::user()->seller && !Auth::user()->seller->is_verified_shop)
                <a href="{{ route('seller.shop-upgrade.index') }}" class="sidebar-link {{ request()->routeIs('seller.shop-upgrade.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-check me-2"></i> Upgrade to Trusted Shop
                </a>
            @endif
            <hr class="text-white-50 my-3">
            <div class="px-3">
                <a href="{{ route('home') }}" class="sidebar-link">
                    <i class="bi bi-house me-2"></i> Back to Store
                </a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="seller-content">
        <!-- Navbar -->
        <div class="seller-navbar">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">@yield('page-title', 'Seller Dashboard')</h5>
                </div>
                <div class="seller-user-info">
                    <div class="d-flex align-items-center gap-2">
                        <div class="text-end">
                            <div style="font-weight: 700; color: #1e293b;">{{ Auth::user()->name }}</div>
                            <small class="text-muted">
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
        // Number counter animation
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

        // Animate counters on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Animate number counters
            document.querySelectorAll('.counter-number').forEach(counter => {
                const count = parseFloat(counter.getAttribute('data-count'));
                if (!isNaN(count) && count > 0) {
                    counter.textContent = '0';
                    setTimeout(() => {
                        animateCounter(counter, count, false);
                    }, 300);
                }
            });

            // Animate currency counters
            document.querySelectorAll('.counter-currency').forEach(counter => {
                const count = parseFloat(counter.getAttribute('data-count'));
                if (!isNaN(count) && count >= 0) {
                    counter.textContent = '₱0.00';
                    setTimeout(() => {
                        animateCounter(counter, count, true);
                    }, 500);
                }
            });

            // Add stagger animation to cards
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });
        
        // Sidebar toggle for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sellerSidebar');
            sidebar.classList.toggle('show');
        }
        
        // Close sidebar when clicking outside on mobile
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
