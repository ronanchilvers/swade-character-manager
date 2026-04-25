---
name: swade-project-orientation
description: Use when Codex needs to understand, trace, or modify the SWADE character manager repository structure, framework wiring, entrypoints, module locations, or current architecture before making code changes.
---

# SWADE Project Orientation

Use this skill to get oriented in `/Users/ronan/Personal/experiments/swade-character-manager` before tracing behavior or adding modules.

## Read First

- Read `AGENTS.md` for the root working contract.
- Read `docs/codebase-review.md` for the current verified architecture snapshot.
- Treat historical plan docs under `docs/` as background only unless the live code confirms them.

## Runtime Shape

- `web/index.php` is the production-style entrypoint.
- `web/index_dev.php` is the local development entrypoint used by `composer run serve`.
- `config/settings.php` loads defaults plus `.env.php`.
- `config/events.php` boots and persists the encrypted cookie-backed session lifecycle.
- `config/services.php` registers DI services.
- `config/maps.php` exposes Flight helpers.
- `config/routes.php` defines live routes and the current character-builder flow.

## Code Map

- Controllers live in `src/Controller/` and `src/Controller/Character/`.
- Generic entities and database persistence live in `src/Entity/` and `src/Entity/Factory/`.
- HTTP response, cookie, and session helpers live in `src/Http/`.
- Flight middleware lives in `src/Middleware/`.
- Catalog loaders live in `src/Service/Data/`.
- Twig extensions live in `src/Twig/`.
- Templates live in `views/`, with shared partials under `views/partials/`.
- Public assets live in `web/`; Sass sources live in `resources/sass/` and compile into `web/css/`.
- Runtime catalog data comes from `data/*.php`; JSON siblings are reference/source material.
- Bootstrap SQL lives in `schema/`, one destructive table rebuild per file.
- Tests live in `tests/`.

## Practical Entry Points

- To trace a request, start with `config/routes.php`, then follow the controller and Twig template.
- To trace dependencies, start with `config/services.php`.
- To understand current state, prefer `docs/codebase-review.md` over older plan documents.
