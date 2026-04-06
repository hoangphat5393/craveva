# Zip deploy → staging (fallback; prefer git pull on server — see docs/SERVER_RUNBOOK_VI.md, docs/STAGING_OPERATIONS.md).
# SSH Host must exist in ~/.ssh/config. Does not touch remote .env.

$ErrorActionPreference = "Stop"
$Here = $PSScriptRoot
. (Join-Path $Here "deploy-zip.common.ps1")

$StagingHost = "craveva-staging"
$StagingPath = "/var/www/craveva-staging/current/craveva"
$RepoRoot = Split-Path $Here -Parent
$LocalTempDir = Join-Path $RepoRoot "temp_deploy"
$ZipFile = Join-Path $RepoRoot "deploy_staging.zip"

# $true = wipe app root before unzip (dangerous). $false = overlay only.
$RemoteWipeAppBeforeUnzip = $false

Initialize-DeployZipWorkspace -LocalTempDir $LocalTempDir -ZipFile $ZipFile
Copy-DeployZipArtifacts -RepoRoot $RepoRoot -LocalTempDir $LocalTempDir
Start-Sleep -Seconds 2

Test-DeployZipCriticalFiles -LocalTempDir $LocalTempDir -RelativePaths @(
    "app/Console/Kernel.php",
    "Modules/Pricing/Http/Controllers/CompanyPricingController.php",
    "Modules/Pricing/module.json",
    "Modules/Pricing/Routes/web.php"
)

Build-DeployZipArchive -RepoRoot $RepoRoot -LocalTempDir $LocalTempDir -ZipFile $ZipFile
$ZipName = Split-Path $ZipFile -Leaf
Write-Host "Uploading..."
scp $ZipFile "${StagingHost}:${ZipName}"

$RemoteCommand = "sudo mv ~/deploy_staging.zip /tmp/deploy_staging.zip && cd $StagingPath"
if ($RemoteWipeAppBeforeUnzip) {
    $RemoteCommand += " && sudo find . -maxdepth 1 ! -name '.' ! -name '..' ! -name '.env' ! -name 'storage' ! -name '.git' -exec rm -rf {} +"
}
else {
    $RemoteCommand += " && echo 'Overlay unzip (no wipe)'"
}
$RemoteCommand += " && sudo mv /tmp/${ZipName} $StagingPath/${ZipName}"
$RemoteCommand += " && sudo unzip -o ${ZipName} && sudo rm ${ZipName}"
$RemoteCommand += " && sudo chown -R www-data:www-data $StagingPath/Modules $StagingPath/resources $StagingPath/storage $StagingPath/bootstrap/cache $StagingPath/public"
$RemoteCommand += " && sudo chmod -R 775 $StagingPath/storage $StagingPath/bootstrap/cache"
$RemoteCommand += " && sudo mkdir -p $StagingPath/storage/logs && sudo chown -R www-data:www-data $StagingPath/storage/logs && sudo chmod 2777 $StagingPath/storage/logs"
$RemoteCommand += " && sudo find $StagingPath/storage/logs -maxdepth 1 -type f -name '*.log' -exec chmod 666 {} + 2>/dev/null || true"
$RemoteCommand += " && DEPLOY_USER=`$(whoami); sudo setfacl -R -m u:www-data:rwX,u:`$DEPLOY_USER:rwX $StagingPath/storage $StagingPath/bootstrap/cache $StagingPath/storage/logs 2>/dev/null || true"
$RemoteCommand += " && DEPLOY_USER=`$(whoami); sudo setfacl -dR -m u:www-data:rwX,u:`$DEPLOY_USER:rwX $StagingPath/storage $StagingPath/bootstrap/cache $StagingPath/storage/logs 2>/dev/null || true"
$RemoteCommand += " && sudo chmod -R 755 $StagingPath/public && sudo chown -R www-data:www-data $StagingPath/public/vendor && sudo chmod -R 755 $StagingPath/public/vendor"
$RemoteCommand += " && sudo -u www-data php artisan migrate --force"
$RemoteCommand += " && sudo -u www-data php artisan migrate --path=database/migrations/2026_01_21_000000_add_storage_and_certification_to_products_table_fb.php --force"

ssh $StagingHost $RemoteCommand
Remove-DeployZipLocalArtifacts -LocalTempDir $LocalTempDir -ZipFile $ZipFile
Write-Host "Done."
