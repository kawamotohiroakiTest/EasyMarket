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
        $this->app->bind(
            \App\Services\easymarket\ImageService\ImageServiceInterface::class,
            \App\Services\easymarket\ImageService\ImageService::class
        );
        $this->app->bind(
            \App\Services\easymarket\UserService\UserServiceInterface::class,
            \App\Services\easymarket\UserService\UserService::class
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