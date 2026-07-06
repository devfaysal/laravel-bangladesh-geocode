<?php

namespace Devfaysal\BangladeshGeocode\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AddPourashava extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'BangladeshGeocode:addpourashava';

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
        $this->info('Adding Pourashava..');
        Artisan::call('db:seed', ['--class' => 'Devfaysal\BangladeshGeocode\Seeders\PourashavaSeeder']);
        $this->info('Pourashava successfully added');

        return 0;
    }
}
