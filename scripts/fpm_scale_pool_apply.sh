#!/usr/bin/env bash
# Apply pm.max_children / pm.max_spare_servers to PHP 8.3 FPM www pool (Ubuntu/Debian).
# Usage (on server, with sudo):
#   sudo bash scripts/fpm_scale_pool_apply.sh staging    # max_children=4
#   sudo bash scripts/fpm_scale_pool_apply.sh hub        # max_children=8
#
# Or: curl | bash — prefer copy from repo after review.

set -euo pipefail

TARGET="${1:-}"
POOL="/etc/php/8.3/fpm/pool.d/www.conf"

case "$TARGET" in
  staging)
    MAX_CHILDREN=4
    MAX_SPARE=4
    ;;
  hub)
    MAX_CHILDREN=8
    MAX_SPARE=8
    ;;
  *)
    echo "Usage: sudo $0 staging|hub"
    exit 1
    ;;
esac

if [[ ! -f "$POOL" ]]; then
  echo "Missing pool file: $POOL"
  exit 1
fi

TS="$(date +%Y%m%d%H%M%S)"
cp -a "$POOL" "${POOL}.bak.fpm_scale_${TS}"

sed -i "s/^pm.max_children = .*/pm.max_children = ${MAX_CHILDREN}/" "$POOL"
sed -i "s/^pm.max_spare_servers = .*/pm.max_spare_servers = ${MAX_SPARE}/" "$POOL"

# Nếu start_servers > max_children, FPM từ chối config
START_VAL=$(awk '/^pm.start_servers =/{print $3}' "$POOL" | head -1)
if [[ -n "${START_VAL}" ]] && [[ "${START_VAL}" -gt "${MAX_CHILDREN}" ]]; then
  sed -i "s/^pm.start_servers = .*/pm.start_servers = ${MAX_CHILDREN}/" "$POOL"
fi

php-fpm8.3 -t
systemctl reload php8.3-fpm

echo "OK ${TARGET}:"
grep -E '^pm\.(max_children|start_servers|min_spare|max_spare)' "$POOL"
