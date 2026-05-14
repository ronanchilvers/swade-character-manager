# Data Path Coverage Plan

## Goal

Cover all data paths used by runtime catalog loading, catalog seeding, persistence factories, schema assumptions, and source/reference catalog files.

The data tests should prove that the app can load the core catalogs from PHP files, prefer database catalog rows when available, fall back safely when database catalog rows are unavailable, seed catalog tables correctly, and persist character/campaign data through factory APIs.

## Current Data Surface

Runtime catalog files:

- [`data/core/hindrances.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/data/core/hindrances.php) with 57 entries.
- [`data/core/skills.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/data/core/skills.php) with 32 entries.
- [`data/core/edges.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/data/core/edges.php) with 134 entries.

Reference/source JSON files:

- [`data/hindrances.json`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/data/hindrances.json)
- [`data/skills.json`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/data/skills.json)
- [`data/edges.json`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/data/edges.json)

Runtime loaders:

- [`src/Service/Data.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Service/Data.php)
- [`src/Service/Data/Manager.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Service/Data/Manager.php)
- [`src/Service/Data/Hindrances.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Service/Data/Hindrances.php)
- [`src/Service/Data/Skills.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Service/Data/Skills.php)
- [`src/Service/Data/Edges.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Service/Data/Edges.php)

Catalog seeders:

- [`src/Service/Data/HindranceCatalogSeeder.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Service/Data/HindranceCatalogSeeder.php)
- [`src/Service/Data/SkillCatalogSeeder.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Service/Data/SkillCatalogSeeder.php)
- [`src/Service/Data/EdgeCatalogSeeder.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Service/Data/EdgeCatalogSeeder.php)
- [`scripts/seed.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/scripts/seed.php)

Persistence factories:

- Generic factory base: [`src/Entity/Factory.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Entity/Factory.php)
- Character and user factories: [`src/Entity/Factory/Character.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Entity/Factory/Character.php), [`src/Entity/Factory/User.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Entity/Factory/User.php)
- Campaign factories: [`src/Entity/Factory/Campaign.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Entity/Factory/Campaign.php), [`src/Entity/Factory/Campaign/Member.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Entity/Factory/Campaign/Member.php)
- Character selection and sheet factories: [`src/Entity/Factory/Hindrance.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Entity/Factory/Hindrance.php), [`src/Entity/Factory/Skill.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Entity/Factory/Skill.php), [`src/Entity/Factory/Edge.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Entity/Factory/Edge.php), [`src/Entity/Factory/Gear.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Entity/Factory/Gear.php), [`src/Entity/Factory/Weapon.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Entity/Factory/Weapon.php)

## Current Coverage Snapshot

Existing data-path tests include:

- Catalog loaders under [`tests/Service/Data`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/tests/Service/Data).
- Catalog seeders for upsert and duplicate key rejection.
- Entity factories under [`tests/Entity/Factory`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/tests/Entity/Factory).
- Character sheet presenter coverage under [`tests/Character/SheetTest.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/tests/Character/SheetTest.php).

Current gap:

- The full suite is failing in factory transaction tests, so factory coverage must be stabilised before new data-path assertions are trusted.

## Plan

### 1. Stabilise factory data tests

Start with the failing factory tests from the baseline plan.

Cover or realign:

- whether `Factory::insert()` wraps `beforeInsert()`, SQL insert, id assignment, and `afterInsert()` in one transaction.
- whether `afterInsert()` errors roll back and return a failed `Result`.
- whether `Campaign::afterInsert()` creates owner membership in the intended transaction boundary.
- whether `Character::afterInsert()` creates core skills in the intended transaction boundary.

Exit criteria:

- All existing factory tests pass.
- Transaction behavior is explicit and covered.

### 2. Cover the base data loader and manager

Add or extend tests for [`src/Service/Data.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Service/Data.php) and [`src/Service/Data/Manager.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Service/Data/Manager.php).

Cover:

- `Data::all()` returns the `entries` array from the expected PHP file.
- `Data::forId()` returns the matching entry.
- `Data::forId()` returns null for unknown ids.
- `Manager::getType()` rejects unregistered classes.
- `Manager::getType()` memoizes loader instances.
- `Manager::getType()` passes `SimplePdo` only to database-aware loaders.
- `Manager::addType()` returns `$this` for fluent service registration.

Exit criteria:

- Loader registration and lookup behavior is covered independently of specific catalogs.

### 3. Expand catalog loader coverage

Extend `HindrancesTest`, `SkillsTest`, and `EdgesTest`.

Shared coverage for all three:

- DB row path returns mapped data when rows exist.
- File fallback path is used when no PDO is provided.
- File fallback path is used when `fetchAll()` throws.
- File fallback path is used when `fetchAll()` returns an empty array.
- Invalid, blank, or non-string JSON column values decode to empty arrays.
- `source` is preserved from DB rows.
- `forId()` works against both DB-backed and file-backed data.

Hindrance-specific:

- `forBuilder()` adds `effects_by_level` without replacing raw `effects`.
- malformed effect rows are ignored when grouping.
- minor and major effects can both be grouped for one hindrance.

Skill-specific:

- `core()`, `nonCore()`, and `attributeForSkill()` agree after DB-backed data is loaded.
- cached core/non-core collections reset when DB entries are loaded.
- `arcane_background` maps null and string values correctly.

Edge-specific:

- `repeatable` maps truthy and falsy database values correctly.
- categories are preserved for controller grouping.
- known repeatable edges from the live catalog remain repeatable.

Exit criteria:

- Every runtime branch in the three catalog loaders is covered.

### 4. Expand catalog seeder coverage

Extend seeder tests for hindrances, skills, and edges.

Shared coverage:

- `seedFile()` rejects missing files.
- `seedFile()` rejects files that do not return an array with `entries`.
- `seedEntries()` rejects blank source strings.
- `seedEntries()` rejects non-array entries.
- `seedEntries()` rejects duplicate keys before opening a transaction.
- `seedEntries()` runs all writes in one transaction.
- `seedEntries()` returns the number of written rows.
- complex fields are encoded as JSON.
- JSON encoding failures throw a `RuntimeException`.
- upsert SQL does not delete rows absent from the source file.

Skill-specific:

- required `id`, `name`, `linked_attribute`, and boolean `core_skill`.
- `arcane_background` accepts null or string and rejects other types.

Edge-specific:

- required `id`, `name`, `category`, and boolean `repeatable`.

Hindrance-specific:

- required `id` and `name`.
- optional `levels`, `requirements`, `effects`, `notes`, and `source_pages` default to empty arrays where applicable.

Exit criteria:

- Every seeder validation and write branch is covered without needing a live database.

### 5. Cover seed CLI dispatch

Add a small process-level or isolated test around [`scripts/seed.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/scripts/seed.php) only if it can be done without a live database dependency.

Minimum useful coverage:

- missing arguments prints usage and exits non-zero.
- unsupported type exits non-zero.
- invalid type/source format exits non-zero.
- valid type/source resolves to `data/<source>/<type>.php`.

If testing the script requires intrusive changes, extract the argument validation and filename resolution into a small testable support class first.

Exit criteria:

- CLI argument validation is covered, or the reason for leaving it untested is documented.

### 6. Add source data integrity tests

Create `tests/Service/Data/CoreCatalogIntegrityTest.php`.

Cover the runtime PHP catalog files:

- Each file returns an array with `entries`.
- Every entry has a non-blank unique `id`.
- Entries are sorted by display name where that is expected by the UI, or the test documents that runtime sorting happens elsewhere.
- Hindrances include valid `levels` values when present.
- Skills include valid `linked_attribute` values and boolean `core_skill`.
- Edges include non-blank `category` and boolean `repeatable`.
- `requirements`, `effects`, `notes`, and `source_pages` are arrays when present.
- Every `source_pages` value is numeric.

Cover reference JSON files:

- JSON files parse successfully.
- JSON entry ids are unique.
- JSON ids match the corresponding PHP catalog ids, or any intentional mismatch is documented.

Exit criteria:

- Data file regressions fail tests before they reach runtime loaders.

### 7. Expand persistence factory coverage

Existing factory tests are a strong base. After the baseline is green, fill gaps around newer data paths.

Cover:

- `Campaign::allWithSummary()` row mapping.
- `Campaign::forMemberUser()` and `Campaign::forUser()` query parameters.
- `Campaign::invitePath()` route format.
- `Campaign::beforeUpdate()` hash regeneration during campaign link reset.
- `Campaign\Member::forUser()`, `forCampaign()`, `isMember()`, `ensureMember()`, and `leaveCampaign()` for success, idempotent, blocked, and failure paths.
- `Character::forCampaign()`, `forCampaignAndUser()`, `forUserWithoutCampaign()`, `joinCampaign()`, and `leaveCampaign()`.
- `Gear::forCharacter()` and `Weapon::forCharacter()` ordering.
- `Hindrance::forCharacter()`, `Skill::forCharacter()`, and `Edge::forCharacter()` query paths in addition to sync paths.

Exit criteria:

- All factory read and write helpers used by controllers have direct tests.

### 8. Add schema consistency checks

Add a low-cost test that inspects schema files as text, not by applying destructive SQL.

Cover:

- Base schema files exist in expected order: `010_users.sql` through `070_weapons.sql`.
- Additive migration files are timestamp-prefixed.
- Catalog migration files define `hindrance_catalog`, `skill_catalog`, and `edge_catalog` tables.
- Character selection tables use stable `*_key` columns.
- Gear and weapon tables include character foreign keys.
- Campaign migrations define `campaigns`, `campaign_members`, and `character_campaign`.

Exit criteria:

- Accidental schema file removal or naming drift is caught without touching a database.

## Verification

Run focused suites first:

```bash
vendor/bin/phpunit --configuration phpunit.xml.dist tests/Service/Data
vendor/bin/phpunit --configuration phpunit.xml.dist tests/Entity/Factory
vendor/bin/phpunit --configuration phpunit.xml.dist tests/Character
```

Then run the full suite:

```bash
vendor/bin/phpunit --configuration phpunit.xml.dist
```

Generate data-path coverage after the suite is green:

```bash
php -d xdebug.mode=coverage vendor/bin/phpunit --configuration phpunit.xml.dist --coverage-filter src --coverage-text=/tmp/swade-data-coverage.txt --show-uncovered-for-coverage-text
```

## Out Of Scope

- JavaScript and CSS behavior.
- Browser or visual verification.
- Live database migration execution.
- Installing new test or coverage packages.
