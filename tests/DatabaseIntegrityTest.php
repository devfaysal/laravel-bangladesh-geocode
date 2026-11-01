<?php

namespace Devfaysal\BangladeshGeocode\Tests;

use Devfaysal\BangladeshGeocode\Seeders\DistrictSeeder;
use Devfaysal\BangladeshGeocode\Seeders\DivisionSeeder;
use Devfaysal\BangladeshGeocode\Seeders\PostOfficeSeeder;
use Devfaysal\BangladeshGeocode\Seeders\PourashavaSeeder;
use Devfaysal\BangladeshGeocode\Seeders\UnionSeeder;
use Devfaysal\BangladeshGeocode\Seeders\UpazilaSeeder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class DatabaseIntegrityTest extends TestCase
{
    public function test_bundled_datasets_have_unique_ids_and_trimmed_values(): void
    {
        $idsByTable = [];
        $untrimmedValues = [];
        $seeders = [
            DivisionSeeder::class => 'divisions',
            DistrictSeeder::class => 'districts',
            UpazilaSeeder::class => 'upazilas',
            UnionSeeder::class => 'unions',
            PostOfficeSeeder::class => 'post_offices',
            PourashavaSeeder::class => 'unions',
        ];
        foreach ($seeders as $class => $table) {
            // Inspect IDs before upserts can silently merge duplicate records.
            $builder = $this->getMockBuilder(Builder::class)->disableOriginalConstructor()->onlyMethods(['upsert'])->getMock();
            $builder->expects($this->once())->method('upsert')->willReturnCallback(
                function (array $records) use ($table, &$idsByTable, &$untrimmedValues): int {
                    $this->assertNotEmpty($records);
                    $idsByTable[$table] = array_merge($idsByTable[$table] ?? [], array_map('intval', array_column($records, 'id')));

                    foreach ($records as $record) {
                        foreach ($record as $column => $value) {
                            if (is_string($value) && $value !== trim($value)) {
                                $untrimmedValues[] = "{$table}.{$record['id']}.{$column}";
                            }
                        }
                    }

                    return count($records);
                }
            );
            DB::shouldReceive('table')->once()->with($table)->andReturn($builder);
            (new $class)->run();
        }

        foreach ($idsByTable as $table => $ids) {
            $this->assertCount(count($ids), array_unique($ids), "Duplicate IDs in {$table}");
        }

        $this->assertEmpty($untrimmedValues, 'Dataset fields contain surrounding whitespace: '.implode(', ', $untrimmedValues));
    }

    public function test_bundled_hierarchy_remains_consistent_after_updates(): void
    {
        $this->artisan('BangladeshGeocode:setup')->assertSuccessful();
        $this->assertValidParentReferences();

        $records = [];
        foreach (['divisions', 'districts', 'upazilas', 'unions', 'post_offices'] as $table) {
            $records[$table] = DB::table($table)->orderBy('id')->get()->all();
        }
        $this->artisan('BangladeshGeocode:addnew')->assertSuccessful();

        foreach ($records as $table => $expected) {
            $this->assertEquals($expected, DB::table($table)->orderBy('id')->get()->all());
        }

        $this->artisan('BangladeshGeocode:addpourashava')->assertSuccessful();
        $this->assertValidParentReferences();
    }

    private function assertValidParentReferences(): void
    {
        $relationships = [
            ['districts', 'division_id', 'divisions'],
            ['upazilas', 'district_id', 'districts'],
            ['unions', 'upazila_id', 'upazilas'],
            ['post_offices', 'district_id', 'districts'],
        ];

        foreach ($relationships as [$table, $column, $parent]) {
            $orphans = DB::table($table)
                ->leftJoin($parent, $table.'.'.$column, '=', $parent.'.id')
                ->whereNull($parent.'.id')
                ->pluck($table.'.id')->all();

            $this->assertEmpty($orphans, "Records in {$table} reference missing {$parent}");
        }
    }
}
