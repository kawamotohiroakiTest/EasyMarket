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
            \App\Services\easymarket\DealService\DealServiceInterface::class,
            \App\Services\easymarket\DealService\DealService::class
        );
        $this->app->bind(
            \App\Services\easymarket\ImageService\ImageServiceInterface::class,
            \App\Services\easymarket\ImageService\ImageService::class
        );
        $this->app->bind(
            \App\Services\easymarket\UserService\UserServiceInterface::class,
            \App\Services\easymarket\UserService\UserService::class
        );
        $this->app->bind(
            \App\Services\easymarket\ProductService\ProductServiceInterface::class,
            \App\Services\easymarket\ProductService\ProductService::class
        );
        // envがtestingか、services.stripe.secretが設定されていない場合はStripeServiceのモックを返す
        if (app()->environment('testing') || empty(config('services.stripe.secret'))) {
            $this->app->bind(
                \App\Services\easymarket\StripeService\StripeServiceInterface::class,
                \App\Services\easymarket\StripeService\StripeServiceMock::class
            );
        } else {
            $this->app->bind(
                \App\Services\easymarket\StripeService\StripeServiceInterface::class,
                \App\Services\easymarket\StripeService\StripeService::class
            );
        }        
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