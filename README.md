# Datebook

Upcoming events with venues and a map that loads only on request.

A WordPress plugin
built with [wp-app-framework](https://webcodingcat.github.io/wp-app-framework/): blocks, hooks and settings are PHP classes with attributes in `src/`, their markup is Twig, their styles Tailwind CSS.

- Slug (directory name): `datebook`
- Namespace: `datebook`, the name in front of blocks (`datebook/…`), REST routes, settings options, post types and tables; content refers to it, so keep it.
- PHP namespace: `Saij\Datebook\` in `src/`

## Requirements

WordPress 7.1 with PHP 8.5, Composer and Node 24 for the build.

## Development

```sh
composer install
npm install
npm run build             # editor runtime, scripts and styles into build/
npm run start             # the same, again on every change
composer build-manifest   # checks attributes and services; writes nothing
```

Put the directory into `wp-content/plugins/` of a WordPress installation (or link it there) and activate it. The framework runs in development mode and builds its manifest itself; you only rebuild the scripts and styles.

The [documentation](https://webcodingcat.github.io/wp-app-framework/) explains blocks, the editor, settings, post types and everything else.

## Checks

```sh
composer lint    # PHPStan (level max) and PHP-CS-Fixer (PER-CS); fix: composer fix
npm run lint     # ESLint; fix: npm run format
```

## Release

```sh
composer release   # builds dist/datebook-<version>.zip
```

The release build prefixes the framework and all dependencies, so the ZIP runs in any WordPress next to other themes and plugins built with the framework. It takes the files Git knows, so commit first; files marked `export-ignore` in `.gitattributes` stay out of the ZIP. The version is `Version:` in `datebook.php`.

A tag `v<version>` (equal to that version) runs `.github/workflows/release.yml`: the release workflow of the framework builds the ZIP, tests it in a fresh WordPress and attaches it to the GitHub release of the tag.
It runs the plugin on the default theme of WordPress.
Keep the framework version of the workflow (`consumer-release.yml@v0.19.0`) at the one your lock file installs.

## Browser tests

The browser tests in `tests/e2e/` run with [wp-test-env](https://github.com/webcodingcat/wp-test-env), which starts fresh WordPress instances in Docker. Check it out next to this project; the directory that contains both is its workspace:

```sh
git clone https://github.com/webcodingcat/wp-test-env.git ../wp-test-env
cd ../wp-test-env
bin/run ../datebook/tests/dev.pools       # the checkout, in development mode
bin/run ../datebook/tests/release.pools   # the release ZIP
```

The plugin runs on the default theme of WordPress in both pools.

Every test fails on console warnings and errors (`tests/e2e/fixtures.js`).

## License

GPL-2.0-or-later
