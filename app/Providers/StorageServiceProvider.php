<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\Filesystem;

class StorageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Configure test disk for testing environment
        if ($this->app->environment('testing')) {
            Storage::extend('test', function ($app, $config) {
                $adapter = new LocalFilesystemAdapter(
                    storage_path('app/test')
                );

                return new Filesystem($adapter);
            });
        }

        // Configure Cloudinary disk
        Storage::extend('cloudinary', function ($app, $config) {
            if (isset($config['url'])) {
                return new \App\Support\CloudinaryAdapter($config);
            }

            throw new \Exception('Cloudinary configuration is missing.');
        });
    }
}