<?php

namespace Devfaysal\BangladeshGeocode;

use Illuminate\Support\ServiceProvider;

class BangladeshGeocodeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'bangladesh-geocode');

        // Register the main class to use with the facade
        $this->app->singleton('bangladesh-geocode', function () {
            return new BangladeshGeocode;
        });
    }
}
