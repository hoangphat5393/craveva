# Upload selected local files to Hub server (partial preview deploy).
# Uses SSH host from ~/.ssh/config: craveva-hub-server

param(
    [string]$HubHost = "craveva-hub-server",
    [string]$RemoteRoot = "/var/www/hub.craveva.com"
)

$ErrorActionPreference = "Stop"

$repoRoot = Split-Path -Parent $PSScriptRoot

$files = @(
    "app/Http/Controllers/SuperAdmin/FrontendController.php",
    "app/DataTables/SuperAdmin/PackageDataTable.php",
    "resources/views/super-admin/saas/pricing.blade.php",
    "resources/views/super-admin/front/home.blade.php",
    "resources/views/super-admin/front/section/pricing.blade.php",
    "FUNC_LOGIC/SUPERADMIN_PACKAGE_AUDIT_VI.md"
)

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
