# Git-based deploy for Hub. SSH Host = craveva-hub-server in ~/.ssh/config.
# Luồng mặc định: trên máy local -> git add -A -> commit (nếu có thay đổi) -> push origin/<Branch>,
# sau đó SSH vào server -> pull + migrate/optimize. Dùng -SkipLocalGit để chỉ deploy pull trên server.
#
# PAT (nếu server dùng HTTPS origin): CRAVEVA_GITHUB_DEPLOY_TOKEN hoặc deploy-secrets.local.ps1 — xem upload_staging.ps1.

param(
    [switch]$GitPull = $true, # Mặc định là pull code
    [string]$Branch = "main",
    [string]$GitHubToken = "",
    [string]$CommitMessage = "", # Nếu rỗng và có commit: dùng message mặc định có timestamp
    [switch]$SkipLocalGit = $false # Bật: bỏ qua add/commit/push local, chỉ SSH pull như cũ
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

if (-not $SkipLocalGit) {
    $repoRoot = Split-Path -Parent $PSScriptRoot
    Push-Location $repoRoot
    try {
        git rev-parse --is-inside-work-tree *> $null
        if ($LASTEXITCODE -ne 0) {
            throw "Not a git repository: $repoRoot"
        }
        Write-Host "Local repo: $repoRoot"
        git fetch origin $Branch
        git checkout $Branch
        Write-Host "git add -A"
        git add -A
        $porcelain = git status --porcelain
        if ($porcelain) {
            $msg = $CommitMessage
            if (-not $msg) {
                $msg = "deploy(hub): $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
            }
            Write-Host "git commit -m ..."
            git commit -m $msg
        } else {
            Write-Host "No local changes to commit (clean after git add -A)."
        }
        Write-Host "git push origin $Branch"
        git push origin $Branch
    } finally {
        Pop-Location
    }
}

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
# Code: owner deploy + group www-data. storage/bootstrap: www-data (cache).
# Language Pack Publish (UI): FPM = www-data phải ghi resources/lang, lang/, Modules/*/Resources/lang — cần ug+rwX + setgid thư mục (SERVER_RUNBOOK_VI §4.8).
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

ssh $HubHost $RemoteCommand
Write-Host "Deploy Hub Done."
