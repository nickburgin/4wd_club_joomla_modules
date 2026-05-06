#!/bin/bash

JOOMLA_ROOT="./public_html"
AUTHOR="Glenn Arkell"
OUTPUT_DIR="./glenn_arkell_extensions"
mkdir -p "$OUTPUT_DIR"

package_extension() {
    local src_dir="$1"
    local ext_name="$2"
    local pkg_dir
    pkg_dir=$(mktemp -d)

    cp -r "$src_dir"/* "$pkg_dir/"

    # Copy language files (site)
    for lang_dir in "$JOOMLA_ROOT/language"/*/; do
        lang_code=$(basename "$lang_dir")
        for lang_file in "$lang_dir${lang_code}.${ext_name}."*; do
            [ -f "$lang_file" ] && cp "$lang_file" "$pkg_dir/"
        done
    done

    # Copy language files (admin)
    for lang_dir in "$JOOMLA_ROOT/administrator/language"/*/; do
        lang_code=$(basename "$lang_dir")
        for lang_file in "$lang_dir${lang_code}.${ext_name}."*; do
            [ -f "$lang_file" ] && cp "$lang_file" "$pkg_dir/"
        done
    done

    (cd "$pkg_dir" && zip -r "$OLDPWD/$OUTPUT_DIR/${ext_name}.zip" .)
    rm -rf "$pkg_dir"
    echo "  -> $OUTPUT_DIR/${ext_name}.zip"
}

echo "=== Modules ==="
grep -rl "<author>$AUTHOR</author>" "$JOOMLA_ROOT/modules/" 2>/dev/null | while read -r xml; do
    mod_dir=$(dirname "$xml")
    mod_name=$(basename "$mod_dir")
    echo "Packaging $mod_name..."
    package_extension "$mod_dir" "$mod_name"
done

echo "=== Components ==="
grep -rl "<author>$AUTHOR</author>" "$JOOMLA_ROOT/administrator/components/" 2>/dev/null | while read -r xml; do
    com_dir=$(dirname "$xml")
    com_name=$(basename "$com_dir")
    echo "Packaging $com_name..."
    pkg_dir=$(mktemp -d)

    # Admin side — copy contents, then hoist manifest and script to root
    mkdir -p "$pkg_dir/administrator"
    cp -r "$com_dir/." "$pkg_dir/administrator/"
    for f in "$pkg_dir/administrator/script.php" "$pkg_dir/administrator/LICENSE.txt"; do
        [ -f "$f" ] && mv "$f" "$pkg_dir/"
    done
    # Manifest filename drops the com_ prefix (e.g. com_gafinance -> gafinance.xml)
    short_name="${com_name#com_}"
    [ -f "$pkg_dir/administrator/${short_name}.xml" ] && mv "$pkg_dir/administrator/${short_name}.xml" "$pkg_dir/"

    # Site side
    if [ -d "$JOOMLA_ROOT/components/$com_name" ]; then
        mkdir -p "$pkg_dir/site"
        cp -r "$JOOMLA_ROOT/components/$com_name/." "$pkg_dir/site/"
    fi

    # Media — copy contents directly into media/ (strip the com_name subdirectory)
    if [ -d "$JOOMLA_ROOT/media/$com_name" ]; then
        mkdir -p "$pkg_dir/media"
        cp -r "$JOOMLA_ROOT/media/$com_name/." "$pkg_dir/media/"
    fi

    # Language files (site)
    for lang_dir in "$JOOMLA_ROOT/language"/*/; do
        lang_code=$(basename "$lang_dir")
        for lang_file in "$lang_dir${lang_code}.${com_name}."*; do
            [ -f "$lang_file" ] && { mkdir -p "$pkg_dir/site/language/$lang_code"; cp "$lang_file" "$pkg_dir/site/language/$lang_code/"; }
        done
    done

    # Language files (admin)
    for lang_dir in "$JOOMLA_ROOT/administrator/language"/*/; do
        lang_code=$(basename "$lang_dir")
        for lang_file in "$lang_dir${lang_code}.${com_name}."*; do
            [ -f "$lang_file" ] && { mkdir -p "$pkg_dir/administrator/language/$lang_code"; cp "$lang_file" "$pkg_dir/administrator/language/$lang_code/"; }
        done
    done

    (cd "$pkg_dir" && zip -r "$OLDPWD/$OUTPUT_DIR/${com_name}.zip" .)
    rm -rf "$pkg_dir"
    echo "  -> $OUTPUT_DIR/${com_name}.zip"
done

echo "=== Plugins ==="
grep -rl "<author>$AUTHOR</author>" "$JOOMLA_ROOT/plugins/" 2>/dev/null | while read -r xml; do
    plg_dir=$(dirname "$xml")
    plg_name=$(basename "$plg_dir")
    plg_group=$(basename "$(dirname "$plg_dir")")
    echo "Packaging plg_${plg_group}_${plg_name}..."
    package_extension "$plg_dir" "plg_${plg_group}_${plg_name}"
done

echo "=== Templates (site) ==="
grep -rl "<author>$AUTHOR</author>" "$JOOMLA_ROOT/templates/" 2>/dev/null | while read -r xml; do
    tpl_dir=$(dirname "$xml")
    tpl_name=$(basename "$tpl_dir")
    echo "Packaging $tpl_name..."
    package_extension "$tpl_dir" "$tpl_name"
done

echo "=== Templates (admin) ==="
grep -rl "<author>$AUTHOR</author>" "$JOOMLA_ROOT/administrator/templates/" 2>/dev/null | while read -r xml; do
    tpl_dir=$(dirname "$xml")
    tpl_name=$(basename "$tpl_dir")
    echo "Packaging admin_${tpl_name}..."
    package_extension "$tpl_dir" "admin_${tpl_name}"
done

echo "=== Libraries ==="
grep -rl "<author>$AUTHOR</author>" "$JOOMLA_ROOT/libraries/" 2>/dev/null | while read -r xml; do
    lib_dir=$(dirname "$xml")
    lib_name=$(basename "$lib_dir")
    echo "Packaging $lib_name..."
    package_extension "$lib_dir" "$lib_name"
done

echo ""
echo "All done. Packages saved to: $OUTPUT_DIR"
ls -lh "$OUTPUT_DIR/"