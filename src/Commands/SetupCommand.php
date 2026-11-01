<?php

namespace Devfaysal\BangladeshGeocode\Commands;

use Devfaysal\BangladeshGeocode\BangladeshGeocodeServiceProvider;
use Devfaysal\BangladeshGeocode\Seeders\BangladeshGeocodeSeeder;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class SetupCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'BangladeshGeocode:setup {--force : Run the command in production}';

    protected $description = 'Migrate and seed Bangladesh geocode data';

    public function handle(): int
    {
        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $this->info('Setting up Laravel Bangladesh Geocode..');

        $status = $this->call('vendor:publish', [
            '--provider' => BangladeshGeocodeServiceProvider::class,
            '--tag' => 'bangladesh-geocode-migrations',
        ]);

        if ($status !== self::SUCCESS) {
            return $status;
        }

        $status = $this->call('migrate', [
            '--path' => dirname(__DIR__, 2).'/database/migrations',
            '--realpath' => true,
            '--force' => true,
        ]);

        if ($status !== self::SUCCESS) {
            return $status;
        }

        $status = $this->call('db:seed', [
            '--class' => BangladeshGeocodeSeeder::class,
            '--force' => true,
        ]);

        if ($status !== self::SUCCESS) {
            return $status;
        }

        $this->info('Setup successful.');

        return self::SUCCESS;
    }
}
