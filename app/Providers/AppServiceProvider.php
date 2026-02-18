<?php

namespace App\Providers;

use App\Services\MailConfigService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load mail configuration from database if system_settings table exists
        // This allows admin to configure SMTP settings via the admin panel
        try {
            if (Schema::hasTable('system_settings')) {
                MailConfigService::loadConfiguration();
            }
        } catch (\Exception $e) {
            // Silently fail if database is not ready yet (e.g., during migrations)
            // Will fall back to .env configuration
        }
    }
}
