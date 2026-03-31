#!/usr/bin/env bash
set -euo pipefail

# Phase 4 precheck gate for staging cutover.
# - Safe by default: read-only checks + dry-run migration report.
# - Does not toggle flags or mutate business data.
#
# Usage:
#   ./scripts/staging_phase4_cutover_precheck.sh [company_id] [sample]
#
# Example:
#   ./scripts/staging_phase4_cutover_precheck.sh 20 20

COMPANY_ID="${1:-}"
SAMPLE_SIZE="${2:-20}"
APP_DIR="${APP_DIR:-/var/www/craveva-staging/current/craveva}"
MIN_FREE_MB="${MIN_FREE_MB:-2048}"
REPORT_DIR="${REPORT_DIR:-storage/app/reports}"
TS="$(date +%Y%m%d-%H%M%S)"

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
if [[ "$(id -un)" == "www-data" ]]; then
  mkdir -p "${REPORT_DIR}"
else
  sudo -u www-data mkdir -p "${REPORT_DIR}"
  sudo chown -R www-data:www-data "${REPORT_DIR}" || true
fi

echo "[1/7] Disk preflight ..."
FREE_MB="$(df -Pm / | awk 'NR==2 {print $4}')"
if [[ "${FREE_MB}" -lt "${MIN_FREE_MB}" ]]; then
  echo "ERROR: low disk space on / (${FREE_MB}MB < ${MIN_FREE_MB}MB)"
  exit 1
fi
echo "OK: free disk ${FREE_MB}MB"

echo "[2/7] App bootstrap and DB connectivity ..."
run_as_www_data php artisan about >/dev/null
run_as_www_data php artisan db:show >/dev/null
echo "OK: artisan about + db:show"

echo "[3/7] Required commands ..."
run_as_www_data php artisan list --raw | awk '/purchase:sales-do-migration-rehearsal|purchase:sales-do-reconcile-report|purchase:sales-do-migrate-data|purchase:sales-do-migrate-rollback/{print $1}'
echo "OK: command list printed"

echo "[4/7] Required tables ..."
run_as_www_data php -r '
  require "vendor/autoload.php";
  $app = require "bootstrap/app.php";
  $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
  $tables = ["sales_shipments","sales_shipment_items","sales_dos","sales_do_items"];
  foreach ($tables as $t) {
      echo $t . "=" . (Illuminate\Support\Facades\Schema::hasTable($t) ? "yes" : "no") . PHP_EOL;
  }
'
echo "OK: table presence printed"

echo "[5/7] Current cutover flags ..."
run_as_www_data php -r '
  require "vendor/autoload.php";
  $app = require "bootstrap/app.php";
  $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
  echo "purchase.flow_naming_mode=" . (string) config("purchase.flow_naming_mode") . PHP_EOL;
  echo "purchase.do_grn_cutover_enabled=" . ((bool) config("purchase.do_grn_cutover_enabled") ? "true" : "false") . PHP_EOL;
'

echo "[6/7] Baseline reconciliation safety gate ..."
bash scripts/staging_sales_do_rehearsal_gate.sh "${COMPANY_ID:-10}" "${SAMPLE_SIZE}"

echo "[7/7] Migration dry-run report ..."
DRYRUN_REPORT="${REPORT_DIR}/sales-do-migrate-cutover-precheck-${TS}.json"
MIGRATE_ARGS=(purchase:sales-do-migrate-data "--sample=${SAMPLE_SIZE}" "--output=${DRYRUN_REPORT}")
if [[ -n "${COMPANY_ID}" ]]; then
  MIGRATE_ARGS+=("--company_id=${COMPANY_ID}")
fi
run_as_www_data php artisan "${MIGRATE_ARGS[@]}"
echo "OK: dry-run report => ${APP_DIR}/${DRYRUN_REPORT}"

echo ""
echo "Precheck PASS. Suggested execute window commands:"
echo "1) Execute migrate:"
if [[ -n "${COMPANY_ID}" ]]; then
  echo "   sudo -u www-data php artisan purchase:sales-do-migrate-data --company_id=${COMPANY_ID} --chunk=200 --execute --force --output=storage/app/reports/sales-do-migrate-execute-company${COMPANY_ID}.json"
else
  echo "   sudo -u www-data php artisan purchase:sales-do-migrate-data --chunk=200 --execute --force --output=storage/app/reports/sales-do-migrate-execute-all.json"
fi
echo "2) Toggle cutover flag in .env (PURCHASE_DO_GRN_CUTOVER_ENABLED=true), then:"
echo "   sudo -u www-data php artisan optimize:clear"
echo "3) If rollback needed:"
echo "   sudo -u www-data php artisan purchase:sales-do-migrate-rollback --manifest=<manifest-path> --execute --force"

