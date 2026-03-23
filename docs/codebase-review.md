# Codebase Review

A thorough review of the SWADE Character Manager codebase — a PHP/Flight framework app for creating Savage Worlds tabletop RPG characters with Google OAuth, encrypted cookie sessions, and a multi-step character creation wizard. The review is split into three addressable sections, ordered by severity within each.

---

## Part 1: Security Issues

### 1.1 No character ownership check (IDOR vulnerability)
**Files:** `src/Entity/Factory/Character.php:23-29`, `src/Controller/Character.php`

`forHash()` returns any character matching the hash regardless of the logged-in user. While 32-char random hashes are hard to guess, any user who obtains a hash (e.g. from a shared URL, browser history, logs) can view and **modify** another user's character. All controller methods (`concept`, `hindrances`, `attributes`, `skills`) are affected.

**Fix:** Add a `user_id` check to `forHash()`, or add a guard in the controller that compares `$entity->user` against `Flight::session()->user->id`.

### 1.2 `var_dump()` + `exit` error handling in Auth controller
**File:** `src/Controller/Auth.php:52-53, 59-60, 88-89`

Three places dump raw debug output (including method names and error messages) directly to the browser, then hard-exit. This leaks internal class names and error details to end users and provides no recovery path.

**Fix:** Replace with flash message + redirect to `/auth` (or a generic error page). Log the actual error details server-side.

### 1.3 No CSRF tokens on forms
**Files:** All character form templates (`views/character/concept.twig`, `hindrances.twig`, `attributes.twig`, `skills.twig`)

Forms submit via POST but have no CSRF token. SameSite=LAX cookies provide some protection, but LAX still allows top-level navigation POST from external sites in some browsers. A dedicated CSRF token is the standard defense-in-depth.

**Fix:** Generate a per-session CSRF token, embed it in a hidden field, and validate it in each POST handler (or via middleware).

### 1.4 `Filter::alpha()` and `Filter::alnum()` use broken character class
**File:** `src/Filter.php:11, 16`

The regex `[^A-z]` matches the ASCII range 65-122, which **includes** `[\]^_`` — six unintended characters between `Z` (90) and `a` (97).

**Fix:** Change to `[^A-Za-z]` and `[^A-Za-z0-9]` respectively.

### 1.5 `unprefix()` uses `str_replace` which can over-strip
**File:** `src/Entity/Factory.php:64`

`str_replace($prefix, '', $string)` replaces **all** occurrences of the prefix in the string, not just the leading one. A column like `user_user_name` would be incorrectly stripped to `name`.

**Fix:** Use `str_starts_with()` + `substr()` to only strip the leading prefix, matching how `prefix()` already works.

### 1.6 Suppressed `unserialize()` in CookieStorage
**File:** `src/Http/Session/CookieStorage.php:71`

`@unserialize($data)` suppresses errors. While the data is encrypted and unlikely to be tampered with, if decryption ever returns garbage, PHP object injection via `unserialize` is a known attack vector. The `@` operator hides any warnings.

**Fix:** Remove `@` suppressor. Consider switching to `json_encode`/`json_decode` for session serialization (Entity already has `__serialize`/`__unserialize` so the session data can be JSON-compatible).

---

## Part 2: Best Practice Violations & Suggestions

### 2.1 Silent exception swallowing in Factory
**File:** `src/Entity/Factory.php:122-124, 144-146, 171-173`

`one()` catches `\Exception` and returns `null`, making database errors indistinguishable from "not found". `insert()` and `update()` catch exceptions and return `false` with no logging — a constraint violation or connection failure is completely invisible.

**Fix:** Log the exception before returning. Consider letting unexpected exceptions propagate (only catch expected ones).

### 2.2 Duplicated "find character or redirect" pattern
**File:** `src/Controller/Character.php:34-41, 48-52, 85-89, 116-120`

The same 5-line block (fetch by hash, check null, flash, redirect) is copy-pasted four times.

**Fix:** Extract to a private helper method like `findCharacterOrRedirect(string $hash): ?Entity`.

### 2.3 Direct `$_POST` access in controllers
**File:** `src/Controller/Character.php:57, 93, 124, 148`

Controllers access `$_POST` directly instead of using `Flight::request()->data` or an injected request object. This couples controllers to the PHP superglobal, making them harder to test.

**Fix:** Use `Flight::request()->data` consistently (already used for query params in Auth controller).

### 2.4 Unused constructor parameter in Auth middleware
**File:** `src/Middleware/Auth.php:12`

`Engine $app` is injected but never used.

**Fix:** Remove the constructor parameter, or use it instead of the static `Flight::` facade.

### 2.5 Missing `declare(strict_types=1)` in CookieStorage
**File:** `src/Http/Session/CookieStorage.php`

This is the only source file missing the strict types declaration, inconsistent with every other file.

**Fix:** Add `declare(strict_types=1);` at the top.

### 2.6 No return type on `upsert()`, `forUser()`, `forHash()`
**Files:** `src/Entity/Factory.php:176`, `src/Entity/Factory/Character.php:15, 23`

These methods lack return type declarations, inconsistent with the rest of the codebase.

**Fix:** Add `: bool` to `upsert()`, `: array` to `forUser()`, `: ?Entity` to `forHash()`.

### 2.7 Loose comparison in `createOrConcept`
**File:** `src/Controller/Character.php:147`

Uses `==` instead of `===` for the method check. Minor but inconsistent — `attributes()` on line 92 uses `===`.

**Fix:** Change to `===`.

### 2.8 Test coverage gaps
**Files:** `tests/`

Good service-layer coverage (26 tests) but zero tests for:
- Controllers (the most bug-prone layer)
- HTTP/Session/Cookie classes
- Auth middleware
- Factory database operations (insert/update/find)
- Twig extensions

**Suggestion:** Prioritise controller and middleware tests next, as these are the integration points where bugs tend to surface.

---

## Part 3: Code Quality & Efficiency

### 3.1 `GameData::allHindrances()` filters on every call
**File:** `src/Service/GameData.php` (the `allHindrances` method)

Filters the full hindrances array each time it's called. In the hindrances view, this is called once per request so it's not a real performance issue, but it's wasteful if ever called multiple times.

**Fix:** Cache the filtered result in a property (lazy initialization).

### 3.2 Magic property bag Entity is too loose
**File:** `src/Entity.php`

`__get` returns `null` for any undefined key with no warning. Typos like `$entity->naem` silently return `null` instead of failing. Combined with the untyped `mixed` storage, this defeats the benefit of `strict_types`.

**Suggestion:** For a future refactor, consider typed entity classes (or at least a known-fields whitelist) to catch typos at development time.

### 3.3 `AssetExtension` redundant key=value storage
**File:** `src/Twig/AssetExtension.php`

Uses `$this->scripts[$path] = $path` for deduplication. The value duplicates the key for no reason.

**Fix:** Use `$this->scripts[$path] = true` or a simple `in_array` check. Minor, low priority.

### 3.4 Game constants scattered across codebase
**Files:** Multiple

Die values `[4, 6, 8, 10, 12]` appear in validation rules (`Character.php:37-41`), service calculations (`CharacterSkills`, `CharacterAttributes`), and JavaScript. The max hindrance points (4), base attribute points (5), and skill points (12) are defined as class constants in their respective services but not centrally.

**Suggestion:** Create a single `GameConstants` class or config section for these shared values. This makes balance changes a one-file edit.

### 3.5 `concept()` doesn't return after redirect
**File:** `src/Controller/Character.php:34-41`

Unlike `hindrances()`, `attributes()`, and `skills()` which `return` after the redirect, `concept()` falls through to `createOrConcept()` after redirecting. The redirect sets the Location header but execution continues.

**Fix:** Add `return;` after the redirect on line 41.

### 3.6 Redundant `return` at end of `createOrConcept`
**File:** `src/Controller/Character.php:165`

Bare `return;` at end of a `void` method does nothing.

**Fix:** Remove it. Trivial cleanup.

---

## Verification

After implementing each section:

- **Part 1 (Security):** Manually test OAuth flow error cases, attempt accessing another user's character hash, submit forms with tampered/missing CSRF tokens, test Filter with edge-case characters `[\]^_``.
- **Part 2 (Best Practices):** Run `vendor/bin/phpunit` to confirm no regressions. Check strict_types and return types with static analysis if available.
- **Part 3 (Quality):** Run `vendor/bin/phpunit`. Manually walk through character creation flow end-to-end.
