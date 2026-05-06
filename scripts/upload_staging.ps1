# Git-based deploy for Staging. SSH Host must exist in ~/.ssh/config.
# Luồng mặc định: trên máy local -> git add -A -> commit (nếu có thay đổi) -> push origin/<Branch>,
# sau đó SSH vào server -> pull + migrate/optimize. Dùng -SkipLocalGit để chỉ deploy pull trên server.
#
# Nếu trên server `git pull` báo "could not read Password" (HTTPS + không có TTY):
# - Khuyến nghị lâu dài: đổi origin sang SSH + deploy key trên server.
# - Tạm thời: đặt PAT trong biến môi trường CRAVEVA_GITHUB_DEPLOY_TOKEN hoặc file
#   scripts/deploy-secrets.local.ps1 (copy từ deploy-secrets.local.ps1.example). Không commit token.

param(
    [bool]$GitPull = $true, # Mặc định là pull code
    [string]$Branch = "main",
    [string]$GitHubToken = "", # Tùy chọn; nếu rỗng dùng env CRAVEVA_GITHUB_DEPLOY_TOKEN
    [string]$CommitMessage = "", # Nếu rỗng và có commit: dùng message mặc định có timestamp
    [switch]$SkipLocalGit = $false # Bật: bỏ qua add/commit/push local, chỉ SSH pull như cũ
)

$ErrorActionPreference = "Stop"
$StagingHost = "craveva-staging"
$StagingPath = "/var/www/craveva-staging/current/craveva"
# Unix user that owns repo + .git on server (cron/deploy). SSH may be Admin; git must still run as this user.
$RemoteGitUser = "hoangphat5393"

function Escape-BashSingleQuotedString {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Value
    )

    # Bash-safe: escape a string intended to be wrapped in single-quotes.
    # Example: abc'def -> 'abc'"'"'def'
    $replacement = "'" + '"' + "'" + '"' + "'"

    return $Value.Replace("'", $replacement)
}

$secretsFile = Join-Path $PSScriptRoot "deploy-secrets.local.ps1"
if (Test-Path $secretsFile) {
    . $secretsFile
}

$resolvedToken = $GitHubToken
if (-not $resolvedToken) {
    $resolvedToken = $env:CRAVEVA_GITHUB_DEPLOY_TOKEN
}

Write-Host "Starting Git-based deploy on Staging..."

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
                $msg = "deploy(staging): $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
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

# Chạy toàn bộ remote trong một bash -lc duy nhất để tránh lỗi context (cd/user/quote).
$remoteBranch = Escape-BashSingleQuotedString $Branch
$remotePath = Escape-BashSingleQuotedString $StagingPath
$remoteToken = ""
if ($resolvedToken) {
    $remoteToken = Escape-BashSingleQuotedString $resolvedToken
}
$gitPullFlag = if ($GitPull) { '1' } else { '0' }

$remoteBashTemplate = @'
set -euo pipefail

cd '{0}'

if [ '{1}' -eq 1 ]; then
  if [ -n '{2}' ]; then
    export GIT_TERMINAL_PROMPT=0
    export GITHUB_DEPLOY_TOKEN='{2}'
    header="AUTHORIZATION: bearer $GITHUB_DEPLOY_TOKEN"

    sudo -u '{3}' git -c http.extraHeader="$header" fetch origin

    if sudo -u '{3}' git status --porcelain | grep -q .; then
      sudo -u '{3}' git stash push -u -m stash-auto-deploy
    fi

    sudo -u '{3}' git checkout '{4}'
    sudo -u '{3}' git -c http.extraHeader="$header" pull origin '{4}'
  else
    sudo -u '{3}' git fetch origin '{4}'

    if sudo -u '{3}' git status --porcelain | grep -q .; then
      sudo -u '{3}' git stash push -u -m stash-auto-deploy
    fi

    sudo -u '{3}' git checkout '{4}'
    sudo -u '{3}' git pull origin '{4}'
  fi
fi

# Lệnh bảo trì (Permissions, Migration, Optimize)
# Publish ngôn ngữ từ UI cần www-data ghi được resources/lang — xem SERVER_RUNBOOK_VI §4.8.
sudo chown -R hoangphat5393:www-data .
sudo mkdir -p lang resources/lang storage/logs
sudo mkdir -p public/user-uploads public/user-uploads/temp public/user-uploads/front/client
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chown -R www-data:www-data public/user-uploads
sudo chmod -R 775 storage bootstrap/cache
sudo chmod -R 775 public/user-uploads
sudo chmod 2777 storage/logs
sudo chmod -R ug+rwX Modules/LanguagePack/Languages resources/lang lang
sudo find Modules/LanguagePack/Languages resources/lang lang -type d -exec chmod g+s {{}} \; 2>/dev/null || true
for d in Modules/*/Resources/lang; do
  [ -d "$d" ] || continue
  sudo chmod -R ug+rwX "$d"
  sudo find "$d" -type d -exec chmod g+s {{}} \;
done

sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan languagepack:publish-translation
sudo -u www-data php artisan optimize:clear
'@

$remoteBash = $remoteBashTemplate -f $remotePath, $gitPullFlag, $remoteToken, $RemoteGitUser, $remoteBranch

$remoteBashEscaped = Escape-BashSingleQuotedString $remoteBash
ssh $StagingHost "bash -lc '$remoteBashEscaped'"
Write-Host "Deploy Staging Done."
