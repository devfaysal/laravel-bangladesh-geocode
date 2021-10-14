<?php

namespace Devfaysal\BangladeshGeocode\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'BangladeshGeocode:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Setting up Laravel Bangladesh Geocode..');
        $this->info('Database migrating..');
        Artisan::call('migrate', ['--path' => 'vendor/devfaysal/laravel-bangladesh-geocode/database/migrations']);
        $this->info('Database successfully migrated');

        $this->info('Seeding Divisions..');
        Artisan::call('db:seed', array('--class' => 'Devfaysal\BangladeshGeocode\Seeders\DivisionSeeder'));

        $this->info('Seeding Districts..');
        Artisan::call('db:seed', array('--class' => 'Devfaysal\BangladeshGeocode\Seeders\DistrictSeeder'));

        $this->info('Seeding Upazilas..');
        Artisan::call('db:seed', array('--class' => 'Devfaysal\BangladeshGeocode\Seeders\UpazilaSeeder'));

        $this->info('Seeding Unions..');
        Artisan::call('db:seed', array('--class' => 'Devfaysal\BangladeshGeocode\Seeders\UnionSeeder'));

        $this->info('Publishing migrations..');
        Artisan::call('vendor:publish', array('--provider' => 'Devfaysal\BangladeshGeocode\BangladeshGeocodeServiceProvider'));
        $this->info('Setup Successfull');
    }
}
