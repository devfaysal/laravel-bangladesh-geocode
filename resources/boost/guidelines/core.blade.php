## Bangladesh Geocode

`devfaysal/laravel-bangladesh-geocode` provides bundled Bangladesh administrative and postal data, migrations, seeders, and Eloquent models. Use its existing data and relationships for Bangladesh address forms and dependent location dropdowns. The host application owns the UI, routes, authorization, and address persistence.

### Models and hierarchy

Models live in `Devfaysal\BangladeshGeocode\Models`:

- `Division::districts()` → `District::upazilas()` → `Upazila::unions()`.
- Parent keys are `districts.division_id`, `upazilas.district_id`, and `unions.upazila_id`.
- `District::postOffices()` and `PostOffice::district()` cover the postal dataset.
- Option values are IDs; labels use `name` or nullable `bn_name` with a `name` fallback.

@verbatim
```php
use Devfaysal\BangladeshGeocode\Models\Division;

$division = Division::findOrFail($divisionId);
$districtOptions = $division->districts()
    ->orderBy('name')
    ->get(['id', 'name', 'bn_name']);
```
@endverbatim

Here `$divisionId` is the selected division's ID. Scope child lists by their parent and validate the whole submitted hierarchy, not just each ID's existence. Reset descendants when a parent changes. Use the `bangladesh-address-dropdowns` skill for the full workflow when available.

### Setup and data updates

- Check the existing installation before running database-writing commands. An already-seeded application can query the models without reseeding to add a form.
- `php artisan BangladeshGeocode:setup` runs package migrations and seeds divisions, districts, upazilas, unions, and post offices.
- `php artisan BangladeshGeocode:addnew` refreshes those five datasets without migrations. All five package tables must exist; older installations without `post_offices` need setup first. Upserts overwrite bundled fields and preserve rows absent from the seeders.
- `php artisan BangladeshGeocode:addpourashava` optionally seeds municipalities into `unions`. There is no separate `Pourashava` model or type column.
- `php artisan db:seed --class="Devfaysal\BangladeshGeocode\Seeders\PostOfficeSeeder"` can refresh only post offices. Postal `thana` is text, not an upazila relationship. The bundled postal dataset is not exhaustive.
- Production commands require confirmation or an intentional `--force` invocation. Data updates can change existing bundled values; consult the installed README's ID-correction notes when updating.

Use Eloquent models for queries; the package facade has no data-query methods. Keep the application's existing frontend and middleware conventions. Package IDs are not a reason to renumber or automatically clean up the application's stored references.
