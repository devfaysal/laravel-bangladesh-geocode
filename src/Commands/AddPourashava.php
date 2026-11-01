<?php

namespace Devfaysal\BangladeshGeocode\Commands;

use Devfaysal\BangladeshGeocode\Seeders\PourashavaSeeder;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class AddPourashava extends Command
{
    use ConfirmableTrait;

    protected $signature = 'BangladeshGeocode:addpourashava {--force : Run the command in production}';

    protected $description = 'Add or update pourashavas in the unions table';

    public function handle(): int
    {
        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $this->info('Adding Pourashava..');

        $status = $this->call('db:seed', [
            '--class' => PourashavaSeeder::class,
            '--force' => true,
        ]);

        if ($status !== self::SUCCESS) {
            return $status;
        }

        $this->info('Pourashava successfully added');

        return self::SUCCESS;
    }
}
