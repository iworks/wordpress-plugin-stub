# Project mechanics — WordPress Plugin Stub

> Loading flow, assets, translations, options framework, dependencies, general conventions.
> Read only when the task involves these areas.

## Overview

A WordPress plugin boilerplate ("stub") in the iWorks style. It is a template:
new plugins are created from it with `assets/bin/wp-plugin-create.sh`, which
renames the slug, class names, prefixes and placeholders. Source files contain
**literal placeholders** (`PLUGIN_VERSION`, `PLUGIN_TITLE`, `CURRENT_YEAR`,
`BUILDTIMESTAMP`, …) — they are replaced by Grunt at build time (see
`build.md`), never in the source.

The version lives only in `package.json` (`"version"`); the main file header
and the version constant both use the `PLUGIN_VERSION` placeholder.

## Directory structure

```
/
├── wordpress-plugin-stub.php   # Plugin bootstrap (header, constants, load filters)
├── etc/
│   └── options.php             # Options page configuration (iworks_options format)
├── includes/
│   └── iworks/
│       ├── class-iworks-wordpress-plugin-stub-base.php      # Base class
│       ├── class-iworks-wordpress-plugin-stub.php           # Main plugin class
│       ├── wordpress-plugin-stub/                           # Feature classes
│       │   ├── class-iworks-wordpress-plugin-stub-posttypes.php
│       │   ├── class-iworks-wordpress-plugin-stub-wp-admin.php
│       │   ├── class-iworks-wordpress-plugin-stub-cron.php
│       │   ├── class-iworks-wordpress-plugin-stub-db.php
│       │   ├── class-iworks-wordpress-plugin-stub-github.php
│       │   └── posttypes/        # Abstract post type + one class per type
│       ├── options/              # Vendored iworks options framework (own git repo)
│       └── rate/                 # Vendored iworks rate/love notices (own git repo)
├── assets/
│   ├── sass/admin/              # SCSS sources (_mixin, media, wp-list-table)
│   ├── scripts/src/admin/       # Admin JS sources (frontend sources dir may not exist yet)
│   ├── styles/                  # Compiled CSS (gitignored)
│   ├── templates/               # Post type templates (featured, hero, opinion, project, testimonial)
│   └── bin/wp-plugin-create.sh  # Scaffolding script for new plugins
├── languages/                   # wordpress-plugin-stub.pot (+ .po/.mo built)
├── tests/php/                   # PHPUnit (phpunit.xml, bootstrap.php, test-sample.php)
├── Gruntfile.js                 # Build system — see build.md
├── package.json                 # Version + release metadata (single source of truth)
└── readme.txt                   # WordPress.org readme
```

## Loading flow

1. WordPress loads `wordpress-plugin-stub.php` (textdomain `wordpress-plugin-stub`):
   - defines `IWORKS_WORDPRESS_PLUGIN_STUB_VERSION` (`'PLUGIN_VERSION'`) and
     `IWORKS_WORDPRESS_PLUGIN_STUB_PREFIX` (`'iworks_wordpress-plugin-stub_'`);
   - requires the main class from `includes/iworks/` (guarded by `class_exists`),
     `etc/options.php`, and the vendored `iworks_options` class;
   - registers the post-type load filters (all `__return_false` — off by default);
   - defines `iworks_wordpress_plugin_stub_get_options()` and instantiates
     `iworks_wordpress_plugin_stub`, registering activation/deactivation hooks.
2. `iworks_wordpress_plugin_stub::__construct()`:
   - registers `init` → `action_init_settings()` (frontend CSS enqueue, non-admin only);
   - loads feature classes from `includes/iworks/wordpress-plugin-stub/` with the
     `is_file()` + `include_once` + `new` pattern: `posttypes`, `github`, `cron`, `db`;
     `wp-admin` only when `is_admin()`;
   - adds the filter `wordpress-plugin-stub/is_active` (`__return_true`).
3. `iworks_wordpress_plugin_stub_posttypes` globs `posttypes/class*.php`, extracts
   the type name from the filename and loads each post type class **only when the
   filter `wordpress-plugin-stub/load/post-type/{name}` returns `true`**
   (name comes from the *filename*, see Known pitfalls).

## Constants

- `IWORKS_WORDPRESS_PLUGIN_STUB_VERSION` — plugin version (`'PLUGIN_VERSION'` placeholder).
- `IWORKS_WORDPRESS_PLUGIN_STUB_PREFIX` — prefix for options (`'iworks_wordpress-plugin-stub_'`).
- `IWORKS_DEV_MODE` (optional, `wp-config.php`) — dev mode: non-minified asset
  handling in `get_version()` (md5 of file / `time()`), see below.
- Global: `$iworks_wordpress_plugin_stub_options` (options object),
  `$iworks_wordpress_plugin_stub` (main instance).

## Post types

- Post types are disabled by default; a theme/plugin must enable them via filters:
  `add_filter( 'wordpress-plugin-stub/load/post-type/{name}', '__return_true' )`.
- The bootstrap file stubs these filters with `__return_false` for:
  `faq`, `hero`, `opinion`, `page`, `person`, `post`, `project`, `promo`, `publication`.
- Post type class names map to registered post types (`iworks_faq`, `iworks_person`,
  …; `page` and `post` reuse the native types). Shared taxonomies: `iworks_faq_group`,
  `iworks_person_role`. Templates live in `assets/templates/`.

## Options layer (etc/options.php + iworks_options)

- `etc/options.php` returns the config array from `iworks_wordpress_plugin_stub_options()`.
- Single page entry `$options['index']`: `page_title`, `menu` (placement:
  `options`, `submenu`, `management`, `theme`, `posts`, `pages`, `users`,
  `plugins`, `comments`, `dashboard`, `settings`, `media`, `custom`), `parent`
  (required for `submenu`), `use_tabs`, `version`.
- Field arrays: `options` (filter `wordpress-plugin-stub/etc/config/options`),
  `metaboxes` (filter `.../metaboxes`), `pages` subpages (filter `.../pages`).
- Field shape: `name`, `type` (e.g. `heading`, `text`, `textarea`), `th` (label),
  `description`, `classes`, `sanitize_callback`, `since`.
- The option object is created by `iworks_wordpress_plugin_stub_get_options()`
  (option function name + `IWORKS_WORDPRESS_PLUGIN_STUB_PREFIX` prefix); classes
  access it via `$this->check_option_object()` (base class).

## Assets layer

- **CSS**: `assets/sass/admin/*.scss` → compiled to `assets/styles/admin/*.css`
  → concatenated to `assets/styles/wordpress-plugin-stub-admin.css` (+ `.min.css`).
  There is no frontend SCSS entry yet.
- **JS**: `assets/scripts/src/admin/*.js` → concatenated to
  `assets/scripts/wordpress-plugin-stub-admin.js` → uglified to `.min.js`.
  The `assets/scripts/src/frontend/` directory does not exist yet (Grunt expects
  it for the frontend bundle — create it when needed).
- **Enqueue**: `action_init_settings()` enqueues
  `assets/styles/wordpress-plugin-stub{dev}.css` on non-admin requests, where
  `dev` is empty in debug mode and `.min` otherwise.
- **Debug/dev mode**: `WP_DEBUG` or `IWORKS_DEV_MODE` → `$this->dev = ''`
  (unminified files) and non-empty `$this->eol`; with `IWORKS_DEV_MODE`,
  `get_version( $file )` returns the file's md5 (or `time()`) for cache busting,
  otherwise the plugin version.
- Compiled files are **gitignored** (`assets/styles/`, `assets/scripts/wordpress-plugin-stub-*.js`,
  `languages/*.mo`, `release/`) — never edit them, edit the sources and rebuild.

## Translations (i18n)

- Textdomain: `wordpress-plugin-stub`; `languages/wordpress-plugin-stub.pot`.
- `grunt i18n` runs `checktextdomain` (validates gettext keywords, also checks
  the placeholder domains `PLUGIN_NAME`, `IWORKS_RATE_TEXTDOMAIN`,
  `IWORKS_OPTIONS_TEXTDOMAIN` used by vendored code) and `makepot`
  (creates/updates the `.pot` and `.po` files).
- `.mo` files are built by `potomo` only in `grunt build:github`.
- The GitHub updater class loads the textdomain on `init`.

## GitHub self-updater

`class-iworks-wordpress-plugin-stub-github.php` — updates the plugin from the
GitHub repository (`iworks/wordpress-plugin-stub`): `plugins_api` → plugin info
popup, `pre_set_site_transient_update_plugins` → update check,
`upgrader_post_install` → install fix-ups. This class is **excluded from the
WordPress.org ZIP** (wporg releases must not self-update from GitHub).

## PHP dependencies

- No Composer. Requires (from `package.json`, replaced into headers at build):
  WordPress ≥ 6.0, PHP ≥ 8.1, tested up to 7.1.
- Vendored frameworks live in `includes/iworks/`: `options/` (settings pages)
  and `rate/` (rate-it / "love" admin notices). Both are separate git repos —
  treat them as vendor code: do not refactor their internals.

## Tests

- PHPUnit in `tests/php/` (`phpunit.xml`, `bootstrap.php`, `test-sample.php`),
  run via `grunt test` (see `build.md`).

## General conventions

- Formatting: tabs for PHP/JS/SCSS, 4 spaces for YAML, LF, final newline
  (`.editorconfig`); WordPress-style docblocks everywhere.
- Every PHP file: `defined( 'ABSPATH' ) || exit;` plus a
  `if ( class_exists( ... ) ) { return; }` guard.
- File naming: kebab-case, `class-iworks-wordpress-plugin-stub-{name}.php`;
  class names: `iworks_wordpress_plugin_stub_{name}` (snake_case, lowercase).
- Hooks and options: `wordpress-plugin-stub/...` or `iworks_wordpress_plugin_stub_...`.
- Never read the vendored frameworks' internals from feature classes — use the
  base-class helpers (`check_option_object()`, `get_meta_name()`, `get_post_meta()`).
- Scratch files (test harnesses, debug scripts) live in `.opencode/tmp/` — never
  outside the project. Gitignored; delete them when the task is done.

## Known pitfalls (do not fix without approval)

- Source placeholders (`PLUGIN_VERSION`, `CURRENT_YEAR`, `BUILDTIMESTAMP`, …)
  are intentional — do not replace them with real values in the repo.
- The bootstrap disables a `promo` post type (`.../load/post-type/promo`) but no
  `posttype-promo.php` exists in `posttypes/`.
- Post-type filter names come from the **filename**: the file
  `posttype-testimonials.php` produces the filter
  `.../load/post-type/testimonials`, while the abstract class map uses the key
  `testimonial` — check both when wiring filters.
- `base::get_post_type()` reads an undefined `$post_type` property (calling it
  fails under strict types) — the property is meant to be set by post type classes.
- The abstract post type map also contains `featured`/`testimonial` entries;
  their files exist but no enable filters are stubbed in the bootstrap.
