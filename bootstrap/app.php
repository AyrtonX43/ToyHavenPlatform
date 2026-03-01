<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'webhooks/paymongo',
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'moderator' => \App\Http\Middleware\ModeratorMiddleware::class,
            'banned' => \App\Http\Middleware\CheckBannedUser::class,
            'redirect.admin.from.customer' => \App\Http\Middleware\RedirectAdminFromCustomerRoutes::class,
            'seller.approved' => \App\Http\Middleware\CheckSellerApproved::class,
            'membership' => \App\Http\Middleware\MembershipRequired::class,
        ]);
        
        // Apply banned check to all web routes (after authentication)
        $middleware->web(append: [
            \App\Http\Middleware\CheckBannedUser::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
