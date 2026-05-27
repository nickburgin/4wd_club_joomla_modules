# 4WD Club Joomla Modules

[![Build status](https://badge.buildkite.com/78b5555ee270cbe6e9aba4c813d10d16e1dbeb552e936136f1.svg?branch=main)](https://buildkite.com/nick-burgin/4wd-club-joomla-modules)

Maintained forks of Joomla extensions originally developed by Glenn Arkell. These extensions were extracted from the live club site after the original author abandoned them. This repo is the ongoing source of truth for all customisation and maintenance work.

## Background

Glenn Arkell published several Joomla extensions tailored to 4WD clubs (trip management, finance, member management, etc.) and hosted them at glennarkell.com.au. Development has ceased and the extensions are no longer receiving updates. The sources here have been brought up to the last known published versions (scraped from Glenn's update server in May 2026) and are ready for ongoing local maintenance.

## Repository layout

```
src/                        Source for all extensions — edit here
  com_gabroadcast/
  com_gacalevents/
  com_gafinance/
  com_gaforsale/
  com_gamerchandise/
  com_gatracklog/
  com_gatripsys/
  com_gausers/
  mod_gacalevents/
  mod_gafinance/
  mod_gaforsale/
  mod_gatripsys/
  mod_gausers/
  mod_gausersexecs/
  mod_glennslideshow/
  mod_glennsnewsletters/
  plg_task_gasubscriptions/
  plg_user_gausers/
  plg_user_profileb4wdc/
  rkic41site/               Site template

packages/                   Package manifests (multi-extension installers)
  pkg_gacalevents.xml
  pkg_gafinance.xml
  pkg_gatripsys.xml
  pkg_gatripsys_script.php
  pkg_gausers.xml

dist/                       Build output — gitignored, safe to delete
build.sh                    Build script
Makefile                    Convenience wrapper around build.sh
extract_extensions.sh       Used to extract extensions from a live Joomla site
```

## Extension inventory

### Packages (install these — they handle dependencies)

| Package | Contents | Package version |
|---|---|---|
| `pkg_gacalevents` | `com_gacalevents` 3.3.1 + `mod_gacalevents` 5.3 | 4.1 |
| `pkg_gafinance` | `com_gafinance` 5.2.3 + `mod_gafinance` 5.3 | 5.5 |
| `pkg_gatripsys` | `com_gatripsys` 5.3.0 + `mod_gatripsys` 5.2 | 5.8 |
| `pkg_gausers` | `com_gausers` 6.0.0 + `mod_gausers` 5.4 + `mod_gausersexecs` 5.3 + `plg_user_gausers` 5.3 + `plg_task_gasubscriptions` 5.3 | 6.0 |

### Standalone extensions

| Extension | Type | Version | Notes |
|---|---|---|---|
| `com_gabroadcast` | Component | 4.3.3 | |
| `com_gaforsale` | Component | 4.2.2 | |
| `com_gamerchandise` | Component | 4.1.2 | |
| `com_gatracklog` | Component | 4.2.0 | |
| `mod_gaforsale` | Module | 3.0.09 | No upstream — live site extract only |
| `mod_glennslideshow` | Module | 4.7 | No upstream — live site extract only |
| `mod_glennsnewsletters` | Module | 4.4 | No upstream — live site extract only |
| `plg_user_profileb4wdc` | Plugin (user) | 5.3 | |
| `rkic41site` | Template | 1.0 | No upstream — live site extract only |

## Building

Requirements: `bash`, `zip`, `make`.

```bash
# Build everything
make all

# Build a single package
make pkg_gafinance

# Build a single extension
./build.sh com_gafinance

# List all available targets
./build.sh list

# Remove all build output
make clean
```

Output zips land in `dist/` and are ready to install directly via **Joomla Admin > Extensions > Install**.

## Component source layout

Components follow the Joomla 4/5 split-directory convention:

```
src/com_gafinance/
  gafinance.xml       Manifest (version lives here)
  script.php          Install/update script
  administrator/      Admin-side PHP, forms, SQL, templates
  site/               Front-end PHP, forms, templates
  media/              CSS, JS, images (installed to media/com_gafinance/)
```

Modules and plugins have a flat structure with the manifest at the root of their `src/` directory.

## Updating a version

1. Make your changes in `src/<extension>/`
2. Bump the `<version>` tag in the extension's XML manifest
3. If the extension is part of a package, update the filename in `packages/pkg_<name>.xml` to match the new version number (the build script patches it anyway, but keeping it in sync avoids confusion)
4. Push to `main` — CI builds the zip, creates a versioned GitHub release, and publishes the updated XML to the `releases` branch

Joomla will pick up the new version automatically on the next update check.

To test locally before pushing:

```bash
make pkg_<name>        # or ./build.sh <ext_name> for standalones
```

Then install the zip from `dist/` via Joomla Admin > Extensions > Install.

## CI/CD

Buildkite runs on every push. On `main`, after a successful build:

- Each extension with a new version gets its own GitHub release: `{name}-{version}`
- All `*-update.xml` files are committed to the `releases` branch, where Joomla polls them
- Docs are deployed to GitHub Pages

To force update XMLs to republish without a version bump, include `[publish-xmls]` in the commit message.

## Notes

### com_gatripsys — media/js/form.js

`src/com_gatripsys/media/js/form.js` is intentionally kept in this repo even though Glenn never included it in any published release package. It provides a `getScript()` utility function that is actively loaded by `Incidentform`, `Invoiceform`, and `Attendeeform` views. Removing it will cause silent 404s on those form pages.

### mod_gaforsale, mod_glennslideshow, mod_glennsnewsletters, rkic41site — no upstream source

These four have no update server and their direct download URLs are 404 on Glenn's server across all Joomla version paths (j4, j5, j6). The copies in `src/` extracted from the live site are the **only known surviving copies** — treat them as irreplaceable.

### plg_user_profileb4wdc

Kept as a standalone (not bundled into `pkg_gausers`) because it has its own independent release history and targets 4WD-club-specific user profile fields.

## Re-extracting from a live site

`extract_extensions.sh` will pull all Glenn Arkell extensions from a live Joomla installation and package them as installable zips. Run it from the directory containing `public_html`:

```bash
./extract_extensions.sh
```

Output goes to `./glenn_arkell_extensions/`. These zips are gitignored — `src/` is the source of truth.
