# Repository State Review

Last verified against the repository on 2026-04-16.

## Overview
This is a small server-rendered PHP 8.5 application for building Savage Worlds characters. It uses Flight for routing and dependency injection, Twig for HTML rendering, Google OAuth for login, and an encrypted cookie-backed session layer.

The active character-builder flow is:

1. Concept
2. Hindrances
3. Attributes
4. Skills

Edges have schema and factory support but no implemented editor flow.

## Verified Runtime Structure
- `web/index.php` and `web/index_dev.php` bootstrap the app.
- `config/settings.php` loads defaults plus `.env.php`.
- `config/events.php` initializes and persists the session around each request.
- `config/services.php` registers Twig, Google OAuth, the session store, the database connection, entity factories, and the catalog data manager.
- `config/routes.php` wires `Home`, `Auth`, and the four current character-builder steps.
- `src/Service/Data/*.php` loads catalog data from `data/*.php`, not from the JSON siblings.

## Persistence and Catalog Model
- The database schema is currently managed by a single bootstrap file: `schema/000_schema.sql`.
- `characters`, `users`, `hindrances`, `skills`, and `edges` tables are created there.
- Hindrances, skills, and edges store catalog references as string keys such as `hindrance_key` and `skill_key`.
- Runtime catalog lookups come from `App\Service\Data\Manager` plus `App\Service\Data\Hindrances`, `Skills`, and `Edges`.

## UI and Asset Pipeline
- Twig views are in `views/`.
- Sass sources are in `resources/sass/`.
- Compiled CSS is committed under `web/css/`.
- Frontend JavaScript is small and page-specific under `web/javascript/`.

## Current Test Status
The documented PHPUnit command is correct:

```bash
vendor/bin/phpunit --configuration phpunit.xml.dist
```

The suite now passes cleanly against the live implementation.

Verified result on 2026-04-16:

- `tests/Budget/` covers the current budget helpers.
- `tests/Entity/Factory/` covers the live character, hindrance, and skill factories.
- `tests/Service/Data/SkillsTest.php` covers the live skill catalog loader.
- `vendor/bin/phpunit --configuration phpunit.xml.dist` exits successfully on the current tree.

The earlier service-layer tests that referenced missing classes were removed and replaced with coverage for the code that actually ships.

## Known Documentation-Sensitive Gaps
- Historical docs previously referred to `schema/001_schema.sql`, but the repo currently has only `schema/000_schema.sql`.
- Historical docs previously described a JSON-first runtime catalog service. The live app loads PHP catalog exports from `data/*.php`.
- Historical docs previously described service-layer character workflows such as `CharacterAttributes` and `CharacterSkills`. Those classes are still not present in `src/Service`; the current flow remains controller/factory driven.

## Recommended Reading Order
1. `AGENTS.md` for the working repo contract.
2. `docs/codebase-review.md` for current verified state.
3. Historical plan docs only when you need background on earlier intended designs.
