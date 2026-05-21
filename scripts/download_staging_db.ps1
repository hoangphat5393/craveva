# Full staging database backup: dump on server -> backup/ on server -> download to local backup/
#
# Prerequisites:
#   - SSH:  ssh craveva-staging   (see scripts/ssh_staging.ps1 / gcloud)
#   - Remote: mysqldump, gzip on staging VM
#   - Local:  scp (OpenSSH)
#
# Usage (repo root):
#   .\scripts\download_staging_db.ps1
#   .\scripts\download_staging_db.ps1 -KeepRemoteBackup
#   .\scripts\download_staging_db.ps1 -StagingHost craveva-staging
#
# Does NOT run php artisan migrate. To import locally (optional):
#   gunzip -c backup\craveva_staging_YYYYMMDD_HHMMSS.sql.gz | mysql -u root -p craveva_staging
#   (or use 7-Zip to decompress .sql.gz on Windows, then import via client)

param(
    [string]$StagingHost = "craveva-staging",
    [string]$StagingPath = "/var/www/craveva-staging/current/craveva",
    [switch]$KeepRemoteBackup,
    [switch]$SkipDownload
)

$ErrorActionPreference = "Stop"

function Escape-BashSingleQuoted {
    param([string]$Value)
    if ($null -eq $Value) { return "''" }
    return "'" + ($Value.Replace("'", "'""'""'")) + "'"
}

$RepoRoot = Split-Path -Parent $PSScriptRoot
Set-Location $RepoRoot

$LocalBackupDir = Join-Path $RepoRoot "backup"
$RemoteDumpScript = Join-Path $PSScriptRoot "backup\staging_dump_db.sh"
$RemoteTmpScript = "/tmp/craveva_staging_dump_db.sh"

if (-not (Test-Path $RemoteDumpScript)) {
    Write-Error "Missing $RemoteDumpScript"
}

New-Item -ItemType Directory -Force -Path $LocalBackupDir | Out-Null

Write-Host "==> Upload dump script to staging..."
# LF-only for bash (avoid CRLF from Windows editor)
$bashContent = [System.IO.File]::ReadAllText($RemoteDumpScript) -replace "`r`n", "`n" -replace "`r", "`n"
$utf8NoBom = New-Object System.Text.UTF8Encoding $false
[System.IO.File]::WriteAllText((Join-Path $env:TEMP "craveva_staging_dump_db.sh"), $bashContent, $utf8NoBom)
scp (Join-Path $env:TEMP "craveva_staging_dump_db.sh") "${StagingHost}:${RemoteTmpScript}"

Write-Host "==> Run mysqldump on staging (may take several minutes)..."
$remoteOut = ssh $StagingHost "bash $RemoteTmpScript $(Escape-BashSingleQuoted $StagingPath)"
Write-Host $remoteOut

$dumpPath = ($remoteOut | Select-String "DUMP_PATH=(.+)" | ForEach-Object { $_.Matches.Groups[1].Value.Trim() })
if (-not $dumpPath) {
    Write-Error "Dump failed: no DUMP_PATH in remote output."
}

$dumpBytes = ($remoteOut | Select-String "DUMP_BYTES=(\d+)" | ForEach-Object { [long]$_.Matches.Groups[1].Value })
if ($dumpBytes) {
    $mb = [math]::Round($dumpBytes / 1MB, 2)
    Write-Host "Remote backup size: $mb MB ($dumpBytes bytes)"
}

if ($SkipDownload) {
    Write-Host "SkipDownload: file remains on server at $dumpPath"
    exit 0
}

$fileName = Split-Path $dumpPath -Leaf
$localFile = Join-Path $LocalBackupDir $fileName

Write-Host "==> Download to $localFile ..."
scp "${StagingHost}:${dumpPath}" $localFile

if (-not $KeepRemoteBackup) {
    Write-Host "==> Remove remote dump (use -KeepRemoteBackup to keep on server)..."
    ssh $StagingHost "rm -f $(Escape-BashSingleQuoted $dumpPath)"
}

Write-Host ""
Write-Host "Done."
Write-Host "  Server backup dir: $StagingPath/backup/"
Write-Host "  Local file:        $localFile"
Write-Host ""
Write-Host "Import to local MySQL only if you intend to replace local DB (confirm migrate separately)."
