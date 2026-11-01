---
name: bangladesh-address-dropdowns
description: Build or update Bangladesh division, district, upazila and union dropdowns in Laravel applications using devfaysal/laravel-bangladesh-geocode. Use when this package is installed or explicitly selected for an address form, with English or Bangla labels and parent-child validation.
license: MIT
---

# Bangladesh address dropdowns

Use the consuming application's existing frontend, routing, and form conventions. This package supplies data and Eloquent relationships; it does not register dropdown endpoints or UI components.

## Inspect the integration

Check the installed package version, existing core tables/data, and any current address fields or selectors. A form change in an initialized application does not require setup or reseeding. If installation or data refresh is required by the task, follow the installed package's README; updates overwrite bundled fields.

For complete route and validation examples, read `docs/dependent-dropdowns.md` inside the installed package. Resolve its root with `Composer\InstalledVersions::getInstallPath('devfaysal/laravel-bangladesh-geocode')` when necessary; do not assume the application uses the default `vendor` directory. In a checkout of the package itself, the guide is under the repository root.

## Query the hierarchy

Import models from `Devfaysal\BangladeshGeocode\Models`:

| Options | Query scope |
| :------ | :---------- |
| Divisions | `Division::query()` |
| Districts | `District::where('division_id', $divisionId)` |
| Upazilas | `Upazila::where('district_id', $districtId)` |
| Unions | `Union::where('upazila_id', $upazilaId)` |

The variables are the selected parent IDs. Alternatively use `Division::districts()`, `District::upazilas()`, and `Upazila::unions()` on a loaded parent model. Retrieve `id`, `name`, and `bn_name`, with a stable display order. Avoid loading the entire country hierarchy for a form that only needs one branch.

Use existing server-side component queries when they fit the application's frontend. If JSON endpoints are needed, add them in the application with its existing middleware and route conventions. Return only option fields and handle nonexistent parents.

## Maintain selection state

- Submit IDs. Render `name` for English or `bn_name` with a `name` fallback for Bangla, using escaped text bindings.
- Changing a division clears district, upazila, and union selections and lists. Changing a district clears upazila and union; changing an upazila clears union.
- Disable children while their parent is absent or options are loading. Handle empty/error states and discard stale responses when the parent changes.
- For edit forms and validation retries, load lists in parent order and restore only values belonging to the loaded list. Preserve valid saved selections during initialization.

## Validate before saving

Validate required/nullable behavior according to the application's address requirements. Reject non-scalar or non-integer IDs before using them in database filters. For selected levels, require the division to exist and scope each child's existence rule by its selected parent:

```php
use Devfaysal\BangladeshGeocode\Models\District;
use Illuminate\Validation\Rule;

// $divisionId has already passed scalar integer validation.
$districtRule = Rule::exists(District::class, 'id')
    ->where('division_id', $divisionId);
```

Apply the equivalent `district_id` constraint to an upazila and `upazila_id` constraint to a union. An unrestricted existence check alone accepts mismatched hierarchies. Keep descendants empty when their parent is absent and persist only values that pass the complete validation.

## Respect dataset boundaries

Pourashavas, if enabled, are rows in `unions` and appear in union queries. There is no type discriminator or separate municipality model. Postal records are included in setup and updates, but the dataset is not exhaustive. They belong to districts, and postal `thana` is text; do not invent an upazila or union relationship for them. Allow manual post-office input where the application's address requirements call for it.

Do not renumber IDs, reseed on page requests, or remove retired duplicates as a side effect of adding a selector. If the task includes updating data, read the installed README's correction mappings and preserve the user's intended scope.

Verify a valid hierarchy, wrong-parent selections at every level, nonexistent IDs, malformed input, parent changes, and restoring an existing address. Exercise the combined union/pourashava list if the application uses that optional dataset.
