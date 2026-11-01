<?php

namespace Devfaysal\BangladeshGeocode\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BangladeshGeocodeSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->call([
                DivisionSeeder::class,
                DistrictSeeder::class,
                UpazilaSeeder::class,
                UnionSeeder::class,
                PostOfficeSeeder::class,
            ]);
        });
    }
}
