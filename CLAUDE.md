# CLAUDE.md

## What this repo is

Maintained forks of abandoned Joomla 5 extensions originally by Glenn Arkell. All source lives in `src/`. Build tooling produces installable zips in `dist/`. See README.md for the full extension inventory.

## Build

```bash
make all                   # build all packages and standalones
make pkg_gafinance         # build one package
./build.sh com_gafinance   # build one extension
make clean                 # rm -rf dist/
```

`build.sh` reads the version from each extension's XML manifest and names the output zip accordingly. Package manifests in `packages/` are patched automatically if the built version differs from what the manifest lists.

## Versioning

- Extension version lives in the `<version>` tag of the XML manifest in `src/<name>/`.
- Component manifests are named after the short name without prefix: `com_gafinance` → `gafinance.xml`.
- Module and plugin manifests use the full directory name: `mod_gatripsys.xml`, `gasubscriptions.xml`.
- After bumping a version, update the filename in `packages/pkg_*.xml` if the extension is part of a package (the build script will patch it anyway, but keeping it in sync avoids confusion).

## Joomla extension conventions

### Component source layout

```
src/com_gafinance/
  gafinance.xml               Manifest
  script.php / com_*_script.php   Install/upgrade script (if present)
  administrator/              Admin PHP, forms, SQL migrations, templates
  site/                       Front-end PHP, forms, templates
  media/                      CSS, JS, images → installed to media/com_gafinance/
```

SQL migrations go in `administrator/sql/updates/mysql/` as `<version>.sql`. Joomla runs them in order when the installed schema version is below the file's version.

### Module / plugin layout

Flat structure with the manifest at the root of the `src/<name>/` directory. Joomla 5 style uses `services/provider.php` + `src/Dispatcher/Dispatcher.php`; old-style used `mod_*.php` + `helper.php` (the latter is gone from all extensions now).

## Known gotchas

### com_gatripsys/media/js/form.js

Never delete this file. It is not shipped in Glenn's published packages but is hard-referenced by `Incidentform`, `Invoiceform`, and `Attendeeform` view classes via `HTMLHelper::script(Uri::base().'media/com_gatripsys/js/form.js')`. Removing it causes silent 404s on those form pages.

### No-upstream extensions

`mod_gaforsale`, `mod_glennslideshow`, `mod_glennsnewsletters`, and `rkic41site` have no live download source. The copies in `src/` are the only known surviving versions — do not overwrite them from an upstream fetch.

### Packages vs standalones

Where a package exists (`pkg_gacalevents`, `pkg_gafinance`, `pkg_gatripsys`, `pkg_gausers`), always install the package rather than individual extensions. Packages carry guided tours, install scripts, and ensure correct dependency ordering.

## CI/CD

Buildkite pipeline at `.buildkite/pipeline.yml`. The build runs in Docker via `docker-compose.yml` and `Dockerfile`.

Steps on every push:
1. `auto/build` — runs `./build.sh all` inside the `build` container, producing zips and update XMLs in `dist/`; artifacts uploaded to Buildkite
2. `auto/docs-build` — builds the MkDocs documentation site

On `main` only (after the above complete):
3. `auto/upload-release-pipeline` — downloads zips, checks which GitHub release tags already exist, and uploads a dynamic pipeline with one `auto/release-ext {name}` step per extension that needs a new release, followed by `auto/publish-update-xmls`
4. Per-extension release steps — each creates a versioned GitHub release tag `{name}-{version}` containing just that extension's zip
5. `auto/publish-update-xmls` — downloads all `*-update.xml` artifacts and commits them to the `releases` branch
6. `auto/docs-deploy` — deploys the docs site to GitHub Pages

To force update XMLs to be republished without a version bump, include `[publish-xmls]` in the commit message.

## Update servers

`build.sh` generates a `{name}-update.xml` in `dist/` for every extension it builds. Each update XML contains the version, download URL, and SHA256 checksum of the zip.

- **Update XML URL** (stable): `https://raw.githubusercontent.com/nickburgin/4wd_club_joomla_modules/releases/{name}-update.xml`
- **Zip download URL** (versioned): `https://github.com/nickburgin/4wd_club_joomla_modules/releases/download/{name}-{version}/{name}-{version}.zip`

Each standalone extension manifest (`src/<name>/<manifest>.xml`) must contain an `<updateservers>` block pointing at its update XML URL on the `releases` branch. Package members do not need this — Joomla tracks updates through the package.

When releasing a new version: bump the `<version>` in the manifest, push to `main`, and CI handles the rest.

## Local testing

```bash
make all                    # build packages into dist/
docker-compose up           # start Joomla 5 + MySQL at http://localhost:8080
```

`dist/` is bind-mounted into the Joomla container at `/tmp/packages`. Use Joomla's admin panel (Extensions → Install → Upload Package File) to install from there.

First run: complete the Joomla installer wizard at http://localhost:8080, then log in to admin.

## Licence

All extensions are GPL v2+, original copyright Glenn Arkell. The `<packager>Nick Burgin</packager>` field in each manifest records the fork maintainer. Do not remove Glenn's copyright headers from individual PHP files — they must be preserved under GPL. Add your own copyright to any new files you create.

## Git

- No `Co-Authored-By` trailers in commit messages.
- Commit only when explicitly asked.
