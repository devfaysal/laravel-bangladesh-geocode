<?php

namespace Devfaysal\BangladeshGeocode\Tests;

use Devfaysal\BangladeshGeocode\BangladeshGeocodeServiceProvider;
use Devfaysal\BangladeshGeocode\Models\District;
use Devfaysal\BangladeshGeocode\Models\Division;
use Devfaysal\BangladeshGeocode\Models\Union;
use Devfaysal\BangladeshGeocode\Models\Upazila;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase;

class BangladeshGeocodeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadLaravelMigrations(['--database' => 'testing']);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Get package providers.  At a minimum this is the package being tested, but also
     * would include packages upon which our package depends, e.g. Cartalyst/Sentry
     * In a normal app environment these would be added to the 'providers' array in
     * the config/app.php file.
     *
     * @param  Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {

        return [
            BangladeshGeocodeServiceProvider::class,
        ];
    }

    public function test_division_can_be_retrived()
    {
        // $this->withoutExceptionHandling();
        $attributes = ['id' => '1', 'name' => 'Chattagram', 'bn_name' => 'চট্টগ্রাম', 'url' => 'www.chittagongdiv.gov.bd'];
        Division::create($attributes);
        $this->assertDatabaseHas('divisions', $attributes);
    }

    public function test_district_can_be_retrived()
    {
        // $this->withoutExceptionHandling();
        $attributes = ['id' => '1', 'division_id' => '1', 'name' => 'Comilla', 'bn_name' => 'কুমিল্লা', 'lat' => '23.4682747', 'lon' => '91.1788135', 'url' => 'www.comilla.gov.bd'];
        District::create($attributes);
        $this->assertDatabaseHas('districts', $attributes);
    }

    public function test_upazila_can_be_retrived()
    {
        // $this->withoutExceptionHandling();
        $attributes = ['id' => '1', 'district_id' => '1', 'name' => 'Debidwar', 'bn_name' => 'দেবিদ্বার', 'url' => 'debidwar.comilla.gov.bd'];
        Upazila::create($attributes);
        $this->assertDatabaseHas('upazilas', $attributes);
    }

    public function test_division_has_many_districts_and_district_belongs_to_division()
    {
        $attributes = ['id' => '1', 'name' => 'Chattagram', 'bn_name' => 'চট্টগ্রাম', 'url' => 'www.chittagongdiv.gov.bd'];
        Division::create($attributes);

        $districts = [
            ['id' => '1', 'division_id' => '1', 'name' => 'Comilla', 'bn_name' => 'কুমিল্লা', 'lat' => '23.4682747', 'lon' => '91.1788135', 'url' => 'www.comilla.gov.bd'],
            ['id' => '2', 'division_id' => '1', 'name' => 'Feni', 'bn_name' => 'ফেনী', 'lat' => '23.023231', 'lon' => '91.3840844', 'url' => 'www.feni.gov.bd'],
            ['id' => '3', 'division_id' => '1', 'name' => 'Brahmanbaria', 'bn_name' => 'ব্রাহ্মণবাড়িয়া', 'lat' => '23.9570904', 'lon' => '91.1119286', 'url' => 'www.brahmanbaria.gov.bd'],
        ];
        DB::table('districts')->insert($districts);

        $division = Division::first();

        $this->assertInstanceOf(District::class, $division->districts->first());
        $this->assertInstanceOf(Collection::class, $division->districts);

        $district = District::first();
        $this->assertInstanceOf(Division::class, $district->division);
    }

    public function test_district_has_many_upazilas_and_upazila_belongs_to_district()
    {
        $attributes = ['id' => '1', 'division_id' => '1', 'name' => 'Comilla', 'bn_name' => 'কুমিল্লা', 'lat' => '23.4682747', 'lon' => '91.1788135', 'url' => 'www.comilla.gov.bd'];
        District::create($attributes);

        $upazilas = [
            ['id' => '1', 'district_id' => '1', 'name' => 'Debidwar', 'bn_name' => 'দেবিদ্বার', 'url' => 'debidwar.comilla.gov.bd'],
            ['id' => '2', 'district_id' => '1', 'name' => 'Barura', 'bn_name' => 'বরুড়া', 'url' => 'barura.comilla.gov.bd'],
            ['id' => '3', 'district_id' => '1', 'name' => 'Brahmanpara', 'bn_name' => 'ব্রাহ্মণপাড়া', 'url' => 'brahmanpara.comilla.gov.bd'],
        ];
        DB::table('upazilas')->insert($upazilas);

        $district = District::first();

        $this->assertInstanceOf(Upazila::class, $district->upazilas->first());
        $this->assertInstanceOf(Collection::class, $district->upazilas);

        $upazila = Upazila::first();
        $this->assertInstanceOf(District::class, $upazila->district);
    }

    public function test_upazila_has_many_unions_and_union_belongs_to_upazila()
    {
        $attributes = ['id' => '1', 'district_id' => '1', 'name' => 'Debidwar', 'bn_name' => 'দেবিদ্বার', 'url' => 'debidwar.comilla.gov.bd'];
        Upazila::create($attributes);

        $unions = [
            ['id' => '1', 'upazila_id' => '1', 'name' => 'Debidwar', 'bn_name' => 'দেবিদ্বার', 'url' => 'debidwarup.comilla.gov.bd'],
            ['id' => '2', 'upazila_id' => '1', 'name' => 'Elahabad', 'bn_name' => 'এলাহাবাদ', 'url' => 'elahabadup.comilla.gov.bd'],
        ];
        DB::table('unions')->insert($unions);

        $upazila = Upazila::first();

        $this->assertInstanceOf(Union::class, $upazila->unions->first());
        $this->assertInstanceOf(Collection::class, $upazila->unions);

        $union = Union::first();
        $this->assertInstanceOf(Upazila::class, $union->upazila);
    }

    public function test_setup_command_is_working_correctly()
    {
        $this->artisan('BangladeshGeocode:setup')
            ->expectsOutput('Setup Successfull')
            ->assertExitCode(0);
    }

    public function test_add_pourashava_command_is_working_correctly()
    {
        $this->artisan('BangladeshGeocode:addpourashava')
            ->expectsOutput('Adding Pourashava..')
            ->expectsOutput('Pourashava successfully added')
            ->assertExitCode(0);

        $this->assertDatabaseCount('unions', 330);
    }

    public function test_addnew_command_is_working_correctly()
    {
        $this->artisan('BangladeshGeocode:addnew')
            ->expectsOutput('Updating Laravel Bangladesh Geocode..')
            ->expectsOutput('Adding new Upazila..')
            ->expectsOutput('New Upazila added successsfully')
            ->expectsOutput('Updating exising Unions parent Upazila..')
            ->expectsOutput('Exising Unions parent Upazila updated successsfully')
            ->assertExitCode(0);
    }
}
