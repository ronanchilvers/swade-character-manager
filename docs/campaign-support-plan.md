# Campaign Support Plan

## Goal

Add authenticated campaign support so users can create campaigns, invite other users by link, let invited users link their characters, and let superusers review campaigns and their linked characters.

The first implementation should let:

- any active logged-in user create a campaign
- the campaign creator own the campaign
- the campaign expose a stable invite link based on a campaign hash
- invited users authenticate, join the campaign, and link their own characters
- superusers view all campaigns and the characters attached to them

The first implementation should not add campaign deletion, ownership transfer, campaign chat/log features, or campaign-specific character permissions beyond linking characters to campaigns.

## Current Code Constraints

- Authentication is Google OAuth based in [`src/Controller/Auth.php`](/Users/ronan/Personal/experiments/swade-character-manager/src/Controller/Auth.php).
- Authenticated routes use [`App\Middleware\Auth`](/Users/ronan/Personal/experiments/swade-character-manager/src/Middleware/Auth.php).
- Superuser-only routes use [`App\Middleware\Superuser`](/Users/ronan/Personal/experiments/swade-character-manager/src/Middleware/Superuser.php).
- Character ownership is currently stored on `characters.character_user` and enforced by character controllers and factories.
- Characters already use stable 32-character hashes for public route identifiers.
- Factory table and column names are inferred from singular factory class names, so new campaign factories should follow the existing singular-class/plural-table and prefixed-column conventions.
- Bootstrap SQL files in [`schema/`](/Users/ronan/Personal/experiments/swade-character-manager/schema) are destructive table rebuilds and must be applied in filename order.
- Additive migrations live under [`schema/migrations/`](/Users/ronan/Personal/experiments/swade-character-manager/schema/migrations).

## Proposed Scope

### 1. Add campaign persistence

Add timestamped migrations for two new campaign tables plus one new nullable column on `characters`:

- `campaigns`
- `campaign_members`
- `characters.character_campaign`

Recommended `campaigns` fields:

```sql
campaign_id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT
campaign_hash        VARCHAR(32) NOT NULL
campaign_user        BIGINT UNSIGNED NOT NULL
campaign_name        VARCHAR(128) NOT NULL
campaign_description TEXT NULL
campaign_created     DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
campaign_updated     DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6)
```

Indexes:

- primary key on `campaign_id`
- unique key on `campaign_hash`
- index on `campaign_user`

Recommended `campaign_members` fields:

```sql
campaign_member_id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT
campaign_member_campaign_id BIGINT UNSIGNED NOT NULL
campaign_member_user_id     BIGINT UNSIGNED NOT NULL
campaign_member_role        VARCHAR(16) NOT NULL DEFAULT 'member'
campaign_member_created     DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
campaign_member_updated     DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6)
```

Indexes:

- primary key on `campaign_member_id`
- unique key on `(campaign_member_campaign_id, campaign_member_user_id)`
- index on `campaign_member_user_id`
- index on `campaign_member_role`

Recommended `characters` addition:

```sql
character_campaign BIGINT UNSIGNED NULL
```

Indexes:

- index on `character_campaign`

Implementation notes:

- Keep `campaign_member_role` as a plain string column with initial valid values of `owner` and `member`.
- Add migration files under `schema/migrations/` for the two campaign tables and the `characters.character_campaign` column.
- Do not update `schema/020_characters.sql` for this feature. Fresh databases should be built by applying migrations after the base schema.
- Do not add new destructive bootstrap files for campaigns in v1 unless the project later standardises fresh database rebuilds around bootstrap files again.
- Do not add a `campaign_characters` join table in v1. A nullable column on `characters` matches the rule that a character can only be in one campaign at a time.

### 2. Add campaign factories

Create factories under [`src/Entity/Factory/`](/Users/ronan/Personal/experiments/swade-character-manager/src/Entity/Factory):

- `Campaign`
- `Campaign\Member`

`Campaign` should:

- validate `hash`, `user`, and `name`
- generate `hash` with `Str::token(32)` before insert
- set `user` from `Flight::session()->user->id` before insert
- create an owner membership after the campaign row is inserted
- keep campaign URLs resolving through `campaign_hash`, but isolate hash lookup and invite-link generation behind helpers so a later hash-rotation action can update the hash without changing route consumers
- expose lookup helpers:
  - `forHash(string $hash): ?Entity`
  - `forUser(int $userId): array`
  - `forMemberUser(int $userId): array`
  - `allWithSummary(): array` for admin campaign listing if SQL joins keep the controller simpler

`Campaign\Member` should:

- expose role constants for `owner` and `member`
- validate campaign id, user id, and role
- override the base factory table name and prefix so it writes to `campaign_members` with `campaign_member_` columns despite the nested class name
- expose helpers:
  - `forCampaign(Entity $campaign): array`
  - `forUser(int $userId): array`
  - `isMember(Entity $campaign, int $userId): bool`
  - `leaveCampaign(Entity $campaign, int $userId): Result`
  - `ensureMember(Entity $campaign, int $userId, string $role = 'member'): Result`
- make duplicate joins idempotent by returning success when the membership already exists
- block membership leave when the user still has any characters assigned to the campaign

Update `Character` to support campaign assignment:

- validate `campaign` as nullable or a positive integer when present
- expose helpers:
  - `forCampaign(Entity $campaign): array`
  - `forCampaignAndUser(Entity $campaign, int $userId): array`
  - `forUserWithoutCampaign(int $userId): array`
  - `joinCampaign(Entity $campaign, Entity $character): Result`
  - `leaveCampaign(Entity $character): Result`
- reject or return an error when joining a campaign if the character already has a different `campaign` value
- make joining the same campaign idempotent

If a future version allows many campaigns per character, introduce `App\Entity\Factory\Campaign\Character` at that point and have it override the table name and prefix to `campaign_characters` and `campaign_character_`.

Register the new factories in [`config/services.php`](/Users/ronan/Personal/experiments/swade-character-manager/config/services.php).

### 3. Preserve invite links through authentication

Invite links should require authentication, but the app should return users to the invite after Google login.

Update the auth middleware and login flow so:

- unauthenticated requests to safe `GET` routes store the current relative URL in the session before redirecting to `/auth`
- successful login redirects to the stored return URL when present
- the stored return URL is cleared after use
- only same-site relative URLs are accepted

This keeps `/campaigns/join/<hash>` usable when shared with a user who is not currently logged in.

### 4. Add campaign user routes

Add a new authenticated route group in [`config/routes.php`](/Users/ronan/Personal/experiments/swade-character-manager/config/routes.php):

- `GET /campaigns` -> `Campaigns::index` alias `campaigns_index`
- `GET|POST /campaigns/create` -> `Campaigns::create` alias `campaigns_create`
- `GET /campaigns/@hash:[a-z0-9]{32}` -> `Campaigns::view` alias `campaigns_view`
- `GET|POST /campaigns/join/@hash:[a-z0-9]{32}` -> `Campaigns::join` alias `campaigns_join`
- `POST /campaigns/@hash:[a-z0-9]{32}/characters` -> `Campaigns::addCharacter` alias `campaigns_add_character`
- `POST /campaigns/@hash:[a-z0-9]{32}/characters/@character_hash:[a-z0-9]{32}/leave` -> `Campaigns::leaveCharacter` alias `campaigns_leave_character`
- `POST /campaigns/@hash:[a-z0-9]{32}/leave` -> `Campaigns::leave` alias `campaigns_leave`

Create [`src/Controller/Campaigns.php`](/Users/ronan/Personal/experiments/swade-character-manager/src/Controller/Campaigns.php).

Controller behavior:

- `index()` lists campaigns owned by or joined by the current user.
- `create()` creates a campaign and redirects to its detail view.
- `view()` requires the current user to be a campaign member or superuser.
- `join()` loads the campaign by hash, creates membership for the current user, and redirects to campaign detail.
- `leave()` removes the current user's campaign membership only when they have no characters currently assigned to that campaign.
- `addCharacter()` requires current-user membership and only links characters owned by the current user that are not already in another campaign.
- `leaveCharacter()` requires current-user membership and only clears `character_campaign` for a character owned by the current user and currently assigned to this campaign.
- missing campaigns or unauthorized access should use the existing flash-and-redirect style.

### 5. Add campaign views

Create campaign Twig views under [`views/campaigns/`](/Users/ronan/Personal/experiments/swade-character-manager/views):

- `index.twig`
- `create.twig`
- `view.twig`
- `join.twig`

The views should:

- extend the existing default layout
- reuse existing button, field, card, and flash patterns
- show the invite link on the campaign detail page for the owner and superusers
- show the campaign roster
- show sheet links only for characters owned by the current user
- show other members' characters as roster entries without sheet links
- show a character picker containing only the current user's characters that are not already in any campaign
- show a leave action only for the current user's characters in this campaign
- show a campaign leave action only when the current member has no characters assigned to the campaign; otherwise explain that characters must leave first

Update [`views/partials/topbar.twig`](/Users/ronan/Personal/experiments/swade-character-manager/views/partials/topbar.twig) to add a `Campaigns` navigation item for logged-in users.

### 6. Add admin campaign routes and views

Add routes under the existing `/admin` group:

- `GET /admin/campaigns` -> `Admin\Campaigns::index` alias `admin_campaigns_index`
- `GET /admin/campaigns/@hash:[a-z0-9]{32}` -> `Admin\Campaigns::view` alias `admin_campaigns_view`

Create [`src/Controller/Admin/Campaigns.php`](/Users/ronan/Personal/experiments/swade-character-manager/src/Controller/Admin/Campaigns.php).

Admin behavior:

- list all campaigns with owner, created date, member count, and linked-character count
- show a campaign detail page with owner, members, and linked characters
- include links to character sheets
- do not add edit or delete controls in v1
- admin sheet links are allowed because superusers already have sheet access

Create views under [`views/admin/campaigns/`](/Users/ronan/Personal/experiments/swade-character-manager/views/admin):

- `index.twig`
- `view.twig`

Update superuser navigation in the top bar to include `Manage Campaigns` alongside existing user management.

### 7. Keep character sheet access private within campaigns

Do not broaden character sheet authorization for campaign members in v1.

Implementation notes:

- keep sheet access limited to the character owner and superusers
- campaign membership should only allow viewing campaign roster metadata, not full character sheets for other players' characters
- user campaign views should avoid rendering sheet links for characters the current user does not own

### 8. Block character deletion while in a campaign

Update character deletion so a character cannot be deleted while `character_campaign` is set.

Behavior:

- if a user tries to delete a character that is currently in a campaign, show a flash error explaining that the character must leave the campaign first
- do not delete or clear campaign membership automatically
- keep the existing user-owned character deletion checks unchanged

## Suggested Delivery Order

1. Add schema migrations for campaign tables and `characters.character_campaign`.
2. Add and register campaign factories.
3. Add `Character` campaign-assignment helpers and validation.
4. Add factory-level tests for lookup, membership, membership leave rules, idempotent joins, character campaign assignment, and character leave behavior.
5. Add safe auth return URL handling for invite links.
6. Add user-facing campaign routes, controller, and views.
7. Add campaign navigation.
8. Add admin campaign routes, controller, and views.
9. Block character deletion while assigned to a campaign.
10. Run focused tests, then the full PHPUnit suite.

## Verification Plan

Add focused PHPUnit coverage for:

- campaign creation assigns the current user and generates a 32-character hash
- campaign creation creates owner membership
- campaign lookup by hash uses `campaign_hash`
- repeated invite joins do not create duplicate memberships
- member leave is blocked while the member still has characters assigned to the campaign
- member leave succeeds after the member's characters have left the campaign
- non-members cannot view campaign detail
- members can view campaign detail
- linked characters must belong to the current user
- linked characters cannot already belong to a different campaign
- repeated character links to the same campaign are idempotent
- character leave clears `character_campaign`
- character deletion is blocked while `character_campaign` is set
- campaign members cannot view sheets for other members' characters
- superusers can render all-campaign admin views
- login redirects back to a safe stored invite URL
- unsafe external return URLs are ignored

Manual verification should cover:

- logged-in user can create a campaign
- campaign detail shows an invite link
- logged-out invite recipient is redirected to login and then returned to the join flow
- invited user can join and link one of their characters
- invited user cannot link another user's character
- invited user cannot link a character already in another campaign
- a user can remove their own character from a campaign
- a member cannot leave a campaign until all their characters have left it
- a campaign character must leave before it can be deleted
- campaign members see other roster characters without sheet links
- campaign owner can see linked characters
- superuser can view all campaigns and attached characters from admin

Run:

```bash
vendor/bin/phpunit --configuration phpunit.xml.dist
```

## Open Decisions For Later Versions

- whether campaign owners can remove members
- whether campaign owners can remove other users' characters
- whether campaign ownership can be transferred
- whether campaigns should support public descriptions or notes
- whether campaign hash rotation should invalidate old invite links immediately or preserve a short-lived redirect/alias history
- whether superusers should be able to edit or delete campaigns

## Assumptions

- Campaign membership is persistent, not just a one-time character attachment.
- The campaign hash is the invite token for v1.
- Campaign hash rotation should be allowed for in the design, but no rotate action, route, or UI is implemented in v1.
- Campaign creators are stored as both `campaign_user` and an `owner` membership row.
- Members can link multiple characters they own, but each character can only belong to one campaign.
- Members can leave a campaign only after all their characters have left it.
- Campaign character assignment is stored on `characters.character_campaign`; there is no `campaign_characters` table in v1.
- Campaign members cannot view other members' character sheets unless they are also superusers.
- Superusers can view campaign data, but edit/delete controls are out of scope for v1.
- No new Composer or Node packages are required.
