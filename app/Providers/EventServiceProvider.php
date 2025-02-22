<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Register OCR-related events
        Event::listen('ocr.training.created', function ($trainingData) {
            // Log OCR training data creation
            \Log::info('OCR Training data created', [
                'id' => $trainingData->id,
                'confidence' => $trainingData->confidence
            ]);
        });

        Event::listen('ocr.training.reviewed', function ($trainingData) {
            // Log OCR training data review
            \Log::info('OCR Training data reviewed', [
                'id' => $trainingData->id,
                'corrected_text' => $trainingData->corrected_text
            ]);
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}