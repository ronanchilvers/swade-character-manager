---
name: swade-sass-assets
description: Use when Codex changes SWADE Sass, compiled CSS, frontend presentation, public assets, or UI styling and needs the repository's asset rebuild and commit expectations.
---

# SWADE Sass Assets

Use this skill for frontend styling or public asset changes in `/Users/ronan/Personal/experiments/swade-character-manager`.

## Locations

- Sass sources live in `resources/sass/`.
- Compiled public CSS lives in `web/css/`.
- Public JavaScript and other public assets live in `web/`.
- Twig templates live in `views/`.

## Commands

- Use `npm run sass-dev` for watch/update development builds.
- Use `npm run sass-prod` for compressed committed output.
- Install frontend tooling with `npm install` only if rebuilding CSS requires it, and ask before installing packages.
- Do not add new Node packages without asking first.

## Commit And Verification

- When Sass changes modify tracked files under `web/css/`, include the rebuilt CSS artifacts in the same change.
- For UI-affecting changes, manually exercise the affected builder step or page and report what was checked.
