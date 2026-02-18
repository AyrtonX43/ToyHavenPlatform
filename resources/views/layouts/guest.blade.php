<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

        <!-- Bootstrap 5 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts - Toys and Joy style -->
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
        <!-- Custom CSS -->
        <link href="{{ asset('css/toyshop-animations.css') }}" rel="stylesheet">
        @stack('styles')
        <style>
            body {
                font-family: 'Quicksand', -apple-system, sans-serif;
            }
            .auth-container {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #ecfeff 0%, #f0fdfa 40%, #fef3c7 100%);
                position: relative;
            }
            .auth-card {
                background: #fff;
                border-radius: 20px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
                position: relative;
                z-index: 2;
                overflow: hidden;
                max-width: 100%;
                border: 1px solid #e2e8f0;
            }
            .auth-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 5px;
                background: linear-gradient(90deg, #0891b2 0%, #06b6d4 100%);
            }
            .auth-card .form-control {
                font-size: 1rem;
                padding: 0.75rem 1rem;
                min-height: 48px;
                border-radius: 12px;
                border: 2px solid #e2e8f0;
            }
            .auth-card .form-control:focus {
                border-color: #0891b2;
                box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.2);
            }
            .auth-card .form-label {
                font-size: 1rem;
                margin-bottom: 0.5rem;
                font-weight: 700;
                color: #1e293b;
            }
            .auth-card .btn {
                font-size: 1rem;
                padding: 0.75rem 1.5rem;
                min-height: 48px;
                border-radius: 12px;
                font-weight: 700;
            }
            .auth-card .btn-primary {
                background: linear-gradient(135deg, #0891b2, #06b6d4);
                border: none;
                color: #fff;
            }
            .auth-card .btn-primary:hover {
                background: linear-gradient(135deg, #0e7490, #0891b2);
                color: #fff;
                box-shadow: 0 6px 20px rgba(8, 145, 178, 0.35);
            }
            .auth-card .btn-primary:focus,
            .auth-card .btn-primary:focus-visible,
            .auth-card .btn-primary.focus {
                background: linear-gradient(135deg, #0e7490, #0891b2);
                border: none;
                color: #fff;
                box-shadow: 0 0 0 0.25rem rgba(8, 145, 178, 0.35);
            }
            .auth-card .btn-primary:active {
                background: linear-gradient(135deg, #0891b2, #06b6d4);
                color: #fff;
            }
            .auth-card .btn-outline-primary {
                border: 2px solid #0891b2;
                color: #0891b2;
                background: transparent;
            }
            .auth-card .btn-outline-primary:hover {
                background: #ecfeff;
                border-color: #0891b2;
                color: #0891b2;
            }
            .auth-card .btn-outline-secondary {
                background-color: transparent;
                border: 2px solid #e2e8f0;
                color: #64748b;
            }
            .auth-card .btn-outline-secondary:hover {
                background-color: #f8fafc;
                border-color: #cbd5e1;
                color: #1e293b;
            }
            .auth-card .btn-outline-secondary:focus,
            .auth-card .btn-outline-secondary:focus-visible,
            .auth-card .btn-outline-secondary.focus {
                background-color: #f8fafc;
                border-color: #cbd5e1;
                color: #1e293b;
                box-shadow: 0 0 0 0.25rem rgba(8, 145, 178, 0.15);
            }
            .auth-card .btn-outline-secondary:active {
                background-color: #e2e8f0;
                border-color: #cbd5e1;
                color: #1e293b;
            }
            .auth-card a.text-primary,
            .auth-card .text-primary {
                color: #0891b2 !important;
            }
            .auth-card a.text-primary:hover {
                color: #0e7490 !important;
            }
            .auth-card h3 {
                font-size: 1.75rem;
            }
            .auth-card p {
                font-size: 1rem;
            }
            .auth-card .form-feedback {
                display: block;
                width: 100%;
                margin-top: 0.5rem;
                font-size: 0.875rem;
                line-height: 1.5;
                min-height: 1.5rem;
            }
            .auth-card .form-feedback small {
                display: flex;
                align-items: flex-start;
                gap: 0.5rem;
            }
            .auth-card .form-feedback i {
                font-size: 0.875rem;
                margin-top: 0.125rem;
                flex-shrink: 0;
            }
            .auth-card .form-feedback span {
                flex: 1;
            }
            .auth-card .form-text {
                display: block;
                margin-top: 0.5rem;
                font-size: 0.875rem;
                color: #6c757d;
            }
            .auth-card .invalid-feedback {
                display: block !important;
                width: 100%;
                margin-top: 0.5rem;
                font-size: 0.875rem;
            }
            .auth-card .valid-feedback {
                display: block !important;
                width: 100%;
                margin-top: 0.5rem;
                font-size: 0.875rem;
            }
            .auth-card .password-strength-meter {
                margin-top: 0.5rem;
                clear: both;
            }
            .auth-card .password-strength-meter {
                margin-top: 0.5rem;
            }
            .auth-card #password-strength-text {
                display: block;
                margin-top: 0.5rem;
                margin-bottom: 0.5rem;
                min-height: 1.25rem;
            }
            .auth-card .password-requirements {
                margin-top: 0.75rem;
            }
            .auth-card .password-requirements ul {
                margin-top: 0.5rem;
            }
            .auth-card .password-requirements li {
                display: flex;
                align-items: flex-start;
                margin-bottom: 0.375rem;
                line-height: 1.4;
            }
            .auth-card .password-requirements li i {
                font-size: 0.75rem;
                width: 1rem;
                margin-top: 0.125rem;
                flex-shrink: 0;
            }
            .auth-card .password-requirements li span {
                flex: 1;
            }
            .auth-card .alert {
                border-radius: 12px;
                border: 2px solid transparent;
                font-weight: 600;
            }
            .auth-card .alert-success { border-color: #51cf66; background: #d3f9d8; }
            .auth-card .alert-danger { border-color: #ef4444; background: #fef2f2; }
            .auth-card .alert-info { border-color: #0891b2; background: #ecfeff; }
            .auth-card .card {
                border-radius: 16px;
                border: 1px solid #e2e8f0;
            }
            .auth-card .card.border-success { border-color: #10b981; background: #ecfdf5; }
            .auth-card .card.border-primary { border-color: #0891b2; background: #ecfeff; }
            .auth-card .position-absolute.bg-white {
                background: #fff !important;
            }
        </style>
    </head>
    <body>
        <div class="auth-container">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-11 col-sm-10 col-md-7 col-lg-6 col-xl-5">
                        <div class="auth-card p-4 p-md-5 p-lg-5 reveal">
                            <div class="text-center mb-4">
                                <a href="{{ route('home') }}" class="text-decoration-none d-inline-block">
                                    <img src="{{ asset('images/logo.png') }}" alt="ToyHaven" height="56" class="mb-2">
                                    <p class="fw-bold mb-0" style="color: #0891b2;">ToyHaven</p>
                                </a>
                                <p class="text-muted small">Welcome back!</p>
                            </div>
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap 5 JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Custom Animations JS -->
        <script src="{{ asset('js/toyshop-animations.js') }}"></script>
        @stack('scripts')
    </body>
</html>
