<?php

namespace Devfaysal\BangladeshGeocode;

use Devfaysal\BangladeshGeocode\Commands\SetupCommand;
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

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'bangladesh-geocode-migrations');
            
            // Registering package commands.
            $this->commands([
                SetupCommand::class
            ]);
        }
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
