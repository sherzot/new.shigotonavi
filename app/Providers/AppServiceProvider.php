<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Auth\StaffCodeUserProvider;
use App\Auth\CustomCompanyUserProvider;
use App\Auth\CustomAgentUserProvider;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;
// use Illuminate\Filesystem\Filesystem;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Staff Code Provider
        Auth::provider('staff_code', function ($app, array $config) {
            return new StaffCodeUserProvider($config['model']);
        });

        // Custom Company Provider
        Auth::provider('custom_company', function ($app, array $config) {
            return new CustomCompanyUserProvider($config['model']);
        });
        Auth::provider('custom_agent', function ($app, array $config) {
            return new CustomAgentUserProvider($config['model']);
        });
        Paginator::useBootstrapFive();
        App::setLocale('ja');
    }
}
