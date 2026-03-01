<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Seller Dashboard') - ToyHaven</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Tabler CSS -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet"/>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">
    
    <!-- Custom Animations -->
    <link href="{{ asset('css/toyhaven-animations.css') }}" rel="stylesheet"/>
    
    @stack('styles')
    
    <style>
        :root {
            --tblr-primary: #10b981;
            --tblr-primary-rgb: 16, 185, 129;
        }
        
        .page-wrapper {
            min-height: 100vh;
        }
        
        .navbar {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .navbar-brand-image {
            height: 2rem;
        }
        
        .navbar-nav .nav-link {
            transition: all 0.3s ease;
        }
        
        .navbar-nav .nav-link:hover {
            transform: translateY(-2px);
            color: var(--tblr-primary);
        }
        
        .card {
            border: none;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }
        
        .btn {
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .page-header {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 1rem 1rem;
            animation: slideInLeft 0.5s ease-out;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-left: 4px solid var(--tblr-primary);
        }
        
        .stat-card .stat-icon {
            width: 3rem;
            height: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            background: rgba(16, 185, 129, 0.1);
            color: var(--tblr-primary);
        }
        
        .dropdown-menu {
            animation: fadeIn 0.2s ease-out;
            border: none;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
        }
        
        .alert {
            animation: slideInRight 0.5s ease-out;
            border: none;
            border-left: 4px solid;
        }
        
        .table tbody tr {
            transition: all 0.3s ease;
        }
        
        .table tbody tr:hover {
            background-color: rgba(16, 185, 129, 0.05);
            transform: scale(1.005);
        }
        
        .badge {
            animation: bounceIn 0.5s ease-out;
        }
        
        @media (max-width: 768px) {
            .page-header {
                padding: 1rem 0;
            }
            
            .card:hover {
                transform: none;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Navbar -->
        <header class="navbar navbar-expand-md navbar-light d-print-none sticky-top">
            <div class="container-xl">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                    <a href="{{ route('seller.dashboard') }}">
                        <img src="{{ asset('images/logo.png') }}" alt="ToyHaven" class="navbar-brand-image animate-float">
                        <span class="ms-2 text-gradient-primary fw-bold">Seller Dashboard</span>
                    </a>
                </h1>
                <div class="navbar-nav flex-row order-md-last">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                            <span class="avatar avatar-sm" style="background-image: url(https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=10b981&color=fff)"></span>
                            <div class="d-none d-xl-block ps-2">
                                <div class="animate-fade-in">{{ Auth::user()->name }}</div>
                                <div class="mt-1 small text-muted">Seller</div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                <i class="ti ti-user me-2"></i>Profile
                            </a>
                            <a href="{{ route('toyshop.business.show', Auth::user()->seller->business_slug ?? '#') }}" class="dropdown-item" target="_blank">
                                <i class="ti ti-external-link me-2"></i>View Store
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="ti ti-logout me-2"></i>Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Navigation -->
        <header class="navbar-expand-md">
            <div class="collapse navbar-collapse" id="navbar-menu">
                <div class="navbar navbar-light">
                    <div class="container-xl">
                        <ul class="navbar-nav">
                            <li class="nav-item {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('seller.dashboard') }}">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <i class="ti ti-home"></i>
                                    </span>
                                    <span class="nav-link-title">Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item {{ request()->routeIs('seller.products.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('seller.products.index') }}">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <i class="ti ti-box"></i>
                                    </span>
                                    <span class="nav-link-title">Products</span>
                                </a>
                            </li>
                            <li class="nav-item {{ request()->routeIs('seller.orders.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('seller.orders.index') }}">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <i class="ti ti-shopping-cart"></i>
                                    </span>
                                    <span class="nav-link-title">Orders</span>
                                </a>
                            </li>
                            <li class="nav-item {{ request()->routeIs('seller.business-page.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('seller.business-page.index') }}">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <i class="ti ti-building-store"></i>
                                    </span>
                                    <span class="nav-link-title">Business Page</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>
        
        <div class="page-wrapper">
            <!-- Page Header -->
            @if(isset($pageTitle) || isset($breadcrumbs))
            <div class="page-header d-print-none">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            @if(isset($breadcrumbs))
                            <nav aria-label="breadcrumb" class="mb-2 animate-fade-in">
                                <ol class="breadcrumb">
                                    @foreach($breadcrumbs as $breadcrumb)
                                        @if($loop->last)
                                            <li class="breadcrumb-item active">{{ $breadcrumb['title'] }}</li>
                                        @else
                                            <li class="breadcrumb-item"><a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['title'] }}</a></li>
                                        @endif
                                    @endforeach
                                </ol>
                            </nav>
                            @endif
                            <h2 class="page-title animate-slide-in-left">
                                {{ $pageTitle ?? 'Dashboard' }}
                            </h2>
                        </div>
                        @if(isset($headerActions))
                        <div class="col-auto ms-auto d-print-none">
                            <div class="btn-list animate-slide-in-right">
                                {!! $headerActions !!}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Page Body -->
            <div class="page-body">
                <div class="container-xl">
                    <!-- Alerts -->
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible animate-slide-in-right" role="alert">
                        <div class="d-flex">
                            <div>
                                <i class="ti ti-check icon alert-icon"></i>
                            </div>
                            <div>
                                <h4 class="alert-title">Success!</h4>
                                <div class="text-muted">{{ session('success') }}</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif
                    
                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible animate-slide-in-right" role="alert">
                        <div class="d-flex">
                            <div>
                                <i class="ti ti-alert-circle icon alert-icon"></i>
                            </div>
                            <div>
                                <h4 class="alert-title">Error!</h4>
                                <div class="text-muted">{{ session('error') }}</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif
                    
                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible animate-slide-in-right" role="alert">
                        <div class="d-flex">
                            <div>
                                <i class="ti ti-alert-circle icon alert-icon"></i>
                            </div>
                            <div>
                                <h4 class="alert-title">Validation Error!</h4>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif
                    
                    <!-- Content -->
                    <div class="animate-fade-in">
                        @yield('content')
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <footer class="footer footer-transparent d-print-none mt-5">
                <div class="container-xl">
                    <div class="row text-center align-items-center flex-row-reverse">
                        <div class="col-lg-auto ms-lg-auto">
                            <ul class="list-inline list-inline-dots mb-0">
                                <li class="list-inline-item">
                                    <a href="#" class="link-secondary">Help Center</a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="#" class="link-secondary">Support</a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                            <ul class="list-inline list-inline-dots mb-0">
                                <li class="list-inline-item">
                                    Copyright &copy; {{ date('Y') }}
                                    <a href="#" class="link-secondary">ToyHaven</a>.
                                    All rights reserved.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>
    <script src="{{ asset('js/toyhaven-page-transitions.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
            
            const forms = document.querySelectorAll('form[data-loading="true"]');
            forms.forEach(function(form) {
                form.addEventListener('submit', function() {
                    const btn = form.querySelector('button[type="submit"]');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
                    }
                });
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>
