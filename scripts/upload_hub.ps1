# Zip deploy → hub production + optional git check. SSH Host = craveva-hub-server in ~/.ssh/config.
# Repo: https://github.com/CRAVEVA/craveva-staging.git — prefer push from local then -HubGitPull on hub.
# Does not modify remote .env.

param(
    [switch]$HubGitPull,
    [switch]$HubGitRepairMain,
    [switch]$HubEnsureGit,
    [string]$HubGitBranch = "main",
    [string]$HubGitRemote = "https://github.com/CRAVEVA/craveva-staging.git"
)

$ErrorActionPreference = "Stop"
$Here = $PSScriptRoot
. (Join-Path $Here "deploy-zip.common.ps1")

$HubHost = "craveva-hub-server"
$HubPath = "/var/www/hub.craveva.com"
$DeployOwner = "hoangphat5393"
$RepoRoot = Split-Path $Here -Parent
$LocalTempDir = Join-Path $RepoRoot ".deploy_hub_tmp"
$ZipFile = Join-Path $RepoRoot "deploy_hub.zip"

if ($HubEnsureGit) {
    ssh "${HubHost}" "sudo DEBIAN_FRONTEND=noninteractive apt-get update -qq && sudo DEBIAN_FRONTEND=noninteractive apt-get install -y git"
}

$doPullFlag = if ($HubGitPull) { "1" } else { "0" }
$repairFlag = if ($HubGitRepairMain) { "1" } else { "0" }
$gitCheckScript = @'
set -euo pipefail
command -v git >/dev/null || { echo "Install git"; exit 1; }
cd "APP_PATH_PLACEHOLDER"
[ -d .git ] || { echo "HUB_NOT_A_GIT_REPO"; exit 2; }
BR="BRANCH_PLACEHOLDER"
OREF="origin/${BR}"
current_url="$(git remote get-url origin 2>/dev/null || true)"
[ -z "${current_url}" ] && git remote add origin "REMOTE_PLACEHOLDER" || true
git fetch origin
git rev-parse -q --verify "${OREF}" >/dev/null || { echo "No ${OREF}"; exit 3; }
NEED_REPAIR=0
[ "REPAIR_FLAG_PLACEHOLDER" = "1" ] && NEED_REPAIR=1
if [ "DO_PULL_PLACEHOLDER" = "1" ] && [ "${NEED_REPAIR}" != "1" ]; then
  if ! git rev-parse -q --verify HEAD >/dev/null 2>&1; then NEED_REPAIR=1
  else
    HC=$(git rev-list --count HEAD 2>/dev/null || echo 0)
    [ "${HC}" -eq 0 ] && NEED_REPAIR=1
  fi
fi
if [ "${NEED_REPAIR}" = "1" ]; then
  git checkout -f -B "${BR}" "${OREF}" || exit 4
  git branch --set-upstream-to="${OREF}" "${BR}"
fi
if [ "DO_PULL_PLACEHOLDER" = "1" ]; then
  git checkout "${BR}"
  git pull --ff-only || exit 4
fi
git status -sb
git log -1 --oneline || true
'@
$gitCheckScript = $gitCheckScript.Replace("APP_PATH_PLACEHOLDER", $HubPath).Replace("REMOTE_PLACEHOLDER", $HubGitRemote).Replace("DO_PULL_PLACEHOLDER", $doPullFlag).Replace("BRANCH_PLACEHOLDER", $HubGitBranch).Replace("REPAIR_FLAG_PLACEHOLDER", $repairFlag)
$gitCheckScript | ssh "${HubHost}" "bash -se"
$gitExit = $LASTEXITCODE
if ($gitExit -ne 0) {
    if ($gitExit -eq 2) { Write-Warning "Not a git repo at $HubPath — continuing zip only." }
    elseif ($gitExit -eq 4) { throw "Git pull/repair failed (permissions? chown $DeployOwner on app, www-data on storage + bootstrap/cache)." }
    else { throw "Git check failed (exit $gitExit)." }
}

Initialize-DeployZipWorkspace -LocalTempDir $LocalTempDir -ZipFile $ZipFile
Copy-DeployZipArtifacts -RepoRoot $RepoRoot -LocalTempDir $LocalTempDir
Start-Sleep -Seconds 2

Test-DeployZipCriticalFiles -LocalTempDir $LocalTempDir -RelativePaths @(
    "Modules/Pricing/Http/Controllers/CompanyPricingController.php",
    "Modules/Pricing/module.json",
    "Modules/Pricing/Routes/web.php"
)

Build-DeployZipArchive -RepoRoot $RepoRoot -LocalTempDir $LocalTempDir -ZipFile $ZipFile
$ZipName = Split-Path $ZipFile -Leaf
scp $ZipFile "${HubHost}:${ZipName}"

$BackupDir = "~/hub_backups"
$BackupName = 'hub_backup_$(date +%Y%m%d_%H%M%S).tar.gz'
$RemoteCommand = "mkdir -p $BackupDir && cd /var/www && sudo tar -czf $BackupDir/$BackupName hub.craveva.com"
$RemoteCommand += " && sudo mv ~/${ZipName} $HubPath/${ZipName} && cd $HubPath"
$RemoteCommand += " && sudo rm -rf Modules/Pricing && sudo unzip -o ${ZipName} && sudo rm ${ZipName}"
$RemoteCommand += " && sudo chown -R ${DeployOwner}:www-data $HubPath"
$RemoteCommand += " && sudo find $HubPath -path $HubPath/storage -prune -o -path $HubPath/bootstrap/cache -prune -o -type d -exec chmod 755 {} \\;"
$RemoteCommand += " && sudo find $HubPath -path $HubPath/storage -prune -o -path $HubPath/bootstrap/cache -prune -o -type f -exec chmod 644 {} \\;"
$RemoteCommand += " && sudo mkdir -p $HubPath/storage $HubPath/bootstrap/cache"
$RemoteCommand += " && sudo chown -R www-data:www-data $HubPath/storage $HubPath/bootstrap/cache"
$RemoteCommand += " && sudo find $HubPath/storage $HubPath/bootstrap/cache -type d -exec chmod 2775 {} \\;"
$RemoteCommand += " && sudo find $HubPath/storage $HubPath/bootstrap/cache -type f -exec chmod 664 {} \\;"
$RemoteCommand += " && [ -f $HubPath/.env ] && sudo chmod 640 $HubPath/.env || true"
$RemoteCommand += " && sudo -u www-data php artisan migrate --force"
$RemoteCommand += " && sudo -u www-data php artisan migrate --path=database/migrations/2026_01_21_000000_add_storage_and_certification_to_products_table_fb.php --force"
$RemoteCommand += " && sudo -u www-data php artisan migrate --path=Modules/Purchase/Database/Migrations/2026_02_02_150000_setup_purchase_custom_fields_merged.php --force"
$RemoteCommand += " && sudo -u www-data php artisan migrate --path=Modules/Pricing/Database/Migrations/2026_02_02_160000_setup_pricing_module_permissions_and_activation.php --force"
$RemoteCommand += " && sudo -u www-data php artisan migrate --path=Modules/Pricing/Database/Migrations/2026_02_11_121332_add_start_and_end_date_to_client_product_pricing_table.php --force"
$RemoteCommand += " && sudo -u www-data php artisan migrate --path=database/migrations/2026_02_02_140000_setup_pricing_module_core_merged.php --force"
$RemoteCommand += " && sudo -u www-data php artisan module:enable Pricing"
$RemoteCommand += " && sudo -u www-data php artisan optimize:clear"
$RemoteCommand += " && sudo -u www-data php check_pricing_v2.php"

ssh "${HubHost}" $RemoteCommand
Remove-DeployZipLocalArtifacts -LocalTempDir $LocalTempDir -ZipFile $ZipFile
Write-Host "Done."
