---
name: swade-data-schema
description: Use when Codex changes or investigates SWADE catalog data, PHP data loaders, JSON reference files, entity factories, database persistence, schema bootstrap SQL, or table naming conventions.
---

# SWADE Data And Schema

Use this skill for catalog, persistence, or schema work in `/Users/ronan/Personal/experiments/swade-character-manager`.

## Catalog Data

- Runtime catalog loaders read PHP exports from `data/*.php`.
- Matching `data/*.json` files are source/reference material unless the runtime loaders are explicitly changed.
- Catalog loader classes live in `src/Service/Data/`.
- Runtime catalog lookups are coordinated through `App\Service\Data\Manager` plus focused loaders such as `Hindrances`, `Skills`, and `Edges`.

## Schema

- Bootstrap SQL files live under `schema/`.
- Files are named `NNN_<table>.sql` and applied in filename order.
- Each schema file drops and recreates its table.
- Existing schema files currently cover `users`, `characters`, `hindrances`, `skills`, `edges`, `gear`, and `weapons`.

## Persistence

- Entity factories live in `src/Entity/Factory/`.
- Factory classes rely on name inference from singular class names to plural table names and prefixes.
- Keep new persistence classes aligned with the existing singular-class/plural-table convention.
- Hindrances, skills, and edges store catalog references as string keys such as `hindrance_key` and `skill_key`.
- The `edges` table stores `edge_count` so one character can take the same catalog edge multiple times without duplicating rows.

## Verification

- Prefer focused PHPUnit tests around the relevant loader or factory.
- If schema behavior changes, call out that database bootstrap files are destructive and must be applied in order.
