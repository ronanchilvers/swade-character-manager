# Repository Guidelines

## Project Structure & Module Organization
The app is a Composer-managed PHP 8.5 project under the `App\\` namespace. Runtime entrypoints live in `web/` (`index.php` for production-style boot, `index_dev.php` for local serving). Framework wiring lives in `config/`: `settings.php` loads defaults plus `.env.php`, `events.php` boots the encrypted cookie-backed session lifecycle, `services.php` registers DI services, `maps.php` exposes Flight helpers, and `routes.php` defines the current character-builder flow.

Application code lives in `src/`. Use `src/Controller/` and `src/Controller/Character/` for Flight handlers, `src/Entity/` and `src/Entity/Factory/` for generic entities plus database persistence, `src/Http/` for response, cookie, and session helpers, `src/Middleware/` for Flight middleware, `src/Service/Data/` for catalog loaders, `src/Budget/` for character point summaries, and `src/Twig/` for Twig extensions.

Templates live in `views/`. Public assets live in `web/`, with Sass sources in `resources/sass/` compiled into `web/css/`. Catalog content currently loads from the PHP exports in `data/*.php`; matching `data/*.json` files exist in the repo as source/reference material, but the runtime `App\Service\Data\*` classes read the PHP files. Database bootstrap SQL currently lives in a single file, [`schema/000_schema.sql`](/Users/ronan/Personal/experiments/swade-character-manager/schema/000_schema.sql), which drops and recreates the schema. Tests live in `tests/`.

## Current Application Flow
The shipped builder flow is:

1. `characters_create` / `characters_concept`
2. `characters_hindrances`
3. `characters_attributes`
4. `characters_skills`

There is persistence support for `edges` in the schema and `App\Entity\Factory\Edge`, but there is no route, controller, or Twig screen for editing edges yet.

Authentication is Google OAuth based (`App\Controller\Auth`) and sessions are stored in an encrypted cookie via `App\Http\Session\CookieStorage`.

## Build, Test, and Development Commands
Install PHP dependencies with `composer install`. Install frontend build tooling with `npm install` only if you actually need to rebuild CSS; do not add packages without asking first.

Run the app locally with:

- `composer run serve`

That command serves `web/index_dev.php` at `http://127.0.0.1:8080`.

Rebuild CSS from Sass with:

- `npm run sass-dev`
- `npm run sass-prod`

Reset the database schema with:

- `mysql ... < schema/000_schema.sql`

Run tests with:

- `vendor/bin/phpunit --configuration phpunit.xml.dist`

As of 2026-04-16, the committed PHPUnit suite is not green: `tests/Service/CharacterAttributesTest.php` and `tests/Service/CharacterSkillsTest.php` reference service classes that do not exist in `src/Service/`, while `tests/Entity/Factory/CharacterTest.php` passes. Treat the service tests as stale/planned coverage until the matching services are implemented or the tests are rewritten.

## Coding Style & Naming Conventions
Follow the existing PHP style: `declare(strict_types=1);`, 4-space indentation, opening braces on the next line for classes and methods, and short array syntax. Keep namespaces PSR-4-compatible under `App\\`.

Use PascalCase for classes, camelCase for methods, and snake_case for Flight route aliases and database column names. Factory classes rely on name inference from class names to table names and prefixes, so keep new persistence classes aligned with the existing singular-class/plural-table convention.

Twig templates follow feature-based naming such as `views/character/hindrances.twig` and shared partials under `views/partials/`. Sass is organized by settings/tools/base/elements/components inside `resources/sass/`.

## Testing Guidelines
Prefer PHPUnit for new coverage, with test files named `*Test.php` under `tests/`. Current committed tests are split between `tests/Entity/Factory/` and `tests/Service/`.

When changing behavior in the live controllers/factories, favor tests that match the implementation that actually exists in `src/`; avoid adding or preserving tests that target planned services or APIs that are not present in the tree. For Twig, JavaScript, or Sass changes, do a manual pass through the affected builder step and note what you exercised.

## Documentation Notes
`docs/codebase-review.md` is the current high-level repository state document. The two plan docs in `docs/` are historical planning records that now include status notes; do not treat their original implementation sketches as current architecture without checking the code.

## Commit & Pull Request Guidelines
Keep commit messages short, specific, and sentence-case. PRs should describe user-visible behavior, call out schema or `.env.php` changes, and mention manual verification for UI work.

## Agent-Specific Notes
Read `/Users/ronan/Agents/General.AGENTS.md` and `/Users/ronan/Agents/Local.AGENTS.md` before starting new work in this repo. Do not install new Composer or Node packages without asking first.
