# PowerShell script to migrate DB from Hub to Staging
$ErrorActionPreference = "Stop"

$HubHost = "craveva-hub-server"
$HubPath = "/var/www/hub.craveva.com"
$StagingHost = "craveva-staging"
$StagingPath = "/var/www/craveva-staging/current/craveva"
$DumpFile = "db_migration_dump.sql"
$LocalDumpPath = ".\$DumpFile"

Write-Host "----------------------------------------------------------------"
Write-Host "STARTING DATABASE MIGRATION: HUB -> STAGING"
Write-Host "WARNING: This will OVERWRITE the Staging database!"
Write-Host "----------------------------------------------------------------"

# 1. Dump Hub DB
Write-Host "1. Dumping Database from Hub Server ($HubHost)..."

# Construct command to get variables and dump
# Note: We escape $ as `$ for PowerShell to treat it as literal char in the string
# REMOVED --column-statistics=0 as it caused error on Hub server
$HubCmd = "cd $HubPath && " +
    "export DB_HOST=`$(grep ^DB_HOST= .env | cut -d '=' -f2- | tr -d '`"' | tr -d '\r') && " +
    "export DB_PORT=`$(grep ^DB_PORT= .env | cut -d '=' -f2- | tr -d '`"' | tr -d '\r') && " +
    "export DB_DATABASE=`$(grep ^DB_DATABASE= .env | cut -d '=' -f2- | tr -d '`"' | tr -d '\r') && " +
    "export DB_USERNAME=`$(grep ^DB_USERNAME= .env | cut -d '=' -f2- | tr -d '`"' | tr -d '\r') && " +
    "export DB_PASSWORD=`$(grep ^DB_PASSWORD= .env | cut -d '=' -f2- | tr -d '`"' | tr -d '\r') && " +
    "echo 'Dumping DB: '`$DB_DATABASE && " +
    "mysqldump -h `$DB_HOST -P `$DB_PORT -u `$DB_USERNAME -p`$DB_PASSWORD `$DB_DATABASE --no-tablespaces > ~/db_migration_dump.sql"

# Run ssh directly
ssh $HubHost $HubCmd

# Check if file exists remotely
ssh $HubHost "ls -l ~/db_migration_dump.sql"

# 2. Download Dump
Write-Host "2. Downloading Dump to Local..."
scp "${HubHost}:~/db_migration_dump.sql" $LocalDumpPath

if (-not (Test-Path $LocalDumpPath)) {
    Write-Error "Failed to download dump file. Aborting."
}

# Check file size locally
$FileSize = (Get-Item $LocalDumpPath).Length
Write-Host "Dump file size: $FileSize bytes"
if ($FileSize -lt 1000) {
    Write-Warning "Dump file is suspiciously small. Please check contents."
}

# 3. Upload to Staging
Write-Host "3. Uploading Dump to Staging Server ($StagingHost)..."
scp $LocalDumpPath "${StagingHost}:~/db_migration_dump.sql"

# 4. Import to Staging
Write-Host "4. Importing Database to Staging..."
$StagingCmd = "cd $StagingPath && " +
    "export DB_HOST=`$(grep ^DB_HOST= .env | cut -d '=' -f2- | tr -d '`"' | tr -d '\r') && " +
    "export DB_PORT=`$(grep ^DB_PORT= .env | cut -d '=' -f2- | tr -d '`"' | tr -d '\r') && " +
    "export DB_DATABASE=`$(grep ^DB_DATABASE= .env | cut -d '=' -f2- | tr -d '`"' | tr -d '\r') && " +
    "export DB_USERNAME=`$(grep ^DB_USERNAME= .env | cut -d '=' -f2- | tr -d '`"' | tr -d '\r') && " +
    "export DB_PASSWORD=`$(grep ^DB_PASSWORD= .env | cut -d '=' -f2- | tr -d '`"' | tr -d '\r') && " +
    "echo 'Importing into DB: '`$DB_DATABASE && " +
    "mysql -h `$DB_HOST -P `$DB_PORT -u `$DB_USERNAME -p`$DB_PASSWORD `$DB_DATABASE < ~/db_migration_dump.sql"

ssh $StagingHost $StagingCmd

# 5. Run Migrations & Clear Cache
Write-Host "5. Running Migrations & Clearing Cache on Staging..."
$PostCmd = "cd $StagingPath && " +
    "sudo -u www-data php artisan migrate --force && " +
    "sudo -u www-data php artisan optimize:clear"

ssh $StagingHost $PostCmd

# 6. Cleanup
Write-Host "6. Cleaning up..."
ssh $HubHost "rm ~/db_migration_dump.sql"
ssh $StagingHost "rm ~/db_migration_dump.sql"
Remove-Item $LocalDumpPath

Write-Host "----------------------------------------------------------------"
Write-Host "MIGRATION COMPLETE!"
Write-Host "----------------------------------------------------------------"
