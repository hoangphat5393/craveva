# Upload selected local files to Hub server (partial preview deploy).
# Uses SSH host from ~/.ssh/config: craveva-hub-server
#
# Default: built-in $files list below.
# Optional: pass -CaseFile "scripts\casefiles\your-case.txt" — file contains one repo-relative path per line (# comments ignored).
# Example: .\scripts\upload_hub_temp.ps1 -CaseFile "scripts\casefiles\hub-upload-pm-ready-remove-temp-api-doc.txt"

param(
    [string]$HubHost = "craveva-hub-server",
    [string]$RemoteRoot = "/var/www/hub.craveva.com",
    [string]$CaseFile = ""
)

$ErrorActionPreference = "Stop"

$repoRoot = Split-Path -Parent $PSScriptRoot

if (-not [string]::IsNullOrWhiteSpace($CaseFile)) {
    $casePath = if ([System.IO.Path]::IsPathRooted($CaseFile)) { $CaseFile } else { Join-Path $repoRoot $CaseFile }
    if (-not (Test-Path -LiteralPath $casePath)) {
        throw "Case file not found: $casePath"
    }
    $files = @(Get-Content -LiteralPath $casePath | ForEach-Object { $_.Trim() } | Where-Object { $_ -ne '' -and -not $_.StartsWith('#') })
    if ($files.Count -eq 0) {
        throw "Case file contains no paths: $casePath"
    }
    Write-Host "Using case file: $casePath" -ForegroundColor Yellow
} else {
    $files = @(
        "app/Http/Controllers/SuperAdmin/FrontendController.php",
        "app/DataTables/SuperAdmin/PackageDataTable.php",
        "resources/views/super-admin/saas/pricing.blade.php",
        "resources/views/super-admin/front/home.blade.php",
        "resources/views/super-admin/front/section/pricing.blade.php",
        "FUNC_LOGIC/SUPERADMIN_PACKAGE_AUDIT_VI.md"
    )
}

Write-Host "Uploading partial files to Hub server..." -ForegroundColor Yellow
Write-Host "Host: $HubHost"
Write-Host "Remote root: $RemoteRoot"

foreach ($relativePath in $files) {
    $localPath = Join-Path $repoRoot $relativePath
    if (-not (Test-Path $localPath)) {
        throw "Local file not found: $localPath"
    }

    $remotePath = "$RemoteRoot/$relativePath".Replace("\", "/")
    $remoteDir = [System.IO.Path]::GetDirectoryName($remotePath).Replace("\", "/")

    Write-Host ""
    Write-Host "-> $relativePath" -ForegroundColor Cyan
    ssh $HubHost "mkdir -p '$remoteDir'"

    scp $localPath "${HubHost}:$remotePath"
    if ($LASTEXITCODE -ne 0) {
        Write-Host "SCP failed, trying SSH fallback for $relativePath..." -ForegroundColor Yellow
        $content = Get-Content -Raw -Path $localPath
        $escaped = $content.Replace("'", "'\''")
        ssh $HubHost "cat > '$remotePath' <<'EOF'
$escaped
EOF"
        if ($LASTEXITCODE -ne 0) {
            throw "Upload failed for: $relativePath"
        }
    }
}

Write-Host ""
Write-Host "Upload Hub temp done." -ForegroundColor Green
