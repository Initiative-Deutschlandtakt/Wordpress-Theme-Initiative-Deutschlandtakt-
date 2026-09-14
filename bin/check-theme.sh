#!/usr/bin/env bash
# Statische Prüfungen für Theme und Repository.
#
# Dieselben Checks laufen lokal und in der CI (.github/workflows/ci.yml) — es
# gibt bewusst nur eine Fassung, damit „grün in der CI“ und „grün auf dem
# Rechner" dasselbe bedeuten.
#
#   ./bin/check-theme.sh
#
# Voraussetzung ist php; node ist optional (ohne node entfällt die
# JS-Syntaxprüfung). Kein Build, keine Paketinstallation.
#
# Geprüft werden die vier Konventionen aus CLAUDE.md — Version an zwei Stellen,
# ein Baustein/eine Render-Funktion, idt-Präfix, Design-Tokens statt Literale —
# dazu Syntax (PHP/JS), Theme-Header, Direktzugriffsschutz, Debug-Reste und die
# Metadaten der Blöcke mit eigener block.json.
#
# Kein `set -e`: Es werden alle Fehler gesammelt und am Ende zusammen gemeldet,
# damit ein Durchlauf die vollständige Liste zeigt.
# Die deutschen Anführungszeichen in den Meldungen sind Absicht, kein
# versehentlich hineinkopiertes Smart-Quote.
# shellcheck disable=SC1111
set -uo pipefail

repo="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
slug="idt-deutschlandtakt"
theme="$repo/theme/$slug"

fail=0
section() { printf '\n\033[1m%s\033[0m\n' "$1"; }
ok()      { printf '  \033[32m✓\033[0m %s\n' "$1"; }
warn()    { printf '  \033[33m•\033[0m %s\n' "$1"; }
err()     { printf '  \033[31m✗\033[0m %s\n' "$1"; fail=1; }

php_files="$(find "$theme" -name '*.php' | sort)"

# -----------------------------------------------------------------------------
section "1) Theme-Grundgerüst"
# WordPress verlangt style.css + index.php; functions.php und screenshot.png
# gehören für dieses Theme dazu (Bausteine bzw. Vorschaubild im Backend).
for f in style.css index.php functions.php screenshot.png LICENSE readme.txt assets/fonts/OFL.txt; do
  if [ -f "$theme/$f" ]; then ok "$f vorhanden"; else err "$f fehlt"; fi
done

# Header-Felder, die der Theme-Upload und die Update-Prüfung brauchen.
for field in "Theme Name" "Version" "Requires at least" "Requires PHP" "Text Domain" "License" "License URI"; do
  if grep -qE "^${field}:[[:space:]]*\S" "$theme/style.css"; then
    ok "Theme-Header: „$field“ gesetzt"
  else
    err "Theme-Header: „$field“ fehlt in style.css"
  fi
done

# -----------------------------------------------------------------------------
section "2) Version synchron (CLAUDE.md, Konvention 1)"
header_version="$(sed -n 's/^[[:space:]]*Version:[[:space:]]*\(.*[^[:space:]]\)[[:space:]]*$/\1/p' "$theme/style.css" | head -n 1)"
const_version="$(sed -n "s/.*define([[:space:]]*'IDT_VERSION'[[:space:]]*,[[:space:]]*'\([^']*\)'.*/\1/p" "$theme/functions.php" | head -n 1)"
if [ -z "$header_version" ]; then
  err "keine „Version:“ im Theme-Header gefunden"
elif [ -z "$const_version" ]; then
  err "keine Konstante IDT_VERSION in functions.php gefunden"
elif [ "$header_version" != "$const_version" ]; then
  err "Version läuft auseinander: style.css $header_version ≠ IDT_VERSION $const_version (Cache-Busting bricht)"
else
  ok "style.css und IDT_VERSION stehen beide auf $header_version"
fi

# -----------------------------------------------------------------------------
section "3) PHP-Syntax"
php_errors=0
while IFS= read -r f; do
  if ! out="$(php -l "$f" 2>&1)"; then
    err "${f#"$repo"/}: ${out#*: }"
    php_errors=$((php_errors + 1))
  fi
done <<< "$php_files"
[ "$php_errors" -eq 0 ] && ok "$(wc -l <<< "$php_files" | tr -d ' ') Dateien fehlerfrei geparst ($(php -r 'echo PHP_VERSION;'))"

# -----------------------------------------------------------------------------
section "4) idt-Präfix (CLAUDE.md, Konvention 3)"
# Funktionsnamen: WordPress-Themes teilen sich den globalen Namensraum mit
# jedem Plugin — ohne Präfix kollidiert früher oder später etwas.
declared="$(php "$repo/bin/php-symbols.php" declared "$theme")"
unprefixed="$(grep -v '^idt_' <<< "$declared" || true)"
if [ -n "$unprefixed" ]; then
  while IFS= read -r fn; do err "Funktion ohne idt_-Präfix: $fn()"; done <<< "$unprefixed"
else
  ok "alle Funktionsdeklarationen tragen idt_"
fi

# Blocknamen: register_block_type( 'idt/…' )
foreign_blocks="$(grep -rhoE "register_block_type\([[:space:]]*'[^']+'" --include='*.php' "$theme" \
  | sed -E "s/.*'([^']*)'/\1/" | grep -v '^idt/' || true)"
if [ -n "$foreign_blocks" ]; then
  while IFS= read -r b; do err "Block außerhalb des idt/-Namensraums: $b"; done <<< "$foreign_blocks"
else
  ok "alle Blöcke liegen unter idt/"
fi

# Textdomain: konsistent 'idt', sonst greift die Übersetzung nicht.
foreign_td="$(grep -rhoE "(__|_e|_x|esc_html__|esc_attr__|_n)\([^)]*,[[:space:]]*'[^']+'[[:space:]]*\)" --include='*.php' "$theme" \
  | grep -oE "'[^']+'[[:space:]]*\)$" | tr -d "') " | sort -u | grep -v '^idt$' || true)"
if [ -n "$foreign_td" ]; then
  while IFS= read -r td; do err "fremde Textdomain in Übersetzungsaufruf: '$td'"; done <<< "$foreign_td"
else
  ok "Textdomain durchgängig 'idt'"
fi

# -----------------------------------------------------------------------------
section "5) Render-Funktionen aufgelöst (CLAUDE.md, Konvention 2)"
# Ein Baustein, drei Oberflächen, eine Render-Funktion: Blöcke und Patterns
# rufen dieselben idt_sc_*()/idt_render_*()-Funktionen auf wie die Shortcodes.
# Zeigt einer dieser Aufrufe ins Leere, merkt PHP das erst zur Laufzeit — auf
# der Seite, die dann weiß bleibt. Ausgewertet wird über den PHP-Tokenizer
# (bin/php-symbols.php), damit Kommentare und Variablen keine Rolle spielen.
missing_calls="$(php "$repo/bin/php-symbols.php" called "$theme" | grep '^idt_' \
  | grep -vxF -f <(printf '%s\n' "$declared") || true)"
if [ -n "$missing_calls" ]; then
  while IFS= read -r fn; do err "Aufruf ohne Deklaration: $fn()"; done <<< "$missing_calls"
else
  ok "alle idt_-Aufrufe zeigen auf deklarierte Funktionen"
fi

# String-Callbacks in add_shortcode/add_action/add_filter zeigen ins Leere,
# ohne dass PHP das vor dem Auslösen des Hooks bemerkt.
missing_cb="$(php "$repo/bin/php-symbols.php" callbacks "$theme" | grep '^idt_' \
  | grep -vxF -f <(printf '%s\n' "$declared") || true)"
if [ -n "$missing_cb" ]; then
  while IFS= read -r fn; do err "Hook-/Shortcode-Callback ohne Deklaration: $fn()"; done <<< "$missing_cb"
else
  ok "alle Shortcode- und Hook-Callbacks sind deklariert"
fi

# -----------------------------------------------------------------------------
section "6) Editor-Palette ↔ Frontend (CLAUDE.md, Konvention 4)"
# Wer in idt_brand_palette() eine Farbe ergänzt, muss Token und
# .has-…-Klassen (style.css 7c) mitliefern, sonst bleibt die Farbe im Editor
# wählbar, aber im Frontend wirkungslos.
slugs="$(sed -n "/function idt_brand_palette/,/^}/p" "$theme/functions.php" \
  | grep -oE "'slug'[[:space:]]*=>[[:space:]]*'[^']+'" | sed -E "s/.*'([^']*)'$/\1/")"
if [ -z "$slugs" ]; then
  err "idt_brand_palette() liefert keine Slugs — Palette nicht auswertbar"
else
  palette_ok=1
  while IFS= read -r s; do
    grep -qE "^[[:space:]]*--$s:" "$theme/style.css"          || { err "Token --$s fehlt in style.css"; palette_ok=0; }
    grep -qF ".has-$s-color" "$theme/style.css"               || { err "Klasse .has-$s-color fehlt (style.css 7c)"; palette_ok=0; }
    grep -qF ".has-$s-background-color" "$theme/style.css"    || { err "Klasse .has-$s-background-color fehlt (style.css 7c)"; palette_ok=0; }
  done <<< "$slugs"
  [ "$palette_ok" -eq 1 ] && ok "$(wc -l <<< "$slugs" | tr -d ' ') Palettenfarben haben Token und Frontend-Klassen"
fi

# -----------------------------------------------------------------------------
section "7) Direktzugriffsschutz"
# functions.php und alles unter inc/ und blocks/ wird nie direkt aufgerufen —
# ohne Guard ist eine direkt angesurfte Datei eine offene Tür. Template-Dateien
# (page.php, header.php …) brauchen ihn nicht, sie laufen nur über WordPress.
guard_ok=1
while IFS= read -r f; do
  grep -q "defined( 'ABSPATH' )" "$f" || { err "${f#"$repo"/}: ABSPATH-Guard fehlt"; guard_ok=0; }
done < <(find "$theme/inc" "$theme/blocks" -name '*.php' 2>/dev/null; echo "$theme/functions.php")
[ "$guard_ok" -eq 1 ] && ok "functions.php, inc/ und blocks/ sind gegen Direktaufruf geschützt"

# -----------------------------------------------------------------------------
section "8) Keine Debug-Reste"
debug_php="$(grep -rnE '\b(var_dump|print_r|error_log|xdebug_break)[[:space:]]*\(' --include='*.php' "$theme" || true)"
debug_js="$(grep -rnE 'console\.(log|debug|dir)[[:space:]]*\(|\bdebugger\b' "$theme/assets" "$theme/blocks" --include='*.js' 2>/dev/null || true)"
if [ -n "$debug_php$debug_js" ]; then
  printf '%s\n%s\n' "$debug_php" "$debug_js" | grep -v '^$' | while IFS= read -r line; do
    err "Debug-Ausgabe: ${line#"$repo"/}"
  done
  fail=1
else
  ok "keine var_dump/print_r/console.log-Reste"
fi

# -----------------------------------------------------------------------------
section "9) JavaScript"
if command -v node >/dev/null 2>&1; then
  js_ok=1
  js_count=0
  # assets/ und die Sicht-/Editor-Skripte der Blöcke unter blocks/.
  while IFS= read -r f; do
    js_count=$((js_count + 1))
    if ! out="$(node --check "$f" 2>&1)"; then
      err "${f#"$repo"/}: $(head -n 3 <<< "$out" | tail -n 1)"
      js_ok=0
    fi
    # Konvention 3: alles in eine IIFE, damit nichts ins globale Scope leakt.
    # Der Kopfkommentar darf davorstehen, deshalb die erste Zeile Code suchen.
    if ! grep -qE '^\(\s*function|^\(\s*\(\s*\)\s*=>|^\s*\(function' "$f"; then
      err "${f#"$repo"/}: nicht in eine IIFE gekapselt"
      js_ok=0
    fi
  done < <(find "$theme/assets" "$theme/blocks" -name '*.js' 2>/dev/null | sort)
  [ "$js_ok" -eq 1 ] && ok "$js_count JS-Dateien parsen und sind gekapselt"
else
  warn "node nicht gefunden — JS-Syntaxprüfung übersprungen"
fi

# -----------------------------------------------------------------------------
section "10) Sprache"
# CLAUDE.md: Kommentare, UI-Texte und Doku sind auf Deutsch. Mechanisch prüfbar
# ist nur das Offensichtliche — englische Platzhalter, die niemand übersetzt hat.
todos="$(grep -rniE '\b(TODO|FIXME|XXX|HACK)\b' --include='*.php' --include='*.js' --include='*.css' "$theme" || true)"
if [ -n "$todos" ]; then
  while IFS= read -r line; do warn "offener Merker: ${line#"$repo"/}"; done <<< "$todos"
else
  ok "keine offenen TODO/FIXME-Merker im Theme"
fi

# -----------------------------------------------------------------------------
section "11) Blöcke mit eigener block.json"
# Die meisten Bausteine stehen in idt_blocks_config() und werden oben schon
# mitgeprüft. Ein Block, der seine Metadaten in einer block.json mitbringt,
# liegt daneben: Namensraum, Textdomain, die render-Datei, die Skript-Handles
# und die Attribut-Vorgaben stehen dort in JSON statt in PHP. Ausgewertet wird
# das in bin/block-meta.php.
block_dirs="$(find "$theme/blocks" -mindepth 1 -maxdepth 1 -type d 2>/dev/null | wc -l | tr -d ' ')"
if [ "$block_dirs" = "0" ]; then
  ok "keine block.json-Blöcke vorhanden"
else
  block_problems="$(php "$repo/bin/block-meta.php" "$theme")"
  if [ -n "$block_problems" ]; then
    while IFS= read -r line; do err "$line"; done <<< "$block_problems"
  else
    ok "$block_dirs Block-Ordner: Namensraum, Textdomain, render-Datei, Handles und Vorgaben stimmen"
  fi
fi

# -----------------------------------------------------------------------------
if [ "$fail" -eq 0 ]; then
  printf '\n\033[32mAlle Prüfungen bestanden.\033[0m\n'
else
  printf '\n\033[31mPrüfungen fehlgeschlagen — siehe ✗ oben.\033[0m\n'
fi
exit "$fail"
