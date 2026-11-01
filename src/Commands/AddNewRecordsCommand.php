<?php

namespace Devfaysal\BangladeshGeocode\Commands;

use Devfaysal\BangladeshGeocode\Seeders\BangladeshGeocodeSeeder;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class AddNewRecordsCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'BangladeshGeocode:addnew {--force : Run the command in production}';

    protected $description = 'Add or update divisions, districts, upazilas, unions and post offices from the bundled seeders';

    public function handle(): int
    {
        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $this->info('Updating Bangladesh geocode data..');

        $status = $this->call('db:seed', [
            '--class' => BangladeshGeocodeSeeder::class,
            '--force' => true,
        ]);

        if ($status !== self::SUCCESS) {
            return $status;
        }

        $this->info('Geocode records successfully updated.');

        return self::SUCCESS;
    }
}
