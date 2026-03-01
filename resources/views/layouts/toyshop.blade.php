<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ToyHaven Platform')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts - Quicksand: Toys and Joy style (clean, playful) -->
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('css/toyshop-animations.css') }}" rel="stylesheet">
    @stack('styles')
    <style>
        /* Stable full-width layout: fill viewport, never overflow */
        html {
            box-sizing: border-box;
            width: 100%;
            overflow-x: hidden;
        }
        *, *::before, *::after { box-sizing: inherit; }
        body {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
            margin: 0;
        }
        /* Container fills viewport; responsive max-widths */
        body .container {
            width: 100%;
            max-width: 100%;
            padding-left: var(--bs-gutter-x, 0.75rem);
            padding-right: var(--bs-gutter-x, 0.75rem);
            margin-left: auto;
            margin-right: auto;
        }
        @media (min-width: 576px) {
            body .container { max-width: 540px; }
        }
        @media (min-width: 768px) {
            body .container { max-width: 720px; }
        }
        @media (min-width: 992px) {
            body .container { max-width: 960px; }
        }
        @media (min-width: 1200px) {
            body .container { max-width: 1140px; }
        }
        @media (min-width: 1400px) {
            body .container { max-width: 1320px; }
        }
        @media (min-width: 1600px) {
            body .container { max-width: 1440px; }
        }
        
        body {
            font-family: 'Quicksand', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Sky Blue theme top bar */
        .top-bar {
            background: linear-gradient(90deg, #0ea5e9 0%, #38bdf8 100%);
            color: #fff;
            font-size: 0.8125rem;
            padding: 0.4rem 0;
        }
        .top-bar a { color: #fff; text-decoration: none; opacity: 0.95; }
        .top-bar a:hover { color: #fff; opacity: 1; }
        
        .navbar {
            background: #fff !important;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            transition: box-shadow 0.2s ease;
        }
        
        .navbar.scrolled {
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            letter-spacing: -0.02em;
            color: #0ea5e9 !important;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .navbar-brand img {
            height: 40px;
            width: auto;
        }
        
        .nav-link {
            color: #334155 !important;
            font-weight: 600;
            font-size: 0.9375rem;
            padding: 0.5rem 0.85rem !important;
            border-radius: 20px;
            transition: color 0.2s, background 0.2s;
        }
        
        .nav-link:hover {
            color: #0ea5e9 !important;
            background: #e0f2fe;
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, #0ea5e9, #38bdf8);
            color: white !important;
        }
        
        .dropdown-menu {
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-radius: 14px;
            padding: 0.5rem;
        }
        
        .dropdown-item {
            border-radius: 10px;
            padding: 0.5rem 1rem;
            font-weight: 600;
            font-size: 0.9375rem;
            transition: background 0.15s;
        }
        
        .dropdown-item:hover {
            background: #ecfeff;
        }
        
        main {
            min-height: calc(100vh - 220px);
        }
        
        footer {
            background: linear-gradient(180deg, #0c4a6e 0%, #075985 100%) !important;
            color: rgba(255,255,255,0.92);
            padding: 3.5rem 0 1.5rem;
            margin-top: 4rem;
            border-top: 3px solid #0ea5e9;
        }
        
        footer h5, footer h6 {
            font-weight: 700;
            letter-spacing: -0.01em;
            color: #fff;
        }
        
        footer a {
            transition: opacity 0.2s ease;
        }
        
        footer a:hover {
            opacity: 1;
            color: #22d3ee !important;
        }
        
        .alert {
            border-radius: 8px;
            font-weight: 500;
        }
        
        .input-group .form-control {
            border-radius: 8px 0 0 8px;
        }
        
        .input-group .btn {
            border-radius: 0 8px 8px 0;
        }

        /* Search bar wrapper - consistent size for both guest and logged-in */
        .search-bar-wrapper {
            flex: 0 1 520px;
            min-width: 220px;
            max-width: 520px;
        }

        /* Search bar - prominent and usable */
        .search-input {
            background: #f8fafc;
            border: 2px solid #cbd5e1;
            border-right: none;
            padding: 0.75rem 1.125rem;
            font-size: 1rem;
            border-radius: 12px 0 0 12px;
            transition: border-color 0.2s, box-shadow 0.2s;
            min-height: 48px;
        }
        .search-input:focus {
            background: #fff;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15);
        }
        .search-input::placeholder {
            color: #94a3b8;
            font-size: 0.9375rem;
        }
        .search-btn {
            background: #0ea5e9;
            color: #fff;
            border: 2px solid #0ea5e9;
            border-radius: 0 12px 12px 0;
            padding: 0.75rem 1.25rem;
            min-height: 48px;
            font-size: 1.0625rem;
            transition: background 0.2s;
        }
        .search-btn:hover, .search-btn:focus {
            background: #0284c7;
            border-color: #0284c7;
            color: #fff;
        }

        /* Search suggestions dropdown */
        .search-suggest-dropdown {
            border-radius: 14px !important;
            min-width: 100%;
        }
        .search-suggest-section-label {
            padding: 0.5rem 1rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
        }
        .search-suggest-item {
            padding: 0.625rem 1rem !important;
            transition: background 0.15s !important;
        }
        .search-suggest-item:hover {
            background: #f0fdfa !important;
        }
        .search-suggest-item img {
            width: 52px !important;
            height: 52px !important;
            object-fit: cover;
            border-radius: 10px !important;
            border: 1px solid #e2e8f0;
            flex-shrink: 0;
        }
        .search-suggest-item .suggest-placeholder {
            width: 52px;
            height: 52px;
            background: #f1f5f9;
            border-radius: 10px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .search-suggest-item .suggest-name {
            font-weight: 600;
            font-size: 0.9375rem;
            color: #1e293b;
            line-height: 1.3;
        }
        .search-suggest-item .suggest-price {
            font-weight: 700;
            font-size: 0.875rem;
            color: #0ea5e9;
        }
        .search-suggest-item .suggest-store-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #e0f2fe, #f0f9ff);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.125rem;
            color: #0ea5e9;
        }
        .search-suggest-item .suggest-store-name {
            font-weight: 600;
            font-size: 0.9375rem;
            color: #1e293b;
        }
        .search-suggest-item .suggest-store-label {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        /* Navbar spacer */
        .navbar-spacer { min-height: 118px; }

        /* Navbar icon items - consistent sizing */
        .navbar-nav .nav-link .bi.fs-5 {
            font-size: 1.25rem !important;
        }

        /* ===== Responsive Breakpoints ===== */

        /* Extra large screens (1400px+) */
        @media (min-width: 1400px) {
            .search-bar-wrapper {
                flex: 0 1 620px;
                max-width: 620px;
            }
        }

        /* Large-XL screens (1200-1399px) */
        @media (min-width: 1200px) and (max-width: 1399px) {
            .search-bar-wrapper {
                flex: 0 1 520px;
                max-width: 520px;
            }
        }

        /* Large screens (992-1199px) - navbar is expanded */
        @media (min-width: 992px) and (max-width: 1199px) {
            .navbar-brand span { display: none; }
            .nav-link {
                font-size: 0.8125rem !important;
                padding: 0.4rem 0.5rem !important;
            }
            .search-bar-wrapper {
                flex: 0 1 360px;
                max-width: 360px;
                min-width: 200px;
            }
            .search-input {
                font-size: 0.875rem;
                padding: 0.5rem 0.75rem;
                min-height: 42px;
            }
            .search-btn {
                padding: 0.5rem 0.875rem;
                min-height: 42px;
                font-size: 0.9375rem;
            }
            .search-input::placeholder {
                font-size: 0.8125rem;
            }
        }

        /* Medium and below: navbar collapses */
        @media (max-width: 991px) {
            .navbar-spacer { min-height: 70px; }
            .navbar-brand { font-size: 1.35rem; }

            .navbar .container {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            /* Search bar goes full width in mobile menu */
            .search-bar-wrapper {
                flex: 1 1 100% !important;
                max-width: 100% !important;
                min-width: 100% !important;
                width: 100%;
                order: -1;
                margin-right: 0 !important;
                margin-bottom: 0.75rem;
            }
            .search-input {
                font-size: 1rem;
                padding: 0.75rem 1rem;
                min-height: 50px;
            }
            .search-btn {
                padding: 0.75rem 1.25rem;
                min-height: 50px;
                font-size: 1.125rem;
            }
            .search-input::placeholder {
                font-size: 0.9375rem;
            }
            .search-suggest-dropdown {
                max-height: 60vh !important;
                min-width: 100% !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            .search-suggest-item img {
                width: 48px !important;
                height: 48px !important;
            }

            /* Stack nav items nicely */
            .navbar-collapse {
                padding: 0.75rem 0;
                border-top: 1px solid #e2e8f0;
                margin-top: 0.5rem;
            }
            .navbar-nav {
                gap: 0.125rem;
            }
            .navbar-nav .nav-link {
                padding: 0.625rem 0.75rem !important;
                border-radius: 10px;
            }

            /* Icon row in mobile */
            .navbar-nav.align-items-center {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 0.25rem;
                padding-top: 0.5rem;
                border-top: 1px solid #f1f5f9;
                margin-top: 0.5rem;
            }
            .navbar-nav.align-items-center > .nav-item {
                flex-shrink: 0;
            }

            /* Notification dropdown - full width on mobile */
            .notification-dropdown {
                position: fixed !important;
                top: auto !important;
                left: 10px !important;
                right: 10px !important;
                width: auto !important;
                max-width: none !important;
                min-width: auto !important;
                max-height: 70vh !important;
                transform: none !important;
            }
            
            /* User dropdown */
            .dropdown-menu-end {
                min-width: 200px !important;
            }
        }

        /* Small screens (576-767px) */
        @media (max-width: 767px) {
            .navbar-spacer { min-height: 65px; }
            .navbar-brand { font-size: 1.25rem; }
            .navbar-brand img { height: 32px; }
            .navbar { padding: 0.375rem 0; }
            .top-bar { font-size: 0.75rem; padding: 0.3rem 0; }
        }

        /* Extra small screens (under 576px) */
        @media (max-width: 575px) {
            .navbar-spacer { min-height: 60px; }
            .navbar-brand { font-size: 1.125rem; gap: 0.35rem !important; }
            .navbar-brand img { height: 28px; }
            
            .container, .container-fluid {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
        }

        /* Very small screens (under 400px) */
        @media (max-width: 399px) {
            .navbar-brand span { font-size: 1rem; }
            .navbar-brand img { height: 26px; }
            .search-input { font-size: 0.875rem; padding: 0.625rem 0.75rem; }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    @php
        $currentRoute = request()->route() ? request()->route()->getName() : null;
        $hideNavbar = in_array($currentRoute, ['category-preferences.show', 'category-preferences.store']);
        $isCategorySelection = in_array($currentRoute, ['category-preferences.show', 'category-preferences.store']);
    @endphp
    @if(!$hideNavbar)
    <!-- ToyStore-style top bar -->
    <div class="top-bar d-none d-md-block">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex gap-4">
                    <a href="tel:+63271234567"><i class="bi bi-telephone me-1"></i>Call Us: +63 2 7123 4567</a>
                    <a href="mailto:hello@toyhaven.ph"><i class="bi bi-envelope me-1"></i>Email: hello@toyhaven.ph</a>
                </div>
                <span class="opacity-90">Your trusted toy & collectibles marketplace</span>
            </div>
        </div>
    </div>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                <img src="{{ asset('images/logo.png') }}" alt="ToyHaven" height="40" class="d-inline-block">
                <span>ToyHaven</span>
            </a>
            <button class="navbar-toggler border-2 border-dark" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('toyshop.*') ? 'active' : '' }}" href="{{ route('toyshop.products.index') }}">
                            <i class="bi bi-shop me-1"></i>Toyshop
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('trading.index') || request()->routeIs('trading.listings.*') ? 'active' : '' }}" href="{{ route('trading.index') }}">
                            <i class="bi bi-arrow-left-right me-1"></i>Trading
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('auctions.*') ? 'active' : '' }}" href="{{ route('auctions.index') }}">
                            <i class="bi bi-hammer me-1"></i>Auctions
                        </a>
                    </li>
                </ul>
                
                <!-- Search Bar -->
                @php
                    $isHomepage = request()->routeIs('home');
                    $isTrading = request()->routeIs('trading.*');
                    $isToyshop = request()->routeIs('toyshop.*');
                    // Use unified search (products + business pages + trade) everywhere except trading index
                    if ($isTrading) {
                        $searchAction = route('trading.index');
                        $searchPlaceholder = 'Search trade listings...';
                        $searchName = 'search';
                    } else {
                        $searchAction = route('search');
                        $searchPlaceholder = $isHomepage ? 'Search toyshop, trade, stores...' : 'Search toys, stores, collectibles...';
                        $searchName = 'q';
                    }
                @endphp
                <div class="search-bar-wrapper position-relative d-flex mb-2 mb-lg-0">
                    <form class="d-flex w-100" method="GET" action="{{ $searchAction }}" id="searchForm">
                        <div class="input-group shadow-sm position-relative">
                            <input class="form-control search-input" type="search" name="{{ $searchName }}" id="navbarSearchInput" placeholder="{{ $searchPlaceholder }}" value="{{ request($searchName) }}" autocomplete="off" aria-label="Search">
                            <button class="btn search-btn" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                            <div id="searchSuggestDropdown" class="search-suggest-dropdown position-absolute top-100 start-0 mt-1 bg-white rounded-3 shadow-lg border overflow-hidden" style="display: none; z-index: 1050; max-height: 520px; overflow-y: auto; min-width: 100%; width: max-content; max-width: min(680px, 95vw); right: 0;"></div>
                        </div>
                    </form>
                </div>
                
                <ul class="navbar-nav align-items-center">
                    @auth
                        <!-- Messages (Trade chat) -->
                        @php
                            $unreadChatCount = \App\Models\Message::whereHas('conversation', function($q) {
                                $q->where('user1_id', auth()->id())->orWhere('user2_id', auth()->id());
                            })->where('sender_id', '!=', auth()->id())->whereNull('seen_at')->count();
                        @endphp
                        <li class="nav-item me-2">
                            <a id="navMessagesLink" class="nav-link position-relative {{ request()->routeIs('trading.conversations.*') ? 'active' : '' }}" href="{{ route('trading.conversations.index') }}" title="Messages">
                                <i class="bi bi-chat-dots fs-5"></i>
                                <span id="chatUnreadBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; display: {{ $unreadChatCount > 0 ? 'inline-block' : 'none' }};">{{ $unreadChatCount > 99 ? '99+' : $unreadChatCount }}</span>
                            </a>
                        </li>
                        <!-- Notifications Dropdown -->
                        <li class="nav-item dropdown me-2">
                            <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                                <i class="bi bi-bell fs-5"></i>
                                @php
                                    $unreadCount = auth()->user()->unreadNotifications()->count();
                                    
                                    // Check for profile completion warnings
                                    $profileWarnings = [];
                                    $user = auth()->user()->load('addresses');
                                    
                                    // Check email verification
                                    if (!$user->hasVerifiedEmail()) {
                                        $profileWarnings[] = [
                                            'type' => 'email',
                                            'message' => 'Please verify your email address to access all features.',
                                            'link' => route('verification.notice'),
                                            'linkText' => 'Verify Email',
                                            'icon' => 'bi-envelope-exclamation',
                                            'color' => 'warning'
                                        ];
                                    }
                                    
                                    // Check for phone number
                                    if (empty($user->phone)) {
                                        $profileWarnings[] = [
                                            'type' => 'phone_missing',
                                            'message' => 'Please add your phone number in your profile.',
                                            'link' => route('profile.edit'),
                                            'linkText' => 'Add Phone',
                                            'icon' => 'bi-telephone',
                                            'color' => 'info'
                                        ];
                                    } elseif (empty($user->phone_verified_at)) {
                                        $profileWarnings[] = [
                                            'type' => 'phone',
                                            'message' => 'Please verify your phone number (PH-based) with OTP.',
                                            'link' => route('profile.edit'),
                                            'linkText' => 'Verify Phone',
                                            'icon' => 'bi-shield-check',
                                            'color' => 'warning'
                                        ];
                                    }
                                    
                                    // Check for address
                                    if ($user->addresses->count() === 0) {
                                        $profileWarnings[] = [
                                            'type' => 'address',
                                            'message' => 'Please add your permanent/work address in your profile.',
                                            'link' => route('profile.edit'),
                                            'linkText' => 'Add Address',
                                            'icon' => 'bi-geo-alt',
                                            'color' => 'info'
                                        ];
                                    }
                                    
                                    $totalNotificationCount = $unreadCount + count($profileWarnings);
                                @endphp
                                @if($totalNotificationCount > 0)
                                <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle notification-badge" style="font-size: 0.65rem; padding: 0.25em 0.5em; min-width: 18px;">{{ $totalNotificationCount > 99 ? '99+' : $totalNotificationCount }}</span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end notification-dropdown shadow-lg border-0" style="min-width: 350px; max-height: 500px; overflow-y: auto;" aria-labelledby="notificationDropdown">
                                <li>
                                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                                        <h6 class="mb-0 fw-bold">
                                            <i class="bi bi-bell me-2"></i>Notifications
                                        </h6>
                                        @if($unreadCount > 0 || count($profileWarnings) > 0)
                                            <a href="{{ route('notifications.index') }}" class="text-decoration-none small text-primary">View All</a>
                                        @endif
                                    </div>
                                </li>
                                
                                @if(count($profileWarnings) > 0)
                                <li>
                                    <div class="px-3 py-2 border-bottom bg-light">
                                        <small class="text-muted fw-semibold text-uppercase" style="font-size: 0.7rem;">
                                            <i class="bi bi-exclamation-circle me-1"></i>Profile Reminders
                                        </small>
                                    </div>
                                </li>
                                @foreach($profileWarnings as $warning)
                                <li>
                                    <a href="{{ $warning['link'] }}" class="dropdown-item notification-item-dropdown profile-warning unread">
                                        <div class="d-flex align-items-start">
                                            <div class="notification-icon-small bg-{{ $warning['color'] }} text-white me-2" style="width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0;">
                                                <i class="bi {{ $warning['icon'] }}"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold small">{{ $warning['message'] }}</div>
                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                    <span class="text-{{ $warning['color'] }}">{{ $warning['linkText'] }}</span>
                                                </div>
                                            </div>
                                            <span class="badge bg-{{ $warning['color'] }} rounded-pill ms-2" style="font-size: 0.6rem;">Action</span>
                                        </div>
                                    </a>
                                </li>
                                @endforeach
                                @if($unreadCount > 0 || auth()->user()->notifications()->count() > 0)
                                <li><hr class="dropdown-divider"></li>
                                @endif
                                @endif
                                
                                <li>
                                    <div id="notifications-list" class="notification-list">
                                        @php
                                            $recentNotifications = auth()->user()->notifications()->orderBy('created_at', 'desc')->limit(5)->get();
                                        @endphp
                                        @if($recentNotifications->count() > 0)
                                            @foreach($recentNotifications as $notification)
                                                @php
                                                    $data = $notification->data;
                                                    $type = $data['type'] ?? class_basename($notification->type);
                                                    $isUnread = is_null($notification->read_at);
                                                    
                                                    $icons = [
                                                        'order_status' => 'bi-box-seam',
                                                        'trade_offer_received' => 'bi-arrow-left-right',
                                                        'trade_offer_accepted' => 'bi-check-circle',
                                                        'trade_status_updated' => 'bi-arrow-repeat',
                                                        'seller_approved' => 'bi-shield-check',
                                                        'seller_rejected' => 'bi-x-circle',
                                                        'seller_suspended' => 'bi-exclamation-triangle',
                                                        'account_banned' => 'bi-ban',
                                                        'account_suspended' => 'bi-pause-circle',
                                                        'profile_update' => 'bi-person-check',
                                                        'auction_won' => 'bi-trophy',
                                                        'auction_outbid' => 'bi-hammer',
                                                    ];
                                                    
                                                    $colors = [
                                                        'order_status' => 'primary',
                                                        'trade_offer_received' => 'info',
                                                        'trade_offer_accepted' => 'success',
                                                        'trade_status_updated' => 'info',
                                                        'seller_approved' => 'success',
                                                        'seller_rejected' => 'danger',
                                                        'seller_suspended' => 'warning',
                                                        'account_banned' => 'danger',
                                                        'account_suspended' => 'warning',
                                                        'profile_update' => 'primary',
                                                        'auction_won' => 'success',
                                                        'auction_outbid' => 'warning',
                                                    ];
                                                    
                                                    $icon = $icons[$type] ?? 'bi-bell';
                                                    $color = $colors[$type] ?? 'secondary';
                                                    
                                                    $url = '#';
                                                    switch ($type) {
                                                        case 'order_status':
                                                            $url = isset($data['order_id']) ? route('orders.show', $data['order_id']) : route('orders.index');
                                                            break;
                                                        case 'trade_offer_received':
                                                        case 'trade_offer_accepted':
                                                            $url = isset($data['offer_id']) ? route('trading.offers.show', $data['offer_id']) : route('trading.offers.received');
                                                            break;
                                                        case 'trade_status_updated':
                                                            $url = isset($data['trade_id']) ? route('trading.trades.show', $data['trade_id']) : route('trading.trades.index');
                                                            break;
                                                        case 'seller_approved':
                                                        case 'seller_rejected':
                                                        case 'seller_suspended':
                                                            $url = route('seller.dashboard');
                                                            break;
                                                        case 'profile_update':
                                                            $url = route('profile.edit');
                                                            break;
                                                        case 'auction_won':
                                                        case 'auction_outbid':
                                                            $url = isset($data['auction_id']) ? route('auctions.show', $data['auction_id']) : route('auctions.index');
                                                            break;
                                                    }
                                                @endphp
                                                <a href="{{ $url }}" class="dropdown-item notification-item-dropdown {{ $isUnread ? 'unread' : '' }}" onclick="markNotificationAsRead('{{ $notification->id }}', event)">
                                                    <div class="d-flex align-items-start">
                                                        <div class="notification-icon-small bg-{{ $color }} text-white me-2" style="width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0;">
                                                            <i class="bi {{ $icon }}"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div class="fw-semibold small">{{ Str::limit($data['message'] ?? 'New notification', 50) }}</div>
                                                            <div class="text-muted" style="font-size: 0.75rem;">{{ $notification->created_at->diffForHumans() }}</div>
                                                        </div>
                                                        @if($isUnread)
                                                            <span class="badge bg-primary rounded-pill ms-2" style="font-size: 0.6rem;">New</span>
                                                        @endif
                                                    </div>
                                                </a>
                                            @endforeach
                                        @else
                                            <div class="dropdown-item text-center py-4">
                                                <i class="bi bi-bell-slash text-muted" style="font-size: 2rem;"></i>
                                                <p class="text-muted small mb-0 mt-2">No notifications</p>
                                            </div>
                                        @endif
                                    </div>
                                </li>
                                @if($recentNotifications->count() > 0)
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-center text-primary fw-semibold" href="{{ route('notifications.index') }}">
                                        <i class="bi bi-arrow-right-circle me-2"></i>View All Notifications
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li>
                        
                        <li class="nav-item d-none d-lg-block">
                            <a class="nav-link position-relative" href="{{ route('wishlist.index') }}" title="Wishlist">
                                <i class="bi bi-heart fs-5"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('cart.index') }}" title="Shopping Cart">
                                <i class="bi bi-cart3 fs-5"></i>
                            </a>
                        </li>
                        @if(!Auth::user()->isSeller())
                            <li class="nav-item d-none d-lg-block">
                                <a class="nav-link" href="{{ route('seller.register') }}">
                                    <i class="bi bi-person-badge me-1"></i><span class="d-none d-xl-inline">Become a Seller</span>
                                </a>
                            </li>
                        @endif
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-weight: 600; font-size: 0.875rem;">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <span class="d-none d-lg-inline">{{ Auth::user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="min-width: 220px;">
                                <li><h6 class="dropdown-header text-primary"><i class="bi bi-person-circle me-2"></i>My Account</h6></li>
                                <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                                <li><a class="dropdown-item" href="{{ route('orders.index') }}"><i class="bi bi-box-seam me-2"></i>My Orders</a></li>
                                <li><a class="dropdown-item" href="{{ route('wishlist.index') }}"><i class="bi bi-heart me-2"></i>My Wishlist</a></li>
                                <li><a class="dropdown-item" href="{{ route('notifications.index') }}"><i class="bi bi-bell me-2"></i>Notifications</a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profile Settings</a></li>
                                <li><a class="dropdown-item" href="{{ route('membership.index') }}"><i class="bi bi-gem me-2"></i>Membership</a></li>
                                @if(Auth::user()->isSeller() && Auth::user()->seller)
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header text-primary"><i class="bi bi-shop me-2"></i>Business</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('seller.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Seller Dashboard</a></li>
                                    @if(Auth::user()->seller->verification_status === 'approved')
                                        <li><a class="dropdown-item" href="{{ route('toyshop.business.show', Auth::user()->seller->business_slug) }}" target="_blank"><i class="bi bi-eye me-2"></i>View Business Page</a></li>
                                        <li><a class="dropdown-item" href="{{ route('seller.business-page.index') }}"><i class="bi bi-gear me-2"></i>Business Settings</a></li>
                                    @endif
                                @else
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('seller.register') }}"><i class="bi bi-person-badge me-2"></i>Become a Seller</a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn ms-2 px-3 rounded-pill" href="{{ route('register') }}" style="font-weight: 700; background: linear-gradient(135deg, #f97316, #fb923c); color: white !important; font-size: 0.9375rem; border: none;">Sign Up</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>
    <div class="navbar-spacer"></div>
    @endif

    <!-- Main Content -->
    @php
        $hideWarnings = in_array($currentRoute, ['category-preferences.show', 'category-preferences.store', 'suggested-products']);
    @endphp
    <main class="{{ $isCategorySelection ? 'p-0' : 'py-4' }}">
        <!-- Modern Flash Notifications -->
        <div class="flash-notifications-container" id="flashNotifications">
            @if(session('success'))
                <div class="flash-notification flash-success" data-auto-dismiss="5000">
                    <div class="flash-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="flash-content">
                        <div class="flash-title">Success</div>
                        <div class="flash-message">{{ session('success') }}</div>
                    </div>
                    <button type="button" class="flash-close" onclick="this.parentElement.remove()">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="flash-notification flash-error" data-auto-dismiss="7000">
                    <div class="flash-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div class="flash-content">
                        <div class="flash-title">Error</div>
                        <div class="flash-message">{{ session('error') }}</div>
                    </div>
                    <button type="button" class="flash-close" onclick="this.parentElement.remove()">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            @endif

            @if(session('info'))
                <div class="flash-notification flash-info" data-auto-dismiss="5000">
                    <div class="flash-icon">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>
                    <div class="flash-content">
                        <div class="flash-title">Information</div>
                        <div class="flash-message">{{ session('info') }}</div>
                    </div>
                    <button type="button" class="flash-close" onclick="this.parentElement.remove()">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            @endif

            @if(session('warning'))
                <div class="flash-notification flash-warning" data-auto-dismiss="6000">
                    <div class="flash-icon">
                        <i class="bi bi-exclamation-circle-fill"></i>
                    </div>
                    <div class="flash-content">
                        <div class="flash-title">Warning</div>
                        <div class="flash-message">{{ session('warning') }}</div>
                    </div>
                    <button type="button" class="flash-close" onclick="this.parentElement.remove()">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            @endif
        </div>


        @yield('content')
    </main>

    <!-- Footer -->
    @if(!$isCategorySelection)
    <footer class="bg-dark text-light mt-5 position-relative">
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-lg-4 mb-2 mb-lg-0">
                    <h5 class="mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-toy"></i> ToyHaven
                    </h5>
                    <p class="text-light opacity-85 small mb-3" style="max-width: 320px; line-height: 1.65;">Your trusted marketplace for toys and collectibles in the Philippines. Verified sellers, secure checkout.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light opacity-75" style="font-size: 1.15rem;" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-light opacity-75" style="font-size: 1.15rem;" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="text-light opacity-75" style="font-size: 1.15rem;" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>
                <div class="col-6 col-lg-2 col-md-4">
                    <h6 class="text-uppercase small fw-semibold mb-3 opacity-90">Shop</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="{{ route('toyshop.products.index') }}" class="text-light opacity-75 text-decoration-none">All Products</a></li>
                        <li class="mb-2"><a href="#" class="text-light opacity-75 text-decoration-none">New Arrivals</a></li>
                        <li class="mb-2"><a href="#" class="text-light opacity-75 text-decoration-none">Best Sellers</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2 col-md-4">
                    <h6 class="text-uppercase small fw-semibold mb-3 opacity-90">Account</h6>
                    <ul class="list-unstyled small">
                        @auth
                            <li class="mb-2"><a href="{{ route('dashboard') }}" class="text-light opacity-75 text-decoration-none">Dashboard</a></li>
                            <li class="mb-2"><a href="{{ route('orders.index') }}" class="text-light opacity-75 text-decoration-none">My Orders</a></li>
                            <li class="mb-2"><a href="{{ route('wishlist.index') }}" class="text-light opacity-75 text-decoration-none">Wishlist</a></li>
                            <li class="mb-2"><a href="{{ route('profile.edit') }}" class="text-light opacity-75 text-decoration-none">Profile</a></li>
                        @else
                            <li class="mb-2"><a href="{{ route('login') }}" class="text-light opacity-75 text-decoration-none">Login</a></li>
                            <li class="mb-2"><a href="{{ route('register') }}" class="text-light opacity-75 text-decoration-none">Register</a></li>
                        @endauth
                    </ul>
                </div>
                <div class="col-6 col-lg-2 col-md-4">
                    <h6 class="text-uppercase small fw-semibold mb-3 opacity-90">Support</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="#" class="text-light opacity-75 text-decoration-none">Help Center</a></li>
                        <li class="mb-2"><a href="#" class="text-light opacity-75 text-decoration-none">Contact</a></li>
                        <li class="mb-2"><a href="#" class="text-light opacity-75 text-decoration-none">Shipping</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2 col-md-4">
                    <h6 class="text-uppercase small fw-semibold mb-3 opacity-90">Legal</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="#" class="text-light opacity-75 text-decoration-none">Privacy</a></li>
                        <li class="mb-2"><a href="#" class="text-light opacity-75 text-decoration-none">Terms</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 border-secondary opacity-25">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                    <p class="mb-0 small text-light opacity-75">&copy; {{ date('Y') }} ToyHaven. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0 small text-light opacity-75">Made with <i class="bi bi-heart-fill text-danger"></i> in the Philippines</p>
                </div>
            </div>
        </div>
    </footer>
    @endif

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Animations JS -->
    <script src="{{ asset('js/toyshop-animations.js') }}"></script>
    <style>
        /* Flash Notifications */
        .flash-notifications-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            max-width: 400px;
        }
        
        .flash-notification {
            background: white;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            animation: slideInRight 0.3s ease-out;
            border-left: 4px solid;
            position: relative;
            overflow: hidden;
        }
        
        .flash-notification::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            height: 4px;
            background: currentColor;
            animation: shrinkWidth linear;
        }
        
        .flash-notification[data-auto-dismiss]::before {
            animation-duration: var(--dismiss-time, 5s);
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes shrinkWidth {
            from { width: 100%; }
            to { width: 0%; }
        }
        
        .flash-success {
            border-left-color: #10b981;
            color: #10b981;
        }
        
        .flash-error {
            border-left-color: #ef4444;
            color: #ef4444;
        }
        
        .flash-info {
            border-left-color: #3b82f6;
            color: #3b82f6;
        }
        
        .flash-warning {
            border-left-color: #f59e0b;
            color: #f59e0b;
        }
        
        .flash-icon {
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        
        .flash-content {
            flex: 1;
        }
        
        .flash-title {
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }
        
        .flash-message {
            font-size: 0.875rem;
            color: #64748b;
        }
        
        .flash-close {
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }
        
        .flash-close:hover {
            background: rgba(0,0,0,0.05);
            color: #64748b;
        }
        
        /* Notification Dropdown */
        .notification-dropdown {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .notification-item-dropdown {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.2s ease;
            text-decoration: none;
            color: inherit;
        }
        
        .notification-item-dropdown:hover {
            background: #f8fafc;
        }
        
        .notification-item-dropdown.unread {
            background: #f0f4ff;
        }
        
        .notification-item-dropdown.profile-warning {
            background: #fffbf0;
            border-left: 3px solid #f59e0b;
        }
        
        .notification-item-dropdown.profile-warning:hover {
            background: #fff7e6;
        }
        
        .notification-icon-small {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        @media (max-width: 768px) {
            .flash-notifications-container {
                right: 10px;
                left: 10px;
                max-width: none;
                top: 70px;
            }
            .flash-notification {
                padding: 0.75rem 1rem;
            }
            .flash-icon { font-size: 1.25rem; }
            .flash-title { font-size: 0.8125rem; }
            .flash-message { font-size: 0.8125rem; }
        }
        @media (max-width: 575px) {
            .flash-notifications-container {
                right: 8px;
                left: 8px;
                top: 65px;
            }
        }
    </style>
    
    <script>
        // Fix foggy/blurred UI and white fade when returning to page via browser back (bfcache)
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
                document.body.style.opacity = '';
                document.body.style.transition = '';
                document.documentElement.style.overflow = '';
                var viewer = document.getElementById('fullscreenViewer');
                if (viewer) {
                    viewer.classList.remove('active');
                    viewer.style.cssText = 'display: none !important;';
                }
            }
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('mainNavbar');
            if (navbar) {
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            }
        });
        
        // Scroll reveal animation
        const reveals = document.querySelectorAll('.reveal');
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, { threshold: 0.1 });
        
        reveals.forEach(reveal => revealObserver.observe(reveal));
        
        // Flash notifications auto-dismiss
        document.addEventListener('DOMContentLoaded', function() {
            const notifications = document.querySelectorAll('.flash-notification[data-auto-dismiss]');
            notifications.forEach(notification => {
                const dismissTime = parseInt(notification.getAttribute('data-auto-dismiss'));
                notification.style.setProperty('--dismiss-time', dismissTime + 'ms');
                
                setTimeout(() => {
                    notification.style.animation = 'slideOutRight 0.3s ease-out';
                    setTimeout(() => notification.remove(), 300);
                }, dismissTime);
            });
        });
        
        // Show flash notification dynamically (e.g. after AJAX delete)
        function showFlashNotification(message, type) {
            type = type || 'success';
            const titles = { success: 'Success', error: 'Error', info: 'Information', warning: 'Warning' };
            const icons = {
                success: 'bi-check-circle-fill',
                error: 'bi-exclamation-triangle-fill',
                info: 'bi-info-circle-fill',
                warning: 'bi-exclamation-circle-fill'
            };
            const container = document.getElementById('flashNotifications');
            if (!container) return;
            const div = document.createElement('div');
            div.className = 'flash-notification flash-' + type;
            div.setAttribute('data-auto-dismiss', '5000');
            div.innerHTML = '<div class="flash-icon"><i class="bi ' + icons[type] + '"></i></div>' +
                '<div class="flash-content"><div class="flash-title">' + titles[type] + '</div><div class="flash-message">' + message + '</div></div>' +
                '<button type="button" class="flash-close" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>';
            container.appendChild(div);
            div.style.setProperty('--dismiss-time', '5000ms');
            setTimeout(() => {
                div.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => div.remove(), 300);
            }, 5000);
        }
        
        // Notification functions
        function markNotificationAsRead(notificationId, event) {
            event.preventDefault();
            const url = event.currentTarget.getAttribute('href');
            
            fetch(`/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(() => {
                // Update UI
                const item = event.currentTarget;
                item.classList.remove('unread');
                const badge = item.querySelector('.badge');
                if (badge) badge.remove();
                
                // Update count
                updateNotificationCount();
                
                // Navigate to URL
                if (url && url !== '#') {
                    window.location.href = url;
                }
            });
        }
        
        function updateNotificationCount() {
            fetch('/notifications/unread-count')
                .then(response => response.json())
                .then(data => {
                    const totalCount = data.totalCount || (data.count + (data.profileWarningsCount || 0));
                    
                    const badge = document.querySelector('.notification-badge');
                    if (badge) {
                        if (totalCount > 0) {
                            badge.textContent = totalCount > 99 ? '99+' : totalCount;
                            badge.style.display = 'block';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                });
        }
        
        // Update notification count periodically
        @auth
        setInterval(updateNotificationCount, 30000); // Every 30 seconds
        @endauth

        // Real-time search suggest (products + business pages)
        (function() {
            const input = document.getElementById('navbarSearchInput');
            const dropdown = document.getElementById('searchSuggestDropdown');
            const form = document.getElementById('searchForm');
            if (!input || !dropdown) return;
            let suggestTimeout = null;
            const suggestUrl = '{{ route("search.suggest") }}';
            input.addEventListener('input', function() {
                const q = (this.value || '').trim();
                clearTimeout(suggestTimeout);
                if (q.length < 2) {
                    dropdown.style.display = 'none';
                    dropdown.innerHTML = '';
                    return;
                }
                suggestTimeout = setTimeout(function() {
                    fetch(suggestUrl + '?q=' + encodeURIComponent(q), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    }).then(function(r) { return r.json(); }).then(function(data) {
                        const products = data.products || [];
                        const businesses = data.businesses || [];
                        let html = '';
                        if (products.length || businesses.length) {
                            if (products.length) {
                                html += '<div class="search-suggest-section-label"><i class="bi bi-box-seam me-1"></i>Products</div>';
                                products.forEach(function(p) {
                                    html += '<a href="' + (p.url || '#') + '" class="search-suggest-item d-flex align-items-center gap-3 text-decoration-none text-dark border-bottom">';
                                    if (p.image) html += '<img src="' + p.image + '" alt="">';
                                    else html += '<div class="suggest-placeholder"><i class="bi bi-image text-muted"></i></div>';
                                    html += '<div class="flex-grow-1 min-w-0">';
                                    html += '<div class="suggest-name text-truncate">' + (p.name || '') + '</div>';
                                    html += '<div class="suggest-price">₱' + (parseFloat(p.price).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})) + '</div>';
                                    html += '</div>';
                                    html += '<i class="bi bi-chevron-right text-muted" style="flex-shrink:0;font-size:0.75rem;"></i>';
                                    html += '</a>';
                                });
                            }
                            if (businesses.length) {
                                html += '<div class="search-suggest-section-label"><i class="bi bi-shop me-1"></i>Stores &amp; Profiles</div>';
                                businesses.forEach(function(b) {
                                    html += '<a href="' + (b.url || '#') + '" class="search-suggest-item d-flex align-items-center gap-3 text-decoration-none text-dark border-bottom">';
                                    html += '<div class="suggest-store-icon"><i class="bi bi-shop"></i></div>';
                                    html += '<div class="flex-grow-1 min-w-0">';
                                    html += '<div class="suggest-store-name text-truncate">' + (b.name || '') + '</div>';
                                    html += '<div class="suggest-store-label">Verified Store</div>';
                                    html += '</div>';
                                    html += '<i class="bi bi-chevron-right text-muted" style="flex-shrink:0;font-size:0.75rem;"></i>';
                                    html += '</a>';
                                });
                            }
                        } else {
                            html = '<div class="px-3 py-4 text-muted text-center"><i class="bi bi-search" style="font-size:1.5rem;display:block;margin-bottom:0.5rem;opacity:0.4;"></i>No results found. Try different keywords.</div>';
                        }
                        dropdown.innerHTML = html;
                        dropdown.style.display = 'block';
                        dropdown.querySelectorAll('.search-suggest-item').forEach(function(el) {
                            el.addEventListener('click', function(e) { dropdown.style.display = 'none'; });
                        });
                    }).catch(function() {
                        dropdown.style.display = 'none';
                    });
                }, 250);
            });
            input.addEventListener('focus', function() {
                if (dropdown.innerHTML) dropdown.style.display = 'block';
            });
            input.addEventListener('blur', function() {
                setTimeout(function() {
                    const active = document.querySelector('.search-suggest-dropdown:hover, .search-suggest-item:hover');
                    if (!active) dropdown.style.display = 'none';
                }, 150);
            });
            dropdown.addEventListener('mouseenter', function() { this._hover = true; });
            dropdown.addEventListener('mouseleave', function() { this._hover = false; });
            document.addEventListener('click', function(e) {
                if (!input.contains(e.target) && !dropdown.contains(e.target)) dropdown.style.display = 'none';
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') { dropdown.style.display = 'none'; input.blur(); }
            });
        })();
    </script>
    @auth
    <!-- Toast container for chat notifications -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
        <div id="chatToastContainer"></div>
    </div>
    <script>
    (function() {
        var unreadUrl = '{{ route("trading.conversations.unread") }}';
        var badgeEl = document.getElementById('chatUnreadBadge');
        var lastCount = {{ $unreadChatCount ?? 0 }};
        var currentConversationId = {{ (request()->routeIs('trading.conversations.show') && isset($conversation)) ? $conversation->id : 'null' }};
        function updateBadge(count) {
            if (!badgeEl) return;
            if (count > 0) {
                badgeEl.textContent = count > 99 ? '99+' : count;
                badgeEl.style.display = 'inline-block';
            } else {
                badgeEl.style.display = 'none';
            }
        }
        function showChatToast(message, link) {
            var container = document.getElementById('chatToastContainer');
            if (!container) return;
            var id = 'toast-' + Date.now();
            var toastHtml = '<div id="' + id + '" class="toast align-items-center text-bg-primary border-0" role="alert"><div class="d-flex"><div class="toast-body"><i class="bi bi-chat-dots-fill me-2"></i>' + (message || 'New message') + '</div>' + (link ? '<a href="' + link + '" class="btn btn-sm btn-light m-2">View</a>' : '') + '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>';
            container.insertAdjacentHTML('beforeend', toastHtml);
            var toastEl = document.getElementById(id);
            if (toastEl && typeof bootstrap !== 'undefined') {
                var t = new bootstrap.Toast(toastEl, { delay: 5000 });
                t.show();
                toastEl.addEventListener('hidden.bs.toast', function() { toastEl.remove(); });
            }
        }
        function pollUnread() {
            if (document.hidden) return;
            fetch(unreadUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.ok ? r.json() : null; })
                .then(function(data) {
                    if (!data) return;
                    var count = data.count || 0;
                    if (count > lastCount && lastCount >= 0) {
                        var convos = data.conversations || [];
                        var onActiveChat = currentConversationId && convos.some(function(c) { return c.id === currentConversationId; });
                        if (!onActiveChat && convos.length > 0) {
                            var first = convos[0];
                            var msg = first.other_name ? 'New message from ' + first.other_name : 'You have new messages';
                            var link = '/trading/conversations/' + first.id;
                            showChatToast(msg, link);
                        }
                    }
                    lastCount = count;
                    updateBadge(count);
                })
                .catch(function() {});
        }
        setInterval(pollUnread, 3000);
        setTimeout(pollUnread, 1000);
    })();
    </script>
    @endauth
    @stack('scripts')
</body>
</html>
