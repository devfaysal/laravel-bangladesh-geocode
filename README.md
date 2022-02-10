# Bangladesh Geocode
Division, District, Upazila and Union data of Bangladesh for Laravel application.
One artisan command and you are all set.

## Do not hesitate to share your thought, create issue or send pull request.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/devfaysal/laravel-bangladesh-geocode.svg?style=flat-square)](https://packagist.org/packages/devfaysal/laravel-bangladesh-geocode)
![](https://github.com/devfaysal/laravel-bangladesh-geocode/workflows/Run%20Tests/badge.svg?branch=master)
[![Quality Score](https://img.shields.io/scrutinizer/g/devfaysal/laravel-bangladesh-geocode.svg?style=flat-square)](https://scrutinizer-ci.com/g/devfaysal/laravel-bangladesh-geocode)
[![Total Downloads](https://img.shields.io/packagist/dt/devfaysal/laravel-bangladesh-geocode.svg?style=flat-square)](https://packagist.org/packages/devfaysal/laravel-bangladesh-geocode)

## Laravel Version Compatibility

 Laravel  | Package
:---------|:----------
 5.5.x    | 0.4.x
 5.6.x    | 0.4.x
 5.7.x    | 0.4.x
 5.8.x    | 0.4.x
 6.x.x    | 0.4.x
 7.x.x    | 0.4.x
 8.x.x    | 1.0.x
 9.x.x    | 2.x.x

## Installation

You can install the package via composer:

``` bash
composer require devfaysal/laravel-bangladesh-geocode
```
Setup everything with just running one artisan command
``` bash
php artisan BangladeshGeocode:setup
```

## Usage

``` php
use Devfaysal\BangladeshGeocode\Models\Division;
use Devfaysal\BangladeshGeocode\Models\District;
use Devfaysal\BangladeshGeocode\Models\Upazila;

$divisions = Division::all();
$districts = District::all();
$upazilas = Upazila::all();

$division = Division::where('name', 'Dhaka);
$allDistrictsOfDhaka = $division->districts;

$division = Division::find(1);
$districts = $division->districts;

$district = District::find(1);
$upazilas = $district->upazilas;

//Use any Laravel (eluquent) model functions...
```

### Testing
The package is well supported by automated test.
``` bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email devfaysal@gmail.com instead of using the issue tracker.

## Credits

- [Faysal Ahamed](https://github.com/devfaysal)
- [All Contributors](../../contributors)
- Special Thanks to [Nuhil Mehdy](https://github.com/nuhil/bangladesh-geocode). All of the Division, District and Upazila data of this package is from his repo.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
