# Bangladesh division, district, upazila and union dropdowns

Build a dependent address selector with `devfaysal/laravel-bangladesh-geocode`:

**Division → District → Upazila → Union**

The package provides the data and Eloquent relationships. The routes, form, and persistence belong to your Laravel application. Use its existing Blade, Livewire, Inertia, Vue, or React conventions; no additional frontend dependency is required by this package.

## Before adding the form

Check that the package is installed and the core tables are populated. For a new installation, follow the [quick start](../README.md#quick-start). Adding a dropdown to an already-seeded application does not require reseeding. Setup and data-refresh commands overwrite bundled fields, so use them only when the task requires loading or updating data.

| Selector | Model | Parent filter |
| :------- | :---- | :------------ |
| Division | `Division` | None |
| District | `District` | `division_id` |
| Upazila | `Upazila` | `district_id` |
| Union | `Union` | `upazila_id` |

All four models live in `Devfaysal\BangladeshGeocode\Models`. Return only `id`, `name`, and `bn_name` for options. Submit IDs rather than names.

## Load the options

For a frontend that loads options over HTTP, add these example GET routes to the application's `routes/web.php`. Choose route names and middleware that fit the application. These routes are not registered by the package.

```php
use Devfaysal\BangladeshGeocode\Models\District;
use Devfaysal\BangladeshGeocode\Models\Division;
use Devfaysal\BangladeshGeocode\Models\Upazila;
use Illuminate\Support\Facades\Route;

Route::prefix('locations')->name('locations.')->group(function () {
    Route::get('/divisions', function () {
        return Division::orderBy('name')->get(['id', 'name', 'bn_name']);
    })->name('divisions');

    Route::get('/divisions/{division}/districts', function (Division $division) {
        return $division->districts()->orderBy('name')->get(['id', 'name', 'bn_name']);
    })->whereNumber('division')->name('districts');

    Route::get('/districts/{district}/upazilas', function (District $district) {
        return $district->upazilas()->orderBy('name')->get(['id', 'name', 'bn_name']);
    })->whereNumber('district')->name('upazilas');

    Route::get('/upazilas/{upazila}/unions', function (Upazila $upazila) {
        return $upazila->unions()->orderBy('name')->get(['id', 'name', 'bn_name']);
    })->whereNumber('upazila')->name('unions');
});
```

Laravel serializes the collections to JSON. Numeric IDs that do not exist return a 404 through model binding. Each child query is scoped to the parent in its route. Generate URLs with Laravel's named-route helpers or the application's existing route tooling, so deployments under a URL prefix also work.

For Livewire or a controller that renders the form on the server, use the same relationship queries directly. Separate JSON routes are unnecessary in that case.

## Keep the form state consistent

Use field names such as `division_id`, `district_id`, `upazila_id`, and `union_id` throughout the form and validation.

| Event | Clear immediately | Load next |
| :---- | :---------------- | :-------- |
| Form opens | No saved selection | Divisions |
| Division changes | District, upazila, and union values and options | Districts for the selected division |
| District changes | Upazila and union values and options | Upazilas for the selected district |
| Upazila changes | Union value and options | Unions for the selected upazila |
| A parent is cleared | Every descendant's value and options | Nothing until a parent is selected |

- Disable each child selector until its parent is selected and its options are loaded. Show loading, empty, and error states.
- Cancel an earlier request or ignore its result if the parent changes before it completes. An old response must not replace the options for the current selection.
- Display `name` for English or `bn_name` with a `name` fallback for Bangla. Render labels as escaped text; use Blade's escaped output or the frontend framework's normal text binding.
- When editing an address or restoring old input after validation, load lists in parent order and restore a selected ID only if it belongs to the loaded list. Do not clear a valid saved selection during initialization.
- Keep the application's existing authorization and CSRF protection on address-saving requests. Store the validated IDs using the application's address model or fields.

## Validate the submitted hierarchy

A record can exist while belonging to a different parent. Checking only `exists:districts,id`, for example, would accept a district outside the selected division.

The following example requires all four levels. Place the logic in your application's request handler, where `$request` is the incoming `Illuminate\Http\Request`. First validate scalar IDs, then check every parent-child relationship with those validated values:

```php
use Devfaysal\BangladeshGeocode\Models\District;
use Devfaysal\BangladeshGeocode\Models\Division;
use Devfaysal\BangladeshGeocode\Models\Union;
use Devfaysal\BangladeshGeocode\Models\Upazila;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

$ids = $request->validate([
    'division_id' => ['bail', 'required', 'integer', Rule::exists(Division::class, 'id')],
    'district_id' => ['bail', 'required', 'integer'],
    'upazila_id' => ['bail', 'required', 'integer'],
    'union_id' => ['bail', 'required', 'integer'],
]);

Validator::make($ids, [
    'district_id' => [
        Rule::exists(District::class, 'id')->where('division_id', $ids['division_id']),
    ],
    'upazila_id' => [
        Rule::exists(Upazila::class, 'id')->where('district_id', $ids['district_id']),
    ],
    'union_id' => [
        Rule::exists(Union::class, 'id')->where('upazila_id', $ids['upazila_id']),
    ],
])->validate();

// Only after both validations pass, persist $ids using your application's model.
```

The first pass rejects arrays and other malformed values before they can reach the relationship queries. The second rejects nonexistent children and valid IDs that belong to the wrong parent. Laravel supports these scoped existence checks through [`Rule::exists`](https://laravel.com/docs/13.x/validation#rule-exists).

If your form permits stopping at a district or upazila, adapt the required/nullable rules and run child checks only for selected levels. Keep descendants empty when their parent is absent. Do not require a union in flows where the application's address rules make it optional.

## Municipalities, postal data, and existing records

- Pourashavas are optional rows in the **same `unions` table**, so `Upazila::unions()` includes them after `BangladeshGeocode:addpourashava` runs. There is no built-in type column or separate municipality model. If you need separate lists, define an explicit classification in the application; do not infer type from spelling or ID order.
- Post offices are seeded by `setup` and refreshed by `addnew`. They belong to districts: `PostOffice::district()` and `District::postOffices()` are available, but postal `thana` is text and is not an upazila foreign key. Do not assume a direct union-to-post-office mapping. The postal dataset is not exhaustive; allow manual post-office input where the application's address requirements call for it.
- Existing installations may retain retired duplicate union IDs. Follow the [documented ID corrections](../README.md#union-id-corrections) when cleanup is requested. Building a dropdown does not require deleting or remapping existing application data.

## Verify the integration

Check the application's behavior with a valid full hierarchy, an unrelated child at each level, nonexistent IDs, and array-shaped input. On the frontend, verify parent resets, quick successive changes, Bangla fallbacks, and restoring an existing address. If pourashavas are enabled, verify the combined final selector matches your intended address flow.
