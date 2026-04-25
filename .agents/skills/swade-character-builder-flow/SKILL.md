---
name: swade-character-builder-flow
description: Use when Codex changes or investigates the SWADE character builder flow, including character creation routes, builder step controllers, Twig views, presenters, factories, validation, or manual UI verification.
---

# SWADE Character Builder Flow

Use this skill for work that touches character creation or any builder step in `/Users/ronan/Personal/experiments/swade-character-manager`.

## Current Flow

The shipped builder flow is:

1. `characters_create` / `characters_concept`
2. `characters_hindrances`
3. `characters_attributes`
4. `characters_skills`
5. `characters_edges`

`config/routes.php` is the source of truth for route aliases and ordering.

## Key Files

- Builder controllers live in `src/Controller/Character/`.
- Builder views live in `views/character/`.
- Shared view fragments live in `views/partials/`.
- Persistence support usually runs through `src/Entity/Factory/`.
- The read-only sheet presenter lives under `src/Character/`.

The edges step is backed by `App\Controller\Character\Edges`, `views/character/edges.twig`, and `App\Entity\Factory\Edge`.

## Working Pattern

- Start from `config/routes.php` to confirm the live route and alias.
- Follow the controller action into the matching Twig template.
- Check the relevant factory when the step persists selected data.
- Keep tests aligned with the live controller/factory implementation; do not reintroduce tests for planned service-layer APIs that are not present.
- For Twig, JavaScript, or Sass changes, manually exercise the affected builder step and note what was checked.
