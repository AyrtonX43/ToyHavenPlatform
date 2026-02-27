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
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            color: white;
            width: 280px;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            border-right: 3px solid #0891b2;
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
            padding: 20px 20px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            flex-shrink: 0;
        }

        .admin-sidebar nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 12px 0;
        }

        .admin-sidebar nav::-webkit-scrollbar { width: 5px; }
        .admin-sidebar nav::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); border-radius: 10px; }
        .admin-sidebar nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }
        .admin-sidebar nav::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.3); }
        .admin-sidebar nav { scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.2) rgba(255,255,255,0.05); }

        /* ── Section labels ── */
        .sidebar-section-label {
            padding: 14px 20px 6px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: rgba(255,255,255,0.35);
        }

        /* ── Flat link (leaf) ── */
        .sidebar-link {
            color: rgba(255,255,255,0.8);
            padding: 9px 20px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
            font-weight: 500;
            font-size: 0.88rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-link i.menu-icon {
            width: 22px;
            font-size: 1rem;
            margin-right: 10px;
            flex-shrink: 0;
            text-align: center;
        }
        .sidebar-link:hover {
            background: rgba(8,145,178,0.15);
            color: #fff;
            border-left-color: #0891b2;
        }
        .sidebar-link.active {
            background: rgba(8,145,178,0.22);
            color: #fff;
            border-left-color: #0891b2;
            font-weight: 700;
        }

        /* ── Collapsible parent button ── */
        .sidebar-parent {
            width: 100%;
            background: none;
            border: none;
            color: rgba(255,255,255,0.8);
            padding: 9px 20px;
            display: flex;
            align-items: center;
            font-family: inherit;
            font-weight: 600;
            font-size: 0.88rem;
            cursor: pointer;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
            text-align: left;
        }
        .sidebar-parent i.menu-icon {
            width: 22px;
            font-size: 1rem;
            margin-right: 10px;
            flex-shrink: 0;
            text-align: center;
        }
        .sidebar-parent .chevron {
            margin-left: auto;
            font-size: 0.7rem;
            transition: transform 0.25s ease;
            opacity: 0.5;
        }
        .sidebar-parent:hover {
            background: rgba(8,145,178,0.1);
            color: #fff;
            border-left-color: rgba(8,145,178,0.4);
        }
        .sidebar-parent.active-section {
            color: #22d3ee;
            border-left-color: #0891b2;
        }
        .sidebar-parent[aria-expanded="true"] .chevron {
            transform: rotate(90deg);
        }

        /* ── Nested sub-menus ── */
        .sidebar-children {
            list-style: none;
            padding: 0;
            margin: 0;
            overflow: hidden;
            max-height: 0;
            transition: max-height 0.35s ease;
        }
        .sidebar-children.open {
            max-height: 2000px;
        }

        /* Level 1 children */
        .sidebar-children .sidebar-link {
            padding-left: 52px;
            font-size: 0.84rem;
            font-weight: 500;
        }
        .sidebar-children .sidebar-parent {
            padding-left: 52px;
            font-size: 0.84rem;
        }

        /* Level 2 children */
        .sidebar-children .sidebar-children .sidebar-link {
            padding-left: 68px;
            font-size: 0.82rem;
        }
        .sidebar-children .sidebar-children .sidebar-parent {
            padding-left: 68px;
            font-size: 0.82rem;
        }

        /* Level 3 children */
        .sidebar-children .sidebar-children .sidebar-children .sidebar-link {
            padding-left: 82px;
            font-size: 0.8rem;
        }
        .sidebar-children .sidebar-children .sidebar-children .sidebar-parent {
            padding-left: 82px;
            font-size: 0.8rem;
        }

        /* Level 4 children */
        .sidebar-children .sidebar-children .sidebar-children .sidebar-children .sidebar-link {
            padding-left: 96px;
            font-size: 0.78rem;
        }

        .admin-content {
            margin-left: 280px;
            padding: 24px;
            min-height: 100vh;
            background: linear-gradient(180deg, #f8fafc 0%, #f0fdfa 50%, #ecfeff 100%);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .admin-navbar {
            background: #fff;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            padding: 18px 25px;
            margin-bottom: 25px;
            border-radius: 16px;
            animation: slideDown 0.5s ease-out;
            border: 1px solid #e2e8f0;
        }
        .admin-navbar h5 { font-weight: 800; color: #0891b2; font-size: 1.25rem; }

        .admin-user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            background: #ecfeff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        .admin-user-info span:first-child { font-weight: 500; color: #475569; }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card {
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border-radius: 16px;
            overflow: hidden;
            background: #fff;
            margin-bottom: 20px;
            transition: box-shadow 0.2s, border-color 0.2s;
        }
        .card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.08); border-color: #22d3ee; }

        .card-header {
            background: #fefcf8;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 700;
            padding: 16px 20px;
            font-size: 1rem;
            color: #1e293b;
        }
        .card-body { padding: 20px; }
        .card.text-white { border: none; }
        .card.text-white:hover { transform: translateY(-2px); }

        .btn {
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            font-weight: 700;
            padding: 10px 20px;
            border: none;
        }
        .btn-primary { background: linear-gradient(135deg, #0891b2, #06b6d4); border: none; }
        .btn-primary:hover { background: linear-gradient(135deg, #0e7490, #0891b2); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(8,145,178,0.35); }
        .btn-sm { padding: 8px 16px; font-size: 0.875rem; }
        .btn-outline-danger { border: 2px solid #ef4444; color: #ef4444; background: transparent; }
        .btn-outline-danger:hover { background: #ef4444; color: white; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(239,68,68,0.3); }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.12); }
        .btn:active { transform: translateY(0); }

        .table { animation: fadeIn 0.5s ease-in; margin-bottom: 0; }
        .table thead { background: #fefcf8; border-bottom: 1px solid #e2e8f0; }
        .table thead th { font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; color: #1e293b; padding: 15px; }
        .table tbody tr { transition: all 0.3s ease; border-bottom: 1px solid #e2e8f0; }
        .table tbody td { padding: 15px; vertical-align: middle; }
        .table tbody tr:hover { background-color: #ecfeff; }

        .badge { transition: all 0.3s ease; padding: 6px 12px; font-weight: 500; font-size: 0.8rem; border-radius: 6px; }

        .alert { animation: slideInRight 0.5s ease-out; border-radius: 12px; border-left-width: 5px; padding: 15px 20px; font-weight: 500; }
        @keyframes slideInRight { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }

        .spinner-border { animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        .counter { display: inline-block; transition: all 0.3s ease; }

        .form-control, .form-select { border-radius: 10px; border: 2px solid #e9ecef; padding: 10px 15px; }
        .form-control:focus, .form-select:focus { border-color: #0891b2; box-shadow: 0 0 0 0.2rem rgba(8,145,178,0.2); }

        .modal.fade .modal-dialog { transition: transform 0.3s ease-out; transform: translate(0, -50px); }
        .modal.show .modal-dialog { transform: translate(0, 0); }

        canvas { animation: fadeIn 1s ease-in; }
        .bi { transition: all 0.3s ease; }
        html { scroll-behavior: smooth; }
        .page-content { animation: fadeInUp 0.6s ease-out both; }

        .card.text-white.bg-primary { background: linear-gradient(135deg, #0891b2, #06b6d4); }
        .card.text-white.bg-success { background: #10b981; }
        .card.text-white.bg-info { background: #3b82f6; }
        .card.text-white.bg-warning { background: #f59e0b; }

        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }
        .pulse { animation: pulse 2s infinite; }

        @keyframes shimmer { 0% { background-position: -1000px 0; } 100% { background-position: 1000px 0; } }
        .shimmer { background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%); background-size: 1000px 100%; animation: shimmer 2s infinite; }

        /* Responsive */
        @media (max-width: 992px) {
            .admin-sidebar { transform: translateX(-100%); transition: transform 0.3s ease; }
            .admin-sidebar.show { transform: translateX(0); }
            .admin-content { margin-left: 0; padding: 20px 15px; }
            .admin-navbar { padding: 15px; }
        }
        @media (max-width: 768px) {
            .admin-content { padding: 15px 10px; }
            .card-body { padding: 15px; }
            .admin-user-info { flex-direction: column; gap: 8px; padding: 10px; }
        }

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
        .sidebar-toggle:hover { transform: scale(1.05); box-shadow: 0 6px 20px rgba(8,145,178,0.5); }
        @media (max-width: 992px) { .sidebar-toggle { display: block; } }
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

            {{-- ═══════════════════════════════════════════
                 1. ANALYTICS DASHBOARD
            ═══════════════════════════════════════════ --}}
            <a href="{{ route('admin.analytics.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.analytics.*') || request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-graph-up menu-icon"></i> Analytics Dashboard
            </a>

            {{-- ═══════════════════════════════════════════
                 2. TOYSHOP MANAGEMENT
            ═══════════════════════════════════════════ --}}
            <div class="sidebar-section-label">Toyshop</div>

            <button class="sidebar-parent {{ request()->routeIs('admin.sellers.*') || request()->routeIs('admin.business-page-revisions.*') || request()->routeIs('admin.products.*') || request()->routeIs('admin.orders.*') ? 'active-section' : '' }}"
                    onclick="toggleMenu(this)"
                    aria-expanded="{{ request()->routeIs('admin.sellers.*') || request()->routeIs('admin.business-page-revisions.*') || request()->routeIs('admin.products.*') || request()->routeIs('admin.orders.*') ? 'true' : 'false' }}">
                <i class="bi bi-shop menu-icon"></i> Toyshop Management
                <i class="bi bi-chevron-right chevron"></i>
            </button>
            <ul class="sidebar-children {{ request()->routeIs('admin.sellers.*') || request()->routeIs('admin.business-page-revisions.*') || request()->routeIs('admin.products.*') || request()->routeIs('admin.orders.*') ? 'open' : '' }}">
                {{-- Seller Toyshop Management --}}
                <li>
                    <a href="{{ route('admin.sellers.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.sellers.*') || request()->routeIs('admin.business-page-revisions.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge menu-icon"></i> Seller Toyshop Management
                    </a>
                </li>
                {{-- Toyshop Product Management --}}
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

            {{-- ═══════════════════════════════════════════
                 3. TRADE MANAGEMENT
            ═══════════════════════════════════════════ --}}
            <div class="sidebar-section-label">Trading</div>

            <button class="sidebar-parent {{ request()->routeIs('admin.trades.*') ? 'active-section' : '' }}"
                    onclick="toggleMenu(this)"
                    aria-expanded="{{ request()->routeIs('admin.trades.*') ? 'true' : 'false' }}">
                <i class="bi bi-arrow-left-right menu-icon"></i> Trade Management
                <i class="bi bi-chevron-right chevron"></i>
            </button>
            <ul class="sidebar-children {{ request()->routeIs('admin.trades.*') ? 'open' : '' }}">
                {{-- Trade Product Management --}}
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
                {{-- Trade Listings --}}
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

            {{-- ═══════════════════════════════════════════
                 4. AUCTION MANAGEMENT
            ═══════════════════════════════════════════ --}}
            <div class="sidebar-section-label">Auction</div>

            <button class="sidebar-parent {{ request()->routeIs('admin.auctions.*') || request()->routeIs('admin.auction-verifications.*') || request()->routeIs('admin.plans.*') || request()->routeIs('admin.subscriptions.*') ? 'active-section' : '' }}"
                    onclick="toggleMenu(this)"
                    aria-expanded="{{ request()->routeIs('admin.auctions.*') || request()->routeIs('admin.auction-verifications.*') || request()->routeIs('admin.plans.*') || request()->routeIs('admin.subscriptions.*') ? 'true' : 'false' }}">
                <i class="bi bi-hammer menu-icon"></i> Auction Management
                <i class="bi bi-chevron-right chevron"></i>
            </button>
            <ul class="sidebar-children {{ request()->routeIs('admin.auctions.*') || request()->routeIs('admin.auction-verifications.*') || request()->routeIs('admin.plans.*') || request()->routeIs('admin.subscriptions.*') ? 'open' : '' }}">
                {{-- Auction Product Management --}}
                <li>
                    <button class="sidebar-parent {{ request()->routeIs('admin.auctions.*') || request()->routeIs('admin.auction-verifications.*') ? 'active-section' : '' }}"
                            onclick="toggleMenu(this)"
                            aria-expanded="{{ request()->routeIs('admin.auctions.*') || request()->routeIs('admin.auction-verifications.*') ? 'true' : 'false' }}">
                        <i class="bi bi-box-seam menu-icon"></i> Auction Product Management
                        <i class="bi bi-chevron-right chevron"></i>
                    </button>
                    <ul class="sidebar-children {{ request()->routeIs('admin.auctions.*') || request()->routeIs('admin.auction-verifications.*') ? 'open' : '' }}">
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
                        <li>
                            <a href="{{ route('admin.auction-verifications.index') }}"
                               class="sidebar-link {{ request()->routeIs('admin.auction-verifications.*') ? 'active' : '' }}">
                                <i class="bi bi-person-check menu-icon"></i> Seller Verification Requests
                            </a>
                        </li>
                    </ul>
                </li>
                {{-- Membership Management --}}
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

            {{-- ═══════════════════════════════════════════
                 5. ADMIN MANAGEMENT
            ═══════════════════════════════════════════ --}}
            <div class="sidebar-section-label">Administration</div>

            <button class="sidebar-parent {{ request()->routeIs('admin.categories.*') || request()->routeIs('admin.admins.*') || request()->routeIs('admin.reports.*') || request()->routeIs('admin.conversation-reports.*') || request()->routeIs('admin.users.*') || request()->routeIs('admin.settings.*') ? 'active-section' : '' }}"
                    onclick="toggleMenu(this)"
                    aria-expanded="{{ request()->routeIs('admin.categories.*') || request()->routeIs('admin.admins.*') || request()->routeIs('admin.reports.*') || request()->routeIs('admin.conversation-reports.*') || request()->routeIs('admin.users.*') || request()->routeIs('admin.settings.*') ? 'true' : 'false' }}">
                <i class="bi bi-shield-lock menu-icon"></i> Admin Management
                <i class="bi bi-chevron-right chevron"></i>
            </button>
            <ul class="sidebar-children {{ request()->routeIs('admin.categories.*') || request()->routeIs('admin.admins.*') || request()->routeIs('admin.reports.*') || request()->routeIs('admin.conversation-reports.*') || request()->routeIs('admin.users.*') || request()->routeIs('admin.settings.*') ? 'open' : '' }}">
                {{-- Toy Categories --}}
                <li>
                    <a href="{{ route('admin.categories.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <i class="bi bi-tags menu-icon"></i> Toy Categories Selections Edit
                    </a>
                </li>
                {{-- Admin Users --}}
                <li>
                    <a href="{{ route('admin.admins.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-check menu-icon"></i> Admin Users Management
                    </a>
                </li>
                {{-- Report Management --}}
                <li>
                    <button class="sidebar-parent {{ request()->routeIs('admin.reports.*') || request()->routeIs('admin.conversation-reports.*') || request()->routeIs('admin.users.*') ? 'active-section' : '' }}"
                            onclick="toggleMenu(this)"
                            aria-expanded="{{ request()->routeIs('admin.reports.*') || request()->routeIs('admin.conversation-reports.*') || request()->routeIs('admin.users.*') ? 'true' : 'false' }}">
                        <i class="bi bi-flag menu-icon"></i> Report Management
                        <i class="bi bi-chevron-right chevron"></i>
                    </button>
                    <ul class="sidebar-children {{ request()->routeIs('admin.reports.*') || request()->routeIs('admin.conversation-reports.*') || request()->routeIs('admin.users.*') ? 'open' : '' }}">
                        {{-- Toyshop Reports --}}
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
                        {{-- Trading Reports --}}
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
                        {{-- Auction Reports --}}
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
                {{-- System Settings --}}
                <li>
                    <a href="{{ route('admin.settings.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <i class="bi bi-gear menu-icon"></i> System Settings
                    </a>
                </li>
            </ul>

            <hr class="text-white-50 my-2 mx-3">
            <div class="px-3 pb-3">
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
        /* ── Sidebar collapse / expand ── */
        function toggleMenu(btn) {
            const list = btn.nextElementSibling;
            if (!list || !list.classList.contains('sidebar-children')) return;
            const isOpen = list.classList.contains('open');
            list.classList.toggle('open');
            btn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        }

        /* ── Mobile sidebar toggle ── */
        function toggleSidebar() {
            document.getElementById('adminSidebar').classList.toggle('show');
        }

        /* ── Number counter animation ── */
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

            document.querySelectorAll('.card').forEach((card, i) => { card.style.animationDelay = i * 0.1 + 's'; });

            document.querySelectorAll('.btn').forEach(btn => {
                btn.addEventListener('mouseenter', function() { this.style.transform = 'translateY(-2px) scale(1.05)'; });
                btn.addEventListener('mouseleave', function() { this.style.transform = 'translateY(0) scale(1)'; });
            });
        });

        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', function(e) {
                e.preventDefault();
                const t = document.querySelector(this.getAttribute('href'));
                if (t) t.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
