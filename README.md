# 4WD Club Joomla Modules

Maintained forks of Joomla extensions originally developed by Glenn Arkell. These extensions were extracted from the live club site after the original author abandoned them. This repo is the ongoing source of truth for all customisation and maintenance work.

## Background

Glenn Arkell published several Joomla extensions tailored to 4WD clubs (trip management, finance, member management, etc.) and hosted them at glennarkell.com.au. Development has ceased and the extensions are no longer receiving updates. The versions here were extracted directly from the live Joomla site and are the last known working state.

Some extensions are behind the final published release (e.g. the live site was running `com_gafinance 5.1.0` when Glenn's last release was `5.1.0` — matched; but `mod_gafinance` was `5.0.2` vs Glenn's `5.1`). Where reference packages were available they have been used to verify the extraction is complete.

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
  plg_user_gausers/
  plg_user_profileb4wdc/
  rkic41site/               Site template

packages/                   Package manifests (multi-extension installers)
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

| Package | Contents | Versions on live site |
|---|---|---|
| `pkg_gafinance` | `com_gafinance` + `mod_gafinance` | 5.1.0 / 5.0.2 |
| `pkg_gatripsys` | `com_gatripsys` + `mod_gatripsys` | 5.1.0 / 5.0.4 |
| `pkg_gausers` | `com_gausers` + `mod_gausers` + `mod_gausersexecs` + `plg_user_gausers` | 5.1.6 (all) |

### Standalone extensions

| Extension | Type | Version |
|---|---|---|
| `com_gabroadcast` | Component | 4.2.1 |
| `com_gacalevents` | Component | 3.0.0 |
| `com_gaforsale` | Component | 4.0.2 |
| `com_gamerchandise` | Component | 4.0.7 |
| `com_gatracklog` | Component | 4.1.0 |
| `mod_gacalevents` | Module | 2.1.2 |
| `mod_gaforsale` | Module | 3.0.09 |
| `mod_glennslideshow` | Module | 4.7 |
| `mod_glennsnewsletters` | Module | 4.4 |
| `plg_user_profileb4wdc` | Plugin (user) | 4.5.9 |
| `rkic41site` | Template | 1.0 |

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
3. If the extension is part of a package, update the filename in `packages/pkg_<name>.xml` to match the new version number
4. Run `make pkg_<name>` (or `./build.sh <ext_name>` for standalones)
5. Install the resulting zip from `dist/` on the target Joomla site

The build script auto-detects the version from the manifest XML and names the output zip accordingly. If the version in `packages/pkg_*.xml` doesn't match what was built, the build script patches the manifest filename inside the assembled package zip automatically.

## Notes

### com_gatripsys — media/js/form.js

This file exists on the live site but was not in any of Glenn's published release packages. It provides a `getScript()` utility function and is actively loaded by `Incidentform`, `Invoiceform`, and `Attendeeform` views. It must be preserved — removing it will cause silent 404s on those form pages.

### plg_user_profileb4wdc

This plugin is a 4WD-club-specific user profile extension. It is at version 4.5.9 while the rest of the gausers system is at 5.1.6 — it has its own independent release history and is kept as a standalone rather than bundled into `pkg_gausers`.

### mod_gaforsale, mod_glennslideshow, rkic41site — no upstream source

These three have no update server and their direct download URLs are 404 on Glenn's server across all Joomla version paths (j4, j5, j6). Glenn has stated he no longer supports J3/J4 extensions. The copies in `src/` extracted from the live site are the **only known surviving copies** — treat them as irreplaceable.

### Older-style modules

Several modules (`mod_gafinance`, `mod_gatripsys`, etc.) still use the pre-Joomla 4 entry point style (`mod_*.php` + `helper.php`) rather than the newer `services/provider.php` + `src/Dispatcher/` architecture. This is a known version gap — the live site was behind Glenn's final releases for these modules. They work fine on the current Joomla version but are worth modernising eventually.

## Re-extracting from a live site

`extract_extensions.sh` will pull all Glenn Arkell extensions from a live Joomla installation and package them as installable zips. Run it from the directory containing `public_html`:

```bash
./extract_extensions.sh
```

Output goes to `./glenn_arkell_extensions/`. These zips are gitignored — `src/` is the source of truth.
