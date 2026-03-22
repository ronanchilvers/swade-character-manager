# Repository Guidelines

## Project Structure & Module Organization
Application code lives in `src/` under the `App\\` namespace. Use `src/Controller/` for Flight route handlers, `src/Entity/` and `src/Entity/Factory/` for data objects and persistence helpers, `src/Http/` for response, cookie, CORS, and session code, and `src/Twig/` for Twig extensions. Framework wiring is kept in `config/` (`routes.php`, `services.php`, `maps.php`, `settings.php`). Templates live in `views/`, static assets in `web/`, SQL bootstrap scripts in `schema/`, project docs in `docs/`, and PHPUnit coverage in `tests/`. Local environment overrides belong in `.env.php`; start from `.env.php.dist`.

## Build, Test, and Development Commands
Install PHP dependencies with `composer install`. Run the app locally with `composer run serve`, which serves `web/` on `http://127.0.0.1:8080`. Rebuild the local database from `schema/000_drop.sql` and `schema/001_schema.sql` when schema changes are involved. Run tests with `vendor/bin/phpunit`, which uses `phpunit.xml` and the `tests/` directory.

## Coding Style & Naming Conventions
Follow the existing PHP style: `declare(strict_types=1);`, 4-space indentation, opening braces on the next line for classes and methods, and short array syntax. Keep namespaces PSR-4-compatible under `App\\`. Use PascalCase for classes (`App\Controller\Character`), camelCase for methods, and snake_case for route aliases, config keys, and database-prefixed columns. Reserve `*_id` for integer database identifiers and use `*_key` for JSON-backed catalog references. Match existing Twig naming such as `views/character/concept.twig` and reuse partials from `views/partials/`.

## Testing Guidelines
Prefer PHPUnit for new coverage, with test files named `*Test.php` under `tests/`. Current coverage lives in `tests/Service/`; extend that structure unless a broader test layout becomes necessary. Focus first on factories, validation services, session handling, and route/controller behavior that can regress easily. For UI changes in Twig or CSS, do a manual pass through the affected flow and note the pages exercised in your PR.

## Commit & Pull Request Guidelines
Recent commits use short, sentence-case summaries such as `Improve hindrances page by adding character validation and navigation link`. Keep messages specific and action-oriented; avoid vague summaries like `Latest changes`. PRs should describe user-visible behavior, call out schema or `.env.php` changes, and include screenshots for Twig/CSS updates.

## Agent-Specific Notes
Read `/Users/ronan/Agents/General.AGENTS.md` and `/Users/ronan/Agents/Local.AGENTS.md` before starting new work in this repo. Do not install new packages without asking first.
