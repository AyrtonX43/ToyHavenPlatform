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
    @stack('styles')
    <style>
        :root {
            --primary-sky: #0ea5e9;
            --primary-sky-dark: #0284c7;
            --primary-sky-light: #38bdf8;
            --bg-gradient-start: #f0f9ff;
            --bg-gradient-end: #e0f2fe;
            --sidebar-bg: #0c4a6e;
            --sidebar-bg-light: #075985;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Quicksand', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            min-height: 100vh;
            font-weight: 500;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInLeft {
            from { transform: translateX(-100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }

        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(14, 165, 233, 0.3); }
            50% { box-shadow: 0 0 40px rgba(14, 165, 233, 0.6); }
        }

        /* Sidebar */
        .seller-sidebar {
            height: 100vh;
            background: linear-gradient(180deg, var(--sidebar-bg) 0%, var(--sidebar-bg-light) 100%);
            color: white;
            width: 280px;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            border-right: 4px solid var(--primary-sky);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: slideInLeft 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            box-shadow: 4px 0 30px rgba(14, 165, 233, 0.2);
        }

        .seller-sidebar h4 {
            font-weight: 800;
            font-size: 1.3rem;
            margin-bottom: 3px;
            letter-spacing: -0.5px;
        }

        .seller-sidebar .sidebar-brand {
            padding: 24px 22px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            flex-shrink: 0;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.1) 0%, transparent 100%);
        }

        .seller-sidebar nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 16px 0;
        }

        .seller-sidebar nav::-webkit-scrollbar { width: 6px; }
        .seller-sidebar nav::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); border-radius: 10px; }
        .seller-sidebar nav::-webkit-scrollbar-thumb { background: rgba(14, 165, 233, 0.4); border-radius: 10px; transition: background 0.3s ease; }
        .seller-sidebar nav::-webkit-scrollbar-thumb:hover { background: rgba(14, 165, 233, 0.6); }
        .seller-sidebar nav { scrollbar-width: thin; scrollbar-color: rgba(14, 165, 233, 0.4) rgba(255,255,255,0.05); }

        .sidebar-section-label {
            padding: 16px 22px 8px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,0.4);
        }

        .sidebar-link {
            color: rgba(255,255,255,0.85);
            padding: 12px 22px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-left: 4px solid transparent;
            font-weight: 600;
            font-size: 0.92rem;
            white-space: nowrap;
            position: relative;
            overflow: hidden;
        }

        .sidebar-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, rgba(14, 165, 233, 0.15), transparent);
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .sidebar-link i {
            width: 24px;
            font-size: 1.1rem;
            margin-right: 12px;
            flex-shrink: 0;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .sidebar-link:hover {
            background: rgba(14, 165, 233, 0.15);
            color: #fff;
            border-left-color: var(--primary-sky);
            transform: translateX(2px);
        }

        .sidebar-link:hover::before {
            transform: translateX(0);
        }

        .sidebar-link:hover i {
            transform: scale(1.1) rotate(5deg);
        }

        .sidebar-link.active {
            background: linear-gradient(90deg, rgba(14, 165, 233, 0.25), rgba(14, 165, 233, 0.1));
            color: #fff;
            border-left-color: var(--primary-sky-light);
            font-weight: 800;
            box-shadow: inset 0 0 20px rgba(14, 165, 233, 0.2);
        }

        /* Main Content */
        .seller-content {
            margin-left: 280px;
            padding: 28px;
            min-height: 100vh;
            animation: fadeIn 0.5s ease-in;
        }

        /* Navbar */
        .seller-navbar {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 4px 20px rgba(14, 165, 233, 0.1);
            padding: 20px 28px;
            margin-bottom: 28px;
            border-radius: 20px;
            animation: slideDown 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border: 2px solid rgba(14, 165, 233, 0.1);
            position: relative;
            overflow: hidden;
        }

        .seller-navbar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-sky), var(--primary-sky-light), var(--primary-sky));
            background-size: 200% 100%;
            animation: shimmer 3s linear infinite;
        }

        .seller-navbar h5 {
            font-weight: 900;
            color: var(--primary-sky);
            font-size: 1.4rem;
            letter-spacing: -0.5px;
            margin: 0;
        }

        .seller-user-info {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 10px 18px;
            background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%);
            border-radius: 16px;
            border: 2px solid rgba(14, 165, 233, 0.2);
            transition: all 0.3s ease;
        }

        .seller-user-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.15);
        }

        .seller-user-info span:first-child {
            font-weight: 600;
            color: #334155;
        }

        /* Cards */
        .card {
            border: 2px solid rgba(14, 165, 233, 0.1);
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.08);
            border-radius: 20px;
            overflow: hidden;
            background: #fff;
            margin-bottom: 24px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            animation: fadeInUp 0.6s ease-out both;
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 35px rgba(14, 165, 233, 0.15);
            border-color: var(--primary-sky-light);
        }

        .card-header {
            background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 100%);
            border-bottom: 2px solid rgba(14, 165, 233, 0.1);
            font-weight: 800;
            padding: 18px 24px;
            font-size: 1.05rem;
            color: #0c4a6e;
        }

        .card-body {
            padding: 24px;
        }

        /* Stat Cards */
        .stat-card {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            padding: 28px;
            color: white;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }

        .stat-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.2);
        }

        .stat-card.bg-primary { background: linear-gradient(135deg, var(--primary-sky), var(--primary-sky-light)); }
        .stat-card.bg-success { background: linear-gradient(135deg, #10b981, #34d399); }
        .stat-card.bg-warning { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
        .stat-card.bg-info { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
        .stat-card.bg-danger { background: linear-gradient(135deg, #ef4444, #f87171); }

        .stat-card .stat-icon {
            font-size: 3.5rem;
            opacity: 0.25;
            position: absolute;
            right: 24px;
            top: 24px;
            animation: float 4s ease-in-out infinite;
        }

        .stat-card .stat-value {
            font-size: 2.8rem;
            font-weight: 900;
            margin: 12px 0;
            letter-spacing: -1px;
        }

        .stat-card .stat-label {
            font-size: 0.95rem;
            opacity: 0.95;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        /* Buttons */
        .btn {
            border-radius: 14px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            font-weight: 700;
            padding: 11px 22px;
            border: none;
            letter-spacing: 0.3px;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-sky), var(--primary-sky-light));
            border: none;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-sky-dark), var(--primary-sky));
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(14, 165, 233, 0.4);
        }

        .btn-sm {
            padding: 9px 18px;
            font-size: 0.88rem;
        }

        .btn-outline-danger {
            border: 2px solid #ef4444;
            color: #ef4444;
            background: transparent;
        }

        .btn-outline-danger:hover {
            background: #ef4444;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary-sky);
            color: var(--primary-sky);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--primary-sky);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.3);
        }

        .btn:active {
            transform: translateY(0) scale(0.98);
        }

        /* Tables */
        .table {
            animation: fadeIn 0.6s ease-in;
            margin-bottom: 0;
        }

        .table thead {
            background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 100%);
            border-bottom: 2px solid rgba(14, 165, 233, 0.2);
        }

        .table thead th {
            font-weight: 800;
            text-transform: uppercase;
            font-size: 0.82rem;
            letter-spacing: 0.8px;
            color: var(--sidebar-bg);
            padding: 16px;
        }

        .table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(14, 165, 233, 0.1);
        }

        .table tbody td {
            padding: 16px;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: linear-gradient(90deg, rgba(14, 165, 233, 0.05), transparent);
            transform: translateX(4px);
        }

        /* Badges */
        .badge {
            transition: all 0.3s ease;
            padding: 7px 14px;
            font-weight: 600;
            font-size: 0.8rem;
            border-radius: 8px;
            letter-spacing: 0.3px;
        }

        .badge:hover {
            transform: scale(1.05);
        }

        /* Alerts */
        .alert {
            animation: slideInRight 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border-radius: 16px;
            border-left: 5px solid;
            padding: 16px 22px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(100px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* Quick Action Cards */
        .quick-action-card {
            border-radius: 16px;
            padding: 24px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            text-decoration: none;
            display: block;
            color: inherit;
            border: 2px solid rgba(14, 165, 233, 0.1);
            background: white;
        }

        .quick-action-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(14, 165, 233, 0.15);
            border-color: var(--primary-sky-light);
        }

        .quick-action-card i {
            font-size: 3rem;
            margin-bottom: 12px;
            color: var(--primary-sky);
            transition: transform 0.3s ease;
        }

        .quick-action-card:hover i {
            transform: scale(1.15) rotate(5deg);
        }

        .quick-action-card h6 {
            font-weight: 700;
            margin-top: 12px;
            color: #0c4a6e;
        }

        /* Form Controls */
        .form-control, .form-select {
            border-radius: 12px;
            border: 2px solid rgba(14, 165, 233, 0.2);
            padding: 11px 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-sky);
            box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1);
            transform: translateY(-2px);
        }

        /* Mobile Responsive */
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
                padding: 20px 16px;
            }

            .seller-navbar {
                padding: 16px 18px;
            }
        }

        @media (max-width: 768px) {
            .seller-content {
                padding: 16px 12px;
            }

            .card-body {
                padding: 18px;
            }

            .seller-user-info {
                flex-direction: column;
                gap: 10px;
                padding: 12px;
            }

            .stat-card .stat-value {
                font-size: 2.2rem;
            }
        }

        /* Mobile Toggle */
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 22px;
            left: 22px;
            z-index: 1001;
            background: linear-gradient(135deg, var(--primary-sky), var(--primary-sky-light));
            color: white;
            border: none;
            padding: 12px 16px;
            border-radius: 14px;
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.4);
            transition: all 0.3s ease;
            animation: pulse-glow 2s infinite;
        }

        .sidebar-toggle:hover {
            transform: scale(1.08);
            box-shadow: 0 8px 25px rgba(14, 165, 233, 0.5);
        }

        @media (max-width: 992px) {
            .sidebar-toggle {
                display: block;
            }
        }

        /* Page Content Animation */
        .page-content {
            animation: fadeInUp 0.7s ease-out both;
        }

        /* Stagger animation for cards */
        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.15s; }
        .card:nth-child(3) { animation-delay: 0.2s; }
        .card:nth-child(4) { animation-delay: 0.25s; }
        .card:nth-child(n+5) { animation-delay: 0.3s; }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
    </style>
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
