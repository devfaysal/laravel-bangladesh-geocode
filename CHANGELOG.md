# Changelog

All notable changes to `laravel-bangladesh-geocode` will be documented in this file

## Unreleased

- Add 1,357 bundled post office records across all 64 districts, a post office migration and model, and district relationships. The postal dataset is not an exhaustive directory of every post office.
- Require Laravel 12.69+ or 13.30+ and declare the console and database dependencies.
- Add production confirmation and `--force` support to package commands, propagate failures, and support custom Composer vendor directories.
- Use anonymous migration classes to allow setup, rollback and reinstall within one PHP process, preserving migration filenames and schema definitions.
- Make seeders repeatable using Laravel's native upsert; setup and updates seed divisions, districts, upazilas, unions and post offices in one transaction.
- Keep `BangladeshGeocode:addnew` and reuse `BangladeshGeocodeSeeder` to refresh all five standard datasets, replacing duplicated update data. Older installations without the post office table must run setup before this command.
- Include post offices in the main seeder while retaining its separate `db:seed` command for postal-only refreshes. Pourashavas remain optional.
- Correct three postal codes and use the seeded Shyampur URL when updating existing installations.
- Remove surrounding whitespace from eleven upazila and union values, and validate trimmed values across all bundled datasets.
- Correct Charfesson's union list to 21 distinct unions: retain Awajpur at ID 2352, replace duplicate ID 2353 with Ahammadpur, and add Omarpur at ID 4550. Existing application references to ID 2353 will resolve to Ahammadpur after reseeding.
- Retire duplicate union IDs 2546 (Talimpur, keep 2540) and 3686 (Bhojoanpur, keep 3685) from the bundled seeder without renumbering other records. Existing installations retain these rows for manual cleanup; retired IDs will not be reused.
- Validate unique dataset IDs, parent references, and update consistency through automated tests.
- Test Laravel 12 and 13 across supported PHP versions in CI and run dependency audits, formatting checks, and static analysis. Target the default `main` branch in workflow triggers and the README test badge.
- Add MySQL 8.4 integration tests for Laravel 12 and 13, with an isolated test database and SQLite retained as the local default.
- Correct the README relationship example and document upgrades and reseeding behavior.
- Improve package search metadata, document cascading Bangladesh address dropdowns, and ship optional Laravel Boost guidelines and an address-dropdown skill without adding runtime dependencies.

## 1.0.0 - 201X-XX-XX

- initial release
