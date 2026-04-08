# Git-based deploy for Hub. SSH Host = craveva-hub-server in ~/.ssh/config.
# This script pulls code from Git on the server and runs post-deploy maintenance.
#
# PAT (nếu server dùng HTTPS origin): CRAVEVA_GITHUB_DEPLOY_TOKEN hoặc deploy-secrets.local.ps1 — xem upload_staging.ps1.

param(
    [switch]$GitPull = $true, # Mặc định là pull code
    [string]$Branch = "main",
    [string]$GitHubToken = ""
)

$ErrorActionPreference = "Stop"
$HubHost = "craveva-hub-server"
$HubPath = "/var/www/hub.craveva.com"

$secretsFile = Join-Path $PSScriptRoot "deploy-secrets.local.ps1"
if (Test-Path $secretsFile) {
    . $secretsFile
}

$resolvedToken = $GitHubToken
if (-not $resolvedToken) {
    $resolvedToken = $env:CRAVEVA_GITHUB_DEPLOY_TOKEN
}

Write-Host "Starting Git-based deploy on Hub..."

$RemoteCommand = "cd $HubPath"
if ($GitPull) {
    if ($resolvedToken) {
        $escaped = $resolvedToken.Replace("'", "'\''")
        $RemoteCommand += " && export GIT_TERMINAL_PROMPT=0"
        $RemoteCommand += " && export GITHUB_DEPLOY_TOKEN='$escaped'"
        $RemoteCommand += " && git -c http.extraHeader=`"AUTHORIZATION: bearer `$GITHUB_DEPLOY_TOKEN`" fetch origin"
        $RemoteCommand += " && git status --porcelain | grep -q . && git stash push -u -m 'auto-stash-before-deploy' || true"
        $RemoteCommand += " && git checkout $Branch"
        $RemoteCommand += " && git -c http.extraHeader=`"AUTHORIZATION: bearer `$GITHUB_DEPLOY_TOKEN`" pull origin $Branch"
    } else {
        $RemoteCommand += " && git fetch origin && git status --porcelain | grep -q . && git stash push -u -m 'auto-stash-before-deploy' || true && git checkout $Branch && git pull origin $Branch"
    }
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
