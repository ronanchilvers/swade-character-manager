# Archetype Character Creation Plan

**Status:** Planned. Not yet implemented.

## Goal

Give players a faster, guided start to character creation by introducing
**archetypes** — pre-defined character templates (e.g. "Barbarian", "Scholar").

Today, clicking **"Create a Character"** goes straight to the Settings form
(`characters_create` → `Settings::create()`), which inserts a blank character with only
core skills seeded at d4; everything else is built up tab-by-tab from nothing.

The new flow lands **"Create a Character"** on an **archetype selection screen**. Picking
an archetype immediately creates and saves a character pre-filled with the archetype's
suggested attribute die types, skills (with die types), hindrances, edges, and a randomly
chosen name, then drops the player into the existing builder to review/edit. A **"Create
from scratch"** option preserves today's blank-start behaviour unchanged.

Archetypes are **defined in JSON files, not in the database** — one file per archetype.
This is a deliberate departure from the Skills/Edges/Hindrances catalogs, which are
DB-aware with a `.php` file fallback and a seeder. Archetypes need **no table and no
seeder**.

### Decisions (confirmed)

- **Template behaviour:** picking an archetype creates & persists the character, then opens
  the builder to edit (vs. merely pre-filling an unsaved form).
- **Names:** auto-pick one name at random from the archetype's list into the name field
  (no reroll control for now).
- **Sources:** core-only for this first version — archetypes reference only core content;
  no source filtering.
- **File layout:** one JSON file per archetype under a new `data/archetypes/` directory.

## How the existing pieces work (reference)

The change reuses three established patterns rather than inventing new ones.

| Concern | File(s) | What to reuse |
| --- | --- | --- |
| Create flow | `config/routes.php` (`characters_create`), `src/Controller/Character/Settings.php` (`create()`/`edit()`) | New character is built as an empty `Entity`, `upsert()`'d, then the player is redirected to `characters_settings/{hash}`. Leave this path intact for "from scratch". |
| Core-skill seeding | `src/Entity/Factory/Character.php` (`afterInsert()`) → `src/Entity/Factory/Skill.php` (`insertCoreForCharacter()`, `CORE_SKILL_DIE`) | `Character::upsert()` already seeds core skills at d4 on insert. The archetype applier then overrides/extends skill dice. |
| Catalog loaders | `src/Service/Data.php` (base: `all()`, `forId()`, `forSources()`), `src/Service/Data/Edges.php`, `config/services.php` (`Manager::addType()`) | New `Archetypes` loader plugs into `Manager` the same way — but reads JSON, not a `.php` file + DB. |
| Card grid UI | `views/campaigns/index.twig`, `resources/sass/components/cards/_base.scss` / `_variables.scss` / `_campaign.scss` / `_index.scss` | `.cards.cards--uniform` + `.card` BEM structure is the model for the archetype grid. |
| Collection persistence | `src/Controller/Character/Hindrances.php`, `src/Controller/Character/Edges.php` | How hindrance/edge selections are written for a character — model the applier's inserts on these. |
| Controller & data tests | `tests/Controller/CampaignsTest.php`, `tests/Service/Data/DataTest.php`, `tests/Support/ControllerTestCase.php` | Mocking/assertion patterns for new controller, applier, and loader tests. |

Key catalog detail: the base `Data` constructor `require`s a single `.php` file
(`data/core/<name>.php`). Archetypes have **no** such file, so the new loader must override
the constructor and read JSON directly.

## Data model

### Archetype JSON files — `data/archetypes/*.json`

One file per archetype. `id` derives from the filename (`barbarian.json` → `barbarian`).
Die faces are stored as integers (`4,6,8,10,12`) to match the DB columns.

Example `data/archetypes/barbarian.json`:

```json
{
  "name": "Barbarian",
  "summary": "A fierce warrior from the wild lands, strong and unrelenting.",
  "description": "Barbarians rely on raw power and instinct over training.",
  "attributes": { "agility": 6, "smarts": 4, "spirit": 6, "strength": 8, "vigor": 8 },
  "skills": [
    { "key": "fighting", "die": 8 },
    { "key": "athletics", "die": 6 },
    { "key": "intimidation", "die": 6 },
    { "key": "survival", "die": 6 }
  ],
  "hindrances": [
    { "key": "loyal", "level": "minor" },
    { "key": "overconfident", "level": "major" }
  ],
  "edges": [
    { "key": "brawny" }
  ],
  "names": ["Brak", "Conan", "Kull", "Thalia", "Vanya"]
}
```

`key` values must match catalog `id`s in `data/core/skills.php`, `hindrances.php`,
`edges.php` — validate against the catalogs during implementation. No DB table, no schema
file, no seeder, no `SchemaConsistencyTest` change.

## Implementation steps

### 1. Archetype catalog loader — `src/Service/Data/Archetypes.php` (new)

Reads `data/archetypes/*.json` and exposes the same shape as other catalogs.

- Extend `App\Service\Data` (so `Manager::getType()` can return it as `Data`), but
  **override the constructor** to NOT call `parent::__construct()`. Instead glob
  `"$dataDir/archetypes/*.json"`, `json_decode` each, derive `id` from the filename, and
  store entries in an own property.
- Override `all()` and `forId(string $id)` to read that property; override `forSources()`
  to just return `all()` (core-only — no filtering yet).
- Implement the abstract `entryFromRow()` as `return []` (only used by DB-aware catalogs).
- It is **not** database-aware: keep it out of `Manager::DATABASE_AWARE_TYPES` so the
  Manager constructs it as `new Archetypes($dataDir)`.

Model JSON decoding/shape handling on `src/Service/Data/Edges.php`; model `all()`/`forId()`
semantics on the base `src/Service/Data.php`.

### 2. Register the loader — `config/services.php`

In the `Manager::class` singleton (~L115–128), add alongside the existing `addType()`
calls:

```php
$manager->addType(Archetypes::class);
```

### 3. Apply-archetype service — `src/Service/Archetype/Applier.php` (new)

Encapsulates "archetype → persisted character" so the controller stays thin and the logic
is unit-testable. Constructor-injects the Character factory and the Skill/Hindrance/Edge
factories (plus the Skills catalog for skill→attribute lookup). One method, e.g.
`applyToNewCharacter(array $archetype): Entity`:

1. Build a new `Entity`: `name` = random pick from `archetype['names']` (`array_rand`, via
   `Filter::noTags`); attributes from `archetype['attributes']` (default `4` when absent);
   `sources = 'core'`; `sharing = 0`. Leave rank/concept at defaults.
2. `Character::upsert($entity)` → insert. This fires `Character::afterInsert()` →
   `Skill::insertCoreForCharacter()`, seeding core skills at d4 and assigning the hash + id.
3. Apply collections to the now-persisted character (id known):
   - **Skills:** for each `archetype['skills']` `{key, die}`, set that skill's die — update
     the existing core-skill row, or insert a non-core row using the linked attribute from
     the Skills catalog. Model on `Skill::insertCoreForCharacter()`.
   - **Hindrances:** insert each `{key, level}` row — model on
     `Controller/Character/Hindrances::index()`.
   - **Edges:** insert each `{key}` row — model on `Controller/Character/Edges::index()`.
4. Return the entity (caller redirects using its `hash`).

Reuse the existing transaction/batch-insert style of `Skill::insertCoreForCharacter()`. If
the codebase already recomputes derived stats (pace/parry/toughness) on attribute/skill
save, reuse that; otherwise leave defaults — the builder recomputes when the player saves
the Attributes tab.

### 4. Selection-screen controller — `src/Controller/Character/Archetypes.php` (new)

Two actions, constructed via container DI like the other `Character/*` controllers (inject
`Manager` and the `Applier`):

- `index()` (GET): fetch `$manager->getType(Archetypes::class)->all()`; render
  `character/archetypes.twig` with the list.
- `create()` (POST): read `$_POST['archetype']`; look it up via `forId()`. On miss, flash
  an error and redirect back to the selection screen. On hit, call
  `Applier::applyToNewCharacter()` and
  `Flight::redirect(Flight::getUrl('characters_settings', ['hash' => $entity->hash]))` —
  the same landing as today's create flow.

### 5. Routes — `config/routes.php`

Inside the existing `/characters` group (behind `MiddlewareAuth`), add:

```php
Flight::route('GET /new', [Archetypes::class, 'index'])->setAlias('characters_new');
Flight::route('POST /new', [Archetypes::class, 'create'])->setAlias('characters_new_apply');
```

Leave `characters_create` (the from-scratch Settings flow) **unchanged** so existing
behaviour and tests stay intact.

### 6. Repoint "Create a Character" links

In `views/home/index.twig` (~L67 and ~L76), change `get_url('characters_create')` →
`get_url('characters_new')`. Sweep the rest of `views/` for other "Create a Character" /
`characters_create` links and repoint those that should hit the new landing screen.

### 7. Selection-screen view — `views/character/archetypes.twig` (new)

Extend `layouts/default.twig` (NOT the builder layout — no character/tabs exist yet).
Render a `.cards.cards--uniform` grid modelled on `views/campaigns/index.twig`:

- One `.card` per archetype: title = `name`, content = `summary` (optionally a compact
  preview of headline attributes/skills), footer = a small `method="post"` form to
  `characters_new_apply` with a hidden `archetype` field and a "Choose" submit button.
- A final **"Create from scratch"** `.card` whose footer links to
  `get_url('characters_create')`.

### 8. Styling — `resources/sass/components/cards/_archetype.scss` (new)

Add archetype-card styles modelled on `resources/sass/components/cards/_campaign.scss`;
import it from `resources/sass/components/cards/_index.scss`. Reuse existing card
tokens/grid from `_base.scss` and `_variables.scss`.

After editing SCSS, **rebuild and commit the compiled CSS** (per AGENTS.md):

```
npm run sass-dev    # or sass-prod for the production build
```

Commit the rebuilt `web/css/` artifact in the same change.

### 9. Tests

Follow existing conventions (`*Test.php` under `tests/`, PHPUnit).

- **`tests/Service/Data/ArchetypesTest.php`** — write temp `*.json` files into a temp
  `archetypes/` dir; assert `all()` and `forId()` return the expected shape and
  `forId('missing')` is null. Model on `tests/Service/Data/DataTest.php`.
- **`tests/Service/Archetype/ApplierTest.php`** — with mocked factories, assert
  `applyToNewCharacter()` sets attributes/name from the archetype, inserts the character,
  and applies skills/hindrances/edges. Model mocking on `tests/Controller/CampaignsTest.php`.
- **`tests/Controller/Character/ArchetypesTest.php`** — `index()` renders
  `character/archetypes.twig` with the catalog list; `create()` POST with a valid id calls
  the applier and redirects to `characters_settings/{hash}`; an unknown id redirects back
  with an error. Use `ControllerTestCase` helpers (`mapRenderToException`,
  `mapRedirectToException`, `mapUrls`, `mapSession`).

Run: `vendor/bin/phpunit --configuration phpunit.xml.dist`.

## Verification

1. `vendor/bin/phpunit --configuration phpunit.xml.dist` — all green, including new tests.
2. `composer run serve` (may already be running at http://localhost:8080); log in.
3. Click **Create a Character** → confirm the archetype grid renders with one card per
   `data/archetypes/*.json` plus a "Create from scratch" card.
4. Pick an archetype → confirm a character is created and you land on its Settings page
   with a name pre-filled. Walk the tabs and confirm **Attributes**, **Skills** (correct
   die types), **Hindrances**, and **Edges** reflect the archetype.
5. Click **Create from scratch** → confirm the original blank Settings flow still works
   unchanged.
6. Confirm no new DB table was introduced (archetypes load purely from JSON).

## Touched files summary

| File | Change |
| --- | --- |
| `data/archetypes/*.json` | New — one JSON file per archetype |
| `src/Service/Data/Archetypes.php` | New — JSON-backed catalog loader |
| `src/Service/Archetype/Applier.php` | New — archetype → persisted character |
| `src/Controller/Character/Archetypes.php` | New — selection screen + apply |
| `views/character/archetypes.twig` | New — archetype card grid |
| `resources/sass/components/cards/_archetype.scss` | New — archetype card styles |
| `config/services.php` | Register `Archetypes` in `Manager` |
| `config/routes.php` | `characters_new`, `characters_new_apply` routes |
| `views/home/index.twig` | Repoint Create-a-Character links to `characters_new` |
| `resources/sass/components/cards/_index.scss` | Import `_archetype` |
| `web/css/*` | Rebuilt compiled CSS artifact |
| `tests/Service/Data/ArchetypesTest.php` | New — loader test |
| `tests/Service/Archetype/ApplierTest.php` | New — applier test |
| `tests/Controller/Character/ArchetypesTest.php` | New — controller test |
