<?php

namespace Devfaysal\BangladeshGeocode\Tests;

use Devfaysal\BangladeshGeocode\Models\Union;
use Devfaysal\BangladeshGeocode\Seeders\PostOfficeSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Symfony\Component\Console\Output\BufferedOutput;

class CommandsTest extends TestCase
{
    public function test_setup_can_be_rolled_back_and_reinstalled_in_the_same_process(): void
    {
        $this->artisan('BangladeshGeocode:setup')->assertSuccessful();
        $this->artisan('migrate:rollback')->assertSuccessful();

        foreach (['divisions', 'districts', 'upazilas', 'unions', 'post_offices'] as $table) {
            $this->assertFalse(Schema::hasTable($table));
        }
        $this->assertDatabaseCount('migrations', 0);

        $this->artisan('BangladeshGeocode:setup')->assertSuccessful();

        foreach (['divisions' => 8, 'districts' => 64, 'upazilas' => 495, 'unions' => 4548, 'post_offices' => 1357] as $table => $count) {
            $this->assertDatabaseCount($table, $count);
        }
    }

    public function test_fresh_setup_works_without_a_default_vendor_directory_and_is_repeatable(): void
    {
        $this->app->setBasePath($this->databaseDirectory);
        $this->assertFalse(Schema::hasTable('divisions'));

        $this->artisan('BangladeshGeocode:setup')->expectsOutput('Setup successful.')->assertSuccessful();
        Union::create(['id' => 9000, 'upazila_id' => 1, 'name' => 'Custom union']);
        $this->artisan('BangladeshGeocode:setup')->assertSuccessful();

        foreach (['divisions' => 8, 'districts' => 64, 'upazilas' => 495, 'unions' => 4549, 'post_offices' => 1357] as $table => $count) {
            $this->assertDatabaseCount($table, $count);
        }

        $this->assertDatabaseHas('unions', ['id' => 9000, 'name' => 'Custom union']);
        $this->assertFileExists(database_path('migrations/2026_08_16_000000_create_post_offices_table.php'));
    }

    public static function productionCommands(): array
    {
        return [
            ['BangladeshGeocode:setup'],
            ['BangladeshGeocode:addpourashava'],
            ['BangladeshGeocode:addnew'],
        ];
    }

    #[DataProvider('productionCommands')]
    public function test_noninteractive_production_commands_refuse_without_force(string $command): void
    {
        $this->app['env'] = 'production';
        $output = new BufferedOutput;

        $this->assertSame(Command::FAILURE, Artisan::call($command, ['--no-interaction' => true], $output));
        $this->assertStringNotContainsString('success', strtolower($output->fetch()));
        $this->assertFalse(Schema::hasTable('divisions'));
    }

    public function test_production_commands_work_with_force(): void
    {
        $this->app['env'] = 'production';

        foreach (array_column(self::productionCommands(), 0) as $command) {
            $this->assertSame(0, Artisan::call($command, ['--force' => true, '--no-interaction' => true]));
        }

        $this->assertDatabaseCount('unions', 4878);
        $this->assertDatabaseCount('post_offices', 1357);
    }

    public function test_setup_rolls_back_all_seeds_on_failure_and_can_be_retried(): void
    {
        $this->app->bind(PostOfficeSeeder::class, fn () => new class extends PostOfficeSeeder
        {
            public function run(): void
            {
                parent::run();
                throw new RuntimeException('Simulated failure after post offices');
            }
        });

        $output = new BufferedOutput;
        try {
            Artisan::call('BangladeshGeocode:setup', [], $output);
            $this->fail('Expected seeder failure');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated failure after post offices', $exception->getMessage());
        }

        $this->assertStringNotContainsString('Setup successful.', $output->fetch());
        foreach (['divisions', 'districts', 'upazilas', 'unions', 'post_offices'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }

        $this->app->bind(PostOfficeSeeder::class, PostOfficeSeeder::class);
        $this->artisan('BangladeshGeocode:setup')->assertSuccessful();
        $this->assertDatabaseCount('unions', 4548);
        $this->assertDatabaseCount('post_offices', 1357);
    }

    public static function childFailures(): array
    {
        return [
            ['BangladeshGeocode:setup', 'vendor:publish {--provider=} {--tag=}'],
            ['BangladeshGeocode:setup', 'migrate {--path=} {--realpath} {--force}'],
            ['BangladeshGeocode:setup', 'db:seed {--class=} {--force}'],
            ['BangladeshGeocode:addnew', 'db:seed {--class=} {--force}'],
            ['BangladeshGeocode:addpourashava', 'db:seed {--class=} {--force}'],
        ];
    }

    #[DataProvider('childFailures')]
    public function test_nested_command_failures_are_returned(string $command, string $signature): void
    {
        $failedCommand = new class($signature) extends Command
        {
            public function __construct(string $signature)
            {
                $this->signature = $signature;
                parent::__construct();
            }

            public function handle(): int
            {
                return 17;
            }
        };
        Artisan::registerCommand($failedCommand);
        $output = new BufferedOutput;

        $this->assertSame(17, Artisan::call($command, [], $output));
        $this->assertStringNotContainsString('success', strtolower($output->fetch()));
        if (Schema::hasTable('divisions')) {
            $this->assertDatabaseCount('divisions', 0);
        }
    }

    public function test_update_and_pourashava_commands_are_repeatable_and_preserve_shyampur_url(): void
    {
        $this->artisan('BangladeshGeocode:setup')->assertSuccessful();
        for ($i = 0; $i < 2; $i++) {
            $this->artisan('BangladeshGeocode:addnew')->assertSuccessful();
            $this->artisan('BangladeshGeocode:addpourashava')->assertSuccessful();
        }

        $this->assertDatabaseHas('unions', ['id' => 4549, 'url' => 'shyampurup.meherpur.gov.bd']);
        $this->assertDatabaseCount('upazilas', 495);
        $this->assertDatabaseCount('unions', 4878);
    }

    public function test_addnew_refreshes_core_datasets_and_preserves_custom_records(): void
    {
        $this->artisan('BangladeshGeocode:setup')->assertSuccessful();
        $counts = ['divisions' => 8, 'districts' => 64, 'upazilas' => 495, 'unions' => 4548];
        $expected = [];

        foreach ($counts as $table => $count) {
            $expected[$table] = DB::table($table)->whereIn('id', [1, 2])->orderBy('id')->get()->all();
            DB::table($table)->where('id', 1)->update(['name' => 'Outdated name', 'bn_name' => 'Outdated name', 'url' => 'outdated.example']);
            DB::table($table)->where('id', 2)->delete();
        }
        DB::table('districts')->where('id', 1)->update(['division_id' => 8, 'lat' => '0', 'lon' => '0']);
        DB::table('upazilas')->where('id', 1)->update(['district_id' => 64]);
        DB::table('unions')->where('id', 1)->update(['upazila_id' => 495]);
        Union::create(['id' => 9000, 'upazila_id' => 1, 'name' => 'Custom union']);

        for ($i = 0; $i < 2; $i++) {
            $this->artisan('BangladeshGeocode:addnew')
                ->expectsOutput('Geocode records successfully updated.')
                ->assertSuccessful();
        }

        foreach ($counts as $table => $count) {
            $this->assertEquals($expected[$table], DB::table($table)->whereIn('id', [1, 2])->orderBy('id')->get()->all());
            $this->assertDatabaseCount($table, $count + ($table === 'unions' ? 1 : 0));
        }
        $this->assertDatabaseHas('unions', ['id' => 9000, 'name' => 'Custom union']);
        $this->assertDatabaseCount('post_offices', 1357);
    }

    public function test_charfesson_union_correction_is_applied_to_fresh_and_existing_installations(): void
    {
        $this->artisan('BangladeshGeocode:setup')->assertSuccessful();

        $charfesson = DB::table('unions')->where('upazila_id', 261)->orderBy('id')->get();
        $this->assertCount(21, $charfesson);
        $this->assertCount(21, $charfesson->pluck('name')->unique());
        $this->assertDatabaseHas('unions', ['id' => 2352, 'upazila_id' => 261, 'name' => 'Awajpur', 'bn_name' => 'আওয়াজপুর', 'url' => 'awajpurup.bhola.gov.bd']);
        $this->assertDatabaseHas('unions', ['id' => 2353, 'upazila_id' => 261, 'name' => 'Ahammadpur', 'bn_name' => 'আহম্মদপুর', 'url' => 'ahamadpurup.bhola.gov.bd']);
        $this->assertDatabaseHas('unions', ['id' => 4550, 'upazila_id' => 261, 'name' => 'Omarpur', 'bn_name' => 'ওমরপুর', 'url' => null]);

        // Recreate the previous dataset's duplicate Awajpur and missing Omarpur.
        DB::table('unions')->where('id', 2353)->update(['name' => 'Awajpur', 'bn_name' => 'আওয়াজপুর', 'url' => 'awajpurup.bhola.gov.bd']);
        DB::table('unions')->where('id', 4550)->delete();

        for ($i = 0; $i < 2; $i++) {
            $this->artisan('BangladeshGeocode:addnew')->assertSuccessful();

            $this->assertEquals($charfesson->all(), DB::table('unions')->where('upazila_id', 261)->orderBy('id')->get()->all());
            $this->assertDatabaseCount('unions', 4548);
        }
    }

    public function test_retired_union_ids_are_omitted_from_new_seeds_and_preserved_in_existing_installations(): void
    {
        $this->artisan('BangladeshGeocode:setup')->assertSuccessful();
        $this->assertDatabaseMissing('unions', ['id' => 2546]);
        $this->assertDatabaseMissing('unions', ['id' => 3686]);
        $this->assertDatabaseHas('unions', ['id' => 2540, 'upazila_id' => 285, 'name' => 'Talimpur']);
        $this->assertDatabaseHas('unions', ['id' => 3685, 'upazila_id' => 403, 'name' => 'Bhojoanpur', 'url' => 'bhojoanpurup.panchagarh.gov.bd']);
        $this->assertSame(10, DB::table('unions')->where('upazila_id', 285)->count());
        $this->assertSame(7, DB::table('unions')->where('upazila_id', 403)->count());

        // Recreate the retired rows as they exist in older installations.
        DB::table('unions')->insert([
            ['id' => 2546, 'upazila_id' => 285, 'name' => 'Talimpur', 'bn_name' => 'তালিমপুর', 'url' => 'talimpurup.moulvibazar.gov.bd'],
            ['id' => 3686, 'upazila_id' => 403, 'name' => 'Bhojoanpur', 'bn_name' => 'ভজনপুর', 'url' => 'bhojoanpur.gazipur.gov.bd'],
        ]);
        $retired = DB::table('unions')->whereIn('id', [2546, 3686])->orderBy('id')->get()->all();

        foreach (['BangladeshGeocode:setup', 'BangladeshGeocode:addnew'] as $command) {
            $this->artisan($command)->assertSuccessful();

            $this->assertEquals($retired, DB::table('unions')->whereIn('id', [2546, 3686])->orderBy('id')->get()->all());
            $this->assertDatabaseCount('unions', 4550);
        }

        // A later reseed must also respect a completed manual cleanup.
        DB::table('unions')->whereIn('id', [2546, 3686])->delete();
        $this->artisan('BangladeshGeocode:addnew')->assertSuccessful();
        $this->assertDatabaseMissing('unions', ['id' => 2546]);
        $this->assertDatabaseMissing('unions', ['id' => 3686]);
        $this->assertDatabaseCount('unions', 4548);
    }

    public function test_addnew_rolls_back_all_seeders_on_failure_and_can_be_retried(): void
    {
        $this->artisan('BangladeshGeocode:setup')->assertSuccessful();
        DB::table('divisions')->where('id', 1)->update(['name' => 'Local division']);
        DB::table('districts')->where('id', 2)->delete();
        DB::table('unions')->where('id', 1)->update(['name' => 'Local union']);
        DB::table('post_offices')->where('id', 1)->update(['name' => 'Local post office']);
        DB::table('post_offices')->where('id', 2)->delete();
        $this->app->bind(PostOfficeSeeder::class, fn () => new class extends PostOfficeSeeder
        {
            public function run(): void
            {
                parent::run();
                throw new RuntimeException('Simulated failure after post offices');
            }
        });

        $output = new BufferedOutput;
        try {
            Artisan::call('BangladeshGeocode:addnew', [], $output);
            $this->fail('Expected seeder failure');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated failure after post offices', $exception->getMessage());
        }

        $this->assertStringNotContainsString('successfully updated', $output->fetch());
        $this->assertDatabaseHas('divisions', ['id' => 1, 'name' => 'Local division']);
        $this->assertDatabaseMissing('districts', ['id' => 2]);
        $this->assertDatabaseHas('unions', ['id' => 1, 'name' => 'Local union']);
        $this->assertDatabaseHas('post_offices', ['id' => 1, 'name' => 'Local post office']);
        $this->assertDatabaseMissing('post_offices', ['id' => 2]);

        $this->app->bind(PostOfficeSeeder::class, PostOfficeSeeder::class);
        $this->artisan('BangladeshGeocode:addnew')->assertSuccessful();
        $this->assertDatabaseCount('districts', 64);
        $this->assertDatabaseMissing('divisions', ['id' => 1, 'name' => 'Local division']);
        $this->assertDatabaseMissing('unions', ['id' => 1, 'name' => 'Local union']);
        $this->assertDatabaseMissing('post_offices', ['id' => 1, 'name' => 'Local post office']);
        $this->assertDatabaseCount('post_offices', 1357);
    }

    public static function postOfficeCommands(): array
    {
        return [
            ['BangladeshGeocode:setup', []],
            ['BangladeshGeocode:addnew', []],
            ['db:seed', ['--class' => PostOfficeSeeder::class]],
        ];
    }

    #[DataProvider('postOfficeCommands')]
    public function test_post_office_commands_refresh_bundled_fields_and_preserve_custom_records(string $command, array $arguments): void
    {
        $this->artisan('BangladeshGeocode:setup')->assertSuccessful();
        $this->assertDatabaseCount('post_offices', 1357);
        $expected = DB::table('post_offices')->whereIn('id', [1, 2])->orderBy('id')->get()->all();
        $custom = array_merge((array) $expected[0], ['id' => 9000, 'name' => 'Custom post office']);
        DB::table('post_offices')->insert($custom);
        DB::table('post_offices')->where('id', 1)->update(['district_id' => 64, 'thana' => 'Local thana', 'thana_bn' => 'Local thana', 'name' => 'Local name', 'bn_name' => 'Local name', 'post_code' => '0000']);
        DB::table('post_offices')->where('id', 2)->delete();
        DB::table('post_offices')->where('id', 144)->update(['post_code' => '7910|']);

        for ($i = 0; $i < 2; $i++) {
            $this->artisan($command, $arguments)->assertSuccessful();
        }
        $this->assertEquals($expected, DB::table('post_offices')->whereIn('id', [1, 2])->orderBy('id')->get()->all());
        $this->assertDatabaseHas('post_offices', $custom);

        foreach ([144 => '7910', 162 => '1820', 373 => '2411'] as $id => $code) {
            $this->assertDatabaseHas('post_offices', ['id' => $id, 'post_code' => $code]);
        }
        $invalid = DB::table('post_offices')->pluck('post_code')->filter(fn ($code) => ! preg_match('/\A[0-9]{4}\z/', $code));
        $this->assertEmpty($invalid->all());
        $this->assertDatabaseCount('post_offices', 1358);
    }
}
