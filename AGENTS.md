# AGENTS.md — WordPress Plugin Stub

> Working rules only. Technical knowledge lives in topic docs — read on demand, never preload.

## What the project is

A WordPress plugin stub — a boilerplate for WordPress plugins:
main file + PHP classes (`/includes/iworks`) + options config (`/etc/options.php`)
+ assets (`/assets`), built with a bun bundler (`/bundler` → `release/` ZIPs).
New plugins are scaffolded from this stub (`assets/bin/wp-plugin-create.sh`);
sources contain build-time placeholders (`PLUGIN_VERSION`, `CURRENT_YEAR`, …).

## Docs map — read only what the task needs

| Task | Read first |
| --- | --- |
| PHP classes, admin layer, post types, options, new class | `.opencode/docs/classes.md` |
| Bun bundler, build, dev watcher, ZIPs, placeholders, translations (.pot/.po) | `.opencode/docs/build.md` |
| Loading flow, assets, enqueue, i18n, dependencies, project conventions | `.opencode/docs/project.md` |
| After any task that changed code | skill `verify` (`.opencode/skills/verify/SKILL.md`) |

- Read ONLY the docs relevant to the current task — do not load everything upfront.
- When a change alters documented behavior, update the corresponding doc in the same task.

## Evergreen rules (every task)

- All documentation and README in English.
- `CHANGELOG` is edited only by the user — never touch it unless the user explicitly asks
  (desired format when asked: `## X.Y.Z - RRRR.MM`, categories `Added` / `Improved` / `Fixed` /
  `Removed` (tabs), English, concise).
- Never commit, push, amend or create PRs without an explicit request.
- Naming: kebab-case files (`class-iworks-wordpress-plugin-stub-{name}.php`);
  PHP classes `iworks_wordpress_plugin_stub_` prefix, snake_case, lowercase.
- Version lives only in `package.json` — placeholders (`PLUGIN_VERSION`,
  `BUILDTIMESTAMP`) are replaced by the bundler during build; never hardcode real values in sources.
- Do not fix known typos/pitfalls without approval (see topic docs).

## Commands

`bun run dev` (watcher) · `bun run compile` (one-shot assets) ·
`bun run build` (production → wp.org ZIP) · `bun run build:github` (GitHub ZIP) ·
`bun run make-pot` (translations) · `bun run test` (phpunit)
