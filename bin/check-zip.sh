#!/usr/bin/env bash
# Prüft das Release-Zip: baut es mit bin/theme-zip.sh in ein temporäres
# Verzeichnis und sieht nach, ob der Upload unter „Design → Themes →
# Hinzufügen“ damit zurechtkäme.
#
#   ./bin/check-zip.sh
#
# Der WordPress-Theme-Upload akzeptiert nur Archive, in denen der Themeordner
# oben liegt; entpackt wird nach wp-content/themes/<oberster Ordner>. Liegt
# style.css stattdessen in der Archivwurzel, landet das Theme als
# „idt-deutschlandtakt-2.0.21“ im Verzeichnis und der Update-Pfad bricht.
set -uo pipefail

repo="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
slug="idt-deutschlandtakt"
theme="$repo/theme/$slug"

fail=0
ok()  { printf '  \033[32m✓\033[0m %s\n' "$1"; }
err() { printf '  \033[31m✗\033[0m %s\n' "$1"; fail=1; }

tmp="$(mktemp -d)"
trap 'rm -rf "$tmp"' EXIT

printf '\n\033[1mRelease-Zip\033[0m\n'

if ! build_out="$("$repo/bin/theme-zip.sh" "$tmp" 2>&1)"; then
  err "bin/theme-zip.sh bricht ab: $build_out"
  exit 1
fi

version="$(sed -n 's/^[[:space:]]*Version:[[:space:]]*\(.*[^[:space:]]\)[[:space:]]*$/\1/p' "$theme/style.css" | head -n 1)"
zip_path="$tmp/$slug-$version.zip"

if [ ! -f "$zip_path" ]; then
  err "erwartete Datei $slug-$version.zip wurde nicht erzeugt (gebaut: $(ls "$tmp"))"
  exit 1
fi
ok "$slug-$version.zip gebaut ($(du -h "$zip_path" | cut -f1))"

entries="$(unzip -Z1 "$zip_path")"

# 1) Alles liegt unter idt-deutschlandtakt/ — Bedingung für den Theme-Upload.
stray="$(grep -v "^$slug/" <<< "$entries" || true)"
if [ -n "$stray" ]; then
  err "Einträge außerhalb von $slug/: $(tr '\n' ' ' <<< "$stray")"
else
  ok "Themeordner liegt an der Archivwurzel"
fi

# 2) Pflichtdateien sind wirklich im Archiv (nicht nur im Arbeitsverzeichnis).
for f in style.css index.php functions.php screenshot.png inc/shortcodes.php assets/blocks.js \
         assets/fonts/InterVariable.woff2 LICENSE readme.txt assets/fonts/OFL.txt \
         blocks/knotendreieck/block.json blocks/knotendreieck/view.js \
         blocks/knotendreieck/editor.js blocks/knotendreieck/standbild.svg; do
  grep -qx "$slug/$f" <<< "$entries" || err "fehlt im Archiv: $f"
done
ok "Pflichtdateien im Archiv geprüft"

# 3) Nichts, was auf einem Webserver nichts zu suchen hat.
junk="$(grep -E '(^|/)\.(git|DS_Store)|\.map$|(^|/)node_modules/' <<< "$entries" || true)"
if [ -n "$junk" ]; then
  err "unerwünschte Dateien im Archiv: $(tr '\n' ' ' <<< "$junk")"
else
  ok "keine .git-, .DS_Store- oder .map-Dateien im Archiv"
fi

# 4) Die Version im Archiv ist die, die draufsteht.
unzip -p "$zip_path" "$slug/style.css" > "$tmp/style.css"
zip_version="$(sed -n 's/^[[:space:]]*Version:[[:space:]]*\(.*[^[:space:]]\)[[:space:]]*$/\1/p' "$tmp/style.css" | head -n 1)"
if [ "$zip_version" != "$version" ]; then
  err "Version im Archiv ($zip_version) passt nicht zum Dateinamen ($version)"
else
  ok "Version im Archiv: $zip_version"
fi

if [ "$fail" -eq 0 ]; then
  printf '\n\033[32mZip ist hochladefertig.\033[0m\n'
else
  printf '\n\033[31mZip-Prüfung fehlgeschlagen.\033[0m\n'
fi
exit "$fail"
