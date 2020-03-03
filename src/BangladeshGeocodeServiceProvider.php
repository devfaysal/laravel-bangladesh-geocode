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

        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            // $this->publishes([
            //     __DIR__.'/../config/config.php' => config_path('bangladesh-geocode.php'),
            // ], 'config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'bangladesh-geocode-migrations');
            $this->publishes([
                __DIR__.'/../database/seeds' => database_path('seeds'),
            ], 'bangladesh-geocode-seeds');

            // Registering package commands.
            // $this->commands([]);
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
