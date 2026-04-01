#!/usr/bin/env bash
# Run on hub as deploy user (sudo for chown if needed). Uses shared Composer cache.
# Removes composer.lock only (never composer.json), then composer update.
set -eu
APP="${1:-/var/www/hub.craveva.com.git-src}"
CACHE="${COMPOSER_CACHE_DIR:-/var/www/.composer-cache}"
cd "$APP"
if [ ! -f composer.json ]; then
  echo "ERROR: composer.json not found in $APP"
  exit 1
fi
sudo mkdir -p "$CACHE"
sudo chown www-data:www-data "$CACHE"
# --no-scripts: skip post-update hooks (e.g. ide-helper) that fail under --no-dev
sudo -u www-data env COMPOSER_CACHE_DIR="$CACHE" bash -c "cd \"$APP\" && rm -f composer.lock && composer update --no-dev --no-interaction --no-scripts"
sudo -u www-data env COMPOSER_CACHE_DIR="$CACHE" bash -c "cd \"$APP\" && composer dump-autoload -o --no-dev"
echo "OK: $APP"
