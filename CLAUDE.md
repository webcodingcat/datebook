# CLAUDE.md – Datebook

WordPress plugin for upcoming events with venues and a map that loads only on request, built with [wp-app-framework](https://github.com/webcodingcat/wp-app-framework) (slug `datebook`, namespace `datebook`: post types `datebook_…`, blocks `datebook/…`, options `datebook_…`, PHP `WebCodingCat\Datebook\`). Not tied to a theme or client; client sites include it as a standalone plugin and adapt its markup with overrides (`overrides/datebook/` in the theme). `PLAN.md` holds the decisions and the steps – read it before you change anything, and keep it current. Framework principles, conventions and testing rules: the [developer documentation](https://webcodingcat.github.io/wp-app-framework/next/development/) and the framework's CLAUDE.md (in the workspace `framework/wp-app-framework/`).

## Rules

- Generic: no assumptions about a theme. Markup is neutral and readable without the theme; the plugin's Tailwind classes carry the prefix `datebook:` so they never meet the theme's.
- No external requests from visitors' browsers without their consent: Leaflet and its assets are self-hosted, map tiles load only after a click. Geocoding runs on the server, once per changed address.
- Dates are `#[DateTime]` strings in the local time of the site (`2026-11-14T20:00`); compare them with strings from `wp_date()`, never with UTC.
- Class properties are never `public` (exceptions: attribute, value and meta classes); everything in this repository is English; other repositories are linked by GitHub URL.

## Commands

Development runs in a site of wp-dev-env that lists the plugin (e.g. a client site), with the framework checkout linked into `vendor/`. Checks and tests run in the tools image of wp-test-env (checked out in `../framework/wp-test-env`).

```sh
docker compose -f ../wp-dev-env/compose.yaml run --rm tools composer lint --working-dir=/workspace/datebook
docker compose -f ../wp-dev-env/compose.yaml run --rm tools npm --prefix /workspace/datebook run lint
docker compose -f ../wp-dev-env/compose.yaml run --rm tools npm --prefix /workspace/datebook run build
docker compose -f ../wp-dev-env/compose.yaml run --rm tools composer build-manifest --working-dir=/workspace/datebook
```

Browser tests run in wp-test-env with the pool files in `tests/` (`dev.pools`, `release.pools`); never against wp-dev-env. wp-test-env lies in `../framework/`, so its workspace is set to this directory's parent:

```sh
WP_TEST_ENV_WORKSPACE=/home/saij/dev/wp-dev ../framework/wp-test-env/bin/run "$PWD/tests/dev.pools"       # before a push
WP_TEST_ENV_WORKSPACE=/home/saij/dev/wp-dev ../framework/wp-test-env/bin/run "$PWD/tests/release.pools"   # the release ZIP
```

The pools activate `tests/fake-nominatim`, a test plugin that answers the geocoding requests, so tests make no requests to OpenStreetMap.

## Keep in sync

- `composer.json` requires the released framework; a feature of the framework's `main` (such as `#[DateTime]` before 0.19) needs a framework release before the release workflow can pass. The dev pools link the framework checkout.
- A release tag `v<version>` equals `Version:` in `datebook.php`; every GitHub release gets written release notes.
