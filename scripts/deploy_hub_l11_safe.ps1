param(
    [string]$HubHost = "craveva-hub-server",
    [string]$HubPath = "/var/www/hub.craveva.com",
    [string]$DeployBranch = "main",
    [string]$DomainUrl = "https://hub.craveva.com/",
    [string]$LocalBackupRoot = "backups",
    [switch]$RunDeploy,
    [switch]$AutoRollbackOnFailure
)

$ErrorActionPreference = "Stop"

function Write-Step {
    param([string]$Message)
    Write-Host ""
    Write-Host "================================================================"
    Write-Host $Message
    Write-Host "================================================================"
}

function Invoke-RemoteScript {
    param(
        [string]$HostName,
        [string]$ScriptBody,
        [string]$StepName
    )

    Write-Step $StepName
    $ScriptBody | ssh $HostName "bash -se"
    if ($LASTEXITCODE -ne 0) {
        throw "Remote step failed: $StepName"
    }
}

function Test-LocalCommand {
    param([string]$Name, [string]$Command)
    Write-Host "[CHECK] $Name"
    Invoke-Expression $Command | Out-Null
    if ($LASTEXITCODE -ne 0) {
        throw "Missing required local command: $Name"
    }
}

function Invoke-HealthCheck {
    param([string]$Url)

    Write-Step "Health check: $Url"
    try {
        $resp = Invoke-WebRequest -Uri $Url -Method Head -MaximumRedirection 5 -TimeoutSec 20 -UseBasicParsing
        $code = [int]$resp.StatusCode
        Write-Host "HTTP status: $code"
        if ($code -lt 200 -or $code -ge 400) {
            throw "Unhealthy HTTP status: $code"
        }
    }
    catch {
        throw "Health check failed for $Url. $($_.Exception.Message)"
    }
}

Write-Step "Safe Hub deploy (PHP 8.3 + Laravel 11)"
Write-Host "Run deploy mode: $($RunDeploy.IsPresent)"
Write-Host "Auto rollback on failure: $($AutoRollbackOnFailure.IsPresent)"

Test-LocalCommand -Name "git" -Command "git --version"
Test-LocalCommand -Name "ssh" -Command "ssh -V"
Test-LocalCommand -Name "scp" -Command "scp -V"

$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupDir = Join-Path $LocalBackupRoot "hub_preupgrade_$timestamp"
New-Item -ItemType Directory -Path $backupDir -Force | Out-Null
Write-Host "Local backup directory: $backupDir"

Write-Step "Backup local source"
$sha = (git rev-parse --short HEAD).Trim()
if (-not $sha) {
    throw "Cannot determine current git SHA."
}
git archive --format=zip --output="$backupDir/source_git_HEAD_$sha.zip" HEAD
if ($LASTEXITCODE -ne 0) { throw "git archive failed." }

if (Test-Path ".env") { Copy-Item ".env" "$backupDir/.env.local.snapshot" -Force }
if (Test-Path "composer.lock") { Copy-Item "composer.lock" "$backupDir/composer.lock.snapshot" -Force }

Write-Step "Remote preflight checks"
$preflightScript = @"
set -euo pipefail
cd "$HubPath"
echo "HOST: \$(hostname)"
echo "PWD: \$(pwd)"
echo "DATE: \$(date -u +"%Y-%m-%dT%H:%M:%SZ")"

command -v php >/dev/null
command -v composer >/dev/null
command -v git >/dev/null

php -v | sed -n '1,2p'
composer --version
php artisan --version
git rev-parse --short HEAD
git status --short

echo "Checking services..."
systemctl is-active nginx
systemctl is-active php8.3-fpm

echo "Disk usage:"
df -h "$HubPath"

echo "Migrate preflight (pretend)..."
php artisan migrate --pretend --force --no-interaction
"@
Invoke-RemoteScript -HostName $HubHost -ScriptBody $preflightScript -StepName "Remote preflight complete"

Write-Step "Backup DB on remote and download to local"
$remoteDbBackupScript = @"
set -euo pipefail
cd "$HubPath"
php artisan backup:run --only-db --disable-notifications --no-interaction
latest_zip=\$(ls -1t storage/backup/*.zip | sed -n '1p')
if [ -z "\$latest_zip" ]; then
  echo "DB_BACKUP_ZIP_NOT_FOUND"
  exit 1
fi
echo "DB_BACKUP_ZIP=\$latest_zip"
"@

$dbBackupOutput = $remoteDbBackupScript | ssh $HubHost "bash -se"
if ($LASTEXITCODE -ne 0) {
    throw "Remote DB backup step failed."
}

$dbBackupLine = $dbBackupOutput | Where-Object { $_ -like "DB_BACKUP_ZIP=*" } | Select-Object -First 1
if (-not $dbBackupLine) {
    throw "Cannot parse DB backup path from remote output."
}
$remoteDbZip = $dbBackupLine.Replace("DB_BACKUP_ZIP=", "").Trim()
$localDbZip = Join-Path $backupDir ("hub_db_backup_" + (Split-Path $remoteDbZip -Leaf))
scp "${HubHost}:$remoteDbZip" "$localDbZip"
if ($LASTEXITCODE -ne 0) { throw "Failed to download remote DB backup zip." }

Write-Step "Backup source on remote"
$remoteSourceBackupScript = @"
set -euo pipefail
backup_dir=~/hub_backups
mkdir -p "\$backup_dir"
ts=\$(date +%Y%m%d_%H%M%S)
backup_file="\$backup_dir/hub_source_predeploy_\$ts.tar.gz"
if sudo -n true >/dev/null 2>&1; then
  sudo tar -czf "\$backup_file" -C /var/www hub.craveva.com
else
  tar -czf "\$backup_file" -C /var/www hub.craveva.com
fi
echo "REMOTE_SOURCE_BACKUP=\$backup_file"
"@
$remoteSourceOutput = $remoteSourceBackupScript | ssh $HubHost "bash -se"
if ($LASTEXITCODE -ne 0) {
    throw "Remote source backup step failed."
}
Write-Host ($remoteSourceOutput -join "`n")

Write-Step "Write checksums"
Get-ChildItem $backupDir -File |
    Get-FileHash -Algorithm SHA256 |
    ForEach-Object { "$($_.Hash)  $($_.Path)" } |
    Set-Content (Join-Path $backupDir "SHA256SUMS.txt")

$beforeShaScript = @"
set -euo pipefail
cd "$HubPath"
echo "BEFORE_SHA=\$(git rev-parse --short HEAD)"
"@
$beforeShaOutput = $beforeShaScript | ssh $HubHost "bash -se"
if ($LASTEXITCODE -ne 0) { throw "Failed to capture pre-deploy SHA." }
$beforeShaLine = $beforeShaOutput | Where-Object { $_ -like "BEFORE_SHA=*" } | Select-Object -First 1
$beforeSha = $beforeShaLine.Replace("BEFORE_SHA=", "").Trim()

if (-not $RunDeploy.IsPresent) {
    Write-Step "Preflight finished (no deploy executed)"
    Write-Host "Deploy was not executed because -RunDeploy was not provided."
    Write-Host "Backups ready in: $backupDir"
    Write-Host "To deploy, run:"
    Write-Host "  .\scripts\deploy_hub_l11_safe.ps1 -RunDeploy -AutoRollbackOnFailure"
    exit 0
}

try {
    $deployScript = @"
set -euo pipefail
cd "$HubPath"

echo "Enable maintenance mode"
php artisan down --render="errors::503" --retry=60 || true

echo "Fetch and fast-forward deploy branch"
git fetch --all --prune
git checkout "$DeployBranch"
git pull --ff-only origin "$DeployBranch"

echo "Install dependencies (production)"
APP_ENV=production composer install --no-dev --optimize-autoloader --no-interaction

echo "Run migrations"
APP_ENV=production php artisan migrate --force --no-interaction

echo "Rebuild caches"
APP_ENV=production php artisan optimize:clear
APP_ENV=production php artisan config:cache
APP_ENV=production php artisan route:cache
APP_ENV=production php artisan view:cache

echo "Restart queue workers (if queue enabled)"
APP_ENV=production php artisan queue:restart || true

echo "Disable maintenance mode"
php artisan up
"@
    Invoke-RemoteScript -HostName $HubHost -ScriptBody $deployScript -StepName "Deploy execution"
}
catch {
    Write-Host "Deploy failed: $($_.Exception.Message)"
    if ($AutoRollbackOnFailure.IsPresent -and $beforeSha) {
        Write-Step "Auto rollback to $beforeSha"
        $rollbackScript = @"
set -euo pipefail
cd "$HubPath"
git fetch --all --prune
git checkout "$beforeSha"
APP_ENV=production composer install --no-dev --optimize-autoloader --no-interaction
APP_ENV=production php artisan optimize:clear
APP_ENV=production php artisan config:cache
APP_ENV=production php artisan route:cache
APP_ENV=production php artisan view:cache
php artisan up || true
"@
        Invoke-RemoteScript -HostName $HubHost -ScriptBody $rollbackScript -StepName "Rollback execution"
    }
    throw
}

Invoke-HealthCheck -Url $DomainUrl

Write-Step "Done"
Write-Host "Deploy completed successfully."
Write-Host "Backup directory: $backupDir"
Write-Host "Rollback reference SHA: $beforeSha"
