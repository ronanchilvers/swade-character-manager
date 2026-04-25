---
name: swade-release-pr
description: Use when Codex prepares SWADE character manager commits, pull request descriptions, release notes, or final change summaries that need repository-specific wording and verification details.
---

# SWADE Release And PR

Use this skill when preparing commits, PRs, or release notes for `/Users/ronan/Personal/experiments/swade-character-manager`.

## Commits

- Keep commit messages short, specific, and sentence-case.
- Mention the behavior changed, not the implementation tool.
- Do not stage unrelated work.

## Pull Requests

PR descriptions should cover:

- User-visible behavior.
- Schema changes, especially destructive bootstrap SQL changes.
- `.env.php` changes or required environment updates.
- Manual verification for UI work.
- PHPUnit or focused test commands run.

Keep the tone natural and concise.
