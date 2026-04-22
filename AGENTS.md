# Repository Guidelines

## Project Structure & Module Organization
The app is a Composer-managed PHP 8.5 project under the `App\\` namespace. Runtime entrypoints live in `web/` (`index.php` for production-style boot, `index_dev.php` for local serving). Framework wiring lives in `config/`: `settings.php` loads defaults plus `.env.php`, `events.php` boots the encrypted cookie-backed session lifecycle, `services.php` registers DI services, `maps.php` exposes Flight helpers, and `routes.php` defines the current character-builder flow.

Application code lives in `src/`. Use `src/Controller/` and `src/Controller/Character/` for Flight handlers, `src/Entity/` and `src/Entity/Factory/` for generic entities plus database persistence, `src/Http/` for response, cookie, and session helpers, `src/Middleware/` for Flight middleware, `src/Service/Data/` for catalog loaders, and `src/Twig/` for Twig extensions.

Templates live in `views/`. Public assets live in `web/`, with Sass sources in `resources/sass/` compiled into `web/css/`. Catalog content currently loads from the PHP exports in `data/*.php`; matching `data/*.json` files exist in the repo as source/reference material, but the runtime `App\Service\Data\*` classes read the PHP files. Database bootstrap SQL lives under `schema/`, with one file per table named `NNN_<table>.sql` and applied in filename order (currently `010_users.sql` through `070_weapons.sql`); each file drops and recreates its table. Tests live in `tests/`.

## Current Application Flow
The shipped builder flow is:

1. `characters_create` / `characters_concept`
2. `characters_hindrances`
3. `characters_attributes`
4. `characters_skills`
5. `characters_edges`

Edges are now edited through a dedicated builder step backed by `App\Controller\Character\Edges`, `views/character/edges.twig`, and `App\Entity\Factory\Edge`.

Authentication is Google OAuth based (`App\Controller\Auth`) and sessions are stored in an encrypted cookie via `App\Http\Session\CookieStorage`.

## Build, Test, and Development Commands
Install PHP dependencies with `composer install`. Install frontend build tooling with `npm install` only if you actually need to rebuild CSS; do not add packages without asking first.

Run the app locally with:

- `composer run serve`

That command serves `web/index_dev.php` at `http://127.0.0.1:8080`.

Rebuild CSS from Sass with:

- `npm run sass-dev`
- `npm run sass-prod`

When Sass changes modify tracked files under `web/css/`, commit those rebuilt CSS artifacts in the same change.

Reset the database schema by applying every file in `schema/` in filename order, for example:

- `cat schema/*.sql | mysql ...`

Run tests with:

- `vendor/bin/phpunit --configuration phpunit.xml.dist`

As of 2026-04-22, the PHPUnit suite passes cleanly against the live code. Current coverage is centered on live factory behavior, presenter behavior, support utilities, and catalog loading, rather than the older planned service-layer architecture.

## Coding Style & Naming Conventions
Follow the existing PHP style: `declare(strict_types=1);`, 4-space indentation, opening braces on the next line for classes and methods, and short array syntax. Keep namespaces PSR-4-compatible under `App\\`.

Use PascalCase for classes, camelCase for methods, and snake_case for Flight route aliases and database column names. Factory classes rely on name inference from class names to table names and prefixes, so keep new persistence classes aligned with the existing singular-class/plural-table convention.

Twig templates follow feature-based naming such as `views/character/hindrances.twig` and shared partials under `views/partials/`. Sass is organized by settings/tools/base/elements/components inside `resources/sass/`.

## Testing Guidelines
Prefer PHPUnit for new coverage, with test files named `*Test.php` under `tests/`. Current committed tests are split between `tests/Character/`, `tests/Entity/Factory/`, `tests/Http/`, `tests/Service/Data/`, and `tests/Support/`.

When changing behavior in the live controllers/factories, favor tests that match the implementation that actually exists in `src/`; do not reintroduce tests for planned services or APIs that are not present in the tree. For Twig, JavaScript, or Sass changes, do a manual pass through the affected builder step and note what you exercised.

## Documentation Notes
`docs/codebase-review.md` is the current high-level repository state document. The two plan docs in `docs/` are historical planning records that now include status notes; do not treat their original implementation sketches as current architecture without checking the code.

## Commit & Pull Request Guidelines
Keep commit messages short, specific, and sentence-case. PRs should describe user-visible behavior, call out schema or `.env.php` changes, and mention manual verification for UI work.

## Agent-Specific Notes
Read `/Users/ronan/Agents/General.AGENTS.md` and `/Users/ronan/Agents/Local.AGENTS.md` before starting new work in this repo. Do not install new Composer or Node packages without asking first.
Ignore any `AGENTS.md` files found under `.claude/` or `.codex/`; they are tool metadata or worktree artifacts and are not authoritative for this repository.
