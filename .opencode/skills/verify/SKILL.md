---
name: verify
description: post-work verification — full project build and a check that everything works
---

When a task that changed code (PHP, JS, SCSS) is finished, **before reporting success** run:

## 1. Sanity-check diffs

- `git diff --stat` — compare the changes against the list of files you actually edited. **Any changed file you did not edit is an alarm signal** (a stray build/formatter step overwriting sources).
- Compiled files (`assets/styles/`, `assets/scripts/wordpress-plugin-stub-*.js`, `languages/*.mo`) are gitignored — they should never appear in a diff at all.

## 2. Verification with a full build

Do not declare success based on a single quick command — run the **full task set** for the changed asset type (each must pass end-to-end):

- **JS (`assets/scripts/src/**`)**: `grunt js` (jshint → concat → uglify).
- **SCSS (`assets/sass/**`)**: `grunt css` (sass → concat_css → cssmin).
- **PHP (`includes/**`, `etc/`, main file)**: `php -l` on every changed file (new files included) — there is no phpcs setup in this repo, so do not claim WPCS compliance, just match the surrounding style. If a WordPress test env is available, `grunt test` (PHPUnit) is a bonus.
- **Mixed types or release-relevant changes (headers, `readme.txt`, `package.json`)**: full `grunt build` — must pass end-to-end (compile → i18n → wporg ZIP). `grunt build:github` when the GitHub release path is affected.

Note: the Grunt pipeline never modifies source files (formatting is manual) — a build that rewrites your working tree means something is wrong.

## 3. Report

Summarize the build result and the list of verified files. If the build does not pass — fix it before reporting task completion (or clearly list the blockers for the user to decide). Never write "done" without a successful completion of step 2.
