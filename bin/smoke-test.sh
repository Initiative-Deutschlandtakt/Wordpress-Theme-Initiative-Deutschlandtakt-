#!/usr/bin/env bash
# Integrationstest: installiert ein frisches WordPress, aktiviert das Theme,
# seedet die Demo-Inhalte und ruft die wichtigsten Seiten über HTTP ab.
#
#   ./bin/smoke-test.sh
#
# Das ist der Test, den die statischen Prüfungen nicht ersetzen können: ob ein
# fehlender Funktionsaufruf, eine geänderte WordPress-API oder ein Tippfehler
# im Template die Seite weiß werden lässt, zeigt sich erst im laufenden System.
#
# Voraussetzungen: php (mit mysqli), curl, eine erreichbare MySQL-/MariaDB-
# Instanz. WP-CLI wird bei Bedarf als Phar nach $TMP geladen.
#
# Konfiguration über Umgebungsvariablen (Standard = Docker-Stack des Repos):
#   IDT_DB_HOST (127.0.0.1)  IDT_DB_NAME (idt_smoke)
#   IDT_DB_USER (root)       IDT_DB_PASS (root)
#   IDT_WP_VERSION (latest)  IDT_PORT (8099)
set -uo pipefail

repo="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
slug="idt-deutschlandtakt"

db_host="${IDT_DB_HOST:-127.0.0.1}"
db_name="${IDT_DB_NAME:-idt_smoke}"
db_user="${IDT_DB_USER:-root}"
db_pass="${IDT_DB_PASS:-root}"
wp_version="${IDT_WP_VERSION:-latest}"
port="${IDT_PORT:-8099}"
url="http://127.0.0.1:$port"

fail=0
section() { printf '\n\033[1m%s\033[0m\n' "$1"; }
ok()      { printf '  \033[32m✓\033[0m %s\n' "$1"; }
err()     { printf '  \033[31m✗\033[0m %s\n' "$1"; fail=1; }

work="$(mktemp -d)"
server_pid=""
# Wird über trap aufgerufen, nicht direkt.
# shellcheck disable=SC2329
cleanup() {
  [ -n "$server_pid" ] && kill "$server_pid" 2>/dev/null
  rm -rf "$work"
}
trap cleanup EXIT

# -----------------------------------------------------------------------------
section "1) WordPress bereitstellen"
if command -v wp >/dev/null 2>&1; then
  # Absoluter Pfad, sonst ruft die Hilfsfunktion wp() unten sich selbst auf.
  wp_bin="$(command -v wp)"
else
  curl -sSL -o "$work/wp-cli.phar" https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar || {
    err "WP-CLI konnte nicht geladen werden"; exit 1; }
  chmod +x "$work/wp-cli.phar"
  wp_bin="php $work/wp-cli.phar"
fi
# --allow-root, weil der Test in CI-Containern als root läuft.
wp() { $wp_bin --path="$work/wp" --allow-root "$@"; }
ok "WP-CLI: $($wp_bin --version --allow-root 2>/dev/null | head -n 1)"

mkdir -p "$work/wp"
wp core download --version="$wp_version" --quiet || { err "wp core download fehlgeschlagen"; exit 1; }
ok "WordPress $wp_version heruntergeladen"

# WP_DEBUG an: Warnungen und Notices sollen im Test sichtbar werden statt
# stillzuliegen; das Log wird am Ende geprüft.
wp config create \
  --dbname="$db_name" --dbuser="$db_user" --dbpass="$db_pass" --dbhost="$db_host" \
  --skip-check --force --quiet \
  --extra-php <<'PHPCONF' || { err "wp config create fehlgeschlagen"; exit 1; }
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', true );
define( 'WP_DISABLE_FATAL_ERROR_HANDLER', true );
PHPCONF

wp db reset --yes --quiet 2>/dev/null || wp db create --quiet 2>/dev/null
wp core install \
  --url="$url" --title="IDT Smoke-Test" \
  --admin_user=admin --admin_password=admin --admin_email=admin@example.com \
  --skip-email --quiet || { err "wp core install fehlgeschlagen"; exit 1; }
wp rewrite structure '/%postname%/' --quiet
ok "WordPress installiert unter $url"

# -----------------------------------------------------------------------------
section "2) Theme aktivieren"
# Das Theme wird kopiert, nicht verlinkt: so wird genau das geprüft, was auch
# im Zip landet, und WordPress folgt keinem Symlink aus dem Arbeitsverzeichnis.
cp -R "$repo/theme/$slug" "$work/wp/wp-content/themes/$slug"
if out="$(wp theme activate "$slug" 2>&1)"; then
  ok "Theme aktiviert (ohne Fatal Error)"
else
  err "Theme-Aktivierung schlägt fehl: $out"
  exit 1
fi

# Seeding wie in wp-cli/init.sh — die Demo-Inhalte sind die Testdaten.
wp eval 'if ( function_exists( "idt_seed_demo_content" ) ) { idt_seed_demo_content(); }' >/dev/null 2>&1
wp rewrite flush --hard --quiet
ok "Demo-Inhalte geseedet"

# -----------------------------------------------------------------------------
section "3) Blöcke, Shortcodes, Patterns"
if out="$(wp eval-file "$repo/bin/smoke-render.php" 2>&1)"; then
  printf '%s\n' "$out" | sed 's/^/  /'
else
  printf '%s\n' "$out" | sed 's/^/  /'
  err "Rendertest fehlgeschlagen (siehe oben)"
fi

# -----------------------------------------------------------------------------
section "4) Seitenaufrufe über HTTP"
$wp_bin --path="$work/wp" --allow-root server --host=127.0.0.1 --port="$port" >"$work/server.log" 2>&1 &
server_pid=$!

for _ in $(seq 1 30); do
  curl -fsS -o /dev/null "$url" 2>/dev/null && break
  sleep 1
done

# Startseite, eine Unterseite, ein Beitrag, Suche, 404 und der Login —
# damit sind Front-Page-Template, page.php, single.php, search.php, 404.php
# und der Adminpfad einmal durchlaufen.
page_url="$(wp post list --post_type=page --posts_per_page=1 --field=url 2>/dev/null | head -n 1)"
post_url="$(wp post list --post_type=post --posts_per_page=1 --field=url 2>/dev/null | head -n 1)"

pruefe_seite() { # $1 = URL, $2 = erwarteter Statuscode, $3 = Bezeichnung
  local body status
  body="$(curl -sS -o "$work/body.html" -w '%{http_code}' "$1" 2>/dev/null)"
  status="$body"
  if [ "$status" != "$2" ]; then
    err "$3: HTTP $status statt $2 ($1)"
    return
  fi
  local meldungen
  meldungen="$(grep -oE '(Fatal error|Parse error|Warning|Notice|Deprecated):[^<]*' "$work/body.html" | head -n 3 || true)"
  if [ -n "$meldungen" ]; then
    err "$3: PHP-Meldung im Markup — $(tr '\n' ' ' <<< "$meldungen")"
    return
  fi
  ok "$3: HTTP $status, sauber ($(wc -c < "$work/body.html" | tr -d ' ') Bytes)"
}

pruefe_seite "$url/" 200 "Startseite"
[ -n "$page_url" ] && pruefe_seite "$page_url" 200 "Seite"
[ -n "$post_url" ] && pruefe_seite "$post_url" 200 "Beitrag"
pruefe_seite "$url/?s=takt" 200 "Suchergebnisse"
pruefe_seite "$url/gibt-es-nicht/" 404 "404-Seite"
pruefe_seite "$url/wp-login.php" 200 "Login"

# Die Startseite muss die Theme-Assets ausliefern — sonst ist das Theme zwar
# aktiv, wird aber nicht wirklich benutzt.
curl -sS "$url/" > "$work/front.html" 2>/dev/null
if grep -q "$slug/style.css" "$work/front.html"; then
  ok "Startseite lädt das Theme-Stylesheet"
else
  err "Startseite lädt style.css des Themes nicht"
fi
if grep -qE 'class="[^"]*idt-' "$work/front.html"; then
  ok "Startseite enthält idt-Markup"
else
  err "Startseite enthält kein idt-Markup"
fi

# -----------------------------------------------------------------------------
section "5) PHP-Log"
log="$work/wp/wp-content/debug.log"
if [ -s "$log" ]; then
  # Was das Theme verursacht (oder überhaupt fatal ist), zählt. Deprecated-
  # Meldungen aus dem WordPress-Kern kommen mit neuen PHP-Versionen und sind
  # hier nicht zu lösen — sie werden nur ausgegeben.
  relevant="$(grep -E "themes/$slug/|Fatal error|Parse error" "$log" || true)"
  if [ -n "$relevant" ]; then
    err "Meldungen aus dem Theme im debug.log:"
    head -n 20 <<< "$relevant" | sed 's/^/      /'
  else
    ok "keine Theme-Meldungen im debug.log ($(wc -l < "$log" | tr -d ' ') Zeile(n) aus dem Kern)"
    head -n 5 "$log" | sed 's/^/      /'
  fi
else
  ok "keine Einträge in debug.log"
fi

if [ "$fail" -eq 0 ]; then
  printf '\n\033[32mSmoke-Test bestanden.\033[0m\n'
else
  printf '\n\033[31mSmoke-Test fehlgeschlagen — siehe ✗ oben.\033[0m\n'
fi
exit "$fail"
