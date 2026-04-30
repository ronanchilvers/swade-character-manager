# Repository Guidelines

## Startup
- Read `/Users/ronan/Agents/General.AGENTS.md` and `/Users/ronan/Agents/Local.AGENTS.md` before starting new work.
- Ignore any `AGENTS.md` files under `.claude/` or `.codex/`; they are tool metadata or worktree artifacts, not repository instructions.
- Do not install new Composer or Node packages without asking first.

## Project Contract
- This is a Composer-managed PHP 8.5 app under the `App\\` namespace.
- Runtime entrypoints are `web/index.php` and `web/index_dev.php`.
- Framework wiring lives in `config/settings.php`, `config/events.php`, `config/services.php`, `config/maps.php`, and `config/routes.php`.
- Runtime catalog loaders read `data/*.php`; matching `data/*.json` files are source/reference material unless the runtime code changes.
- Bootstrap SQL files in `schema/` are destructive table rebuilds and must be applied in filename order.

## Commands
- Serve locally: `composer run serve` (`http://localhost:8080`). Note the dev server may already be running.
- Run tests: `vendor/bin/phpunit --configuration phpunit.xml.dist`.
- Rebuild CSS only when needed: `npm run sass-dev` or `npm run sass-prod`.
- When Sass changes modify tracked files under `web/css/`, commit those rebuilt CSS artifacts in the same change.

## Conventions
- PHP uses `declare(strict_types=1);`, 4-space indentation, PSR-4-compatible namespaces under `App\\`, and short array syntax.
- Use PascalCase for classes, camelCase for methods, and snake_case for Flight route aliases and database columns.
- Factory classes rely on class-name inference for table names and prefixes; keep new persistence classes aligned with the existing singular-class/plural-table convention.
- Prefer PHPUnit tests named `*Test.php` under `tests/`, covering the implementation that actually exists in `src/`.

## References
- Current repository state: `docs/codebase-review.md`.
- Builder flow: `config/routes.php`, `src/Controller/Character/`, and `views/character/`.
- Data and persistence work: `src/Service/Data/`, `src/Entity/Factory/`, and `schema/`.
- Sass sources are in `resources/sass/`; compiled public CSS is in `web/css/`.
