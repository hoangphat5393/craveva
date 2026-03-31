param(
    [Parameter(Mandatory = $false)]
    [string]$SshHost = "craveva-staging",

    [Parameter(Mandatory = $false)]
    [int]$CompanyId = 10,

    [Parameter(Mandatory = $false)]
    [int]$Sample = 50,

    [Parameter(Mandatory = $false)]
    [string]$AppDir = "/var/www/craveva-staging/current/craveva",

    [Parameter(Mandatory = $false)]
    [int]$MinFreeMb = 2048,

    [Parameter(Mandatory = $false)]
    [switch]$NoBackup
)

$ErrorActionPreference = "Stop"

Write-Host "== Phase 3 safe execute on staging ==" -ForegroundColor Cyan
Write-Host "Host      : $SshHost"
Write-Host "CompanyId : $CompanyId"
Write-Host "Sample    : $Sample"
Write-Host "AppDir    : $AppDir"
Write-Host "MinFreeMb : $MinFreeMb"
Write-Host "NoBackup  : $($NoBackup.IsPresent)"

$noBackupValue = if ($NoBackup.IsPresent) { "1" } else { "0" }

# Normalize shell scripts on remote to avoid CRLF issues before execution.
$remote = @"
set -euo pipefail
cd "$AppDir"
sed -i 's/\r$//' scripts/staging_sales_do_rehearsal_gate.sh scripts/staging_phase3_safe_execute.sh || true
chmod +x scripts/staging_sales_do_rehearsal_gate.sh scripts/staging_phase3_safe_execute.sh || true
MIN_FREE_MB="$MinFreeMb" NO_BACKUP="$noBackupValue" bash scripts/staging_phase3_safe_execute.sh "$CompanyId" "$Sample"
"@

$remoteB64 = [Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($remote))
ssh $SshHost "echo '$remoteB64' | base64 -d | bash -s"
if ($LASTEXITCODE -ne 0) {
    throw "Remote execution failed with exit code $LASTEXITCODE"
}

Write-Host "Done. Phase 3 safe execution completed." -ForegroundColor Green

