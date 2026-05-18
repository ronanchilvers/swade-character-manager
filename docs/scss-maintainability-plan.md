# Sass Cleanup And Modularisation Plan

Status: implementation started after approval.

## Goal

Clean up the Sass under `resources/sass/` without changing the current UI. The work should make the code easier to scan, easier to extend, and less risky to modify by splitting large files into smaller modules with clearer ownership.

This plan builds on the completed CSS optimisation work in `docs/css-optimization-plan.md`. It is not a byte-size optimisation pass; the main success metric is source clarity.

## Current State

The Sass already has a useful top-level shape:

- Root entrypoints: `styles.scss`, `admin.scss`, `builder.scss`, `campaign.scss`, `login.scss`, `sheet.scss`.
- Shared foundations: `settings/`, `tools/`, `base/`, `elements/`.
- Shared and route-specific UI: `components/`, with existing subfolders for `cards/`, `campaign/`, and `character/`.

Approximate source size by pressure point:

| Area | Current file | Lines | Notes |
| --- | --- | ---: | --- |
| Character sheet | `resources/sass/components/character/_sheet.scss` | 925 | Layout, panels, identity, stats, rails, lists, editing states, help dialog, print styles all in one module. |
| Admin views | `resources/sass/components/_admin.scss` | 309 | Admin shell, user tables, status pills, groups, character summaries, responsive table behavior. |
| Buttons | `resources/sass/components/_buttons.scss` | 212 | Base button, variants, toggle LED behavior, slider toggle behavior. |
| Toasts | `resources/sass/components/_toast.scss` | 197 | Toast tokens, layout, visual style, variants, motion. |
| Panels | `resources/sass/components/_panels.scss` | 129 | Base panel, collapsible behavior, help variant, controls variant. |
| Cards | `resources/sass/components/cards/_base.scss` | 127 | Card grid, base card, collapsible card behavior, selected state. |

## Findings

### F1. Some modules are too large to review safely

`components/character/_sheet.scss` is the main issue. It contains many independent concepts that change for different reasons: sheet grid layout, identity fields, trait circles, wound/fatigue rails, skills, weapons, editable rows, help dialog, and print styles. Any sheet change currently requires scanning almost a thousand lines.

`components/_admin.scss` has the same pattern at a smaller scale. Admin page shell, tables, pills, grouped summaries, and character cards are all bundled together.

### F2. Several files mix component and page responsibilities

Examples:

- `components/cards/_character.scss` contains `.character-delete` dialog styles and `.home__empty` empty-state styles. Those are not card variants.
- `components/character/_sheet.scss` includes global `.help-icon` and `.sheet__help-dialog` styles. These are sheet-adjacent, but they can be isolated.
- `components/_buttons.scss` includes two separate control systems: normal buttons and checkbox-backed slider/toggle controls.

This makes it harder to know where a new rule belongs.

### F3. Token boundaries are partly clear, partly confusing

The current split is a good start:

- `settings/_colors.scss` is the Sass-only authority for colour values.
- `settings/_sheet-tokens.scss` holds Sass-only sheet typography tokens and `%sheet-heading`.
- `settings/_css-variables.scss` holds Sass variables, despite the name suggesting CSS custom properties.

There are still many local hard-coded values for repeated concepts such as danger colors, borders, paper surfaces, shadows, and control dimensions. Not every value needs a token, but repeated visual decisions should have semantic names.

### F4. Import conventions are simple, but not yet explicit enough for bigger folders

The root entrypoints are intentionally small, which is good. As files split further, use explicit folder indexes so future readers can tell the difference between an entrypoint, an aggregate module, and a leaf partial.

Because Sass can become ambiguous when a folder and partial share a load path, avoid having both `components/_buttons.scss` and `components/buttons/_index.scss` loaded through `@use "components/buttons"`. Prefer explicit imports such as `@use "components/buttons/index"`.

### F5. A few selector patterns hide ownership

Most selectors use BEM-like names, but a few patterns are harder to grep or reason about:

- `.roster { &-header { ... } }` emits `.roster-header`, but the source does not show the final selector directly.
- Deep element chains such as `.sheet__campaign-banner__label` work, but can blur whether `campaign-banner` is a sheet element or its own component.
- Generic descendants inside components, such as nested `p`, `th`, `td`, `li`, and `svg`, are sometimes fine but should be kept close to their owning block.

This is not an urgent problem, but cleanup should avoid adding more hidden selector construction.

## Proposed Module Rules

Use these rules during the cleanup:

1. Root files under `resources/sass/*.scss` remain compile entrypoints only.
2. `settings/` contains design values. Files named for themes may emit CSS; files named tokens should not emit CSS unless documented.
3. `tools/` contains mixins, functions, and placeholders that do not emit standalone CSS.
4. A component folder may have an `_index.scss` that only `@use`s child modules.
5. Leaf partials should own one visible component, one page section, or one behavior.
6. Prefer directly greppable selectors over generated selector fragments when the output class name is meaningful.
7. Do not create tokens for one-off values. Tokenise repeated or semantic decisions only.

## Target Structure

The exact split can be adjusted during implementation, but the target shape should be close to this. This tree shows the areas being reorganised; unchanged partials such as `components/_form.scss`, `components/_dropdown.scss`, `components/_menu.scss`, `components/_topbar.scss`, `components/_panels.scss`, `components/_login-form.scss`, and `components/campaign/` can stay where they are until they need work.

```text
resources/sass/
  styles.scss
  admin.scss
  builder.scss
  campaign.scss
  login.scss
  sheet.scss
  settings/
    _css-variables.scss        # consider renaming in a later phase
    _colors.scss
    _sheet-tokens.scss
  tools/
    _breakpoints.scss
    _torn-paper.scss
  components/
    buttons/
      _index.scss
      _base.scss
      _variants.scss
      _toggle.scss
      _slider.scss
    admin/
      _index.scss
      _view.scss
      _users-table.scss
      _pills.scss
      _groups.scss
      _character-summary.scss
    character/
      _delete-dialog.scss
      _tabs.scss
      sheet/
        _index.scss
        _tokens.scss
        _layout.scss
        _panel.scss
        _identity.scss
        _attributes.scss
        _traits.scss
        _lists.scss
        _skills.scss
        _edges.scss
        _hindrances.scss
        _rail.scss
        _weapons.scss
        _editing.scss
        _help-dialog.scss
        _print.scss
    cards/
      _index.scss
      _base.scss
      _edge.scss
      _hindrance.scss
      _skill.scss
    home/
      _empty-state.scss
    toast/
      _index.scss
      _tokens.scss
      _layout.scss
      _variants.scss
      _motion.scss
```

The main route entrypoints would then become explicit:

```scss
// resources/sass/admin.scss
@use "components/admin/index";

// resources/sass/sheet.scss
@use "components/character/sheet/index";

// resources/sass/styles.scss
@use "components/buttons/index";
@use "components/cards/index";
@use "components/toast/index";
```

## Implementation Plan

### Phase 1 - Establish module indexes

Create `_index.scss` aggregator files for the areas that already have submodules or are about to be split:

- `components/cards/_index.scss`
- `components/buttons/_index.scss`
- `components/toast/_index.scss`
- `components/admin/_index.scss`
- `components/character/sheet/_index.scss`

Update entrypoints to import explicit indexes. Keep the compiled CSS equivalent after this phase.

When an index replaces an existing aggregate wrapper, delete the old wrapper in the same phase. For example, `components/cards/_index.scss` should replace `components/_cards.scss`, and `components/buttons/_index.scss` should replace `components/_buttons.scss` once the button split is complete.

Acceptance checks:

- `npm run sass-prod` succeeds.
- Compiled CSS has no intentional visual diff.
- Root entrypoint files still contain only high-level `@use` statements.

### Phase 2 - Split the character sheet module

Move sections out of `components/character/_sheet.scss` into focused sheet partials. Keep the selector output the same unless a selector cleanup is explicitly called out and verified.

Suggested split:

- `_tokens.scss`: local aliases and sheet-only constants currently at the top of `_sheet.scss`.
- `_layout.scss`: `.sheet`, top/middle/right/bottom grid regions, campaign banner, topbar.
- `_panel.scss`: `.sheet__panel`, torn variant, shared title treatment.
- `_identity.scss`: identity grid and rows.
- `_attributes.scss`: attribute list and dice display.
- `_traits.scss`: trait circles and benny counter controls.
- `_lists.scss`: shared `.sheet__list` rows and add rows.
- `_skills.scss`: skills list and die strip if it remains skill-specific.
- `_edges.scss` and `_hindrances.scss`: right-column list styling.
- `_rail.scss`: wound/fatigue rail and rail interaction states.
- `_weapons.scss`: weapons table and scrolling behavior.
- `_editing.scss`: `[data-counter]`, `[contenteditable="true"]`, gear rows, remove buttons.
- `_help-dialog.scss`: `.help-icon` and `.sheet__help-dialog`.
- `_print.scss`: print-only overrides.

Acceptance checks:

- The compiled `sheet.css` is behaviorally equivalent.
- The sheet route still supports editable gear/weapons, counters, help dialogs, and print hiding.
- No single sheet partial should exceed roughly 180-220 lines unless there is a clear reason.

### Phase 3 - Split admin and high-churn shared components

Split `components/_admin.scss` by visible admin concept:

- Admin page shell.
- Users/campaigns table.
- Admin pills.
- Admin groups.
- Admin character summary cards.
- Responsive table/group overrides.

Then split `components/_buttons.scss`:

- Base `.button`.
- Visual variants and sizes.
- Toggle LED button behavior.
- Slider control behavior.

Split `components/_toast.scss` only if the implementation still has momentum after the larger files. It is less painful than the sheet/admin files, but it has a clean natural split into tokens, layout, variants, and motion.

Acceptance checks:

- `admin.css`, `styles.css`, and affected route CSS compile.
- Admin user, admin campaign, topbar buttons, builder toggle buttons, and toast states render unchanged.

### Phase 4 - Move misplaced rules to clearer homes

Move rules that currently live in a technically valid but misleading file:

- Move `.character-delete` out of `components/cards/_character.scss` into `components/character/_delete-dialog.scss`.
- Move `.home__empty` into `components/home/_empty-state.scss`.
- Keep actual card grid and card variants in `components/cards/`.
- Consider whether `.help-icon` should remain sheet-specific or become a small shared component if it is reused outside the sheet.

Acceptance checks:

- Home character deletion dialog and empty home state render unchanged.
- Card partials only contain card-related selectors.

### Phase 5 - Clarify tokens and selector style

Make a small token pass after the split, when repeated values are easier to see:

- Rename `settings/_css-variables.scss` to a clearer Sass-token name, such as `settings/_layout-tokens.scss`, or leave it in place if the churn is not worth it.
- Add semantic Sass tokens for repeated values only, such as danger red, common border colors, paper surfaces, and common shadows.
- Keep colour values in Sass tokens rather than CSS custom properties.
- Prefer direct selector declarations for meaningful class names, for example writing `.roster-header` directly instead of `.roster { &-header { ... } }`.

Acceptance checks:

- Token names explain intent rather than raw appearance.
- No broad one-off token layer is introduced.
- Grepping for an emitted class name usually finds the owning source selector.

## Verification For The Later Implementation

For each implementation phase:

1. Run `npm run sass-prod`.
2. Include any changed tracked files under `web/css/` with the Sass change.
3. Manually check at least:
   - `/login`
   - `/`
   - one character builder step
   - one character sheet
   - one campaign view
   - `/admin/users`
   - one admin campaign or character detail page
4. For sheet-specific phases, also check editable gear/weapons, sheet counters, help dialogs, and print styles.
5. Run `vendor/bin/phpunit --configuration phpunit.xml.dist` if the implementation touches templates or anything beyond Sass/CSS.

## Approval Questions

Before implementation, confirm these choices:

1. Should the implementation preserve visual output exactly, treating any visible change as a bug?
2. Is the proposed folder/index style acceptable even though it changes many import paths?
3. Should token cleanup include renaming `settings/_css-variables.scss`, or should that wait until after the structural split?
4. Should `components/_toast.scss` and `components/_panels.scss` be split in the first cleanup pass, or left alone until they need feature work?

Approved choices for this pass:

- Preserve visual output exactly.
- Use the proposed folder/index structure.
- Leave `settings/_css-variables.scss` unchanged for now.
- Remove colour CSS custom properties and consolidate colour values in `settings/_colors.scss`.
- Leave toast and panels for a later iteration.
