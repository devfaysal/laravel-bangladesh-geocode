# Bangladesh Geocode for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/devfaysal/laravel-bangladesh-geocode.svg?style=flat-square)](https://packagist.org/packages/devfaysal/laravel-bangladesh-geocode)
[![Tests](https://github.com/devfaysal/laravel-bangladesh-geocode/actions/workflows/php.yml/badge.svg?branch=main)](https://github.com/devfaysal/laravel-bangladesh-geocode/actions/workflows/php.yml)
[![Quality Score](https://img.shields.io/scrutinizer/g/devfaysal/laravel-bangladesh-geocode.svg?style=flat-square)](https://scrutinizer-ci.com/g/devfaysal/laravel-bangladesh-geocode)
[![Total Downloads](https://img.shields.io/packagist/dt/devfaysal/laravel-bangladesh-geocode.svg?style=flat-square)](https://packagist.org/packages/devfaysal/laravel-bangladesh-geocode)

Bangladesh's divisions, districts, upazilas, unions, and post offices as Laravel migrations, seeders, and Eloquent models. Includes English and Bangla names, with an optional pourashava (municipality) dataset.

Use it to build Bangladesh address forms and cascading location dropdowns: **Division → District → Upazila → Union**. Your application supplies the UI; this package supplies the data and relationships.

Data is bundled with the package and stored in your application's database. Seeding does not fetch data from an external service.

**This README describes the current development branch.** For a released version, use the documentation and `composer.json` at its [release tag](https://github.com/devfaysal/laravel-bangladesh-geocode/tags). See the [changelog](CHANGELOG.md) for unreleased changes.

## Contents

- [Requirements](#requirements)
- [Quick start](#quick-start)
- [Commands](#commands)
- [Dataset details](#dataset-details)
- [Querying the data](#querying-the-data)
- [Bangladesh division, district, upazila and union dropdowns](#bangladesh-division-district-upazila-and-union-dropdowns)
- [AI coding assistants and Laravel Boost](#ai-coding-assistants-and-laravel-boost)
- [Updating existing installations](#updating-existing-installations)
- [Troubleshooting](#troubleshooting)
- [Development and AI agent reference](#development-and-ai-agent-reference)
- [Contributing and security](#contributing-and-security)

## Requirements

| Laravel version | Minimum PHP version |
| :-------------- | :------------------ |
| 12.69+ within Laravel 12 | 8.2 |
| 13.30+ within Laravel 13 | 8.3 |

The package's exact constraints are in [composer.json](composer.json); Laravel also sets its own [PHP requirements](https://laravel.com/docs/13.x/releases#support-policy). Older package releases have different constraints.

Configure your Laravel application's database connection before running setup. CI tests this branch with SQLite and MySQL 8.4 on Laravel 12 and 13.

## Quick start

Run these commands from your **Laravel application root**:

```bash
composer require devfaysal/laravel-bangladesh-geocode
php artisan BangladeshGeocode:setup
```

Laravel's [package discovery](https://laravel.com/docs/12.x/packages#package-discovery) registers the service provider automatically unless discovery is disabled in your application.

Setup publishes the package migrations, runs pending package migrations, and seeds divisions, districts, upazilas, unions, and post offices.

| Dataset | Bundled records | Database table | Seeded by setup |
| :------ | --------------: | :------------- | :-------------- |
| Divisions | 8 | `divisions` | Yes |
| Districts | 64 | `districts` | Yes |
| Upazilas | 495 | `upazilas` | Yes |
| Unions | 4,548 | `unions` | Yes |
| Pourashavas | 330 | `unions` | Optional command |
| Post offices | 1,357 | `post_offices` | Yes |

Counts describe this branch's bundled records. Existing databases may also contain custom records or retired duplicates.

## Commands

Use the command names exactly as shown, including capitalization.

| Command | Purpose | Prerequisite |
| :------ | :------ | :----------- |
| `php artisan BangladeshGeocode:setup` | Publish and run package migrations, then insert or update all five standard datasets | Configured database |
| `php artisan BangladeshGeocode:addnew` | Insert or update divisions, districts, upazilas, unions, and post offices without running migrations | All five package tables already exist |
| `php artisan BangladeshGeocode:addpourashava` | Insert or update pourashavas in the `unions` table | Core setup completed |

Despite its name, `addnew` updates existing bundled records as well as adding missing ones. Both `setup` and `addnew` call `Devfaysal\BangladeshGeocode\Seeders\BangladeshGeocodeSeeder`, including post offices. Pourashavas require their separate command.

### Production and unattended runs

The three package commands ask for confirmation when Laravel's environment is `production`. For an unattended production update, use:

```bash
php artisan BangladeshGeocode:addnew --force --no-interaction
```

The same flags work with `setup` and `addpourashava`. Laravel's separate `migrate` and `db:seed` commands also accept them. `--force` bypasses production confirmation; it does not delete existing data or force-overwrite published migration files.

## Dataset details

### Pourashavas

After core setup, run:

```bash
php artisan BangladeshGeocode:addpourashava
```

This adds or updates 330 municipalities in the **same `unions` table**. Query them through the `Union` model and `Upazila::unions()` relationship. There is no separate `Pourashava` model, table, or type column, so union queries will include these rows after this command runs.

Rerun this command separately when you want to refresh the bundled pourashavas.

### Post offices

Post offices are seeded by `setup` and refreshed by `addnew`. The bundled records cover all 64 districts but are not a complete directory of every post office.

To refresh only post offices after setup, run:

```bash
php artisan db:seed --class="Devfaysal\BangladeshGeocode\Seeders\PostOfficeSeeder"
```

The same command updates existing post offices and inserts missing ones. For unattended production use, append `--force --no-interaction`.

For an older installation that does not have `post_offices`, run setup before using `addnew`:

```bash
php artisan BangladeshGeocode:setup
```

Setup runs pending package migrations and refreshes all five standard datasets. Already-applied migrations keep their existing filenames and are not rerun.

## Querying the data

Use the models in `Devfaysal\BangladeshGeocode\Models`. They support normal Eloquent queries and relationships.

```php
use Devfaysal\BangladeshGeocode\Models\District;
use Devfaysal\BangladeshGeocode\Models\Division;
use Devfaysal\BangladeshGeocode\Models\Union;
use Devfaysal\BangladeshGeocode\Models\Upazila;

$divisions = Division::orderBy('name')->get(['id', 'name', 'bn_name']);

$division = Division::with('districts')->where('name', 'Dhaka')->firstOrFail();
$districts = $division->districts;

$district = District::with('upazilas')->findOrFail(1);
$upazilas = $district->upazilas;

$upazila = Upazila::with('unions')->findOrFail(261); // Charfesson
$unions = $upazila->unions;

$union = Union::with('upazila')->findOrFail(2352); // Awajpur
$parentUpazila = $union->upazila;
```

Query post offices after setup:

```php
use Devfaysal\BangladeshGeocode\Models\District;
use Devfaysal\BangladeshGeocode\Models\PostOffice;

$district = District::with('postOffices')->findOrFail(47);
$postOffices = $district->postOffices;

$matches = PostOffice::where('post_code', '1360')->get();
```

### Model reference

| Model | Table | Relationships |
| :---- | :---- | :------------ |
| `Division` | `divisions` | `districts()` |
| `District` | `districts` | `division()`, `upazilas()`, `postOffices()` |
| `Upazila` | `upazilas` | `district()`, `unions()` |
| `Union` | `unions` | `upazila()` |
| `PostOffice` | `post_offices` | `district()` |

- Core geographic records have `id`, `name`, `bn_name`, and `url`. Parent keys are `districts.division_id`, `upazilas.district_id`, and `unions.upazila_id`.
- Districts also have `lat` and `lon`, stored as nullable strings.
- Post offices have `id`, `district_id`, `thana`, `thana_bn`, `name`, `bn_name`, and `post_code`. Postal codes are strings. `thana` is text, not an `upazila_id` relationship.
- Core `bn_name` and `url` fields are nullable. A `url` may be a hostname without a scheme such as `https://`.
- All tables include `created_at` and `updated_at`; bundled seeders do not populate these fields.
- Models use `$guarded = []`. Authorize application writes and pass only validated attributes to `create()`, `fill()`, and `update()`.

## Bangladesh division, district, upazila and union dropdowns

Use the package models to load each list by its selected parent. For example:

```php
use Devfaysal\BangladeshGeocode\Models\Division;

$division = Division::findOrFail($divisionId);
$districtOptions = $division->districts()
    ->orderBy('name')
    ->get(['id', 'name', 'bn_name']);
```

Here, `$divisionId` is the selected division's ID. Repeat the pattern with `District::upazilas()` and `Upazila::unions()`. Use `name` for English labels and `bn_name` with a `name` fallback for Bangla labels; submit IDs as option values.

The [dependent dropdown guide](docs/dependent-dropdowns.md) includes JSON routes, frontend state handling, and validation that checks every parent-child relationship. Use it with your existing Blade, Livewire, Inertia, Vue, or React frontend.

## AI coding assistants and Laravel Boost

This package ships optional [Laravel Boost](https://laravel.com/docs/boost#third-party-package-ai-guidelines) resources:

- [Package guidelines](resources/boost/guidelines/core.blade.php): model names, relationships, seeding behavior, and integration boundaries.
- [Bangladesh address dropdown skill](resources/boost/skills/bangladesh-address-dropdowns/SKILL.md): a focused workflow for building and validating dependent location selectors.

In an application that already has this package and Laravel Boost installed, run:

```bash
php artisan boost:install
```

Select your agents and the guidelines/skills features you want. Boost discovers these resources from installed packages; it does not find or install this package into an unrelated project. The package works without Boost and adds no AI dependency to your application. See [Boost's package skill documentation](https://laravel.com/docs/boost#third-party-package-skills) for supported installation behavior.

For an assistant without Boost, point it to this README and the dropdown guide. For example:

> Use devfaysal/laravel-bangladesh-geocode to add Bangladesh division, district, upazila and union dropdowns using this application's existing frontend. Filter children by their parent, support English and Bangla labels, and validate the submitted hierarchy. Check the existing installation before running any setup or data-refresh commands.

## Updating existing installations

After updating the Composer package, review the [changelog](CHANGELOG.md) and [ID corrections below](#union-id-corrections), then refresh the geographic and postal data:

```bash
php artisan BangladeshGeocode:addnew
```

Use `setup` when package migrations also need to run, including on older installations without `post_offices`. All five package tables are required by `addnew`. Refresh optional pourashavas with their own command.

### Reseeding behavior

| Situation | Result |
| :-------- | :----- |
| A bundled ID is missing from the database | The record is inserted |
| A bundled ID already exists | Its bundled fields, including names and parent IDs, are updated |
| An application has edited a bundled field | Reseeding overwrites that edit |
| A database ID is absent from the seeder | The row is left untouched |
| A record has been removed from the bundled dataset | Existing installations retain it until manually cleaned up |

All seeders use Laravel's native query builder `upsert`, matching by primary ID. They do not truncate tables or automatically merge or delete retired records. Because they use the query builder, Eloquent model events and automatic model timestamps are not applied.

The main seeder runs divisions, districts, upazilas, unions, and post offices in that order within one database transaction. A seeding failure rolls back that transaction on a transactional database. Schema migrations completed before the failure remain applied. After resolving the cause, rerun the command. Package commands return a nonzero exit status when a child command fails.

### Union ID corrections

These corrections are part of the current development branch.

**Charfesson (`upazila_id = 261`):** the core dataset now contains 21 distinct unions.

| ID | Current record | Effect on an existing installation |
| :--- | :------------- | :---------------------------------- |
| `2352` | Awajpur | Retained |
| `2353` | Ahammadpur | Replaces the duplicate Awajpur record when reseeded |
| `4550` | Omarpur | Inserted if missing; URL is currently `null` |

Before reseeding, review application references to `2353`. References that mean Awajpur should point to `2352`. The package does not update your application's referencing records.

**Retired duplicates:** these IDs are omitted from fresh installations and must never be reused.

| Retired ID | Replacement ID | Union | Upazila |
| :--------- | :------------- | :---- | :------ |
| `2546` | `2540` | Talimpur | Barlekha |
| `3686` | `3685` | Bhojoanpur | Tetulia |

On existing installations, both `setup` and `addnew` leave the retired rows untouched. Cleanup is optional and manual: update all application references to the replacement IDs, resolve any conflicts, then delete the retired rows. Later reseeding will not recreate them.

## Troubleshooting

| Symptom | What to check |
| :------ | :------------ |
| Package commands are missing | Check whether the application disables package discovery. If it does, register `Devfaysal\BangladeshGeocode\BangladeshGeocodeServiceProvider` in the application's provider list. |
| `addnew` reports a missing package table, including `post_offices` | Run `BangladeshGeocode:setup` to apply the package migrations first. |
| A post office is missing from the dropdown | The bundled postal dataset is not exhaustive; allow manual input where your application requires it. |
| An unattended production command is cancelled | Supply `--force --no-interaction` when the database update is intended. |
| Retired duplicates remain after an update | Follow the manual cleanup mapping under [Union ID corrections](#union-id-corrections). |

## Development and AI agent reference

Run development commands from the **package repository root**, not from a host application's root:

```bash
composer install
composer test
composer lint
composer analyse
```

`composer lint` checks formatting with Laravel Pint; `composer format` applies formatting. `composer analyse` runs PHPStan with Larastan. Run `composer audit` when reviewing dependency changes.

### Where to make changes

| Concern | Source |
| :------ | :----- |
| Bundled records | [src/Seeders](src/Seeders) |
| Dropdown integration recipe | [docs/dependent-dropdowns.md](docs/dependent-dropdowns.md) |
| Instructions shipped to consuming applications | [resources/boost](resources/boost) |
| Core seeding order and transaction | [BangladeshGeocodeSeeder.php](src/Seeders/BangladeshGeocodeSeeder.php) |
| CLI behavior | [src/Commands](src/Commands) |
| Eloquent models and relationships | [src/Models](src/Models) |
| Table definitions | [database/migrations](database/migrations) |
| Compatibility and development commands | [composer.json](composer.json) |
| Integration tests and test databases | [tests](tests), [TestCase.php](tests/TestCase.php) |
| Tested PHP, Laravel, and database combinations | [CI workflow](.github/workflows/php.yml) |

When changing data:

1. Verify the correction against an authoritative source and check the parent division, district, or upazila.
2. Preserve existing IDs and document any change in what an ID represents. Never reuse retired IDs `2546` or `3686`.
3. Check new IDs against the whole relevant table's dataset. Unions and pourashavas share a table and must have distinct IDs. Row position and record count do not determine the next available ID.
4. Keep records grouped by parent, even when a newly added ID is larger. Trim surrounding whitespace and preserve the established record fields.
5. Include post offices in the main seeder and keep pourashavas optional. Setup and `addnew` share the main seeder; update commands should not maintain separate copies of the data.
6. Update affected test expectations and upgrade notes. Preserve existing migration filenames so installations can recognize migrations they already ran.

### Test databases

Tests use SQLite in memory by default. They cover relationships, dataset integrity, repeated seeding, production confirmation, failure handling, and compatibility with existing records. CI also runs the suite on MySQL 8.4 with Laravel 12 and 13.

To test with MySQL, create a database and user reserved for package tests, then supply their credentials:

```bash
TEST_DB_CONNECTION=mysql \
TEST_DB_DATABASE=bangladesh_geocode_test \
TEST_DB_USERNAME=geocode \
TEST_DB_PASSWORD=geocode \
composer test
```

**The MySQL test connection deletes every table in its configured database before each test. Never point it at an application database.** `TEST_DB_HOST` and `TEST_DB_PORT` default to `127.0.0.1` and `3306`; `TEST_DB_SOCKET` supports a Unix socket connection.

## Contributing and security

Issues and pull requests are welcome. See [CONTRIBUTING.md](CONTRIBUTING.md) for contribution guidelines. Include a source when proposing a geographic data correction.

Report security vulnerabilities privately to [devfaysal@gmail.com](mailto:devfaysal@gmail.com).

## Credits

- [Faysal Ahamed](https://github.com/devfaysal)
- [All contributors](https://github.com/devfaysal/laravel-bangladesh-geocode/graphs/contributors)
- [Nuhil Mehdy's Bangladesh Geocode dataset](https://github.com/nuhil/bangladesh-geocode), the original source for the division, district, and upazila data.

## License

MIT. See [LICENSE.md](LICENSE.md).
