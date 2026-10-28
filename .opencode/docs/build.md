# Build — Grunt

> Toolchain, tasks, placeholders, release ZIPs, translations, tests.
> Read only when working on the build.

## Toolchain

- Grunt (tasks loaded by `load-grunt-tasks`), Node/npm; `npm install` sets up
  `node_modules`. Dart Sass is required directly in `Gruntfile.js` (`require('sass')`).
- No Composer, no Bun — the build is pure Grunt. PHP ≥ 8.1 for the plugin itself;
  PHPUnit for tests.
- `time-grunt` prints per-task timings. `grunt.util.linefeed = '\n'`.

## Tasks (`grunt ...`)

| Command            | What it does                                                                                                     |
| ------------------ | ---------------------------------------------------------------------------------------------------------------- |
| `grunt` (default)  | `clean:temp` → `concat` (JS) → `uglify` (JS min) → `sass` → `concat_css` → `cssmin` — compile assets only          |
| `grunt js`         | `concat` + `uglify` only                                                                                          |
| `grunt css`        | `sass` + `concat_css` + `cssmin` only                                                                             |
| `grunt i18n`       | `checktextdomain` + `makepot`                                                                                     |
| `grunt build`      | default + `i18n` + `clean:wporg` + `copy:wporg` + `replace` + `compress:wporg` → WordPress.org ZIP                 |
| `grunt build:github` | default + `i18n` + `potomo` + `clean:github` + `copy:github` + `replace` + `compress:github` → GitHub ZIP        |
| `grunt release`    | meant to run both builds + release notes — **broken** (see pitfalls)                                              |
| `grunt test`       | `phpunit` (`tests/php/phpunit.xml`) + `jshint` + release notes                                                    |
| `grunt watch`      | dev watcher: `.scss` → `sass`+`concat_css`+`cssmin`; `assets/scripts/src/{admin,frontend}/*.js` → `jshint`+`concat`+`uglify` (500 ms debounce) |

## Inputs → outputs (assets)

- JS concat (`conf.js_files_concat`):
    - `assets/scripts/src/frontend/*.js` → `assets/scripts/wordpress-plugin-stub-frontend.js`
    - `assets/scripts/src/admin/*.js` → `assets/scripts/wordpress-plugin-stub-admin.js`
  then `uglify` minifies every `assets/scripts/*.js` to `*.min.js`
  (`mangle` keeps `jQuery`; banner from `package.json` metadata).
- SCSS (`conf.css_files_compile`): `assets/sass/admin/media.scss` →
  `assets/styles/admin/media.css`, `assets/sass/admin/wp-list-table.scss` →
  `assets/styles/admin/wp-list-table.css` (source maps on).
- CSS concat: `assets/styles/admin/*.css` → `assets/styles/wordpress-plugin-stub-admin.css`;
  `cssmin` produces `.min.css` copies of all `assets/styles/*.css`.
- `clean:compiled` removes all built JS/CSS and `languages/*.mo`; compiled
  files are gitignored — rebuild after clone, never edit them directly.

## Placeholders (replace step)

`conf.replace_patterns` are applied **only to the copies inside `release/`**
(never to the working tree; binary types and `.mo` are skipped):

| Placeholder              | Replaced with                                |
| ------------------------ | -------------------------------------------- |
| `PLUGIN_VERSION`         | `package.json` `version`                     |
| `PLUGIN_TITLE` / `PLUGIN_NAME` | package `title` / `name` (slug)        |
| `PLUGIN_TAGLINE` / `PLUGIN_DESCRIPTION` | package `tagline` / `description` |
| `PLUGIN_URI` / `PLUGIN_URL` / `PLUGIN_GITHUB_WEBSITE` | package `uri` / repository `website` |
| `PLUGIN_TAGS`            | comma-joined package `tags`                  |
| `AUTHOR_NAME` / `AUTHOR_URI` / `AUTHOR_URL` | first `contributors` entry |
| `PLUGIN_REQUIRES_PHP` / `PLUGIN_REQUIRES_WORDPRESS` / `PLUGIN_TESTED_WORDPRESS` | package `requires` / `tested` |
| `PLUGIN_TILL_YEAR`       | current year (copyright range end)           |
| `BUILDTIMESTAMP` / `BUILDTIME` | build time (ms timestamp / ISO string) |
| `IWORKS_RATE_TEXTDOMAIN` / `IWORKS_OPTIONS_TEXTDOMAIN` | package `name` (domains used by vendored frameworks) |
| `^Version: .+$` (main file header) | `Version: {package version}`       |

The version lives **only** in `package.json`; the base class stores the literal
`'PLUGIN_VERSION.BUILDTIMESTAMP'` and the main class `'PLUGIN_VERSION'` — both
are replaced in the release copies.

## Release ZIPs (copy → replace → compress)

Two flavors, both packed with a **wrapper folder** `{plugin-name}/` inside the ZIP:

- **WordPress.org** (`grunt build`) → `release/wporg/{version}/{name}/`, ZIP at
  `release/wporg/{name}.zip`. Excludes (see `excludeCopyFilesWPorg`): repo
  meta (`.git*`, `Gruntfile.js`, `package*.json`, `README.md`, `LICENSE`,
  `.editorconfig`, tests, `node_modules`, `release/`, `.sass-cache`),
  asset sources (`assets/sass`, `assets/scripts/src`, `assets/scss`,
  `assets/styles/frontend`, `**/img/src`, `**/js/src`), the GitHub updater
  class, `includes/pro`, `languages/*.po|*.mo` and `*.map` files. `readme.txt`
  **is** included (required by wp.org).
- **GitHub** (`grunt build:github`) → `release/github/{version}/{name}/`, ZIP at
  `release/github/{name}.zip`. Base excludes (same repo-meta list) plus
  `readme.txt` and `includes/pro`; asset sources (`assets/sass`,
  `assets/scripts/src`) and `.po` files **are** included, and `.mo` files are
  built by `potomo` before packing. Pairs with the GitHub self-updater class.
- A missing/empty source (e.g. `assets/scripts/src/frontend/`) makes the
  matching concat/compile step produce nothing or warn — create the source
  directory before relying on its output.

## i18n

- `checktextdomain` — validates gettext keywords in `{name}.php` and
  `includes/**/*.php` against the domains `wordpress-plugin-stub`,
  `PLUGIN_NAME`, `IWORKS_RATE_TEXTDOMAIN`, `IWORKS_OPTIONS_TEXTDOMAIN`
  (the placeholder domains exist because vendored framework code still carries
  them until `replace` runs on the release copy).
- `makepot` — builds `languages/wordpress-plugin-stub.pot` (wp-plugin type,
  iWorks headers) and updates existing `.po` files in the same directory.
- `potomo` (only `build:github`) — compiles `languages/*.po` → `*.mo`.

## Tests

- `grunt test` → PHPUnit with `tests/php/phpunit.xml` (bootstrap:
  `tests/php/bootstrap.php`) + `jshint` over `Gruntfile.js`,
  `assets/scripts/src/**`, `assets/scripts/test/**`.
- PHPUnit needs a WordPress test environment (see `tests/php/bootstrap.php`);
  `jshint` runs standalone.

## Scaffolding — assets/bin/wp-plugin-create.sh

Turns this stub into a new plugin: clones the repo, then runs `perl -pi -e`
replacements over all text files — `CURRENT_YEAR` → current year,
`wordpress-plugin-stub` → new slug (lowercased name, or `$2`),
`WORDPRESS_PLUGIN_STUB` → uppercase prefix, `wordpress_plugin_stub` → class
prefix, `WordPress Plugin Stub` → display name. Because of this, every literal
occurrence of the stub's names in source is a rename target — keep the
placeholders/naming exact so the script keeps working.

## Runtime quirks / pitfalls (do not fix without approval)

- `grunt release` calls the task `build:wporg`, which is **not registered**
  (only `build` and `build:github` exist) — run the two build tasks instead.
- The `watch` SCSS glob still references `include/modules/**/*.scss`
  (no `include/` directory exists) — harmless leftover.
- `excludeCopyFiles` lists negations after `'**'`; with grunt's minimatch
  ordering the later `!` rules win — keep that order when editing.
- `clean:compiled` deletes `assets/scripts/*.js` (top level only — the built
  bundles, not `src/`) plus built CSS and `languages/*.mo`; never keep source
  JS at the `assets/scripts/` root.
