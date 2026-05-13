<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\DomainService;

class DomainServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('domain-manager', function ($app) {
            return new DomainService();
        });

        $this->app->singleton(DomainService::class, function ($app) {
            return $app->make('domain-manager');
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
