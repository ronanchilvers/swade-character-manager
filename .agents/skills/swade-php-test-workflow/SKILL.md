---
name: swade-php-test-workflow
description: Use when Codex adds, updates, runs, or interprets PHPUnit tests for the SWADE character manager, especially for controllers, entity factories, presenters, support utilities, and catalog loaders.
---

# SWADE PHP Test Workflow

Use this skill when changing PHP behavior or adding PHPUnit coverage in `/Users/ronan/Personal/experiments/swade-character-manager`.

## Command

Run the suite with:

```bash
vendor/bin/phpunit --configuration phpunit.xml.dist
```

## Test Layout

- Prefer PHPUnit tests named `*Test.php` under `tests/`.
- Current test areas include `tests/Character/`, `tests/Entity/Factory/`, `tests/Http/`, `tests/Service/Data/`, and `tests/Support/`.
- Coverage is centered on live factory behavior, presenter behavior, support utilities, and catalog loading.

## Expectations

- Test the implementation that actually exists in `src/`.
- Do not reintroduce tests for planned service-layer classes or APIs that are not present in the tree.
- For controller/factory changes, prefer focused tests around the affected behavior.
- For Twig, JavaScript, or Sass changes, do a manual pass through the affected builder step and report what was exercised.

As of `docs/codebase-review.md` on 2026-04-22, the PHPUnit suite was documented as passing cleanly against the live code.
