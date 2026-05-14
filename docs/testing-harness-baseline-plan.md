# Testing Harness And Baseline Plan

## Goal

Establish a trustworthy PHPUnit baseline before expanding coverage for controllers and data paths.

This project already has a small PHPUnit harness, but the current baseline is not green. Coverage expansion should start by fixing the existing signal, then adding lightweight coverage reporting and reusable controller-test support.

## Current State

- PHPUnit is configured by [`phpunit.xml.dist`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/phpunit.xml.dist).
- The test suite is loaded from [`tests/`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/tests).
- The configured bootstrap is Composer autoload only: `vendor/autoload.php`.
- The local PHP runtime has Xdebug available, so PHPUnit coverage can be generated without adding Composer packages.
- The repository contract says to run:

```bash
vendor/bin/phpunit --configuration phpunit.xml.dist
```

## Baseline Result

Initial baseline on 2026-05-14:

```text
Tests: 89, Assertions: 393, Failures: 5, Deprecations: 14, PHPUnit Notices: 5.
```

Failing tests:

- `Tests\Entity\Factory\CampaignTest::testInsertSetsCurrentUserHashAndOwnerMembership`
- `Tests\Entity\Factory\CharacterTest::testInsertCreatesCoreSkillsAfterCharacterIdIsAssigned`
- `Tests\Entity\Factory\CharacterTest::testInsertReturnsErrorWhenCoreSkillCreationFails`
- `Tests\Entity\Factory\FactoryTest::testInsertRunsAfterInsertWithAssignedId`
- `Tests\Entity\Factory\FactoryTest::testInsertReturnsErrorsFromAfterInsertFailures`

The failures all concern transaction expectations around factory insert hooks. Fixing or intentionally realigning these tests is the first gate for any coverage project.

Current baseline after completing this plan:

```text
Tests: 89, Assertions: 394.
Status: OK, with no displayed PHPUnit notices or PHP deprecations.
```

The factory insert path intentionally does not open a transaction. `beforeInsert()`, SQL insert, id assignment, and `afterInsert()` run in order, and insert-hook failures are reported as failed `Result` objects. This avoids nested transaction conflicts when insert hooks create related entities.

## Plan

### 1. Stabilise the existing baseline

1. Inspect the current factory transaction behavior in [`src/Entity/Factory.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Entity/Factory.php), [`src/Entity/Factory/Campaign.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Entity/Factory/Campaign.php), and [`src/Entity/Factory/Character.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Entity/Factory/Character.php).
2. Decide whether the expected transaction wrapping was removed intentionally or whether production behavior regressed.
3. Update either the production code or the stale tests so the suite communicates the intended behavior.
4. Re-run the full suite until it exits cleanly.
5. Address the 14 deprecations and 5 PHPUnit notices enough that new test failures are easy to see.

Exit criteria:

- `vendor/bin/phpunit --configuration phpunit.xml.dist` exits with status 0.
- No PHPUnit notices remain from expectation-free mocks or tests that do not assert behavior.
- Any remaining deprecations are either fixed or documented with a clear reason.

### 2. Add a coverage check workflow

Use Xdebug-backed coverage locally, without installing packages:

```bash
php -d xdebug.mode=coverage vendor/bin/phpunit --configuration phpunit.xml.dist --coverage-filter src --coverage-text=/tmp/swade-coverage.txt --show-uncovered-for-coverage-text
```

Recommended first reporting scope:

- `src/Controller`
- `src/Service/Data`
- `src/Entity/Factory`
- `src/Character`
- `src/Http`
- `src/Middleware`
- `src/Support` equivalents such as [`src/Dice.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Dice.php) and [`src/Filter.php`](/Users/ronan/.codex/worktrees/2fc3/swade-character-manager/src/Filter.php)

Do not gate on a percentage at first. Use uncovered files and branches to drive the controller and data-path plans.

Exit criteria:

- A documented command can produce local text coverage.
- The first coverage pass records uncovered controller and data classes before new tests are added.

### 3. Create reusable test support for Flight controllers

Current controller tests repeatedly reset Flight and define small render, redirect, session, request, response, and URL doubles inside individual files.

Add shared test support under `tests/Support`, for example:

- `ControllerTestCase` to call `Flight::setEngine(new Engine())`, clear `$_POST`, and install common doubles.
- `RenderedResponse` exception for asserting template and render data.
- `RedirectedResponse` exception for asserting redirect target.
- `FlashSession` test double for `success()`, `error()`, `delete()`, and session properties.
- `JsonResponse` or response double for sheet JSON endpoints.
- `FlightUrlMap` helper for stable `Flight::getUrl()` aliases used by controllers.

Keep these helpers test-only and framework-light. The goal is to make controller branch coverage cheap without changing application code.

Implemented support:

- `Tests\Support\ControllerTestCase`
- `Tests\Support\RenderedResponse`
- `Tests\Support\RedirectedResponse`
- `Tests\Support\FlashSession`
- `Tests\Support\RequestStub`
- `Tests\Support\JsonResponse`
- `Tests\Support\FlightUrlMap`

Composer dev autoload now maps `Tests\\` to `tests/`.

Exit criteria:

- Existing controller tests can be migrated gradually without large rewrites.
- New controller tests do not need per-file duplicate helper classes.
- Each controller test can assert render, redirect, flash, request method, POST body, JSON body, and JSON response status.

### 4. Group focused suites by path

Keep the full suite as the final verification command, but use path-focused commands during implementation:

```bash
vendor/bin/phpunit --configuration phpunit.xml.dist tests/Controller
vendor/bin/phpunit --configuration phpunit.xml.dist tests/Service/Data
vendor/bin/phpunit --configuration phpunit.xml.dist tests/Entity/Factory
vendor/bin/phpunit --configuration phpunit.xml.dist
```

Optional `phpunit.xml.dist` follow-up:

- Add named suites for `Controllers`, `Data`, and `Factories` if this becomes a repeated workflow.
- Add `<source>` coverage configuration only after the coverage command has proved useful.

## Risks And Notes

- Do not add Composer or Node packages for coverage.
- Do not test JavaScript or CSS in this phase.
- Treat route coverage and controller method coverage separately: route registration belongs in a small config test, while branch behavior belongs in direct controller tests.
- Avoid browser/manual testing as a substitute for these unit tests; manual passes can remain useful for Twig and client-side behavior later.
