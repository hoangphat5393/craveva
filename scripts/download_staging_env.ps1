# Download staging .env for local reference (APP_KEY sync after DB import).
#
# Prerequisites: SSH host `craveva-staging` (see scripts/ssh_staging.ps1)
#
# Usage (repo root):
#   .\scripts\download_staging_env.ps1
#   .\scripts\download_staging_env.ps1 -SyncAppKey   # also copy APP_KEY into local .env
#
# Output (gitignored):
#   backup/.env.staging.reference
#
# After DB import from staging, run with -SyncAppKey then:
#   php artisan config:clear
#   php artisan cache:clear-decrypt-related

param(
    [string]$StagingHost = "craveva-staging",
    [string]$StagingPath = "/var/www/craveva-staging/current/craveva",
    [switch]$SyncAppKey
)

$ErrorActionPreference = "Stop"

$RepoRoot = Split-Path -Parent $PSScriptRoot
Set-Location $RepoRoot

$LocalBackupDir = Join-Path $RepoRoot "backup"
$OutFile = Join-Path $LocalBackupDir ".env.staging.reference"
$RemoteEnv = "$StagingPath/.env"

if (-not (Test-Path $LocalBackupDir)) {
    New-Item -ItemType Directory -Path $LocalBackupDir -Force | Out-Null
}

Write-Host "Downloading $RemoteEnv from $StagingHost ..."
scp -o ConnectTimeout=15 "${StagingHost}:${RemoteEnv}" $OutFile
Write-Host "Saved: $OutFile"

if ($SyncAppKey) {
    $localEnvPath = Join-Path $RepoRoot ".env"
    if (-not (Test-Path $localEnvPath)) {
        Write-Error "Missing $localEnvPath"
    }

    $stagingKey = (Select-String -Path $OutFile -Pattern '^APP_KEY=(.+)$').Matches[0].Groups[1].Value.Trim()
    if ([string]::IsNullOrWhiteSpace($stagingKey)) {
        Write-Error "APP_KEY not found in $OutFile"
    }

    $content = Get-Content -Path $localEnvPath -Raw
    if ($content -match '(?m)^APP_KEY=.*$') {
        $content = $content -replace '(?m)^APP_KEY=.*$', "APP_KEY=$stagingKey"
    } else {
        $content = "APP_KEY=$stagingKey`r`n" + $content
    }
    Set-Content -Path $localEnvPath -Value $content.TrimEnd() -NoNewline
    Add-Content -Path $localEnvPath -Value ""
    Write-Host "Updated APP_KEY in .env (local DB/APP_URL unchanged)."

    Write-Host "Clearing config/cache ..."
    php artisan config:clear
    php artisan cache:clear-decrypt-related
}

Write-Host "Done. Do not commit backup/.env.staging.reference (secrets)."
