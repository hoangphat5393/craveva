# Git-based deploy for Hub. SSH Host = craveva-hub-server in ~/.ssh/config.
# This script pulls code from Git on the server and runs post-deploy maintenance.

param(
    [switch]$GitPull = $true, # Mặc định là pull code
    [string]$Branch = "main"
)

$ErrorActionPreference = "Stop"
$HubHost = "craveva-hub-server"
$HubPath = "/var/www/hub.craveva.com"

Write-Host "Starting Git-based deploy on Hub..."

$RemoteCommand = "cd $HubPath"
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

ssh $HubHost $RemoteCommand
Write-Host "Deploy Hub Done."
