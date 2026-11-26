<?php

namespace App\Providers;

use App\Services\CloudinaryImageUploader;
use App\Services\ConfirmerPaiement;
use App\Services\ImageUploader;
use App\Services\Paiement\PaiementFedaPay;
use App\Services\Paiement\PaiementService;
use App\Services\Paiement\PaiementSimule;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ImageUploader::class, CloudinaryImageUploader::class);

        // Prestataire de paiement choisi par configuration
        $this->app->bind(PaiementService::class, function () {
            return config('kolporte.paiement') === 'fedapay'
                ? $this->app->make(PaiementFedaPay::class)
                : $this->app->make(PaiementSimule::class);
        });

        $this->app->singleton(ConfirmerPaiement::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
