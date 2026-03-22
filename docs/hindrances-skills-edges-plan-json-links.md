# Rule Catalog and Character Selections Plan: JSON Catalog with String Links

## Summary
Use the JSON files in `data/` as the only catalog source of truth and store the embedded entry IDs directly in character linking tables.

- Keep `data/hindrances.json`, `data/skills.json`, and `data/edges.json` canonical.
- Do not import catalog rows into database reference tables.
- Store selected entry codes such as `all_thumbs`, `alertness`, and `notice` directly against characters.
- Resolve names, summaries, requirements, and `effects.details` text by loading the JSON catalog into indexed PHP structures.

This keeps content management simple and makes adding new rules mostly a data-file change.

## Why This Is Viable
- The current catalog is small: 57 hindrances, 32 skills, and 134 edges.
- The three JSON files are only about 188 KB combined, so loading and indexing them in PHP is inexpensive.
- The main tradeoff is not request-time performance. It is losing database-enforced foreign keys to catalog rows and moving catalog filtering into PHP.
- For this project’s current size, that tradeoff is acceptable if the catalog IDs are treated as stable public keys.

## Schema Changes
Add three linking tables and keep `characters` as the parent record.

### Migration SQL
Example migration file: `schema/002_character_options.sql`

```sql
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `character_hindrances` (
    character_hindrance_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    character_hindrance_character BIGINT UNSIGNED NOT NULL,
    character_hindrance_code VARCHAR(64) NOT NULL,
    character_hindrance_level ENUM('minor', 'major') NOT NULL,
    character_hindrance_points TINYINT UNSIGNED NOT NULL DEFAULT 0,
    character_hindrance_created DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    character_hindrance_updated DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (character_hindrance_id),
    UNIQUE KEY uniq_character_hindrance (character_hindrance_character, character_hindrance_code),
    KEY idx_character_hindrance_character (character_hindrance_character)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `character_skills` (
    character_skill_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    character_skill_character BIGINT UNSIGNED NOT NULL,
    character_skill_code VARCHAR(64) NOT NULL,
    character_skill_die_sides TINYINT UNSIGNED NOT NULL,
    character_skill_die_modifier TINYINT NOT NULL DEFAULT 0,
    character_skill_specialization VARCHAR(128) NULL,
    character_skill_created DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    character_skill_updated DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (character_skill_id),
    UNIQUE KEY uniq_character_skill (character_skill_character, character_skill_code, character_skill_specialization),
    KEY idx_character_skill_character (character_skill_character)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `character_edges` (
    character_edge_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    character_edge_character BIGINT UNSIGNED NOT NULL,
    character_edge_code VARCHAR(64) NOT NULL,
    character_edge_source VARCHAR(32) NULL,
    character_edge_created DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    character_edge_updated DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (character_edge_id),
    UNIQUE KEY uniq_character_edge (character_edge_character, character_edge_code),
    KEY idx_character_edge_character (character_edge_character)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Notes on Constraints
- Keep `VARCHAR(64)` for codes to match the current JSON `id` values with room for future additions.
- Do not add foreign keys to catalog data because the catalog lives in JSON, not SQL.
- Keep character-side indexes only on the character ID because most reads will fetch selections for one character at a time.

## Detailed Implementation Steps
### 1. Lock the JSON IDs as stable keys
- Treat every `entries[].id` value in `data/*.json` as immutable once it is referenced by saved characters.
- Add a validation rule to reject duplicate IDs within each file and ideally across all three files if cross-type ambiguity is undesirable.
- Document that renaming a JSON ID is a data migration, not a content edit.

### 2. Add a catalog service layer
- Create a service such as `src/Catalog/Repository.php`.
- Load `data/hindrances.json`, `data/skills.json`, and `data/edges.json`.
- Build in-memory indexes:
  - `byType[type][code]`
  - `allByType[type]`
  - `edgesByCategory[category]`
  - `skillsByAttribute[linked_attribute]`
- Normalize access so controllers never read JSON directly.

Example:

```php
<?php

declare(strict_types=1);

namespace App\Catalog;

final class Repository
{
    private array $byType = [];
    private array $allByType = [];

    public function __construct(string $dataDir)
    {
        $this->loadFile($dataDir . '/hindrances.json', 'hindrance');
        $this->loadFile($dataDir . '/skills.json', 'skill');
        $this->loadFile($dataDir . '/edges.json', 'edge');
    }

    public function all(string $type): array
    {
        return $this->allByType[$type] ?? [];
    }

    public function find(string $type, string $code): ?array
    {
        return $this->byType[$type][$code] ?? null;
    }

    private function loadFile(string $file, string $type): void
    {
        $json = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        $this->allByType[$type] = $json['entries'];

        foreach ($json['entries'] as $entry) {
            $this->byType[$type][$entry['id']] = $entry;
        }
    }
}
```

### 3. Register the catalog service in DI
Update `config/services.php` so controllers and factories can depend on the catalog service.

Example:

```php
use App\Catalog\Repository as CatalogRepository;

$container->singleton(
    CatalogRepository::class,
    function () {
        return new CatalogRepository(__DIR__ . '/../data');
    }
);
```

If request-level caching is enough, the singleton is sufficient. If reload cost ever matters, add APCu or filemtime-aware caching later.

### 4. Add persistence factories for the new linking tables
- Create `src/Entity/Factory/CharacterHindrance.php`
- Create `src/Entity/Factory/CharacterSkill.php`
- Create `src/Entity/Factory/CharacterEdge.php`

These should follow the existing `Factory` base class so table naming, prefixing, `find()`, and `upsert()` work consistently.

Example for hindrances:

```php
<?php

declare(strict_types=1);

namespace App\Entity\Factory;

use App\Entity;
use App\Entity\Factory;
use Respect\Validation\ValidatorBuilder as v;

class CharacterHindrance extends Factory
{
    public function getValidationRules(): array
    {
        return [
            'character' => v::intVal()->greaterThan(0),
            'code' => v::stringType()->notBlank(),
            'level' => v::in(['minor', 'major']),
            'points' => v::intVal()->between(0, 4),
        ];
    }

    public function forCharacter(int $characterId): array
    {
        return $this->find(
            $this->prefix('character') . ' = ?',
            [$characterId],
        );
    }
}
```

Add the new factory classes to the `$classes` array in `config/services.php`.

### 5. Add a character options application service
- Create a service that coordinates DB rows and JSON metadata rather than putting all of that logic in the controller.
- Responsibilities:
  - list available hindrances, skills, and edges
  - validate that submitted codes exist in the catalog
  - save character selections
  - return display-ready metadata for selected entries, including `effects.details`

Example skeleton:

```php
<?php

declare(strict_types=1);

namespace App\Catalog;

use App\Entity\Factory\CharacterEdge;
use App\Entity\Factory\CharacterHindrance;
use App\Entity\Factory\CharacterSkill;

final class CharacterOptions
{
    public function __construct(
        private Repository $catalog,
        private CharacterHindrance $hindrances,
        private CharacterSkill $skills,
        private CharacterEdge $edges,
    ) {
    }

    public function availableHindrances(): array
    {
        return $this->catalog->all('hindrance');
    }

    public function findHindrance(string $code): ?array
    {
        return $this->catalog->find('hindrance', $code);
    }
}
```

### 6. Update the controller flow
- Keep `src/Controller/Character.php` responsible for HTTP concerns only.
- On the hindrances step:
  - load the character
  - load available hindrances from the catalog service
  - on POST, validate selected codes and levels
  - save `character_hindrances` rows
- Add later steps for skills and edges using the same pattern.

Example POST flow:

```php
if ("POST" == Flight::request()->getMethod()) {
    $selected = $_POST['hindrances'] ?? [];

    foreach ($selected as $row) {
        $entry = $catalog->find('hindrance', $row['code']);
        if (!$entry) {
            $errors['hindrances'][] = 'Unknown hindrance selected';
            continue;
        }

        $entity = new Entity([
            'character' => $character->id,
            'code' => $row['code'],
            'level' => $row['level'],
            'points' => $row['level'] === 'major' ? 2 : 1,
        ]);

        if (!$hindranceFactory->validate($entity)) {
            $hindranceFactory->upsert($entity);
        }
    }
}
```

The exact POST shape can be decided in the template layer, but use arrays keyed by code or indexed rows so multiple hindrances can be submitted cleanly.

### 7. Surface effect details as user guidance
- Fetch selected rows from `character_hindrances`, `character_skills`, and `character_edges`.
- For each row, resolve the JSON catalog entry by type and code.
- Build a display model that includes:
  - entry name
  - summary
  - selected level where relevant
  - `effects.details` strings for the chosen level or for the base entry
- Do not calculate modifiers, generate roll instructions, or derive game-state changes in PHP.
- The UI should present the details text as a reminder to the player; the player decides how to roll and apply the rule.

Example:

```php
public function hindranceDetailsForCharacter(int $characterId): array
{
    $details = [];

    foreach ($this->hindrances->forCharacter($characterId) as $selection) {
        $entry = $this->catalog->find('hindrance', $selection->code);
        if (!$entry) {
            continue;
        }

        foreach ($entry['effects'] as $effect) {
            if (($effect['level'] ?? null) && $effect['level'] !== $selection->level) {
                continue;
            }

            if (!empty($effect['details'])) {
                $details[] = [
                    'source_type' => 'hindrance',
                    'source_code' => $selection->code,
                    'source_name' => $entry['name'],
                    'level' => $selection->level,
                    'detail' => $effect['details'],
                ];
            }
        }
    }

    return $details;
}
```

This becomes the source for UI text such as "Subtract 2 from Vigor rolls made to resist Fatigue." The app displays it, and the player applies it manually.

### 8. Add JSON integrity checks
- Add automated validation for:
  - valid JSON
  - expected root keys
  - unique `id` values
  - valid `entry_type`
  - presence of `effects[].details` where mechanical guidance is expected to be shown to the user
- Add drift checks to ensure every stored DB code still exists in JSON.

Example test cases:

```php
$this->assertNotNull($catalog->find('edge', 'alertness'));
$this->assertNull($catalog->find('skill', 'does_not_exist'));
```

### 9. Backfill and migration strategy
- Apply the new schema migration.
- Leave existing `characters` rows untouched.
- No backfill is required unless the app already stores hindrances, skills, or edges elsewhere.
- If JSON IDs ever change, ship a one-off SQL migration to rename the codes in the character linking tables.

Example:

```sql
UPDATE character_edges
SET character_edge_code = 'improved_nerves_of_steel'
WHERE character_edge_code = 'better_nerves_of_steel';
```

## Code-Level Acceptance Criteria
- A service can list all hindrances, skills, and edges without querying catalog tables.
- A character can save selected hindrances, skills, and edges using JSON IDs as keys.
- A character’s saved selections can be rehydrated into full display records for Twig templates.
- The UI can render `effects.details` text for selected hindrances, skills, and edges without interpreting the rules.
- Invalid or deleted codes are detected by tests before they silently break saved characters.

## Performance and Tradeoffs
- This is fast enough for the current app size.
- String-based link columns are larger and slightly slower than integer foreign keys, but the practical difference is negligible here.
- The real cost is operational:
  - no database-level referential integrity to the catalog
  - more application logic to filter catalog entries and present rule text
  - more care required when renaming JSON IDs
- If this grows into a much larger catalog or needs heavy SQL reporting, the project can still add imported catalog tables later while keeping the character-side string codes stable.

## Test Plan
- JSON validation tests: schema shape, unique IDs, and required fields.
- Catalog repository tests: list all by type, fetch by code, category filtering, and skill attribute filtering.
- Persistence tests: unique constraints, update behavior, and character deletion behavior.
- Controller/integration tests for hindrance submission and redisplay.
- Display tests:
  - `alertness` shows its `effects.details` text to the user
  - `all_thumbs` shows its `effects.details` text to the user
  - `bad_eyes` shows the correct `minor` or `major` detail text based on the saved level
- Drift tests that fail when a code in DB no longer exists in JSON.

## Assumptions
- The JSON files remain global-only and are edited through version control, not by user CRUD.
- Catalog entry IDs are stable and treated as permanent keys.
- Roll resolution is out of scope for now; the app only surfaces rule text from `effects.details`.
- The first implementation will focus on hindrances, then apply the same pattern to skills and edges.
