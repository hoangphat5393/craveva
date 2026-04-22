# Git-based deploy for Staging. SSH Host must exist in ~/.ssh/config.
# This script pulls code from Git on the server and runs post-deploy maintenance.
#
# Nếu trên server `git pull` báo "could not read Password" (HTTPS + không có TTY):
# - Khuyến nghị lâu dài: đổi origin sang SSH + deploy key trên server.
# - Tạm thời: đặt PAT trong biến môi trường CRAVEVA_GITHUB_DEPLOY_TOKEN hoặc file
#   scripts/deploy-secrets.local.ps1 (copy từ deploy-secrets.local.ps1.example). Không commit token.

param(
    [switch]$GitPull = $true, # Mặc định là pull code
    [string]$Branch = "main",
    [string]$GitHubToken = "" # Tùy chọn; nếu rỗng dùng env CRAVEVA_GITHUB_DEPLOY_TOKEN
)

$ErrorActionPreference = "Stop"
$StagingHost = "craveva-staging"
$StagingPath = "/var/www/craveva-staging/current/craveva"

$secretsFile = Join-Path $PSScriptRoot "deploy-secrets.local.ps1"
if (Test-Path $secretsFile) {
    . $secretsFile
}

$resolvedToken = $GitHubToken
if (-not $resolvedToken) {
    $resolvedToken = $env:CRAVEVA_GITHUB_DEPLOY_TOKEN
}

Write-Host "Starting Git-based deploy on Staging..."

$RemoteCommand = "cd $StagingPath"
if ($GitPull) {
    $stashBeforePull = " && git status --porcelain | grep -q . && git stash push -u -m 'auto-stash-before-deploy' || true"
    if ($resolvedToken) {
        $escaped = $resolvedToken.Replace("'", "'\''")
        $RemoteCommand += " && export GIT_TERMINAL_PROMPT=0"
        $RemoteCommand += " && export GITHUB_DEPLOY_TOKEN='$escaped'"
        $RemoteCommand += " && git -c http.extraHeader=`"AUTHORIZATION: bearer `$GITHUB_DEPLOY_TOKEN`" fetch origin"
        $RemoteCommand += $stashBeforePull
        $RemoteCommand += " && git checkout $Branch"
        $RemoteCommand += " && git -c http.extraHeader=`"AUTHORIZATION: bearer `$GITHUB_DEPLOY_TOKEN`" pull origin $Branch"
    } else {
        $RemoteCommand += " && git fetch origin"
        $RemoteCommand += $stashBeforePull
        $RemoteCommand += " && git checkout $Branch && git pull origin $Branch"
    }
}

# Lệnh bảo trì (Permissions, Migration, Optimize)
# Publish ngôn ngữ từ UI cần www-data ghi được resources/lang — xem SERVER_RUNBOOK_VI §4.8.
$RemoteCommand += " && sudo chown -R hoangphat5393:www-data ."
$RemoteCommand += " && sudo mkdir -p lang resources/lang storage/logs"
$RemoteCommand += " && sudo mkdir -p public/user-uploads public/user-uploads/temp public/user-uploads/front/client"
$RemoteCommand += " && sudo chown -R www-data:www-data storage bootstrap/cache"
$RemoteCommand += " && sudo chown -R www-data:www-data public/user-uploads"
$RemoteCommand += " && sudo chmod -R 775 storage bootstrap/cache"
$RemoteCommand += " && sudo chmod -R 775 public/user-uploads"
$RemoteCommand += " && sudo chmod 2777 storage/logs"
$RemoteCommand += " && sudo chmod -R ug+rwX Modules/LanguagePack/Languages resources/lang lang"
$RemoteCommand += " && sudo find Modules/LanguagePack/Languages resources/lang lang -type d -exec chmod g+s {} \; 2>/dev/null || true"
$RemoteCommand += ' && for d in Modules/*/Resources/lang; do [ -d "$d" ] && sudo chmod -R ug+rwX "$d" && sudo find "$d" -type d -exec chmod g+s {} \;; done'
$RemoteCommand += " && sudo -u www-data php artisan migrate --force"
$RemoteCommand += " && sudo -u www-data php artisan languagepack:publish-translation"
$RemoteCommand += " && sudo -u www-data php artisan optimize:clear"

ssh $StagingHost $RemoteCommand
Write-Host "Deploy Staging Done."
