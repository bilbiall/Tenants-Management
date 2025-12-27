<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Login;
use App\Helpers\ActivityLogger;
use App\Models\Tenant;
use App\Observers\TenantObserver;

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
        // Register Tenant observer
        Tenant::observe(TenantObserver::class);

        // Record user login events
        Event::listen(Login::class, function (Login $event) {
            try {
                ActivityLogger::log('login', $event->user->id ?? null, 'User logged in');
            } catch (\Throwable $e) {
                // avoid breaking boot if logging fails
            }
        });
    }
}
