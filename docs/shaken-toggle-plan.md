# Shaken Toggle Plan

**Status:** Implemented on branch `shaken-toggle`.
**Branch:** `shaken-toggle`

> **Implementation note:** The SCSS used the **wrapper approach** (a `.sheet__rails`
> flex-column container) rather than the magic-number absolute offset described in step 5.
> Visual verification showed the Wounds rail is nearly full-column height, so a stacked
> absolute offset would have been fragile; the wrapper keeps Shaken and Wounds in line and
> stacked structurally. Verified at desktop (1200px) and mobile (375px) widths.

## Goal

Add a **Shaken** on/off toggle to the character sheet, styled to match the existing
Wounds / Fatigue rail. The toggle is a single circle with no text inside it, sits in a
new box just above and in line with the Wounds rail, and persists its on/off state to
the backend the same way wounds, fatigue, incapacitated, and bennies already do.

In SWADE, *Shaken* is a binary condition (you are or you aren't), so it behaves exactly
like the existing **Inc** (`incapacitated`) toggle — a boolean stored as `0`/`1` — not
like the multi-step wounds/fatigue rails.

## How the existing state toggles work (reference)

The condition rails are a complete, repeatable pattern. `incapacitated` is the closest
match for Shaken because it is boolean.

| Layer | File | What it does |
| --- | --- | --- |
| Schema (rebuild) | `schema/020_characters.sql` | `character_incapacitated TINYINT UNSIGNED NOT NULL DEFAULT 0` |
| Migration | `schema/migrations/*.sql` | Incremental `ALTER TABLE` for existing databases |
| Presenter | `src/Character/Sheet.php` → `buildState()` | Maps the column to `sheet.state.incapacitated` (cast to bool) |
| View | `views/character/sheet.twig` (rail, ~L199–214) | Renders the circle, adds `--selected` when set, wires `data-rail="incapacitated"` |
| JS | `web/javascript/sheet.js` → `wireBooleanRailItem()` | Click/keyboard toggles the class and `POST`s `{ incapacitated: 1|0 }` |
| Controller | `src/Controller/Character/Sheet.php` → `updateState()` | Whitelisted via `STATE_FIELDS`, clamps with `max(0, (int) …)`, persists via factory `update()` |
| Persistence | `src/Entity/Factory.php` → `update()` | Writes every entity column (loaded via `SELECT *`) back to the row |

Key detail: `Factory::update()` persists whatever columns are present on the entity, and
entities are hydrated with `SELECT *`. So the **database column must exist** for the value
to round-trip — there is no separate column allow-list in PHP, but the controller's
`STATE_FIELDS` constant is the write allow-list for the `updateState` endpoint.

No new route or controller method is required: the existing
`POST /characters/sheet/{hash}/state` endpoint and the `saveState()` JS helper already
handle arbitrary state fields.

## Implementation steps

### 1. Database column

**`schema/020_characters.sql`** (destructive rebuild — keep it the source of truth):
add a column alongside the other session-state columns, e.g. directly after
`character_incapacitated`:

```sql
character_shaken        TINYINT UNSIGNED NOT NULL DEFAULT 0,
```

**New migration** `schema/migrations/20260625072042_add_character_shaken.sql`
(follow the existing timestamped pattern, e.g. `20260526093000_add_character_share_token.sql`):

```sql
SET NAMES utf8mb4;

ALTER TABLE characters
    ADD COLUMN character_shaken TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER character_incapacitated;
```

> Migrations are applied in filename order; pick a timestamp later than the most recent
> existing migration. Regenerate the timestamp at implementation time if needed.

### 2. Presenter — expose `sheet.state.shaken`

**`src/Character/Sheet.php`**, in `buildState()` (currently L48–57), add a boolean entry
mirroring `incapacitated`:

```php
'shaken'        => ((int) ($character->shaken ?? 0)) > 0,
```

### 3. Controller — allow the field through

**`src/Controller/Character/Sheet.php`**, extend the write allow-list (L24):

```php
private const STATE_FIELDS = ['wounds', 'fatigue', 'incapacitated', 'shaken', 'bennies'];
```

The existing `updateState()` loop (`max(0, (int) $payload[$field])`) already coerces the
incoming `0`/`1` correctly — no further controller changes needed.

### 4. View — new rail box above Wounds

**`views/character/sheet.twig`**, in the `.sheet__middle` block (L198+), add a new
`<aside>` **immediately before** the existing `sheet__rail--wounds` aside so it stacks
above it. The box reuses the rail label and list item classes, contains a single empty,
boolean toggle circle (no text, accessible label only):

```twig
<aside class="sheet__rail sheet__panel sheet__rail--shaken">
    <span class="sheet__rail__label">Shaken</span>
    <ul class="sheet__rail__list">
        <li class="sheet__rail__list__item sheet__rail__list__item--shaken{% if sheet.state.shaken %} sheet__rail__list__item--selected{% endif %}"
            aria-label="Shaken"
            {% if not read_only %}data-rail="shaken"{% endif %}>&nbsp;</li>
    </ul>
</aside>
<aside class="sheet__rail sheet__panel sheet__rail--wounds">
    {# …existing wounds/fatigue rail unchanged… #}
</aside>
```

Notes:
- `&nbsp;` keeps the circle the same height as the text-bearing circles without showing a glyph.
- `aria-label="Shaken"` gives the empty circle an accessible name; the JS also sets
  `role="button"` / `aria-pressed` (see step 6).
- DOM order (shaken before wounds) makes the mobile/`phablet` layout — where rails switch to
  relative flow — stack Shaken above Wounds for free.

### 5. SCSS — position and style the new box

**`resources/sass/components/character/sheet/_rail.scss`**. The `--wounds` modifier is
absolutely positioned within `.sheet__middle` (`top: -3rem; right: -0.5rem`). Add a
`--shaken` modifier that sits in line (same right edge) just above the wounds box on
desktop, and falls back to normal flow at `phablet`:

```scss
&--shaken {
    top: -9rem;   // tune so it rests directly above sheet__rail--wounds
    right: -0.5rem;

    @include breakpoints.at('phablet') {
        top: auto;
        right: auto;
    }
}
```

The single circle automatically inherits the shared `sheet__rail__list li` styling
(3rem circle, 2px border, 50% radius) and `&__item` hover/`--selected` states, so it
matches the wounds/fatigue circles with no extra rules. The exact `top` offset is a
visual decision — **verify in the browser** (see Verification) and adjust; consider that
the wounds rail already pokes up into the gap above `.sheet__middle`.

> Alternative if stacked absolute offsets prove fragile across breakpoints: wrap both
> asides in a single absolutely-positioned `.sheet__rails` flex-column container and let
> the two boxes stack with a `gap`, moving the positioning off the individual `--wounds`
> rail. This is a slightly larger refactor; prefer the minimal modifier above unless the
> offset is hard to stabilise.

After editing SCSS, **rebuild and commit the compiled CSS** (per AGENTS.md):

```
npm run sass-dev    # or sass-prod for the production build
```

The rail lives in `resources/sass/sheet.scss` → compiles to `web/css/sheet.css`
(+ `.map`). Commit the rebuilt artifact in the same change.

### 6. JS — wire the boolean toggle

**`web/javascript/sheet.js`**. `wireBooleanRailItem()` already does exactly what Shaken
needs (toggle `--selected`, set `aria-pressed`, `POST { field: 1|0 }`). Add one call next
to the existing `incapacitated` wiring (L145):

```js
wireBooleanRailItem('shaken');
```

No new JS function is required.

### 7. Tests

- **`tests/Controller/Character/SheetControllerTest.php`** →
  `testUpdateStateClampsKnownFieldsAndReturnsJsonSuccess` (L291): add `'shaken' => 1` to
  the posted payload and assert `1 === $entity->shaken` in the `update()` callback. This
  guards the `STATE_FIELDS` allow-list so a future edit can't silently drop the field.
- **`tests/Character/SheetTest.php`** (presenter): `buildState()` currently has no direct
  coverage. Optional but recommended — add a small test asserting `sheet.state.shaken`
  is `true` for `character_shaken = 1` and `false`/absent otherwise, mirroring the
  `incapacitated` boolean cast.

Run: `vendor/bin/phpunit --configuration phpunit.xml.dist`.

## Verification

1. Apply the migration to a dev database (or rebuild from `schema/` in filename order).
2. `composer run serve` (note: the dev server may already be running) and open a character sheet.
3. Confirm the **Shaken** box renders just above and aligned with the Wounds rail, with a
   single empty toggle circle matching the wound/fatigue circle styling.
4. Click the circle: it should fill (selected state), and the network tab should show
   `POST /characters/sheet/{hash}/state` with body `{"shaken":1}` returning `{"ok":true}`.
5. Reload the page: the toggle should remain on (persisted). Toggle off and reload to
   confirm it clears.
6. Check the `phablet` breakpoint (narrow viewport): Shaken should sit above Wounds in
   normal flow without overlap.
7. Confirm read-only/shared sheets render the circle without the `data-rail` hook (not
   interactive) and reflect the stored state.

## Touched files summary

| File | Change |
| --- | --- |
| `schema/020_characters.sql` | Add `character_shaken` column |
| `schema/migrations/2026…_add_character_shaken.sql` | New `ALTER TABLE` migration |
| `src/Character/Sheet.php` | `buildState()` → expose `shaken` boolean |
| `src/Controller/Character/Sheet.php` | Add `'shaken'` to `STATE_FIELDS` |
| `views/character/sheet.twig` | New `sheet__rail--shaken` aside with toggle circle |
| `resources/sass/components/character/sheet/_rail.scss` | `&--shaken` positioning modifier |
| `web/css/sheet.css` (+ `.map`) | Rebuilt compiled CSS |
| `web/javascript/sheet.js` | `wireBooleanRailItem('shaken')` |
| `tests/Controller/Character/SheetControllerTest.php` | Cover `shaken` in state update |
| `tests/Character/SheetTest.php` | (Optional) cover `buildState` shaken |
