# Catalog Database Migration Notes

## Goal

Move file-backed catalog data into database catalog tables without changing how existing characters store selections.

The hindrance migration is the reference implementation. Use the same pattern for edges and skills:

- keep the existing character selection tables unchanged
- add a separate catalog table
- seed from `data/<source>/<type>.php`
- keep the string keys stable
- make `source` a real indexed string column
- keep complex rule data as JSON until there is a real query need to normalize it

## Hindrance Pattern

The existing `hindrances` table is not a catalog table. It stores character selections:

- `hindrance_character_id`
- `hindrance_key`
- `hindrance_level`

To preserve characters, the migration added a separate `hindrance_catalog` table instead of renaming or replacing `hindrances`.

Important implementation pieces:

- `schema/migrations/20260507174532_create_hindrance_catalog.sql`
  - non-destructive `CREATE TABLE IF NOT EXISTS`
  - unique key on `hindrance_catalog_key`
  - indexed `hindrance_catalog_source`
  - JSON columns for structured data
- `src/Service/Data/Hindrances.php`
  - accepts optional `SimplePdo`
  - reads DB rows first
  - falls back to `data/core/hindrances.php` if the table is missing or empty
  - returns the same array shape as the old file loader
- `src/Service/Data/HindranceCatalogSeeder.php`
  - validates source data
  - rejects duplicate keys
  - upserts by catalog key
  - never deletes catalog rows missing from the source file
- `scripts/seed.php`
  - generic CLI wrapper: `php scripts/seed.php <type> <source>`
  - currently dispatches `hindrances` to `HindranceCatalogSeeder`
  - `composer run seed:hindrances` calls `php scripts/seed.php hindrances core`

## Seeder Contract

Source files live under source-specific folders:

```text
data/<source>/<type>.php
```

Examples:

```bash
php scripts/seed.php hindrances core
php scripts/seed.php edges core
php scripts/seed.php skills core
```

The `<source>` string should be lowercase words separated by hyphens, for example:

- `core`
- `fantasy-companion`
- `scifi-companion`

Seeder behavior should stay consistent across catalog types:

- load exactly one PHP source file for the requested type/source
- require an array with an `entries` array
- validate required fields before writing
- reject duplicate keys inside the source file
- write inside a transaction
- upsert existing rows by catalog key
- update existing metadata when the source file changes
- do not delete rows that are absent from the source file

## Database Shape

Use separate catalog tables rather than changing the existing character tables:

- `edge_catalog`
- `skill_catalog`

Do not reuse `edges` or `skills` for catalog data. Those tables already store character state.

Recommended shared fields:

```sql
<type>_catalog_id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT
<type>_catalog_key          VARCHAR(64) NOT NULL
<type>_catalog_source       VARCHAR(64) NOT NULL DEFAULT 'core'
<type>_catalog_name         VARCHAR(128) NOT NULL
<type>_catalog_summary      TEXT NOT NULL
<type>_catalog_requirements JSON NOT NULL
<type>_catalog_effects      JSON NOT NULL
<type>_catalog_notes        JSON NOT NULL
<type>_catalog_source_pages JSON NOT NULL
<type>_catalog_created      DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
<type>_catalog_updated      DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6)
```

Indexes:

- primary key on `<type>_catalog_id`
- unique key on `<type>_catalog_key`
- index on `<type>_catalog_source`
- index on `<type>_catalog_name`

Type-specific fields:

- edges need `edge_catalog_category VARCHAR(64) NOT NULL` and `edge_catalog_repeatable TINYINT(1) UNSIGNED NOT NULL DEFAULT 0`
- skills need `skill_catalog_linked_attribute VARCHAR(20) NOT NULL`, `skill_catalog_core_skill TINYINT(1) UNSIGNED NOT NULL DEFAULT 0`, and nullable `skill_catalog_arcane_background VARCHAR(64)`
- hindrances need `hindrance_catalog_levels JSON NOT NULL`

Keep catalog keys globally unique across sources unless the application is deliberately changed to select by `(source, key)`.

## Loader Pattern

For each catalog service:

1. Accept optional `SimplePdo` in the constructor.
2. Call the base `Data` constructor with the core fallback folder for that type.
3. Override `all()`.
4. If DB rows are available, map them back to the existing file-backed array shape.
5. If the table is missing, the query fails, or no rows exist, return `parent::all()`.

That fallback keeps local development and tests working before migrations and seeders have been run.

The returned shape should match existing callers:

- edges still expose `id`, `name`, `category`, `summary`, `repeatable`, `requirements`, `effects`, `notes`, and `source_pages`
- skills still expose `id`, `name`, `linked_attribute`, `core_skill`, `arcane_background`, `summary`, `requirements`, `effects`, `notes`, and `source_pages`
- all catalog rows should also expose `source`

After adding DB support to `Skills`, clear or rebuild the cached `core` and `nonCore` arrays from `all()` so `core()`, `nonCore()`, and `attributeForSkill()` continue to agree.

## Implementation Steps For Edges And Skills

1. Move the source file into `data/core/<type>.php` if it has not already moved.
2. Add a timestamped migration under `schema/migrations/` for `<type>_catalog`.
3. Add `<Type>CatalogSeeder` under `src/Service/Data/`.
4. Update `scripts/seed.php` to dispatch the new type.
5. Add a Composer shortcut if useful, for example `seed:edges` or `seed:skills`.
6. Update the matching data service to read from the DB and fall back to `data/core`.
7. Update `App\Service\Data\Manager` only if constructor wiring needs special handling for the new DB-aware service.
8. Add focused PHPUnit tests for:
   - DB rows decode into the expected catalog array shape
   - source is preserved
   - seeding upserts rows
   - duplicate source keys are rejected
   - no delete is called during seeding
   - existing edge/skill consumer behavior still works

## Verification

Run focused tests first, then the full suite:

```bash
vendor/bin/phpunit --configuration phpunit.xml.dist tests/Service/Data
vendor/bin/phpunit --configuration phpunit.xml.dist
```

If a Composer script is added or changed, refresh `composer.lock` with:

```bash
/Users/ronan/bin/composer update --lock
```

Then validate:

```bash
/Users/ronan/bin/composer validate --no-check-publish
```

