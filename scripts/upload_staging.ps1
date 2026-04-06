# Git-based deploy for Staging. SSH Host must exist in ~/.ssh/config.
# This script pulls code from Git on the server and runs post-deploy maintenance.

param(
    [switch]$GitPull = $true, # Mặc định là pull code
    [string]$Branch = "main"
)

$ErrorActionPreference = "Stop"
$StagingHost = "craveva-staging"
$StagingPath = "/var/www/craveva-staging/current/craveva"

Write-Host "Starting Git-based deploy on Staging..."

$RemoteCommand = "cd $StagingPath"
if ($GitPull) {
    $RemoteCommand += " && git fetch origin && git checkout $Branch && git pull origin $Branch"
}

# Lệnh bảo trì (Permissions, Migration, Optimize)
$RemoteCommand += " && sudo chown -R hoangphat5393:www-data ."
$RemoteCommand += " && sudo chown -R www-data:www-data storage bootstrap/cache"
$RemoteCommand += " && sudo chmod -R 775 storage bootstrap/cache"
$RemoteCommand += " && sudo mkdir -p storage/logs && sudo chmod 2777 storage/logs"
$RemoteCommand += " && sudo -u www-data php artisan migrate --force"
$RemoteCommand += " && sudo -u www-data php artisan languagepack:publish-translation"
$RemoteCommand += " && sudo -u www-data php artisan optimize:clear"

ssh $StagingHost $RemoteCommand
Write-Host "Deploy Staging Done."
