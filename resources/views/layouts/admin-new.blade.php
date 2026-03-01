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
        .admin-sidebar {
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

        .admin-sidebar h4 {
            font-weight: 800;
            font-size: 1.3rem;
            margin-bottom: 3px;
            letter-spacing: -0.5px;
        }

        .admin-sidebar .sidebar-brand {
            padding: 24px 22px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            flex-shrink: 0;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.1) 0%, transparent 100%);
        }

        .admin-sidebar nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 16px 0;
        }

        .admin-sidebar nav::-webkit-scrollbar { width: 6px; }
        .admin-sidebar nav::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); border-radius: 10px; }
        .admin-sidebar nav::-webkit-scrollbar-thumb { background: rgba(14, 165, 233, 0.4); border-radius: 10px; transition: background 0.3s ease; }
        .admin-sidebar nav::-webkit-scrollbar-thumb:hover { background: rgba(14, 165, 233, 0.6); }
        .admin-sidebar nav { scrollbar-width: thin; scrollbar-color: rgba(14, 165, 233, 0.4) rgba(255,255,255,0.05); }

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

        .sidebar-link i.menu-icon {
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

        .sidebar-link:hover i.menu-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .sidebar-link.active {
            background: linear-gradient(90deg, rgba(14, 165, 233, 0.25), rgba(14, 165, 233, 0.1));
            color: #fff;
            border-left-color: var(--primary-sky-light);
            font-weight: 800;
            box-shadow: inset 0 0 20px rgba(14, 165, 233, 0.2);
        }

        .sidebar-parent {
            width: 100%;
            background: none;
            border: none;
            color: rgba(255,255,255,0.85);
            padding: 12px 22px;
            display: flex;
            align-items: center;
            font-family: inherit;
            font-weight: 700;
            font-size: 0.92rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-left: 4px solid transparent;
            text-align: left;
            position: relative;
            overflow: hidden;
        }

        .sidebar-parent::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, rgba(14, 165, 233, 0.1), transparent);
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .sidebar-parent i.menu-icon {
            width: 24px;
            font-size: 1.1rem;
            margin-right: 12px;
            flex-shrink: 0;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .sidebar-parent .chevron {
            margin-left: auto;
            font-size: 0.75rem;
            transition: transform 0.3s ease;
            opacity: 0.6;
        }

        .sidebar-parent:hover {
            background: rgba(14, 165, 233, 0.1);
            color: #fff;
            border-left-color: rgba(14, 165, 233, 0.5);
        }

        .sidebar-parent:hover::before {
            transform: translateX(0);
        }

        .sidebar-parent.active-section {
            color: var(--primary-sky-light);
            border-left-color: var(--primary-sky);
        }

        .sidebar-parent[aria-expanded="true"] .chevron {
            transform: rotate(90deg);
        }

        .sidebar-children {
            list-style: none;
            padding: 0;
            margin: 0;
            overflow: hidden;
            max-height: 0;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-children.open {
            max-height: 3000px;
        }

        .sidebar-children .sidebar-link {
            padding-left: 58px;
            font-size: 0.88rem;
            font-weight: 500;
        }

        .sidebar-children .sidebar-parent {
            padding-left: 58px;
            font-size: 0.88rem;
        }

        .sidebar-children .sidebar-children .sidebar-link {
            padding-left: 74px;
            font-size: 0.85rem;
        }

        .sidebar-children .sidebar-children .sidebar-parent {
            padding-left: 74px;
            font-size: 0.85rem;
        }

        .sidebar-children .sidebar-children .sidebar-children .sidebar-link {
            padding-left: 90px;
            font-size: 0.82rem;
        }

        .sidebar-children .sidebar-children .sidebar-children .sidebar-parent {
            padding-left: 90px;
            font-size: 0.82rem;
        }

        .sidebar-children .sidebar-children .sidebar-children .sidebar-children .sidebar-link {
            padding-left: 106px;
            font-size: 0.8rem;
        }

        /* Main Content */
        .admin-content {
            margin-left: 280px;
            padding: 28px;
            min-height: 100vh;
            animation: fadeIn 0.5s ease-in;
        }

        /* Navbar */
        .admin-navbar {
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

        .admin-navbar::before {
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

        .admin-navbar h5 {
            font-weight: 900;
            color: var(--primary-sky);
            font-size: 1.4rem;
            letter-spacing: -0.5px;
            margin: 0;
        }

        .admin-user-info {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 10px 18px;
            background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%);
            border-radius: 16px;
            border: 2px solid rgba(14, 165, 233, 0.2);
            transition: all 0.3s ease;
        }

        .admin-user-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.15);
        }

        .admin-user-info span:first-child {
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

        .card.text-white {
            border: none;
            position: relative;
            overflow: hidden;
        }

        .card.text-white::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }

        .card.text-white:hover {
            transform: translateY(-8px) scale(1.02);
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

        /* Stat Cards */
        .card.text-white.bg-primary {
            background: linear-gradient(135deg, var(--primary-sky), var(--primary-sky-light));
        }

        .card.text-white.bg-success {
            background: linear-gradient(135deg, #10b981, #34d399);
        }

        .card.text-white.bg-info {
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
        }

        .card.text-white.bg-warning {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
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
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .admin-sidebar.show {
                transform: translateX(0);
            }

            .admin-content {
                margin-left: 0;
                padding: 20px 16px;
            }

            .admin-navbar {
                padding: 16px 18px;
            }
        }

        @media (max-width: 768px) {
            .admin-content {
                padding: 16px 12px;
            }

            .card-body {
                padding: 18px;
            }

            .admin-user-info {
                flex-direction: column;
                gap: 10px;
                padding: 12px;
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
    <div class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('images/logo.png') }}" alt="ToyHaven" height="34" class="d-inline-block align-top me-2">
            <h4 class="mb-0 d-inline-block align-middle"><i class="bi bi-shield-check me-2"></i>Admin Panel</h4>
            <small class="text-white-50 d-block mt-1" style="font-size: 0.78rem;">ToyHaven Platform</small>
        </div>
        <nav>
            {{-- Analytics Dashboard --}}
            <a href="{{ route('admin.analytics.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.analytics.*') || request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-graph-up menu-icon"></i> Analytics Dashboard
            </a>

            {{-- Toyshop Management --}}
            <div class="sidebar-section-label">Toyshop</div>

            <button class="sidebar-parent {{ request()->routeIs('admin.sellers.*') || request()->routeIs('admin.business-page-revisions.*') || request()->routeIs('admin.products.*') || request()->routeIs('admin.orders.*') ? 'active-section' : '' }}"
                    onclick="toggleMenu(this)"
                    aria-expanded="{{ request()->routeIs('admin.sellers.*') || request()->routeIs('admin.business-page-revisions.*') || request()->routeIs('admin.products.*') || request()->routeIs('admin.orders.*') ? 'true' : 'false' }}">
                <i class="bi bi-shop menu-icon"></i> Toyshop Management
                <i class="bi bi-chevron-right chevron"></i>
            </button>
            <ul class="sidebar-children {{ request()->routeIs('admin.sellers.*') || request()->routeIs('admin.business-page-revisions.*') || request()->routeIs('admin.products.*') || request()->routeIs('admin.orders.*') ? 'open' : '' }}">
                <li>
                    <a href="{{ route('admin.sellers.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.sellers.*') || request()->routeIs('admin.business-page-revisions.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge menu-icon"></i> Seller Toyshop Management
                    </a>
                </li>
                <li>
                    <button class="sidebar-parent {{ request()->routeIs('admin.products.*') ? 'active-section' : '' }}"
                            onclick="toggleMenu(this)"
                            aria-expanded="{{ request()->routeIs('admin.products.*') ? 'true' : 'false' }}">
                        <i class="bi bi-box-seam menu-icon"></i> Toyshop Product Management
                        <i class="bi bi-chevron-right chevron"></i>
                    </button>
                    <ul class="sidebar-children {{ request()->routeIs('admin.products.*') ? 'open' : '' }}">
                        <li>
                            <a href="{{ route('admin.products.pending') }}"
                               class="sidebar-link {{ request()->routeIs('admin.products.pending') ? 'active' : '' }}">
                                <i class="bi bi-hourglass-split menu-icon"></i> Request Products
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.products.approved') }}"
                               class="sidebar-link {{ request()->routeIs('admin.products.approved') || request()->routeIs('admin.products.rejected') ? 'active' : '' }}">
                                <i class="bi bi-check2-square menu-icon"></i> Approved & Rejected Products
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>

            {{-- Trading Management --}}
            <div class="sidebar-section-label">Trading</div>

            <button class="sidebar-parent {{ request()->routeIs('admin.trades.*') ? 'active-section' : '' }}"
                    onclick="toggleMenu(this)"
                    aria-expanded="{{ request()->routeIs('admin.trades.*') ? 'true' : 'false' }}">
                <i class="bi bi-arrow-left-right menu-icon"></i> Trade Management
                <i class="bi bi-chevron-right chevron"></i>
            </button>
            <ul class="sidebar-children {{ request()->routeIs('admin.trades.*') ? 'open' : '' }}">
                <li>
                    <button class="sidebar-parent {{ request()->routeIs('admin.trades.listings*') ? 'active-section' : '' }}"
                            onclick="toggleMenu(this)"
                            aria-expanded="{{ request()->routeIs('admin.trades.listings*') ? 'true' : 'false' }}">
                        <i class="bi bi-box menu-icon"></i> Trade Product Management
                        <i class="bi bi-chevron-right chevron"></i>
                    </button>
                    <ul class="sidebar-children {{ request()->routeIs('admin.trades.listings*') ? 'open' : '' }}">
                        <li>
                            <a href="{{ route('admin.trades.listings') }}"
                               class="sidebar-link {{ request()->routeIs('admin.trades.listings') && !request()->routeIs('admin.trades.listings.*') ? 'active' : '' }}">
                                <i class="bi bi-send menu-icon"></i> Request Listings
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.trades.listings.approved') }}"
                               class="sidebar-link {{ request()->routeIs('admin.trades.listings.approved') || request()->routeIs('admin.trades.listings.rejected') ? 'active' : '' }}">
                                <i class="bi bi-check2-square menu-icon"></i> Approved & Rejected Listings
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <button class="sidebar-parent {{ request()->routeIs('admin.trades.index') || request()->routeIs('admin.trades.show') ? 'active-section' : '' }}"
                            onclick="toggleMenu(this)"
                            aria-expanded="{{ request()->routeIs('admin.trades.index') || request()->routeIs('admin.trades.show') ? 'true' : 'false' }}">
                        <i class="bi bi-list-ul menu-icon"></i> Trade Listings
                        <i class="bi bi-chevron-right chevron"></i>
                    </button>
                    <ul class="sidebar-children {{ request()->routeIs('admin.trades.index') || request()->routeIs('admin.trades.show') ? 'open' : '' }}">
                        <li>
                            <a href="{{ route('admin.trades.index') }}"
                               class="sidebar-link {{ request()->routeIs('admin.trades.index') || request()->routeIs('admin.trades.show') ? 'active' : '' }}">
                                <i class="bi bi-person menu-icon"></i> User Seller Trade Info
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>

            {{-- Auction Management --}}
            <div class="sidebar-section-label">Auction</div>

            <button class="sidebar-parent {{ request()->routeIs('admin.auctions.*') || request()->routeIs('admin.auction-verifications.*') || request()->routeIs('admin.auction-sellers.*') || request()->routeIs('admin.plans.*') || request()->routeIs('admin.subscriptions.*') ? 'active-section' : '' }}"
                    onclick="toggleMenu(this)"
                    aria-expanded="{{ request()->routeIs('admin.auctions.*') || request()->routeIs('admin.auction-verifications.*') || request()->routeIs('admin.auction-sellers.*') || request()->routeIs('admin.plans.*') || request()->routeIs('admin.subscriptions.*') ? 'true' : 'false' }}">
                <i class="bi bi-hammer menu-icon"></i> Auction Management
                <i class="bi bi-chevron-right chevron"></i>
            </button>
            <ul class="sidebar-children {{ request()->routeIs('admin.auctions.*') || request()->routeIs('admin.auction-verifications.*') || request()->routeIs('admin.auction-sellers.*') || request()->routeIs('admin.plans.*') || request()->routeIs('admin.subscriptions.*') ? 'open' : '' }}">
                <li>
                    <button class="sidebar-parent {{ request()->routeIs('admin.auction-sellers.*') || request()->routeIs('admin.auction-verifications.*') ? 'active-section' : '' }}"
                            onclick="toggleMenu(this)"
                            aria-expanded="{{ request()->routeIs('admin.auction-sellers.*') || request()->routeIs('admin.auction-verifications.*') ? 'true' : 'false' }}">
                        <i class="bi bi-people menu-icon"></i> Auction Seller Management
                        <i class="bi bi-chevron-right chevron"></i>
                    </button>
                    <ul class="sidebar-children {{ request()->routeIs('admin.auction-sellers.*') || request()->routeIs('admin.auction-verifications.*') ? 'open' : '' }}">
                        <li>
                            <a href="{{ route('admin.auction-sellers.index') }}"
                               class="sidebar-link {{ request()->routeIs('admin.auction-sellers.*') ? 'active' : '' }}">
                                <i class="bi bi-person-badge menu-icon"></i> Approved Auction Sellers
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.auction-verifications.index') }}"
                               class="sidebar-link {{ request()->routeIs('admin.auction-verifications.*') ? 'active' : '' }}">
                                <i class="bi bi-person-check menu-icon"></i> Seller Verification Requests
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <button class="sidebar-parent {{ request()->routeIs('admin.auctions.*') ? 'active-section' : '' }}"
                            onclick="toggleMenu(this)"
                            aria-expanded="{{ request()->routeIs('admin.auctions.*') ? 'true' : 'false' }}">
                        <i class="bi bi-box-seam menu-icon"></i> Auction Product Management
                        <i class="bi bi-chevron-right chevron"></i>
                    </button>
                    <ul class="sidebar-children {{ request()->routeIs('admin.auctions.*') ? 'open' : '' }}">
                        <li>
                            <a href="{{ route('admin.auctions.index') }}"
                               class="sidebar-link {{ request()->routeIs('admin.auctions.index') || request()->routeIs('admin.auctions.create') ? 'active' : '' }}">
                                <i class="bi bi-send menu-icon"></i> Auction Request Listing
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.auctions.index') }}?filter=live"
                               class="sidebar-link">
                                <i class="bi bi-broadcast menu-icon"></i> Live Bid & Results
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.auctions.index') }}?filter=reviewed"
                               class="sidebar-link">
                                <i class="bi bi-check2-square menu-icon"></i> Approved & Rejected Auction Products
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <button class="sidebar-parent {{ request()->routeIs('admin.plans.*') || request()->routeIs('admin.subscriptions.*') ? 'active-section' : '' }}"
                            onclick="toggleMenu(this)"
                            aria-expanded="{{ request()->routeIs('admin.plans.*') || request()->routeIs('admin.subscriptions.*') ? 'true' : 'false' }}">
                        <i class="bi bi-gem menu-icon"></i> Membership Management
                        <i class="bi bi-chevron-right chevron"></i>
                    </button>
                    <ul class="sidebar-children {{ request()->routeIs('admin.plans.*') || request()->routeIs('admin.subscriptions.*') ? 'open' : '' }}">
                        <li>
                            <a href="{{ route('admin.plans.index') }}"
                               class="sidebar-link {{ request()->routeIs('admin.plans.*') ? 'active' : '' }}">
                                <i class="bi bi-pencil-square menu-icon"></i> Membership Edit Plan
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.subscriptions.index') }}"
                               class="sidebar-link {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}">
                                <i class="bi bi-people menu-icon"></i> User Subscriptions by Member Badge
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>

            {{-- Admin Management --}}
            <div class="sidebar-section-label">Administration</div>

            <button class="sidebar-parent {{ request()->routeIs('admin.categories.*') || request()->routeIs('admin.admins.*') || request()->routeIs('admin.reports.*') || request()->routeIs('admin.conversation-reports.*') || request()->routeIs('admin.users.*') || request()->routeIs('admin.settings.*') ? 'active-section' : '' }}"
                    onclick="toggleMenu(this)"
                    aria-expanded="{{ request()->routeIs('admin.categories.*') || request()->routeIs('admin.admins.*') || request()->routeIs('admin.reports.*') || request()->routeIs('admin.conversation-reports.*') || request()->routeIs('admin.users.*') || request()->routeIs('admin.settings.*') ? 'true' : 'false' }}">
                <i class="bi bi-shield-lock menu-icon"></i> Admin Management
                <i class="bi bi-chevron-right chevron"></i>
            </button>
            <ul class="sidebar-children {{ request()->routeIs('admin.categories.*') || request()->routeIs('admin.admins.*') || request()->routeIs('admin.reports.*') || request()->routeIs('admin.conversation-reports.*') || request()->routeIs('admin.users.*') || request()->routeIs('admin.settings.*') ? 'open' : '' }}">
                <li>
                    <a href="{{ route('admin.categories.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <i class="bi bi-tags menu-icon"></i> Toy Categories Selections Edit
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.admins.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-check menu-icon"></i> Admin Users Management
                    </a>
                </li>
                <li>
                    <button class="sidebar-parent {{ request()->routeIs('admin.reports.*') || request()->routeIs('admin.conversation-reports.*') || request()->routeIs('admin.users.*') ? 'active-section' : '' }}"
                            onclick="toggleMenu(this)"
                            aria-expanded="{{ request()->routeIs('admin.reports.*') || request()->routeIs('admin.conversation-reports.*') || request()->routeIs('admin.users.*') ? 'true' : 'false' }}">
                        <i class="bi bi-flag menu-icon"></i> Report Management
                        <i class="bi bi-chevron-right chevron"></i>
                    </button>
                    <ul class="sidebar-children {{ request()->routeIs('admin.reports.*') || request()->routeIs('admin.conversation-reports.*') || request()->routeIs('admin.users.*') ? 'open' : '' }}">
                        <li>
                            <button class="sidebar-parent" onclick="toggleMenu(this)" aria-expanded="false">
                                <i class="bi bi-shop menu-icon"></i> Toyshop
                                <i class="bi bi-chevron-right chevron"></i>
                            </button>
                            <ul class="sidebar-children">
                                <li>
                                    <a href="{{ route('admin.business-page-revisions.index') }}"
                                       class="sidebar-link {{ request()->routeIs('admin.business-page-revisions.*') ? 'active' : '' }}">
                                        <i class="bi bi-building menu-icon"></i> Business Toyshop Page
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.products.index') }}"
                                       class="sidebar-link {{ request()->routeIs('admin.products.index') ? 'active' : '' }}">
                                        <i class="bi bi-box-seam menu-icon"></i> Products
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.users.index') }}"
                                       class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                        <i class="bi bi-people menu-icon"></i> Users (Customer & Seller)
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <button class="sidebar-parent" onclick="toggleMenu(this)" aria-expanded="false">
                                <i class="bi bi-arrow-left-right menu-icon"></i> Trading
                                <i class="bi bi-chevron-right chevron"></i>
                            </button>
                            <ul class="sidebar-children">
                                <li>
                                    <a href="{{ route('admin.conversation-reports.index') }}"
                                       class="sidebar-link {{ request()->routeIs('admin.conversation-reports.*') ? 'active' : '' }}">
                                        <i class="bi bi-chat-dots menu-icon"></i> Chat
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.trades.listings') }}"
                                       class="sidebar-link">
                                        <i class="bi bi-card-list menu-icon"></i> Products Listing
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.users.index') }}"
                                       class="sidebar-link">
                                        <i class="bi bi-people menu-icon"></i> Users (Customer & Seller)
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <button class="sidebar-parent" onclick="toggleMenu(this)" aria-expanded="false">
                                <i class="bi bi-hammer menu-icon"></i> Auction
                                <i class="bi bi-chevron-right chevron"></i>
                            </button>
                            <ul class="sidebar-children">
                                <li>
                                    <a href="{{ route('admin.auctions.index') }}"
                                       class="sidebar-link">
                                        <i class="bi bi-box menu-icon"></i> Auction Products
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.users.index') }}"
                                       class="sidebar-link">
                                        <i class="bi bi-people menu-icon"></i> Users (Individual & Business Seller, Customer)
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.conversation-reports.index') }}"
                                       class="sidebar-link">
                                        <i class="bi bi-chat-dots menu-icon"></i> Chat
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="{{ route('admin.settings.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <i class="bi bi-gear menu-icon"></i> System Settings
                    </a>
                </li>
            </ul>

            <hr class="text-white-50 my-3 mx-4">
            <div class="px-4 pb-3">
                <small class="text-white-50" style="font-size:0.72rem;">
                    <i class="bi bi-info-circle me-1"></i> Admin accounts are restricted to the Admin Panel only.
                </small>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="admin-content">
        <!-- Navbar -->
        <div class="admin-navbar">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="mb-0">@yield('page-title', 'Admin Dashboard')</h5>
                </div>
                <div class="admin-user-info">
                    <div class="d-flex align-items-center gap-2">
                        <div class="text-end">
                            <div style="font-weight: 700; color: var(--primary-sky);">{{ Auth::user()->name }}</div>
                            <small class="text-muted" style="font-size: 0.8rem;">Administrator</small>
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
        function toggleMenu(btn) {
            const list = btn.nextElementSibling;
            if (!list || !list.classList.contains('sidebar-children')) return;
            const isOpen = list.classList.contains('open');
            list.classList.toggle('open');
            btn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        }

        function toggleSidebar() {
            document.getElementById('adminSidebar').classList.toggle('show');
        }

        function animateCounter(el, target, isCurrency, duration) {
            duration = duration || 2000;
            let current = 0;
            const inc = target / (duration / 16);
            const fmt = v => isCurrency
                ? '₱' + v.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})
                : Math.floor(v).toLocaleString();
            const timer = setInterval(() => {
                current += inc;
                if (current >= target) { el.textContent = fmt(target); clearInterval(timer); }
                else { el.textContent = fmt(current); }
            }, 16);
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.counter-number').forEach(c => {
                const n = parseFloat(c.dataset.count);
                if (!isNaN(n) && n > 0) { c.textContent = '0'; setTimeout(() => animateCounter(c, n, false), 300); }
            });
            document.querySelectorAll('.counter-currency').forEach(c => {
                const n = parseFloat(c.dataset.count);
                if (!isNaN(n) && n >= 0) { c.textContent = '₱0.00'; setTimeout(() => animateCounter(c, n, true), 500); }
            });
        });

        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('adminSidebar');
            const toggle = document.querySelector('.sidebar-toggle');
            if (window.innerWidth <= 992 && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>
