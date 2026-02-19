<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel - ToyHaven')</title>
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

        .admin-sidebar {
            height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0e7490 100%);
            color: white;
            width: 260px;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            border-right: 3px solid #0891b2;
            animation: slideInLeft 0.5s ease-out;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .admin-sidebar h4 {
            font-weight: 600;
            font-size: 1.25rem;
            margin-bottom: 2px;
        }
        
        .admin-sidebar .sidebar-brand {
            padding: 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            flex-shrink: 0;
        }
        
        .admin-sidebar nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 20px 0;
        }
        
        /* Custom Scrollbar for Sidebar */
        .admin-sidebar nav::-webkit-scrollbar {
            width: 6px;
        }
        
        .admin-sidebar nav::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }
        
        .admin-sidebar nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            transition: background 0.3s ease;
        }
        
        .admin-sidebar nav::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        /* Firefox Scrollbar */
        .admin-sidebar nav {
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
            transition: transform 0.2s ease;
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

        .sidebar-link i {
            transition: transform 0.3s ease;
        }


        .admin-content {
            margin-left: 260px;
            padding: 24px;
            min-height: 100vh;
            background: #fefcf8;
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
        .admin-navbar {
            background: #fff;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            padding: 18px 25px;
            margin-bottom: 25px;
            border-radius: 16px;
            animation: slideDown 0.5s ease-out;
            border: 2px solid #e2e8f0;
            transition: box-shadow 0.3s ease, border-color 0.3s ease;
        }
        .admin-navbar:hover {
            box-shadow: 0 4px 20px rgba(8, 145, 178, 0.1);
            border-color: #e2e8f0;
        }
        
        .admin-navbar h5 {
            font-weight: 800;
            color: #0891b2;
            font-size: 1.25rem;
        }
        
        .admin-user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            background: #ecfeff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        
        .admin-user-info span:first-child {
            font-weight: 500;
            color: #475569;
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

        .card {
            border: 2px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border-radius: 16px;
            overflow: hidden;
            background: #fff;
            margin-bottom: 20px;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card:hover {
            box-shadow: 0 8px 24px rgba(8, 145, 178, 0.15);
            border-color: #0891b2;
            transform: translateY(-2px);
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-header {
            background: #fefcf8;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 700;
            padding: 16px 20px;
            font-size: 1rem;
            color: #1e293b;
        }
        
        .card-body {
            padding: 20px;
        }

        .card.text-white {
            border: none;
        }

        .card.text-white:hover {
            transform: translateY(-2px);
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
        
        .btn-outline-danger {
            border: 2px solid #ef4444;
            color: #ef4444;
            background: transparent;
        }
        
        .btn-outline-danger:hover {
            background: #ef4444;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* Tables */
        .table {
            animation: fadeIn 0.5s ease-in;
            margin-bottom: 0;
        }
        
        .table thead {
            background: #fefcf8;
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
            animation: bounceIn 0.5s ease-out;
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }


        /* Alerts */
        .alert {
            animation: slideInRight 0.5s ease-out;
            border-radius: 10px;
            border-left: 4px solid;
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

        /* Loading Spinner */
        .spinner-border {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Number Counter Animation */
        .counter {
            display: inline-block;
            transition: all 0.3s ease;
        }

        /* Form Elements */
        .form-control, .form-select {
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        .form-control:focus, .form-select:focus {
            transform: scale(1.02);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* Modal Animations */
        .modal.fade .modal-dialog {
            transition: transform 0.3s ease-out;
            transform: translate(0, -50px);
        }

        .modal.show .modal-dialog {
            transform: translate(0, 0);
        }

        /* Chart Container */
        canvas {
            animation: fadeIn 1s ease-in;
        }

        /* Icon Animations */
        .bi {
            transition: all 0.3s ease;
        }


        /* Smooth Scroll */
        html {
            scroll-behavior: smooth;
        }

        /* Page Content Animation - Auction style reveal */
        .page-content {
            animation: fadeInUp 0.6s ease-out;
        }
        .page-content .card {
            animation: fadeInUp 0.5s ease-out both;
        }
        .page-content .card:nth-child(1) { animation-delay: 0.05s; }
        .page-content .card:nth-child(2) { animation-delay: 0.1s; }
        .page-content .card:nth-child(3) { animation-delay: 0.15s; }
        .page-content .card:nth-child(4) { animation-delay: 0.2s; }
        .page-content .card:nth-child(5) { animation-delay: 0.25s; }
        .page-content .card:nth-child(6) { animation-delay: 0.3s; }

        .card.text-white.bg-primary { background: linear-gradient(135deg, #0891b2, #06b6d4); }
        .card.text-white.bg-success { background: #10b981; }
        .card.text-white.bg-info { background: #3b82f6; }
        .card.text-white.bg-warning { background: #f59e0b; }

        /* Pulse Animation for Important Elements */
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

        /* Shimmer Effect */
        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }
            100% {
                background-position: 1000px 0;
            }
        }

        .shimmer {
            background: linear-gradient(
                90deg,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.3) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            background-size: 1000px 100%;
            animation: shimmer 2s infinite;
        }

        /* Float Animation for Icons */
        @keyframes float {
            0%, 100% { 
                transform: translateY(0px) rotate(0deg); 
            }
            50% { 
                transform: translateY(-10px) rotate(5deg); 
            }
        }

        /* Badge Improvements */
        .badge {
            padding: 6px 12px;
            font-weight: 500;
            font-size: 0.8rem;
            border-radius: 6px;
        }
        
        /* Form Improvements */
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 10px 15px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #0891b2;
            box-shadow: 0 0 0 0.2rem rgba(8, 145, 178, 0.25);
        }
        
        /* Alert Improvements */
        .alert {
            border-radius: 12px;
            border-left-width: 5px;
            padding: 15px 20px;
            font-weight: 500;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .admin-sidebar.show {
                transform: translateX(0);
            }
            
            .admin-content {
                margin-left: 0;
                padding: 20px 15px;
            }
            
            .admin-navbar {
                padding: 15px;
            }
        }
        
        @media (max-width: 768px) {
            .admin-content {
                padding: 15px 10px;
            }
            
            .card-body {
                padding: 15px;
            }
            
            .admin-user-info {
                flex-direction: column;
                gap: 8px;
                padding: 10px;
            }
        }
        
        /* Mobile Menu Toggle - Auction color scheme */
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
            box-shadow: 0 4px 16px rgba(8, 145, 178, 0.35);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .sidebar-toggle:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(8, 145, 178, 0.45);
        }
        
        @media (max-width: 992px) {
            .sidebar-toggle {
                display: block;
            }
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
    <div class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('images/logo.png') }}" alt="ToyHaven" height="32" class="d-inline-block align-top me-2">
            <h4 class="mb-0 d-inline-block align-middle"><i class="bi bi-shield-check me-2"></i>Admin Panel</h4>
            <small class="text-white-50 d-block mt-1">ToyHaven Platform</small>
        </div>
        <nav>
            <div class="px-3 mb-2">
                <small class="text-white-50 text-uppercase">Management</small>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
            <a href="{{ route('admin.sellers.index') }}" class="sidebar-link {{ request()->routeIs('admin.sellers.*') ? 'active' : '' }}">
                <i class="bi bi-shop me-2"></i> Seller Management
            </a>
            <a href="{{ route('admin.business-page-revisions.index') }}" class="sidebar-link {{ request()->routeIs('admin.business-page-revisions.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-check me-2"></i> Business Page Approvals
            </a>
            <a href="{{ route('admin.products.index') }}" class="sidebar-link {{ request()->routeIs('admin.products.index') ? 'active' : '' }}">
                <i class="bi bi-box-seam me-2"></i> Product Moderation
            </a>
            <a href="{{ route('admin.products.pending') }}" class="sidebar-link {{ request()->routeIs('admin.products.pending') ? 'active' : '' }}">
                <i class="bi bi-hourglass-split me-2"></i> Products Requesting Approval
            </a>
            <a href="{{ route('admin.products.approved') }}" class="sidebar-link {{ request()->routeIs('admin.products.approved') ? 'active' : '' }}">
                <i class="bi bi-check2-square me-2"></i> Approved Products by Category
            </a>
            <a href="{{ route('admin.products.rejected') }}" class="sidebar-link {{ request()->routeIs('admin.products.rejected') ? 'active' : '' }}">
                <i class="bi bi-x-circle me-2"></i> Rejected Products
            </a>
            <a href="{{ route('admin.orders.index') }}" class="sidebar-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <i class="bi bi-cart-check me-2"></i> Orders
            </a>
            <a href="{{ route('admin.trades.listings') }}" class="sidebar-link {{ request()->routeIs('admin.trades.listings') && !request()->routeIs('admin.trades.listings.approved') && !request()->routeIs('admin.trades.listings.rejected') ? 'active' : '' }}">
                <i class="bi bi-arrow-left-right me-2"></i> Trade Listings
            </a>
            <a href="{{ route('admin.trades.listings.approved') }}" class="sidebar-link {{ request()->routeIs('admin.trades.listings.approved') ? 'active' : '' }}">
                <i class="bi bi-check2-square me-2"></i> Approved Trade Listings
            </a>
            <a href="{{ route('admin.trades.listings.rejected') }}" class="sidebar-link {{ request()->routeIs('admin.trades.listings.rejected') ? 'active' : '' }}">
                <i class="bi bi-x-circle me-2"></i> Rejected Trade Listings
            </a>
            <a href="{{ route('admin.trades.index') }}" class="sidebar-link {{ request()->routeIs('admin.trades.index') || request()->routeIs('admin.trades.show') ? 'active' : '' }}">
                <i class="bi bi-bag-check me-2"></i> Trades
            </a>
            <a href="{{ route('admin.auctions.index') }}" class="sidebar-link {{ request()->routeIs('admin.auctions.*') ? 'active' : '' }}">
                <i class="bi bi-hammer me-2"></i> Auctions
            </a>
            <a href="{{ route('admin.plans.index') }}" class="sidebar-link {{ request()->routeIs('admin.plans.*') ? 'active' : '' }}">
                <i class="bi bi-gem me-2"></i> Plans
            </a>
            <a href="{{ route('admin.subscriptions.index') }}" class="sidebar-link {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}">
                <i class="bi bi-credit-card me-2"></i> Subscriptions
            </a>
            <a href="{{ route('admin.reports.index') }}" class="sidebar-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <i class="bi bi-flag me-2"></i> Report Management
            </a>
            <a href="{{ route('admin.conversation-reports.index') }}" class="sidebar-link {{ request()->routeIs('admin.conversation-reports.*') ? 'active' : '' }}">
                <i class="bi bi-chat-dots me-2"></i> Conversation Reports
            </a>
            <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="bi bi-people me-2"></i> User Management
            </a>
            <a href="{{ route('admin.admins.index') }}" class="sidebar-link {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
                <i class="bi bi-shield-check me-2"></i> Admin Management
            </a>
            <a href="{{ route('admin.categories.index') }}" class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i class="bi bi-tags me-2"></i> Category Management
            </a>
            <hr class="text-white-50 my-3">
            <div class="px-3 mb-2">
                <small class="text-white-50 text-uppercase">Analytics & Settings</small>
            </div>
            <a href="{{ route('admin.analytics.index') }}" class="sidebar-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
                <i class="bi bi-graph-up me-2"></i> Analytics Dashboard
            </a>
            <a href="{{ route('admin.settings.index') }}" class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <i class="bi bi-gear me-2"></i> System Settings
            </a>
            <hr class="text-white-50 my-3">
            <div class="px-3">
                <small class="text-white-50">
                    <i class="bi bi-info-circle me-1"></i> Admin accounts are restricted to the Admin Panel only.
                </small>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="admin-content">
        <!-- Navbar -->
        <div class="admin-navbar">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">@yield('page-title', 'Admin Dashboard')</h5>
                </div>
                <div class="admin-user-info">
                    <div class="d-flex align-items-center gap-2">
                        <div class="text-end">
                            <div style="font-weight: 600; color: #0891b2;">{{ Auth::user()->name }}</div>
                            <small class="text-muted">Administrator</small>
                        </div>
                            <span class="badge bg-primary" style="font-size: 0.75rem;">Admin</span>
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

        <!-- Page Content -->
        <div class="page-content">
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
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

            // Add hover sound effect (optional - can be removed)
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(btn => {
                btn.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px) scale(1.05)';
                });
                btn.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Sidebar toggle for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            sidebar.classList.toggle('show');
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('adminSidebar');
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
