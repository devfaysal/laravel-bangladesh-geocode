<?php

namespace Devfaysal\BangladeshGeocode\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Devfaysal\BangladeshGeocode\Models\Upazila;
use Devfaysal\BangladeshGeocode\Models\District;
use Devfaysal\BangladeshGeocode\Models\Division;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Devfaysal\BangladeshGeocode\BangladeshGeocodeServiceProvider;

class BangladeshGeocode extends TestCase
{
    // use RefreshDatabase;
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }
    /**
     * Get package providers.  At a minimum this is the package being tested, but also
     * would include packages upon which our package depends, e.g. Cartalyst/Sentry
     * In a normal app environment these would be added to the 'providers' array in
     * the config/app.php file.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        
        return [
            BangladeshGeocodeServiceProvider::class,
        ];
    }

    /** @test */
    public function division_has_many_districts_and_district_belongs_to_division()
    {
        $division = Division::first();

        $this->assertInstanceOf(District::class, $division->districts->first());
        $this->assertInstanceOf(Collection::class, $division->districts);

        $district = District::first();
        $this->assertInstanceOf(Division::class, $district->division);
    }

    /** @test */
    public function district_has_many_upazilas_and_upazila_belongs_to_district()
    {
        $district = District::first();

        $this->assertInstanceOf(Upazila::class, $district->upazilas->first());
        $this->assertInstanceOf(Collection::class, $district->upazilas);

        $upazila = Upazila::first();
        $this->assertInstanceOf(District::class, $upazila->district);
    }

    /** @test */
    public function it_returns_all_divisions()
    {
        $divisions = Division::all();
        $this->assertEquals(8, $divisions->count());
    }

    /** @test */
    public function it_returns_all_districts()
    {
        $districts = District::all();
        $this->assertEquals(64, $districts->count());
    }

    /** @test */
    public function it_returns_all_upazilas()
    {
        $upazilas = Upazila::all();
        $this->assertEquals(491, $upazilas->count());
    }

}