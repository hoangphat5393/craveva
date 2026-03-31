#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/craveva-staging/current/craveva}"
FLOW_NAMING_MODE="${FLOW_NAMING_MODE:-compat_v2}"
CUTOVER_ENABLED="${CUTOVER_ENABLED:-true}"

cd "${APP_DIR}"

python3 - <<'PY'
from pathlib import Path
from datetime import datetime

env_path = Path(".env")
backup = Path(f".env.backup-cutover-{datetime.now().strftime('%Y%m%d-%H%M%S')}")
backup.write_bytes(env_path.read_bytes())

target = {
    "PURCHASE_FLOW_NAMING_MODE": "compat_v2",
    "PURCHASE_DO_GRN_CUTOVER_ENABLED": "true",
}

lines = env_path.read_text(encoding="utf-8", errors="ignore").splitlines()
found = {k: False for k in target.keys()}
new_lines = []

for line in lines:
    replaced = False
    for k, v in target.items():
        if line.startswith(k + "="):
            new_lines.append(f"{k}={v}")
            found[k] = True
            replaced = True
            break
    if not replaced:
        new_lines.append(line)

for k, v in target.items():
    if not found[k]:
        new_lines.append(f"{k}={v}")

env_path.write_text("\n".join(new_lines) + "\n", encoding="utf-8")
print(f"env_backup={backup}")
PY

if [[ "$(id -un)" == "www-data" ]]; then
  php artisan optimize:clear
  php -r 'require "vendor/autoload.php"; $app=require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); echo "flow_naming_mode=".config("purchase.flow_naming_mode").PHP_EOL; echo "cutover=".(((bool)config("purchase.do_grn_cutover_enabled"))?"true":"false").PHP_EOL;'
else
  sudo -u www-data php artisan optimize:clear
  sudo -u www-data php -r 'require "vendor/autoload.php"; $app=require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); echo "flow_naming_mode=".config("purchase.flow_naming_mode").PHP_EOL; echo "cutover=".(((bool)config("purchase.do_grn_cutover_enabled"))?"true":"false").PHP_EOL;'
fi

