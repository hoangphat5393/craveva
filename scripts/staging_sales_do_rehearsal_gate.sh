#!/usr/bin/env bash
set -euo pipefail

# Usage:
#   ./scripts/staging_sales_do_rehearsal_gate.sh <company_id> [sample]
#
# Example:
#   ./scripts/staging_sales_do_rehearsal_gate.sh 10 50
#
# Notes:
# - Run on staging host where app code exists.
# - Script is read/report only. It does NOT migrate/modify business data.

COMPANY_ID="${1:-}"
SAMPLE_SIZE="${2:-50}"
APP_DIR="${APP_DIR:-/var/www/craveva-staging/current/craveva}"
REPORT_DIR="${REPORT_DIR:-storage/app/reports}"
TS="$(date +%Y%m%d-%H%M%S)"
BASELINE_FILE="${REPORT_DIR}/sales-do-baseline-company${COMPANY_ID}-${TS}.json"
RECON_FILE="${REPORT_DIR}/sales-do-reconcile-company${COMPANY_ID}-${TS}.json"

if [[ -z "${COMPANY_ID}" ]]; then
  echo "ERROR: missing company_id"
  echo "Usage: $0 <company_id> [sample]"
  exit 2
fi

if [[ ! -d "${APP_DIR}" ]]; then
  echo "ERROR: APP_DIR not found: ${APP_DIR}"
  exit 2
fi

run_artisan() {
  local cmd_args=("$@")
  if [[ "$(id -un)" == "www-data" ]]; then
    php artisan "${cmd_args[@]}"
  else
    sudo -u www-data php artisan "${cmd_args[@]}"
  fi
}

cd "${APP_DIR}"
if [[ "$(id -un)" == "www-data" ]]; then
  mkdir -p "${REPORT_DIR}"
else
  sudo -u www-data mkdir -p "${REPORT_DIR}"
  # Ensure existing report dir from previous runs is writable by www-data.
  sudo chown -R www-data:www-data "${REPORT_DIR}" || true
fi

echo "[1/3] Creating baseline dry-run report..."
run_artisan purchase:sales-do-migration-rehearsal \
  --company_id="${COMPANY_ID}" \
  --sample="${SAMPLE_SIZE}" \
  --output="${BASELINE_FILE}"

echo "[2/3] Creating reconciliation report..."
run_artisan purchase:sales-do-reconcile-report \
  --company_id="${COMPANY_ID}" \
  --sample="${SAMPLE_SIZE}" \
  --baseline="${BASELINE_FILE}" \
  --output="${RECON_FILE}"

echo "[3/3] Validating reconciliation gate..."
php -r '
  $path = $argv[1] ?? "";
  $data = json_decode((string) @file_get_contents($path), true);
  if (!is_array($data)) { fwrite(STDERR, "ERROR: cannot read reconcile JSON\n"); exit(2); }
  $cmp = $data["comparison"] ?? [];
  $deltaShip = (int)($cmp["shipments_count_delta"] ?? 0);
  $deltaItems = (int)($cmp["items_count_delta"] ?? 0);
  $deltaQty = (float)($cmp["total_quantity_shipped_delta"] ?? 0);
  $okOrphan = (bool)($cmp["quality_checks_ok"]["orphan_item_count_is_zero"] ?? false);
  $okDup = (bool)($cmp["quality_checks_ok"]["duplicate_shipment_number_count_is_zero"] ?? false);

  $pass = ($deltaShip === 0) && ($deltaItems === 0) && (abs($deltaQty) < 0.0000001) && $okOrphan && $okDup;

  echo "Gate summary:\n";
  echo " - shipments_count_delta: " . $deltaShip . "\n";
  echo " - items_count_delta: " . $deltaItems . "\n";
  echo " - total_quantity_shipped_delta: " . $deltaQty . "\n";
  echo " - orphan_item_count_is_zero: " . ($okOrphan ? "true" : "false") . "\n";
  echo " - duplicate_shipment_number_count_is_zero: " . ($okDup ? "true" : "false") . "\n";

  if (!$pass) {
    fwrite(STDERR, "FAIL: reconciliation gate not passed\n");
    exit(1);
  }
  echo "PASS: reconciliation gate passed\n";
' "${RECON_FILE}"

echo "Done."
echo "Baseline: ${APP_DIR}/${BASELINE_FILE}"
echo "Reconcile: ${APP_DIR}/${RECON_FILE}"

