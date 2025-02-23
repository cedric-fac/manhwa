<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Register the review-ocr gate
        Gate::define('review-ocr', function (User $user) {
            return $user->is_admin;
        });

        // Register other OCR-related gates
        Gate::define('manage-ocr', function (User $user) {
            return $user->is_admin;
        });

        Gate::define('view-ocr-statistics', function (User $user) {
            return $user->is_admin;
        });

        Gate::define('train-ocr', function (User $user) {
            return $user->is_admin;
        });
    }
}