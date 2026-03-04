# PowerShell script to migrate DB from Hub to Staging
param(
    [switch] $KeepRemoteDump,
    [switch] $NoMaintenanceMode
)

$ErrorActionPreference = "Stop"

$HubHost = "craveva-hub-server"
$HubPath = "/var/www/hub.craveva.com"
$StagingHost = "craveva-staging"
$StagingPath = "/var/www/craveva-staging/current/craveva"

$Stamp = Get-Date -Format "yyyyMMdd_HHmmss"
$DumpFileBase = "db_migration_dump_$Stamp"
$RemoteDumpGz = "$DumpFileBase.sql.gz"
$RemoteDumpSha = "$DumpFileBase.sql.gz.sha256"
$LocalDumpPath = ".\$RemoteDumpGz"
$LocalShaPath = ".\$RemoteDumpSha"

Write-Host "----------------------------------------------------------------"
Write-Host "STARTING DATABASE MIGRATION: HUB -> STAGING"
Write-Host "WARNING: This will OVERWRITE the Staging database!"
Write-Host "----------------------------------------------------------------"

# 1. Dump Hub DB
Write-Host "1. Dumping Database from Hub Server ($HubHost)..."

$HubCmd = "cd $HubPath && " +
    "DB_HOST=`$(grep -E '^DB_HOST=' .env | head -n 1 | cut -d '=' -f2- | tr -d '`"' | tr -d '\r') && " +
    "DB_PORT=`$(grep -E '^DB_PORT=' .env | head -n 1 | cut -d '=' -f2- | tr -d '`"' | tr -d '\r') && " +
    "DB_DATABASE=`$(grep -E '^DB_DATABASE=' .env | head -n 1 | cut -d '=' -f2- | tr -d '`"' | tr -d '\r') && " +
    "DB_USERNAME=`$(grep -E '^DB_USERNAME=' .env | head -n 1 | cut -d '=' -f2- | tr -d '`"' | tr -d '\r') && " +
    "DB_PASSWORD=`$(grep -E '^DB_PASSWORD=' .env | head -n 1 | cut -d '=' -f2- | tr -d '`"' | tr -d '\r') && " +
    "echo 'Dumping DB: '`$DB_DATABASE && " +
    "MYSQL_PWD=`"$DB_PASSWORD`" mysqldump -h `"$DB_HOST`" -P `"$DB_PORT`" -u `"$DB_USERNAME`" `"$DB_DATABASE`" --no-tablespaces --single-transaction --quick --routines --triggers --events | gzip -1 > ~/$RemoteDumpGz && " +
    "sha256sum ~/$RemoteDumpGz > ~/$RemoteDumpSha"

# Run ssh directly
ssh $HubHost $HubCmd

# Check if file exists remotely
ssh $HubHost "ls -l ~/$RemoteDumpGz ~/$RemoteDumpSha"

# 2. Download Dump
Write-Host "2. Downloading Dump to Local..."
scp "${HubHost}:~/$RemoteDumpGz" $LocalDumpPath
scp "${HubHost}:~/$RemoteDumpSha" $LocalShaPath

if (-not (Test-Path $LocalDumpPath)) {
    Write-Error "Failed to download dump file. Aborting."
}
if (-not (Test-Path $LocalShaPath)) {
    Write-Error "Failed to download sha256 file. Aborting."
}

# Check file size locally
$FileSize = (Get-Item $LocalDumpPath).Length
Write-Host "Dump file size: $FileSize bytes"
if ($FileSize -lt 1000) {
    Write-Warning "Dump file is suspiciously small. Please check contents."
}

# Verify hash locally
$ExpectedHash = (Get-Content $LocalShaPath -Raw).Split(" ", [System.StringSplitOptions]::RemoveEmptyEntries)[0].Trim()
$ActualHash = (Get-FileHash -Algorithm SHA256 -Path $LocalDumpPath).Hash.ToLowerInvariant()
if ($ActualHash -ne $ExpectedHash.ToLowerInvariant()) {
    Write-Error "SHA256 mismatch. Expected=$ExpectedHash Actual=$ActualHash"
}
Write-Host "[OK] SHA256 verified: $ActualHash"

# 3. Upload to Staging
Write-Host "3. Uploading Dump to Staging Server ($StagingHost)..."
scp $LocalDumpPath "${StagingHost}:~/$RemoteDumpGz"
scp $LocalShaPath "${StagingHost}:~/$RemoteDumpSha"

# 4. Import to Staging
Write-Host "4. Backing up and Importing Database to Staging..."
$MaintenanceCmd = ""
if (-not $NoMaintenanceMode) {
    $MaintenanceCmd = "sudo -u www-data php artisan down || true; "
}

$StagingCmd = "cd $StagingPath && " +
    "DB_HOST=`$(grep -E '^DB_HOST=' .env | head -n 1 | cut -d '=' -f2- | tr -d '`"' | tr -d '\r') && " +
    "DB_PORT=`$(grep -E '^DB_PORT=' .env | head -n 1 | cut -d '=' -f2- | tr -d '`"' | tr -d '\r') && " +
    "DB_DATABASE=`$(grep -E '^DB_DATABASE=' .env | head -n 1 | cut -d '=' -f2- | tr -d '`"' | tr -d '\r') && " +
    "DB_USERNAME=`$(grep -E '^DB_USERNAME=' .env | head -n 1 | cut -d '=' -f2- | tr -d '`"' | tr -d '\r') && " +
    "DB_PASSWORD=`$(grep -E '^DB_PASSWORD=' .env | head -n 1 | cut -d '=' -f2- | tr -d '`"' | tr -d '\r') && " +
    $MaintenanceCmd +
    "BACKUP=staging_backup_before_hub_import_$Stamp.sql.gz && " +
    "echo 'Backing up current staging DB to ~/'`$BACKUP && " +
    "MYSQL_PWD=`"$DB_PASSWORD`" mysqldump -h `"$DB_HOST`" -P `"$DB_PORT`" -u `"$DB_USERNAME`" `"$DB_DATABASE`" --no-tablespaces --single-transaction --quick --routines --triggers --events | gzip -1 > ~/" + '$BACKUP' + " && " +
    "sha256sum ~/$RemoteDumpGz | grep -q `$(cut -d ' ' -f1 ~/$RemoteDumpSha) && " +
    "echo 'Importing into DB: '`$DB_DATABASE && " +
    "gunzip -c ~/$RemoteDumpGz | mysql -h `"$DB_HOST`" -P `"$DB_PORT`" -u `"$DB_USERNAME`" `"$DB_DATABASE`""

ssh $StagingHost $StagingCmd

# 5. Run Migrations & Clear Cache
Write-Host "5. Running Migrations & Clearing Cache on Staging..."
$UpCmd = ""
if (-not $NoMaintenanceMode) {
    $UpCmd = " && sudo -u www-data php artisan up"
}

$PostCmd = "cd $StagingPath && " +
    "sudo -u www-data php artisan migrate --force && " +
    "sudo -u www-data php artisan optimize:clear" +
    $UpCmd

ssh $StagingHost $PostCmd

# 6. Cleanup
Write-Host "6. Cleaning up..."
if (-not $KeepRemoteDump) {
    ssh $HubHost "rm -f ~/$RemoteDumpGz ~/$RemoteDumpSha"
    ssh $StagingHost "rm -f ~/$RemoteDumpGz ~/$RemoteDumpSha"
}

Write-Host "----------------------------------------------------------------"
Write-Host "MIGRATION COMPLETE!"
Write-Host "----------------------------------------------------------------"
