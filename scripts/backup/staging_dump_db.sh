#!/usr/bin/env bash
# Full MySQL dump on staging VM. Reads DB_* from Laravel .env in APP dir.
# Output: APP/backup/<database>_YYYYMMDD_HHMMSS.sql.gz
# Prints: DUMP_PATH=...  DUMP_BYTES=...
set -e

APP="${1:-/var/www/craveva-staging/current/craveva}"
cd "$APP"

read_env() {
  local key="$1"
  grep -E "^${key}=" .env | head -1 | cut -d= -f2- | tr -d '\r'
}

DB_HOST=$(read_env DB_HOST)
DB_PORT=$(read_env DB_PORT)
DB_DATABASE=$(read_env DB_DATABASE)
DB_USERNAME=$(read_env DB_USERNAME)
DB_PASSWORD=$(read_env DB_PASSWORD)

if [ -z "$DB_HOST" ] || [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ]; then
  echo "ERROR: Missing DB_* in $APP/.env" >&2
  exit 1
fi

BACKUP_DIR="$APP/backup"
mkdir -p "$BACKUP_DIR"

STAMP=$(date +%Y%m%d_%H%M%S)
OUT="$BACKUP_DIR/${DB_DATABASE}_${STAMP}.sql.gz"

mysqldump \
  -h"$DB_HOST" \
  -P"$DB_PORT" \
  -u"$DB_USERNAME" \
  -p"$DB_PASSWORD" \
  "$DB_DATABASE" \
  --single-transaction \
  --routines \
  --triggers \
  --set-gtid-purged=OFF \
  | gzip -c > "$OUT"

echo "DUMP_PATH=$OUT"
echo "DUMP_BYTES=$(wc -c < "$OUT" | tr -d ' ')"
