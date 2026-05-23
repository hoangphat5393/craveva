# Git-based deploy for Staging. SSH Host phải có trong ~/.ssh/config.
#
# Mặc định (2026-05): **có** chạy git add/commit/push trên máy dev rồi mới SSH deploy — khớp mong đợi “deploy = đẩy code lên rồi kéo staging”.
# Chỉ deploy mà **không** đụng repo local (chỉ pull những gì đã có trên origin — máy khác/CI đã push):
#   .\scripts\upload_staging.ps1 -DeployOnly
#   hoặc: .\scripts\upload_staging.ps1 -SkipLocalGit:$true
#
# Quyền trên server: thư mục .git thuộc user RemoteGitUser. Đăng nhập SSH bằng Admin rồi `git pull` trực tiếp
# thường báo Permission denied trên .git/FETCH_HEAD — phải chạy git với đúng owner, ví dụ:
#   cd /var/www/craveva-staging/current/craveva && sudo -u hoangphat5393 git pull origin main
#
# Nếu trên server `git pull` báo "could not read Password" (HTTPS + không có TTY):
# - Khuyến nghị lâu dài: đổi origin sang SSH + deploy key trên server.
# - Tạm thời: đặt PAT trong biến môi trường CRAVEVA_GITHUB_DEPLOY_TOKEN hoặc file
#   scripts/deploy-secrets.local.ps1 (copy từ deploy-secrets.local.ps1.example). Không commit token.

param(
    [bool]$GitPull = $true, # Mặc định là pull code trên server
    [string]$Branch = "main",
    [string]$GitHubToken = "", # Tùy chọn; nếu rỗng dùng env CRAVEVA_GITHUB_DEPLOY_TOKEN
    [string]$CommitMessage = "", # Khi bật push local: nếu rỗng và có commit — dùng message mặc định có timestamp
    [switch]$DeployOnly, # Chỉ SSH deploy: không add/commit/push trên máy này (server chỉ git pull origin)
    [switch]$PushLocalFirst, # Giữ tương thích: ép chạy push local (tương đương -SkipLocalGit:$false)
    [bool]$SkipLocalGit = $false # $false (mặc định): fetch/checkout, git add -A, commit nếu cần, push — rồi SSH. $true hoặc -DeployOnly: bỏ qua bước local.
)

if ($DeployOnly) {
    $SkipLocalGit = $true
}
if ($PushLocalFirst) {
    $SkipLocalGit = $false
}

$ErrorActionPreference = "Stop"
$StagingHost = "craveva-staging" # Phải khớp Host trong ~/.ssh/config — user SSH thường là Admin (GCP), không phải hoangphat5393 (owner git trên disk).
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
# Tránh CR/newline trong PAT làm vỡ dòng bash trên server (lỗi kiểu: bearer: command not found).
if ($resolvedToken) {
    $resolvedToken = ($resolvedToken -replace "`r`n?", '').Trim()
}

Write-Host "Starting Git-based deploy on Staging..."

if ($SkipLocalGit) {
    Write-Host ""
    Write-Host "  [Local git] SKIPPED (-DeployOnly / -SkipLocalGit:`$true)." -ForegroundColor Yellow
    Write-Host "  Server will only git pull what is already on origin/$Branch — uncommitted work here is NOT deployed." -ForegroundColor Yellow
    Write-Host "  To push from this PC first: run again **without** -DeployOnly (default runs add/commit/push)." -ForegroundColor Yellow
    Write-Host ""
} else {
    Write-Host "  [Local git] Will fetch/checkout $Branch, git add -A, commit (if needed), push — then remote deploy." -ForegroundColor Cyan
    Write-Host ""
}

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
# Dùng placeholder + .Replace (không dùng PowerShell -f cho cả template): -f ăn nhầm {{}} trong find
# và dễ tương tác xấu với chuỗi có dấu ' khi Escape-BashSingleQuotedString bọc toàn script.
$remoteBranchEscaped = Escape-BashSingleQuotedString $Branch
$remotePathEscaped = Escape-BashSingleQuotedString $StagingPath
$remoteTokenEscaped = ""
if ($resolvedToken) {
    $remoteTokenEscaped = Escape-BashSingleQuotedString $resolvedToken
}
$remoteGitUserEscaped = Escape-BashSingleQuotedString $RemoteGitUser
$gitPullFlag = if ($GitPull) { '1' } else { '0' }

$gitAuthHeaderB64 = ""
if ($resolvedToken) {
    $gitAuthPlain = "AUTHORIZATION: Bearer " + $resolvedToken
    $gitAuthHeaderB64 = [Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($gitAuthPlain))
}

$remoteBashTemplate = @'
set -euo pipefail

cd '__RB_PATH__'

if [ '__RB_GITPULL__' -eq 1 ]; then
  # Git user must own the tree so reset can replace files previously owned by www-data.
  sudo chown -R '__RB_GITUSER__':www-data .
  if [ -n '__RB_TOKEN__' ]; then
    export GIT_TERMINAL_PROMPT=0
    export GITHUB_DEPLOY_TOKEN='__RB_TOKEN__'
    # GIT extraHeader: decode __RB_HDR_B64__ via base64; avoid PAT literal in remote script.
    _B64_PAD=__RB_HDR_B64__
    _GIT_HDR=$(printf %s "$_B64_PAD" | base64 -d)
    sudo -u '__RB_GITUSER__' env \
      GIT_CONFIG_COUNT=1 \
      GIT_CONFIG_KEY_0=http.extraHeader \
      GIT_CONFIG_VALUE_0="$_GIT_HDR" \
      git fetch origin '__RB_BRANCH__'

    if sudo -u '__RB_GITUSER__' git status --porcelain | grep -q .; then
      # Stash tracked changes only: no -u (untracked stash can hit Permission denied on www-data files).
      sudo -u '__RB_GITUSER__' git stash push -m stash-auto-deploy
    fi

    sudo -u '__RB_GITUSER__' git checkout '__RB_BRANCH__'
    sudo -u '__RB_GITUSER__' env \
      GIT_CONFIG_COUNT=1 \
      GIT_CONFIG_KEY_0=http.extraHeader \
      GIT_CONFIG_VALUE_0="$_GIT_HDR" \
      git reset --hard "origin/__RB_BRANCH__"
  else
    sudo -u '__RB_GITUSER__' git fetch origin '__RB_BRANCH__'

    if sudo -u '__RB_GITUSER__' git status --porcelain | grep -q .; then
      # Stash tracked changes only: no -u (untracked stash can hit Permission denied on www-data files).
      sudo -u '__RB_GITUSER__' git stash push -m stash-auto-deploy
    fi

    sudo -u '__RB_GITUSER__' git checkout '__RB_BRANCH__'
    sudo -u '__RB_GITUSER__' git reset --hard "origin/__RB_BRANCH__"
  fi
fi

# Lệnh bảo trì (Permissions, Migration, Optimize)
# Publish ngôn ngữ từ UI cần www-data ghi được resources/lang — xem SERVER_RUNBOOK_VI §4.8.
# FPM (www-data) phải ghi storage/framework/sessions — xem SERVER_RUNBOOK_VI §4.6.
sudo chown -R '__RB_GITUSER__':www-data .
sudo mkdir -p lang resources/lang storage/logs
sudo mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data
sudo mkdir -p public/user-uploads public/user-uploads/temp public/user-uploads/front/client
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chown -R www-data:www-data public/user-uploads
sudo chmod -R ug+rwX storage bootstrap/cache
sudo chmod -R 775 public/user-uploads
sudo chmod 2777 storage/logs
sudo find storage bootstrap/cache -type d -exec chmod g+s {} \; 2>/dev/null || true
sudo find storage/framework/sessions -type f ! -user www-data -delete 2>/dev/null || true
sudo -u www-data touch storage/framework/sessions/.deploy_write_test && sudo rm -f storage/framework/sessions/.deploy_write_test
sudo chmod -R ug+rwX Modules/LanguagePack/Languages resources/lang lang
sudo find Modules/LanguagePack/Languages resources/lang lang -type d -exec chmod g+s {} \; 2>/dev/null || true
for d in Modules/*/Resources/lang; do
  [ -d "$d" ] || continue
  sudo chmod -R ug+rwX "$d"
  sudo find "$d" -type d -exec chmod g+s {} \;
done

sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan languagepack:publish-translation
sudo -u www-data php artisan optimize:clear
'@

$remoteBash = $remoteBashTemplate.
    Replace('__RB_PATH__', $remotePathEscaped).
    Replace('__RB_GITPULL__', $gitPullFlag).
    Replace('__RB_TOKEN__', $remoteTokenEscaped).
    Replace('__RB_GITUSER__', $remoteGitUserEscaped).
    Replace('__RB_BRANCH__', $remoteBranchEscaped).
    Replace('__RB_HDR_B64__', $gitAuthHeaderB64)
# Chuẩn hóa EOL: mọi CR (kể cả CR đơn không kèm LF) tránh bash parse gãy dòng (Bearer: command not found).
$remoteBash = ($remoteBash -replace "`r`n", "`n") -replace "`r", ""

$remoteBashEscaped = Escape-BashSingleQuotedString $remoteBash
ssh $StagingHost "bash -lc '$remoteBashEscaped'"
if ($LASTEXITCODE -ne 0) {
    throw "Remote deploy failed (ssh/bash exit $LASTEXITCODE). Kiểm tra log phía trên."
}
Write-Host "Deploy Staging Done."
