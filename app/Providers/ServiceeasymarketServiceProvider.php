<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ServiceeasymarketServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \App\Services\easymarket\AuthService\AuthServiceInterface::class,
            \App\Services\easymarket\AuthService\AuthService::class
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}