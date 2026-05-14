# Controller Coverage Plan

## Goal

Cover every controller route path and controller branch with focused PHPUnit tests, without testing JavaScript or CSS.

The controller tests should verify request handling, factory calls, authorization decisions inside controllers, redirects, rendered templates, flash messages, and JSON responses. They should not try to verify Twig layout or browser behavior.

## Current Coverage Snapshot

Existing direct controller tests:

- [`tests/Controller/AuthTest.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/tests/Controller/AuthTest.php)
- [`tests/Controller/AdminUsersTest.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/tests/Controller/AdminUsersTest.php)
- [`tests/Controller/AdminCharactersTest.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/tests/Controller/AdminCharactersTest.php)

Controller classes with no direct controller tests yet:

- [`src/Controller/Home.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Controller/Home.php)
- [`src/Controller/Campaigns.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Controller/Campaigns.php)
- [`src/Controller/Admin/Campaigns.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Controller/Admin/Campaigns.php)
- [`src/Controller/Character/Base.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Controller/Character/Base.php)
- [`src/Controller/Character/Hindrances.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Controller/Character/Hindrances.php)
- [`src/Controller/Character/Attributes.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Controller/Character/Attributes.php)
- [`src/Controller/Character/Skills.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Controller/Character/Skills.php)
- [`src/Controller/Character/Edges.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Controller/Character/Edges.php)
- [`src/Controller/Character/Sheet.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Controller/Character/Sheet.php)

## Plan

### 1. Add route registration coverage

Create a focused test for [`config/routes.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/config/routes.php) that verifies every route alias is registered to the expected controller method and middleware group.

Route aliases to cover:

- `home_page`
- `auth_login`, `auth_logout`, plus the unaliased `GET /auth/return` callback route.
- `admin_campaigns_index`, `admin_campaigns_view`
- `admin_characters_index`
- `admin_users_index`, `admin_users_edit`, `admin_users_disable`, `admin_users_enable`
- `campaigns_index`, `campaigns_create`, `campaigns_view`, `campaigns_edit`, `campaigns_join`, `campaigns_add_character`, `campaigns_leave_character`, `campaigns_leave`, `campaigns_reset`
- `characters_create`, `characters_delete`, `characters_concept`, `characters_hindrances`, `characters_attributes`, `characters_skills`, `characters_edges`, `characters_sheet`, `characters_sheet_state`, `characters_sheet_notes`, `characters_sheet_gear`, `characters_sheet_weapons`

Exit criteria:

- Route drift fails a test before controller tests silently stop matching the live app.
- Authenticated, superuser, campaign, and character route groups have middleware expectations.

### 2. Finish existing controller coverage

Extend existing tests before adding new files:

- `AuthTest`
  - `index()` renders the Google auth URL and stores `oauth2state`.
  - `logout()` deletes the user session and redirects home.
  - `return()` handles missing state, mismatched state, explicit provider error, Google exception, new-user insert success, and new-user insert failure.
- `AdminUsersTest`
  - `index()` renders ordered users.
  - `edit()` GET renders the user form.
  - `edit()` missing or invalid id redirects with flash.
  - `edit()` blocks self-demotion and self-disable.
  - `edit()` surfaces validation/update errors without redirecting.
  - `enable()` reactivates a user.
  - `disable()` missing target and update failure branches.
- `AdminCharactersTest`
  - Existing happy and missing-user paths are adequate once shared helpers are introduced.

Exit criteria:

- Every public method in the currently tested controllers has at least one success path and one failure path where applicable.

### 3. Add home controller coverage

Create `tests/Controller/HomeTest.php`.

Cover:

- `index()` loads characters for `Flight::session()->user->id`.
- `index()` renders `home/index.twig` with `page_title` and the returned characters.

Exit criteria:

- The authenticated home page controller has direct coverage independent of route middleware.

### 4. Add admin campaign controller coverage

Create `tests/Controller/AdminCampaignsTest.php`.

Cover:

- `index()` renders all campaign summaries from `Campaign::allWithSummary()`.
- `view()` redirects with flash when the campaign hash is missing.
- `view()` renders owner and roster when the campaign exists.
- `view()` handles roster members whose user lookup returns null by returning an empty character list for that row.

Exit criteria:

- Both admin campaign routes are covered.
- The private roster behavior is covered through the rendered view data.

### 5. Add campaign controller coverage

Create `tests/Controller/CampaignsTest.php`.

Cover:

- `index()` renders campaigns for the current member user.
- `create()` GET renders an empty campaign entity.
- `create()` POST sanitizes input, validates, inserts, flashes success, and redirects to the campaign view.
- `create()` validation or insert failure renders errors and flashes a generic failure.
- `edit()` redirects when the campaign is missing.
- `edit()` rejects non-owner/non-superuser users and redirects to the campaign view.
- `edit()` GET renders the campaign.
- `edit()` POST success updates and redirects.
- `edit()` POST validation or update failure renders errors.
- `view()` redirects for missing or unauthorized campaigns.
- `view()` renders owner, invite URL, membership flags, roster, available characters, current-user characters, and `can_leave`.
- `join()` GET renders campaign join state.
- `join()` POST success creates membership and redirects to campaign view.
- `join()` POST failure flashes error and re-renders.
- `reset()` requires membership, clears the hash before update, and redirects to the new campaign view URL.
- `reset()` update failure flashes the factory error.
- `addCharacter()` requires membership.
- `addCharacter()` redirects with an error when the submitted character hash is missing or not owned by the user.
- `addCharacter()` success and failure branches.
- `leaveCharacter()` rejects missing characters and characters not assigned to the campaign.
- `leaveCharacter()` success and failure branches.
- `leave()` success redirects to campaign index.
- `leave()` failure redirects back to the campaign view with the member-factory error.

Exit criteria:

- Every route in the `/campaigns` group has direct success and failure coverage.
- Permission decisions inside the controller are covered for owner, member, superuser, and unrelated user where relevant.

### 6. Add character builder controller coverage

Create focused tests under `tests/Controller/Character`.

`BaseTest`:

- `create()` GET renders an empty concept form.
- `create()` POST success validates, upserts, flashes success, and redirects to hindrances.
- `create()` POST validation/upsert failure renders errors.
- `index()` redirects for missing hash.
- `index()` GET renders an existing entity.
- `delete()` redirects for missing character.
- `delete()` rejects confirmation name mismatch.
- `delete()` rejects characters assigned to a campaign.
- `delete()` success and factory failure branches.

`HindrancesTest`:

- missing character redirects home.
- GET loads existing hindrance selections and catalog builder data.
- POST filters selected hindrance levels, syncs selections, and redirects to attributes on success.
- POST failure renders errors and preserves selected input.

`AttributesTest`:

- missing character redirects home.
- GET renders dice options and attribute fields.
- POST filters valid dice values, updates entity attributes, and redirects to skills on success.
- POST update failure renders factory errors.

`SkillsTest`:

- missing character redirects home.
- GET loads persisted skills into selected values.
- GET merges core and non-core catalog skills and prepends `0` to dice options.
- POST filters submitted dice values, drops zero values, syncs skills, updates the character, and redirects to edges.
- POST skill sync failure and character update failure both flash the thrown message and render the form.

`EdgesTest`:

- missing character redirects home.
- GET loads persisted edge counts.
- GET groups catalog edges by category.
- POST ignores unknown edge keys.
- POST forces non-repeatable edges to count `1`.
- POST preserves repeatable counts.
- POST success redirects to sheet.
- POST failure renders factory errors.

Exit criteria:

- Every builder route has direct coverage for missing character, GET render, POST success, and POST failure.
- Catalog manager interactions are tested through mocks or small fakes.

### 7. Add character sheet controller coverage

Create `tests/Controller/Character/SheetControllerTest.php`.

Cover:

- `index()` redirects missing hashes.
- `index()` redirects when a non-superuser tries to view another user's character.
- `index()` allows the owner.
- `index()` allows a superuser and includes the character owner when viewing someone else's sheet.
- `index()` passes hindrances, skills, edges, gear, weapons, manager, and character factory into the presenter.
- `updateState()` parses JSON, updates only known state fields, clamps negative values to zero, and returns JSON success.
- `updateNotes()` stores notes as a string and returns JSON success.
- `updateGear()` passes JSON `rows` arrays to the gear factory.
- `updateGear()` treats non-array rows as empty arrays.
- `updateWeapons()` passes JSON `rows` arrays to the weapon factory.
- `updateWeapons()` treats non-array rows as empty arrays.
- JSON update methods return 422 and error JSON for failed factory results.
- JSON update methods return 404 JSON for missing hashes.

Important security coverage:

- Add a test documenting whether JSON update methods should reject non-owner users. The HTML sheet route checks owner/superuser access in `resolve()`, while JSON update methods currently use `resolveForJson()` and only check that the hash exists.

Exit criteria:

- Sheet read and write endpoints are covered separately.
- The current JSON authorization behavior is made explicit by a test before it is changed.

## Verification

Run in this order:

```bash
vendor/bin/phpunit --configuration phpunit.xml.dist tests/Controller
vendor/bin/phpunit --configuration phpunit.xml.dist
```

Then run coverage text after the full suite is green:

```bash
php -d xdebug.mode=coverage vendor/bin/phpunit --configuration phpunit.xml.dist --coverage-filter src/Controller --coverage-text=/tmp/swade-controller-coverage.txt --show-uncovered-for-coverage-text
```

## Out Of Scope

- Twig visual rendering.
- Browser automation.
- JavaScript and CSS behavior.
- End-to-end Google OAuth calls.
