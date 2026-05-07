# Admin User Management Plan

## Goal

Add a simple superuser-only admin area for managing all application users.

The first implementation should let a superuser:

- see every user in the system
- inspect and edit a single user
- grant or revoke the `superuser` flag
- disable or re-enable a user account

The first implementation should not delete users. User deletion has follow-on effects for `characters.character_user` and there is no existing reassignment or cascade policy.

## Current Code Constraints

- Authentication is Google OAuth based in [`src/Controller/Auth.php`](/Users/ronan/Personal/experiments/swade-character-manager/src/Controller/Auth.php).
- Users are auto-provisioned on first login through [`src/Entity/Factory/User.php`](/Users/ronan/Personal/experiments/swade-character-manager/src/Entity/Factory/User.php).
- Route protection is currently binary authenticated-or-not through [`src/Middleware/Auth.php`](/Users/ronan/Personal/experiments/swade-character-manager/src/Middleware/Auth.php).
- The `users` table currently has no role, privilege, or account-status columns in [`schema/010_users.sql`](/Users/ronan/Personal/experiments/swade-character-manager/schema/010_users.sql).
- The session stores the full user object in the encrypted cookie-backed session, so authorization data can go stale unless the current user is refreshed from the database.
- `schema/migrations/` exists but is currently empty, so this feature can establish the timestamp-prefixed migration pattern for additive schema changes.

## Proposed Scope

### 1. Add persisted privilege and status fields

Add a new timestamp-prefixed migration file under [`schema/migrations/`](/Users/ronan/Personal/experiments/swade-character-manager/schema/migrations), for example:

- [`schema/migrations/20260507120000_add_superuser_and_status_to_users.sql`](/Users/ronan/Personal/experiments/swade-character-manager/schema/migrations/20260507120000_add_superuser_and_status_to_users.sql)

The migration should add:

- `user_superuser TINYINT(1) UNSIGNED NOT NULL DEFAULT 0`
- `user_status VARCHAR(16) NOT NULL DEFAULT 'active'`

Implementation note:

- Keep `user_status` as a plain string column, not an enum, with initial valid values of `active` and `inactive`.
- The migration filename should be prefixed with a sortable timestamp so files can be applied in sequence.
- Update [`schema/010_users.sql`](/Users/ronan/Personal/experiments/swade-character-manager/schema/010_users.sql) as the canonical bootstrap schema as well, so a fresh rebuild matches the migrated structure.
- Existing local databases should use the migration rather than a manual one-off `ALTER TABLE`.

### 2. Extend the user factory

Update [`src/Entity/Factory/User.php`](/Users/ronan/Personal/experiments/swade-character-manager/src/Entity/Factory/User.php) to:

- include validation for `superuser`
- include validation for `status`, restricted to `active` and `inactive`
- default new Google-provisioned accounts to `superuser = 0`
- default new Google-provisioned accounts to `status = 'active'`
- add a small query helper for admin screens if that improves readability, for example an ordered `all()`/`find()` wrapper for user listing

### 3. Keep the logged-in user fresh

Adjust the authenticated request path so `Flight::session()->user` is reloaded from the database by id before authorization decisions are made.

Suggested approach:

- update [`src/Middleware/Auth.php`](/Users/ronan/Personal/experiments/swade-character-manager/src/Middleware/Auth.php) to:
  - confirm a session user id exists
  - reload that user from the `User` factory
  - clear the session and redirect to `/auth` if the user no longer exists
  - clear the session and redirect to `/auth` if the user status is not `active`
  - replace `session.user` with the freshly loaded entity before continuing

This avoids stale `superuser` and `status` state in the cookie-backed session and also keeps edited names and emails in sync in the top bar.

### 4. Block inactive users at login

Update [`src/Controller/Auth.php`](/Users/ronan/Personal/experiments/swade-character-manager/src/Controller/Auth.php) so that after loading or provisioning the user:

- users with `status = 'inactive'` are not stored in the session
- the app shows a generic login failure message, for example `Login failed`

This ensures inactive users are blocked both when starting a new session and when an existing session is refreshed by auth middleware.

Security note:

- keep login-screen auth failures generic to avoid leaking whether an account exists or is disabled
- use the same generic message for inactive-user denial and any comparable local-auth rejection path

### 5. Add a dedicated superuser guard

Create [`src/Middleware/Superuser.php`](/Users/ronan/Personal/experiments/swade-character-manager/src/Middleware/Superuser.php) to enforce:

- a logged-in user exists
- the refreshed session user has `superuser` truthy

Behaviour:

- if not logged in, let the existing auth middleware handle redirect to `/auth`
- if logged in but not a superuser, set a flash error and redirect to the home page

### 6. Add an admin users controller

Create [`src/Controller/Admin/Users.php`](/Users/ronan/Personal/experiments/swade-character-manager/src/Controller/Admin/Users.php) with an intentionally small surface area:

- `index(): void`
  - list all users ordered by last name, first name, then email
  - surface each user status clearly
  - include a `Disable` action for active users
  - include a `Re-enable` action for inactive users
- `edit(string $id): void`
  - render a simple edit form on `GET`
  - process updates on `POST`
  - allow editing:
    - first name
    - last name
    - email
    - superuser flag
    - status
- `disable(string $id): void`
  - handle a `POST` from the list view
  - set `status` to `inactive`
- `enable(string $id): void`
  - handle a `POST` from the list view
  - set `status` to `active`

Implementation details:

- reuse `Filter::noTags()` for scalar input sanitisation
- use the existing factory validation and `update()` flow
- flash success/error messages through the existing session helper
- redirect back to the edit screen after save
- redirect list actions back to the listing page

Safety rule:

- prevent a superuser from removing their own `superuser` flag in v1, or handle it explicitly with a confirmation and immediate redirect out of admin

The safer first pass is to block self-demotion to avoid accidental lockout.

Additional safety rule:

- prevent a superuser from disabling their own account from either the listing or edit screen in v1

That avoids an easy self-lockout path and keeps the first admin implementation recoverable.

### 7. Register admin routes

Update [`config/routes.php`](/Users/ronan/Personal/experiments/swade-character-manager/config/routes.php) with a new route group:

- `GET /admin/users` -> `Admin\Users::index` alias `admin_users_index`
- `GET|POST /admin/users/@id:[0-9]+` -> `Admin\Users::edit` alias `admin_users_edit`
- `POST /admin/users/@id:[0-9]+/disable` -> `Admin\Users::disable` alias `admin_users_disable`
- `POST /admin/users/@id:[0-9]+/enable` -> `Admin\Users::enable` alias `admin_users_enable`

Middleware stack:

- `App\Middleware\Auth`
- `App\Middleware\Superuser`

### 8. Add simple admin views

Create:

- [`views/admin/users/index.twig`](/Users/ronan/Personal/experiments/swade-character-manager/views/admin/users/index.twig)
- [`views/admin/users/edit.twig`](/Users/ronan/Personal/experiments/swade-character-manager/views/admin/users/edit.twig)

These should extend the existing default layout and stay visually plain:

- user list as a table or stacked cards
- clear columns/labels for name, email, created date, superuser status
- clear status display for `active` or `inactive`
- obvious edit action
- `Disable` button for active users directly in the listing
- `Re-enable` button for inactive users directly in the listing
- edit form reusing existing button and field classes
- a simple badge or text label for superuser state and account status

### 9. Expose navigation only for superusers

Update [`views/partials/topbar.twig`](/Users/ronan/Personal/experiments/swade-character-manager/views/partials/topbar.twig) to show an `Admin` or `Manage Users` action only when:

- `session.has('user')`
- `session.user.superuser`

This is convenience navigation only; authorization must still be enforced server-side by middleware.

## Suggested Delivery Order

1. Add a timestamped migration for `user_superuser` and `user_status`, and update `schema/010_users.sql` to match.
2. Update the user factory defaults and validation for both fields.
3. Block inactive users in the login flow.
4. Refresh the authenticated user from the database in the auth middleware.
5. Add the superuser middleware.
6. Add the admin users controller and routes, including list-level disable and re-enable actions.
7. Add the list and edit Twig views.
8. Add the conditional top-bar link.
9. Verify the self-edit, self-demotion, and self-disable behaviour explicitly.

## Verification Plan

Because there is little existing controller coverage, implementation should include focused verification around the most fragile parts:

- add a new [`tests/Entity/Factory/UserTest.php`](/Users/ronan/Personal/experiments/swade-character-manager/tests/Entity/Factory/UserTest.php) for validation/default expectations
- add middleware tests if practical for:
  - unauthenticated access
  - authenticated non-superuser access
  - authenticated superuser access
  - inactive authenticated users being forced back out
- if controller tests are too expensive for the current harness, perform manual verification for:
  - superuser can access `/admin/users`
  - non-superuser is redirected away
  - inactive users cannot log in
  - inactive-user login denial shows only a generic failure message
  - disabling an active user immediately prevents access on the next request
  - re-enabling an inactive user allows login again
  - superuser flag changes take effect on the next request without logging out
  - edited first/last name appears in the top bar after save

## Open Decisions

These should be settled before implementation starts:

- whether admins may edit user email addresses, or whether email should remain identity data sourced only from Google login
- whether self-demotion should be blocked outright or allowed with an explicit confirmation path
- whether status should be editable in the detail form as well as through list actions

## Recommended v1 Shape

Keep v1 narrow:

- list all users
- edit one user
- toggle superuser
- disable or re-enable from the list
- no delete
- no create
- no bulk actions

That matches the current Google-provisioned account model and avoids introducing character ownership edge cases before the admin surface exists.
