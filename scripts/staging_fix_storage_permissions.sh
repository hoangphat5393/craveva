#!/usr/bin/env bash
# Fix Laravel storage / bootstrap/cache permissions on staging (or hub).
# PHP-FPM must write sessions, cache, views, logs as www-data.
#
# Run ON the server (or via SSH):
#   APP=/var/www/craveva-staging/current/craveva bash scripts/staging_fix_storage_permissions.sh
#   ssh craveva-staging 'APP=/var/www/craveva-staging/current/craveva bash -s' < scripts/staging_fix_storage_permissions.sh
#
# See docs/SERVER_RUNBOOK.md section 4.6

set -euo pipefail

APP="${APP:-/var/www/craveva-staging/current/craveva}"
DEPLOY_USER="${DEPLOY_USER:-hoangphat5393}"

if [ ! -f "$APP/artisan" ]; then
  echo "ERROR: artisan not found under APP=$APP" >&2
  exit 1
fi

echo "APP=$APP"

sudo mkdir -p \
  "$APP/storage/framework/sessions" \
  "$APP/storage/framework/views" \
  "$APP/storage/framework/cache/data" \
  "$APP/storage/logs" \
  "$APP/bootstrap/cache"

sudo chown -R www-data:www-data "$APP/storage" "$APP/bootstrap/cache"
sudo chmod -R ug+rwX "$APP/storage" "$APP/bootstrap/cache"
sudo find "$APP/storage" "$APP/bootstrap/cache" -type d -exec chmod g+s {} \; 2>/dev/null || true
sudo chmod 2777 "$APP/storage/logs"

# Optional ACL when setfacl exists (deploy user can still read logs without breaking FPM writes)
if command -v setfacl >/dev/null 2>&1; then
  sudo setfacl -R -m "u:www-data:rwX,u:${DEPLOY_USER}:rwX" "$APP/storage" "$APP/bootstrap/cache" "$APP/storage/logs" 2>/dev/null || true
  sudo setfacl -dR -m "u:www-data:rwX,u:${DEPLOY_USER}:rwX" "$APP/storage" "$APP/bootstrap/cache" "$APP/storage/logs" 2>/dev/null || true
fi

# Remove session files owned by wrong user (common after git pull without post-deploy chmod)
if [ -d "$APP/storage/framework/sessions" ]; then
  find "$APP/storage/framework/sessions" -type f ! -user www-data -print 2>/dev/null | head -5 || true
  sudo find "$APP/storage/framework/sessions" -type f ! -user www-data -delete 2>/dev/null || true
fi

sudo -u www-data touch "$APP/storage/framework/sessions/.write_test" \
  && sudo rm -f "$APP/storage/framework/sessions/.write_test" \
  && echo "OK: www-data can write storage/framework/sessions"

sudo -u www-data touch "$APP/storage/framework/cache/data/.write_test" \
  && sudo rm -f "$APP/storage/framework/cache/data/.write_test" \
  && echo "OK: www-data can write storage/framework/cache/data"

sudo -u www-data php "$APP/artisan" optimize:clear
echo "Done. Reload PHP-FPM if errors persist: sudo systemctl reload php8.3-fpm"
