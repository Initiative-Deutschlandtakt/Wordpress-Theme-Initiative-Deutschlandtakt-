#!/usr/bin/env bash
# One-shot bootstrap for the local IDT WordPress demo.
# Idempotent: safe to re-run. Heavy lifting (demo content) happens in the
# theme's after_switch_theme hook, so this only installs core + activates.
set -euo pipefail

cd /var/www/html

echo "==> Waiting for WordPress core files (wp-config.php)…"
for i in $(seq 1 60); do
  [ -f wp-config.php ] && break
  sleep 2
done

echo "==> Waiting for database…"
until wp db check >/dev/null 2>&1; do
  sleep 2
done

if ! wp core is-installed >/dev/null 2>&1; then
  echo "==> Installing WordPress core…"
  wp core install \
    --url="http://localhost:8090" \
    --title="Initiative Deutschlandtakt" \
    --admin_user="admin" \
    --admin_password="admin" \
    --admin_email="admin@example.com" \
    --skip-email
else
  echo "==> WordPress already installed."
fi

echo "==> Setting locale to German and pretty permalinks…"
wp option update WPLANG de_DE >/dev/null 2>&1 || true
wp language core install de_DE >/dev/null 2>&1 || true
wp site switch-language de_DE >/dev/null 2>&1 || true
wp rewrite structure '/%postname%/' --hard >/dev/null 2>&1 || true

echo "==> Activating IDT Deutschlandtakt theme…"
wp theme activate idt-deutschlandtakt

# Seeding läuft NUR noch hier explizit (kein after_switch_theme-Hook mehr,
# damit eine Aktivierung auf Production-Seiten folgenlos bleibt): bei leerer
# Installation die Demo-Inhalte anlegen.
if [ "$(wp post list --post_type=page --format=count 2>/dev/null || echo 0)" -lt 2 ]; then
  echo "==> Seeding demo content…"
  wp option delete idt_seeded >/dev/null 2>&1 || true
  wp eval 'if (function_exists("idt_seed_demo_content")) { idt_seed_demo_content(); }'
fi

wp rewrite flush --hard >/dev/null 2>&1 || true

echo ""
echo "============================================================"
echo "  IDT Deutschlandtakt is ready:"
echo "    Site:  http://localhost:8090"
echo "    Admin: http://localhost:8090/wp-admin  (admin / admin)"
echo "============================================================"
