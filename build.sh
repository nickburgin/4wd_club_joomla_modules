#!/bin/bash
# Build installable Joomla extension zips from src/
# Usage:
#   ./build.sh all                  — build everything
#   ./build.sh pkg_gafinance        — build a package and its extensions
#   ./build.sh com_gafinance        — build a single extension
#   ./build.sh list                 — list available targets

set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
SRC="$ROOT/src"
DIST="$ROOT/dist"
PKGS="$ROOT/packages"

GITHUB_REPO="${GITHUB_REPO:-https://github.com/nickburgin/4wd_club_joomla_modules}"

mkdir -p "$DIST"

# ── helpers ──────────────────────────────────────────────────────────────────

get_version() {
    grep -m1 "<version>" "$1" | sed 's/.*<version>\(.*\)<\/version>.*/\1/' | tr -d '[:space:]'
}

get_display_name() {
    grep -m1 "<name>" "$1" | sed 's/[[:space:]]*<name>\(.*\)<\/name>.*/\1/'
}

generate_update_xml() {
    local name="$1" version="$2" zip_name="$3" manifest="$4"
    local display_name type element extra_xml=""

    display_name=$(get_display_name "$manifest")

    case "$name" in
        pkg_*)
            type="package"; element="$name" ;;
        com_*)
            type="component"; element="$name" ;;
        mod_*)
            type="module"; element="$name"
            extra_xml=$'\n        <client>site</client>' ;;
        plg_*_*)
            type="plugin"
            local grp; grp=$(echo "$name" | cut -d'_' -f2)
            element=$(echo "$name" | cut -d'_' -f3-)
            extra_xml=$'\n        <folder>'"$grp"'</folder>' ;;
        *)
            type="extension"; element="$name" ;;
    esac

    cat > "$DIST/${name}-update.xml" <<EOF
<?xml version="1.0" encoding="utf-8"?>
<updates>
    <update>
        <name>${display_name}</name>
        <element>${element}</element>
        <type>${type}</type>
        <version>${version}</version>
        <infourl title="4WD Club Joomla Modules">${GITHUB_REPO}</infourl>
        <downloads>
            <downloadurl type="full" format="zip">${GITHUB_REPO}/releases/latest/download/${zip_name}</downloadurl>
        </downloads>
        <maintainer>Nick Burgin (fork of Glenn Arkell)</maintainer>
        <maintainerurl>${GITHUB_REPO}</maintainerurl>
        <targetplatform name="joomla" version="5.*"/>${extra_xml}
    </update>
</updates>
EOF
}

find_manifest() {
    local name="$1" src="$SRC/$1"
    case "$name" in
        com_*)       echo "$src/${name#com_}.xml" ;;
        mod_*)       echo "$src/${name}.xml" ;;
        plg_*_*)     echo "$src/${name##plg_*_}.xml" ;;
        *)           find "$src" -maxdepth 1 -name "*.xml" | head -1 ;;
    esac
}

# ── build a single extension ──────────────────────────────────────────────────

build_ext() {
    local name="$1"
    local src="$SRC/$name"

    if [ ! -d "$src" ]; then
        echo "ERROR: src/$name not found" >&2; return 1
    fi

    local manifest
    manifest=$(find_manifest "$name")

    if [ ! -f "$manifest" ]; then
        echo "ERROR: manifest not found for $name ($manifest)" >&2; return 1
    fi

    local version
    version=$(get_version "$manifest")
    local out="$DIST/${name}-${version}.zip"

    echo "  building $name ($version)..."
    (cd "$src" && zip -rq "$DIST/${name}-${version}.zip" . \
        --exclude "*.DS_Store" --exclude "__MACOSX/*")
    generate_update_xml "$name" "$version" "${name}-${version}.zip" "$manifest"
    echo "$out"
}

# ── build a package ───────────────────────────────────────────────────────────

build_pkg() {
    local pkg="$1"
    local manifest="$PKGS/${pkg}.xml"

    if [ ! -f "$manifest" ]; then
        echo "ERROR: packages/${pkg}.xml not found" >&2; return 1
    fi

    echo "Building $pkg..."
    local tmp
    tmp=$(mktemp -d)
    mkdir -p "$tmp/packages"

    # Build each constituent extension and collect its zip
    local assembled_manifest="$tmp/${pkg}.xml"
    cp "$manifest" "$assembled_manifest"

    grep '<file ' "$manifest" | sed "s/.*>\(.*\.zip\)<.*/\1/" | while read -r ref_zip; do
        # Derive extension name by stripping trailing -version.zip
        local extname="${ref_zip%%-[0-9]*}"

        # Build it (or reuse if already built at this version)
        local built
        built=$(build_ext "$extname" | tail -1)

        # Copy into packages/ dir with the exact filename the manifest expects
        # (update the manifest entry if our version differs)
        local actual
        actual=$(basename "$built")
        cp "$built" "$tmp/packages/$actual"

        # Patch manifest filename if version changed
        if [ "$actual" != "$ref_zip" ]; then
            sed -i "s|${ref_zip}|${actual}|g" "$assembled_manifest"
        fi
    done

    # Optional package-level script.php
    [ -f "$PKGS/${pkg}_script.php" ] && cp "$PKGS/${pkg}_script.php" "$tmp/script.php"

    local pkg_version
    pkg_version=$(get_version "$manifest")
    local out="$DIST/${pkg}-${pkg_version}.zip"

    (cd "$tmp" && zip -rq "$DIST/${pkg}-${pkg_version}.zip" .)
    rm -rf "$tmp"
    generate_update_xml "$pkg" "$pkg_version" "${pkg}-${pkg_version}.zip" "$manifest"
    echo "  -> $out"
}

# ── targets ───────────────────────────────────────────────────────────────────

PACKAGES="pkg_gafinance pkg_gatripsys pkg_gausers pkg_gacalevents"
STANDALONES="com_gabroadcast com_gaforsale com_gamerchandise com_gatracklog
             mod_gaforsale mod_glennslideshow mod_glennsnewsletters
             plg_user_profileb4wdc plg_task_gasubscriptions rkic41site"

cmd="${1:-all}"

case "$cmd" in
    list)
        echo "Packages:    $PACKAGES"
        echo "Standalones: $STANDALONES"
        ;;
    all)
        echo "=== Packages ==="
        for pkg in $PACKAGES; do
            build_pkg "$pkg"
        done
        echo ""
        echo "=== Standalones ==="
        for ext in $STANDALONES; do
            build_ext "$ext"
        done
        ;;
    pkg_*)
        build_pkg "$cmd"
        ;;
    com_*|mod_*|plg_*|tpl_*|rkic41site)
        build_ext "$cmd"
        ;;
    *)
        echo "Unknown target: $cmd"
        echo "Usage: $0 [all|list|pkg_NAME|ext_name]"
        exit 1
        ;;
esac

echo ""
echo "Output in: $DIST/"
