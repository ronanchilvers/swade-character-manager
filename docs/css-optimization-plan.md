# CSS Optimisation Plan

Status: implemented on branch `claude/optimize-css-2AlNv`. See
"Outcome" at the bottom for the actual numbers and any deviations from
the original plan.

Goal: reduce compiled CSS bytes per page (and source duplication) without
changing any visual output.

## Current state (compiled, compressed)

| File          | Size   |
|---------------|--------|
| `styles.css`  | 18,255 |
| `sheet.css`   | 26,161 |
| `admin.css`   |  5,627 |
| `login.css`   |  3,662 |
| `builder.css` |  1,588 |
| `campaign.css`|    963 |
| **total**     | 56,256 |

Per-page bytes loaded today (`base.twig` always emits `styles.css`):

| Page          | Stylesheets loaded     | Bytes  |
|---------------|------------------------|--------|
| Home          | `styles.css`           | 18,255 |
| Builder*      | `styles.css + builder` | 19,843 |
| Sheet         | `styles.css + sheet`   | 44,416 |
| Login         | `styles.css + login`   | 21,917 |
| Admin*        | `styles.css + admin`   | 23,882 |
| Campaign view | `styles.css + campaign`|  19,218 |

The sheet page is the biggest target by a wide margin.

## Findings

### F1. Every secondary stylesheet re-bundles base layers from `styles.css`

`sheet.scss` re-`@use`s settings, reset, box-sizing, typography, document,
main, section, topbar, menu, dropdown, buttons, toast — all of which are
already in `styles.css`, which the layout always loads. The same is true
to a lesser degree of `login.scss` (re-includes `panels`), `admin.scss`,
`builder.scss`, and `campaign.scss` (which all re-include the settings
modules).

Because the secondary CSS is loaded *after* `styles.css`, the duplicated
rules are downloaded, parsed, and then re-applied identically. Visible
effect: none. Cost: ~6–8 KB of duplicate CSS on the sheet page alone, plus
parse work.

### F2. Dead Sass partials and dead rules

Confirmed by grepping every Twig view, PHP source, and JS file:

- `resources/sass/components/_pill.scss` — `.pill` is never referenced.
  Admin uses its own `.admin-pill` (copy-pasted into `_admin.scss`).
- `resources/sass/components/_progress.scss` — no `<progress>` element
  and no `.progress` class anywhere in views or JS.
- `resources/sass/elements/_aside.scss` — not `@use`d in any entry point;
  the only `<aside>` tags in templates carry their own class names.
- `resources/sass/base/_box-sizing.scss` — duplicates the rule already
  defined in `_reset.scss` lines 2-4.
- `.menu__item--user` (`_menu.scss:29-33`) — class never appears in
  markup. The bare `.menu__item` is used.

### F3. Large commented-out blocks

Pure noise in the source. Compressed output is unaffected, but the
files are harder to read and tempt drift. Locations:

- `resources/sass/settings/_sheet-theme.scss:2-12`
- `resources/sass/components/_form.scss:41-80`
- `resources/sass/components/_buttons.scss:80-94` (`.button--google`)
- `resources/sass/components/cards/_character.scss:5-15`
- `resources/sass/components/cards/_edge.scss:3-12, 14-38, 87-106`
- `resources/sass/components/character/_sheet.scss:41, 44, 53, 384-386,
   394-395, 451-470, 539-541`
- `resources/sass/components/_menu.scss:4`,
   `_dropdown.scss:4`, `_campaign/_roster.scss:5`

### F4. Repeated heading recipe

The same six declarations (`font-family: $sheet-title-font`,
`text-transform: uppercase`, `letter-spacing: 0.06–0.08em`,
`font-weight: 400`, `color: var(--c-sheet-heading)`,
`font-family: sheet.$sheet-title-font`) recur in at least 11 selectors
across `_admin.scss`, `_panels.scss`, `_cards/base.scss`,
`_cards/character.scss`, `_character/sheet.scss`. Each repetition costs
~110-150 B compressed. A single Sass placeholder (`%sheet-heading`) or
mixin would emit one rule with a long selector list.

### F5. Duplicate `clip-path: polygon(...)` torn-paper outline

The same shape (with minor coordinate variations) appears in
`_login-form.scss:47-54`, `_character/sheet.scss:54-61`, and
`_character/sheet.scss:808-815` (help dialog). Each polygon is ~360 B
compressed. A `@mixin torn-paper($depth)` would centralise the recipe and
make the three uses visually consistent. (Compression savings are real
only if we accept that the variations get normalised — they’re minor
enough that the visual diff should be undetectable.)

### F6. Duplicate `.pill` block

`_pill.scss` and `_admin.scss:157-181` define identical rules under two
class names. Once `.pill` is deleted (F2), the same selectors can be
expressed as `.pill, .admin-pill` in a single block — or `.admin-pill`
can simply `@extend %pill`. Saves ~250 B.

### F7. Two adjacent `:root { ... }` rule sets

`_colours.scss` and `_sheet-theme.scss` each emit their own `:root` block.
The browser merges them correctly, but compiled bytes are slightly higher
than necessary. Folding the colour variables into the sheet-theme `:root`
(or vice versa) saves the duplicate selector.

### F8. Minor bugs that are cheap to fix while we're in the file

- `_panels.scss:126` — `justfy-content: center` (typo, currently
  ignored by the browser).
- `_buttons.scss:225` — `opacity: none;` is invalid; intended `opacity: 0`.
- `_buttons.scss:210` — `-webkit-transition: .4s;` prefix is obsolete.
- `_typography.scss:17-24` — `@for $level from 1 through 4` leaves h5/h6
  unstyled. Probably intentional; flag only.

These are correctness issues, not byte wins. Worth folding into the same
pass because we'll be touching the files.

## Recommended changes (prioritised by byte/risk ratio)

### Phase 1 — Pure deletions (zero visual risk)

1. Delete `resources/sass/base/_box-sizing.scss` and the `@use
   "base/box-sizing"` lines from `styles.scss` and `sheet.scss`. (Reset
   already does this.)
2. Delete `resources/sass/components/_pill.scss` and its `@use` from
   `styles.scss`.
3. Delete `resources/sass/components/_progress.scss` and its `@use` from
   `styles.scss`.
4. Delete `resources/sass/elements/_aside.scss` (not `@use`d anywhere).
5. Delete `.menu__item--user` from `_menu.scss`.
6. Strip the commented-out blocks listed in F3.

Expected drop in `styles.css`: ~1.2–1.5 KB.

### Phase 2 — Stop loading `styles.css` rules twice (sheet page)

Two options; pick one before implementing.

**Option A — drop redundant `@use`s from secondary entries.**
Remove the base/settings/reset/typography/document/main/section/topbar/
menu/dropdown/buttons/toast `@use` lines from `sheet.scss` (and the
`panels` `@use` from `login.scss`). Trust that `styles.css` is always
loaded first via `base.twig`. Smallest change; the only risk is that one
day someone loads `sheet.css` without `styles.css` and gets unstyled
output — that path doesn't currently exist.

**Option B — make `sheet.css` the only stylesheet on the sheet page.**
Add a `block stylesheets` in `base.twig`, override it on the sheet page,
and have `sheet.scss` continue to import everything it needs. Slightly
more refactor, but keeps each compiled file independently usable.

Option A is the recommended path because it matches the current
hub-and-spoke loading model and is a few-line change. Expected drop in
`sheet.css`: ~6 KB compressed (settings + reset + typography + document +
main + section + topbar + menu + dropdown + buttons + toast).

Same treatment for `admin.scss`, `builder.scss`, `campaign.scss`,
`login.scss` settings imports — they each emit a duplicate `:root` block
from `colours`/`sheet-theme`. Drop those redundant `@use`s. Saves a few
hundred bytes per file.

### Phase 3 — Share the heading recipe

Introduce a Sass placeholder in `settings/_sheet-theme.scss` (or a new
`tools/_typography.scss`):

```scss
%sheet-heading {
  font-family: $sheet-title-font;
  font-weight: 400;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--c-sheet-heading);
}
```

`@extend %sheet-heading` in:

- `.card__title`, `.card__subtitle`
- `.panel__title`
- `.sheet__title`, `.sheet__help-dialog__title`
- `.admin-view__title`, `.admin-users__table th`, `.admin-group__title`,
  `.admin-character__title`
- `.character-delete__title`, `.home__empty__title`
- `.pill`/`.admin-pill` (uppercase + letter-spacing parts only — they’d
  need a separate placeholder or to opt out of the colour declaration).

Sass `@extend` will fold these into a single comma-selector list, which
compresses well. Estimated saving: 1.0–1.5 KB across `styles.css` +
`sheet.css` + `admin.css`. Risk: low, because `@extend` preserves cascade
order and each call site’s additional `font-size`/`margin` rules remain
unchanged.

### Phase 4 — Mixin the torn-paper clip-path

Create `tools/_torn-paper.scss` exporting:

```scss
@mixin torn-paper($depth: 6px) { clip-path: polygon( … ); }
```

Replace the three near-identical polygons with `@include
torn-paper(6px)` / `@include torn-paper(7px)`. Note: this normalises the
polygons to the same coordinate set — diff each rendered surface against
main before merging; the help dialog uses a slightly more jagged outline
than the login panel, so accept that small visual change or keep two
mixin variants. Estimated saving: 0.3–0.5 KB.

### Phase 5 — Minor correctness fixes (F8)

Fold these into Phase 1’s commit so the cleanup pass also fixes silent
bugs. Zero byte impact; positive correctness impact.

## Out of scope (call out, don't fix)

- **Class names referenced in markup but never styled** —
  `button--filled` (topbar.twig) and `card__content--empty`,
  `card__title--selected` (home/edges). These fall back to base styles
  and produce no errors. Either remove the references from templates or
  add the rules — but that’s a markup decision, not a CSS optimisation.
- **Google Fonts request** in `base.twig:12` pulls four families with
  the full Roboto axis range. Subsetting or dropping `Special Elite`
  (not referenced in any Sass) would save bytes, but it’s outside the
  CSS file optimisation scope.

## Verification

After each phase:

1. `npm run sass-prod` and capture `wc -c web/css/*.css`.
2. Visit at minimum: `/login`, `/`, `/characters/<id>/concept` (one
   builder tab), `/characters/<id>/sheet`, `/campaigns/<id>`,
   `/admin/users`.
3. Compare against `main` screenshots for each route (manual diff —
   visual regression tooling is not set up in this repo).
4. `vendor/bin/phpunit --configuration phpunit.xml.dist` to confirm
   nothing else regresses (CSS shouldn’t affect tests, but cheap to run).
5. Commit the rebuilt `web/css/*.css` alongside the Sass changes — repo
   convention per `AGENTS.md`.

## Estimated impact

- `styles.css`:  18.3 KB → ~16.5 KB  (Phase 1 + 3)
- `sheet.css`:   26.2 KB → ~19–20 KB  (Phase 2 + 3 + 4)
- `admin.css`, `login.css`, `builder.css`, `campaign.css`: small drops
  (~0.2–0.5 KB each) from removing duplicated `:root` blocks.
- Total per-page CSS on the sheet route: 44.4 KB → ~36 KB (~19% drop).
- Source files: noticeably tidier; dead modules removed; one canonical
  heading recipe and torn-paper recipe.

No visual changes expected.

## Outcome (after implementation)

Decisions taken at implementation time:

- **Phase 2** — Option A (trim `@use` lines in secondary entries).
- **Phase 3** — `%sheet-heading` covers font-family + color +
  font-weight + text-transform only. Letter-spacing varies per call
  site and stays at the call site. `.panel__title` was intentionally
  excluded because its templates render mixed-case text and the
  placeholder would force uppercase.
- **Phase 4** — implemented as `%torn-paper` (placeholder, not mixin) so
  the polygon is emitted once with a combined selector inside each
  compiled stylesheet. The help dialog's slightly jagged 7px variant is
  normalised to the same 6px shape as the other two.
- **Phase 5** — all three incidental bugs (panels `justfy-content`,
  buttons `opacity: none`, obsolete `-webkit-transition`) fixed in the
  Phase 1 commit.

Additional small change: merged `_colours.scss` into the single
`:root` block in `_sheet-theme.scss` and deleted `_colours.scss`. The
sheet tokens were also split into `_sheet-tokens.scss` (Sass scalar
vars, no CSS output) so partials can pull token values without
re-emitting the `:root` block in every compiled stylesheet.

### Final per-file sizes (compressed)

| File          | Before  | After   | Δ        |
|---------------|---------|---------|----------|
| `styles.css`  | 18,255  | 17,100  | -1,155 B |
| `sheet.css`   | 26,161  | 14,783  | -11,378 B |
| `admin.css`   |  5,627  |  4,872  |    -755 B |
| `login.css`   |  3,662  |  1,215  |  -2,447 B |
| `builder.css` |  1,588  |    937  |    -651 B |
| `campaign.css`|    963  |    793  |    -170 B |
| **total**     | 56,256  | 39,700  | **-16,556 B (-29.4%)** |

### Per-page bytes loaded (CSS only; both files where applicable)

| Page          | Before | After  | Δ        |
|---------------|--------|--------|----------|
| Home          | 18,255 | 17,100 |  -6.3%   |
| Builder       | 19,843 | 18,037 |  -9.1%   |
| Sheet         | 44,416 | 31,883 | **-28.2%** |
| Login         | 21,917 | 18,315 | -16.4%   |
| Admin         | 23,882 | 21,972 |  -8.0%   |
| Campaign view | 19,218 | 17,893 |  -6.9%   |

### Source changes

- 4 dead partials removed: `_box-sizing.scss`, `_pill.scss`,
  `_progress.scss`, `_aside.scss`. `_colours.scss` folded into
  `_sheet-theme.scss`.
- 2 new partials: `settings/_sheet-tokens.scss` (Sass scalars +
  `%sheet-heading`) and `tools/_torn-paper.scss` (`%torn-paper`).
- All five secondary entry files now contain only the entry-specific
  `@use` lines; settings/base/element/shared-component rules come from
  `styles.css` (which `base.twig` always loads first).
- 10 heading rules now `@extend %sheet-heading` instead of repeating the
  four-property recipe.
- 3 torn-paper polygons now `@extend %torn-paper`; sheet.css emits one
  shared rule for `.sheet__panel--torn, .sheet__help-dialog`.
- Incidental bugs fixed: `justfy-content` typo, invalid `opacity: none`,
  obsolete `-webkit-transition`.

No template, JS, or PHP changes were required.

