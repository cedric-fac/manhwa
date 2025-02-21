<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use App\Support\CloudinaryAdapter;

class CloudinaryServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Storage::extend('cloudinary', function ($app, $config) {
            $client = new \Cloudinary\Cloudinary([
                'cloud_name' => $config['cloud_name'],
                'api_key' => $config['key'],
                'api_secret' => $config['secret'],
                'secure' => true
            ]);

            return new Filesystem(new CloudinaryAdapter($client));
        });
    }
}