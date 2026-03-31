#!/usr/bin/env bash
set -euo pipefail

# Safe runner for Phase 3 rehearsal on staging.
# - Preflight checks (disk/app/db)
# - Optional MySQL backup from current .env connection
# - Runs rehearsal gate (baseline + reconcile + pass/fail gate)
#
# Usage:
#   ./scripts/staging_phase3_safe_execute.sh <company_id> [sample]
#
# Optional environment variables:
#   APP_DIR=/var/www/craveva-staging/current/craveva
#   MIN_FREE_MB=2048
#   NO_BACKUP=1            # set 1 to skip DB backup
#   BACKUP_DIR=storage/app/backups/phase3

COMPANY_ID="${1:-}"
SAMPLE_SIZE="${2:-50}"
APP_DIR="${APP_DIR:-/var/www/craveva-staging/current/craveva}"
MIN_FREE_MB="${MIN_FREE_MB:-2048}"
NO_BACKUP="${NO_BACKUP:-0}"
BACKUP_DIR="${BACKUP_DIR:-storage/app/backups/phase3}"
TS="$(date +%Y%m%d-%H%M%S)"

if [[ -z "${COMPANY_ID}" ]]; then
  echo "ERROR: missing company_id"
  echo "Usage: $0 <company_id> [sample]"
  exit 2
fi

if [[ ! -d "${APP_DIR}" ]]; then
  echo "ERROR: APP_DIR not found: ${APP_DIR}"
  exit 2
fi

run_as_www_data() {
  local cmd=("$@")
  if [[ "$(id -un)" == "www-data" ]]; then
    "${cmd[@]}"
  else
    sudo -u www-data "${cmd[@]}"
  fi
}

cd "${APP_DIR}"

echo "[Preflight] Checking free disk on / ..."
FREE_MB="$(df -Pm / | awk "NR==2 {print \$4}")"
if [[ "${FREE_MB}" -lt "${MIN_FREE_MB}" ]]; then
  echo "ERROR: low disk space on / (${FREE_MB}MB < ${MIN_FREE_MB}MB)"
  exit 1
fi
echo "OK: free disk ${FREE_MB}MB"

echo "[Preflight] Checking Laravel bootstrap ..."
run_as_www_data php artisan about >/dev/null
echo "OK: artisan about"

echo "[Preflight] Checking DB connectivity ..."
run_as_www_data php artisan db:show >/dev/null
echo "OK: artisan db:show"

if [[ "${NO_BACKUP}" != "1" ]]; then
  echo "[Backup] Building DB connection metadata ..."
  DB_JSON="$(run_as_www_data php -r 'require "vendor/autoload.php"; $app=require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); $default=config("database.default"); $conn=config("database.connections.".$default); echo json_encode(["default"=>$default,"host"=>$conn["host"]??null,"port"=>$conn["port"]??3306,"database"=>$conn["database"]??null,"username"=>$conn["username"]??null,"password"=>$conn["password"]??null]);')"

  DB_DEFAULT="$(php -r '$j=json_decode($argv[1],true); echo $j["default"] ?? "";' "${DB_JSON}")"
  DB_HOST="$(php -r '$j=json_decode($argv[1],true); echo $j["host"] ?? "";' "${DB_JSON}")"
  DB_PORT="$(php -r '$j=json_decode($argv[1],true); echo $j["port"] ?? "3306";' "${DB_JSON}")"
  DB_NAME="$(php -r '$j=json_decode($argv[1],true); echo $j["database"] ?? "";' "${DB_JSON}")"
  DB_USER="$(php -r '$j=json_decode($argv[1],true); echo $j["username"] ?? "";' "${DB_JSON}")"
  DB_PASS="$(php -r '$j=json_decode($argv[1],true); echo $j["password"] ?? "";' "${DB_JSON}")"

  if [[ "${DB_DEFAULT}" != "mysql" && "${DB_DEFAULT}" != "mariadb" ]]; then
    echo "WARN: DB default is '${DB_DEFAULT}', auto backup currently supports mysql/mariadb only. Skipping backup."
  else
    if ! command -v mysqldump >/dev/null 2>&1; then
      echo "ERROR: mysqldump not found. Install client tools or rerun with NO_BACKUP=1 (not recommended)."
      exit 1
    fi

    mkdir -p "${BACKUP_DIR}"
    DUMP_FILE="${BACKUP_DIR}/db-${DB_NAME}-${TS}.sql.gz"

    echo "[Backup] Running mysqldump -> ${DUMP_FILE}"
    MYSQL_PWD="${DB_PASS}" mysqldump \
      --host="${DB_HOST}" \
      --port="${DB_PORT}" \
      --user="${DB_USER}" \
      --single-transaction \
      --quick \
      "${DB_NAME}" | gzip > "${DUMP_FILE}"

    echo "OK: DB backup created: ${APP_DIR}/${DUMP_FILE}"
  fi
else
  echo "WARN: NO_BACKUP=1 -> skipping DB backup by operator request."
fi

echo "[Gate] Running rehearsal gate script ..."
bash scripts/staging_sales_do_rehearsal_gate.sh "${COMPANY_ID}" "${SAMPLE_SIZE}"

echo "All checks completed."

