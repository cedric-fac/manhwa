<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

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
        Schema::defaultStringLength(191);

        // Prevent lazy loading in development
        Model::preventLazyLoading(!$this->app->isProduction());

        // Force strict mode in development
        Model::shouldBeStrict(!$this->app->isProduction());
    }
}
