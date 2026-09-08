#!/usr/bin/env bash
# Baut das Release-Zip des Themes aus theme/idt-deutschlandtakt/.
#
# Das Zip ist ein Build-Ergebnis und liegt deshalb NICHT im Repository
# (siehe .gitignore) — es wird bei Bedarf hier erzeugt und dann unter
# Design → Themes → Hinzufügen → Theme hochladen eingespielt.
#
#   ./bin/theme-zip.sh              → dist/idt-deutschlandtakt-<version>.zip
#   ./bin/theme-zip.sh /pfad/ziel   → legt das Zip in diesem Verzeichnis ab
set -euo pipefail

repo="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
theme_dir="$repo/theme"
slug="idt-deutschlandtakt"
out_dir="${1:-$repo/dist}"

# Version aus dem Theme-Header (style.css) lesen — eine Quelle, kein zweiter Ort,
# an dem die Nummer gepflegt werden müsste.
version="$(sed -n 's/^[[:space:]]*Version:[[:space:]]*\(.*[^[:space:]]\)[[:space:]]*$/\1/p' \
  "$theme_dir/$slug/style.css" | head -n 1)"
if [ -z "$version" ]; then
  echo "Fehler: keine 'Version:' im Theme-Header von $slug/style.css gefunden." >&2
  exit 1
fi

mkdir -p "$out_dir"
zip_path="$out_dir/$slug-$version.zip"
rm -f "$zip_path"

# Aus theme/ heraus packen, damit im Archiv der Ordner idt-deutschlandtakt/
# oben liegt — genau so erwartet es der WordPress-Theme-Upload.
( cd "$theme_dir" && zip -q -r -X "$zip_path" "$slug" \
    -x '*.DS_Store' -x '*/.git/*' -x '*.map' )

echo "Fertig: $zip_path"
