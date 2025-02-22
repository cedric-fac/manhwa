<?php

namespace App\Providers;

use App\Support\OcrManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class OcrServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('ocr', function ($app) {
            return new OcrManager();
        });

        $this->mergeConfigFrom(
            __DIR__.'/../../config/ocr.php', 'ocr'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register OCR-related gates
        Gate::define('review-ocr', function ($user) {
            return $user->canReviewOcr();
        });

        // Publish OCR configuration
        $this->publishes([
            __DIR__.'/../../config/ocr.php' => config_path('ocr.php'),
        ], 'ocr-config');

        // Register OCR review event listeners
        $this->app['events']->listen('ocr.training.created', function ($trainingData) {
            // Log OCR training data creation
            \Log::info('OCR Training data created', [
                'id' => $trainingData->id,
                'confidence' => $trainingData->confidence
            ]);
        });

        $this->app['events']->listen('ocr.training.reviewed', function ($trainingData) {
            // Log OCR training data review
            \Log::info('OCR Training data reviewed', [
                'id' => $trainingData->id,
                'corrected_text' => $trainingData->corrected_text
            ]);
        });
    }
}