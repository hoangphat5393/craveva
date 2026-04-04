#!/usr/bin/env bash
# Tune PHP 8.3 for ERP import (FPM = web, CLI = artisan / queue workers).
# Run on Ubuntu/Debian with sudo (staging / hub):
#   sudo bash scripts/tune_php83_import_limits.sh
#
# Applies:
#   - max_execution_time / max_input_time = 300 (fpm + cli)
#   - upload_max_filesize / post_max_size = 64M (fpm only)
#
# After run: reload FPM. If Nginx returns 504 on long imports, raise fastcgi_read_timeout
# to >= 300s in the site config.

set -euo pipefail

PHPVER="${PHPVER:-8.3}"
FPM_INI="/etc/php/${PHPVER}/fpm/php.ini"
CLI_INI="/etc/php/${PHPVER}/cli/php.ini"

tune_ini_file() {
  local file="$1"
  local mode="$2" # fpm | cli

  if [[ ! -f "$file" ]]; then
    echo "Skip (missing): $file"
    return 0
  fi

  local ts
  ts="$(date +%Y%m%d%H%M%S)"
  cp -a "$file" "${file}.bak.${ts}"

  sed -i \
    -e 's/^[;[:space:]]*max_execution_time[[:space:]]*=.*/max_execution_time = 300/' \
    -e 's/^[;[:space:]]*max_input_time[[:space:]]*=.*/max_input_time = 300/' \
    "$file"

  if [[ "$mode" == "fpm" ]]; then
    sed -i \
      -e 's/^[;[:space:]]*upload_max_filesize[[:space:]]*=.*/upload_max_filesize = 64M/' \
      -e 's/^[;[:space:]]*post_max_size[[:space:]]*=.*/post_max_size = 64M/' \
      "$file"
  fi

  echo "Updated: $file ($mode)"
}

if [[ "${EUID:-0}" -ne 0 ]]; then
  echo "Run with sudo: sudo bash $0"
  exit 1
fi

tune_ini_file "$FPM_INI" fpm
tune_ini_file "$CLI_INI" cli

if systemctl is-active --quiet "php${PHPVER}-fpm" 2>/dev/null; then
  systemctl reload "php${PHPVER}-fpm"
  echo "Reloaded php${PHPVER}-fpm."
else
  echo "php${PHPVER}-fpm not active; reload manually when ready."
fi

echo "Done. Verify: php -i | grep -E 'max_execution|max_input|post_max|upload_max'"
