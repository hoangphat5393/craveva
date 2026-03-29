#!/usr/bin/env bash
# Chạy một worker xử lý tất cả hàng đợi import (ưu tiên trước) + default (webhook…).
# Dùng giống nhau trên staging/Linux và máy dev (Git Bash/WSL).
#
#   chmod +x scripts/run-queue-worker-all.sh
#   ./scripts/run-queue-worker-all.sh
#
# Tuỳ chọn thêm flag artisan, ví dụ: ./scripts/run-queue-worker-all.sh --verbose

set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [[ ! -f artisan ]]; then
  echo "Lỗi: không thấy artisan — chạy script từ trong project (scripts/ nằm cạnh artisan)." >&2
  exit 1
fi

echo "Preflight (chỉ đọc DB)…"
php scripts/queue-worker-preflight.php || exit 1
echo ""

# Thứ tự bên trái được ưu tiên — import trước, default sau (khớp ImportController::ALLOWED_IMPORT_QUEUE_NAMES + default)
QUEUES="ClientImport,ProductImport,EmployeeImport,ProjectImport,DealImport,LeadImport,ExpenseImport,AttendanceImport,JobApplicationImport,ClientProductPricingImport,PricingTierItemsImport,WarehouseImport,InventoryImport,default"

exec php artisan queue:work database \
  --queue="$QUEUES" \
  --tries=3 \
  --sleep=3 \
  "$@"
