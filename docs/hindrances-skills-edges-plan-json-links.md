# Rule Catalog and Character Selections Plan

Status: historical planning document, updated on 2026-04-16 to reflect repository reality.

## Original Goal
This plan proposed a JSON-catalog-driven character system with string keys persisted in junction tables for hindrances, skills, and edges.

## What Matches the Current Repository
- The schema stores string catalog keys in `hindrances`, `skills`, and `edges`.
- The app has factories for `App\Entity\Factory\Hindrance`, `Skill`, and `Edge`.
- Hindrance and skill editor screens are implemented.
- The builder flow persists selections against a single character row.

## What Changed From the Original Plan
- The runtime catalog source is `data/*.php`, loaded through `App\Service\Data\Manager`, not a JSON repository service.
- The proposed dedicated catalog repository and character-options services were not added.
- The edges step is still not implemented in routes, controllers, or Twig views.
- The schema currently lives in `schema/000_schema.sql`, not in a later additive migration file.

## Current Live Architecture
- Hindrances: `App\Controller\Character\Hindrances` plus `App\Entity\Factory\Hindrance`
- Skills: `App\Controller\Character\Skills` plus `App\Entity\Factory\Skill`
- Catalog loading: `App\Service\Data\Manager` with `Hindrances`, `Skills`, and `Edges`

## Implication for Future Work
Do not assume this document’s original repository/service design exists in the codebase. If future work resumes on edges or catalog architecture:

1. Start from the live controller/factory/data-manager structure.
2. Decide whether to evolve that structure or replace it with the previously planned service layer.
3. Align tests and docs at the same time so the repository continues to test only implemented APIs.
