<?php

namespace App\Providers;

use App\Services\CloudinaryImageUploader;
use App\Services\ImageUploader;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ImageUploader::class, CloudinaryImageUploader::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
