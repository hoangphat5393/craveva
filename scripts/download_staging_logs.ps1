# Download logs from staging server to local folder
# Chạy từ thư mục gốc project (craveva-staging). Log sẽ lưu vào .\staging_logs_YYYYMMDD\

$StagingHost = "craveva-staging"
$StagingPath = "/var/www/craveva-staging/current/craveva"
$DateStr = Get-Date -Format "yyyyMMdd"
$LocalLogDir = ".\staging_logs_$DateStr"

# Create local directory
if (-not (Test-Path $LocalLogDir)) {
    New-Item -ItemType Directory -Force -Path $LocalLogDir | Out-Null
}
Write-Host "Logs will be saved to: $LocalLogDir" -ForegroundColor Cyan

# 1. Laravel logs (storage/logs/ - thường có quyền đọc với user deploy)
$LaravelLogsRemote = "${StagingPath}/storage/logs"
Write-Host "`n[1/3] Downloading Laravel logs from ${StagingHost}:${LaravelLogsRemote} ..." -ForegroundColor Yellow
# scp cả thư mục logs → tạo LocalLogDir/logs/ (chứa laravel.log, laravel-YYYY-MM-DD.log)
scp -r "${StagingHost}:${LaravelLogsRemote}" $LocalLogDir 2>$null
if ($LASTEXITCODE -ne 0) {
    Write-Host "  (Nếu lỗi permission, chạy trên staging: ls -la $LaravelLogsRemote)" -ForegroundColor Gray
} else {
    Write-Host "  Saved to: $LocalLogDir\logs" -ForegroundColor Green
}

# 2. Nginx error log (trên server thường cần sudo)
Write-Host "`n[2/3] Fetching Nginx error.log (via ssh sudo cat) ..." -ForegroundColor Yellow
$NginxErrorLocal = Join-Path $LocalLogDir "nginx_error.log"
ssh $StagingHost "sudo cat /var/log/nginx/error.log 2>/dev/null" | Set-Content -Path $NginxErrorLocal -Encoding UTF8
if (Test-Path $NginxErrorLocal) { Write-Host "  Saved: $NginxErrorLocal" -ForegroundColor Green }

# 3. PHP-FPM log (nếu có)
Write-Host "`n[3/3] Fetching PHP-FPM log (via ssh sudo cat) ..." -ForegroundColor Yellow
$PhpFpmLocal = Join-Path $LocalLogDir "php8.2-fpm.log"
# Ubuntu thường: /var/log/php8.2-fpm.log hoặc trong syslog
ssh $StagingHost "sudo cat /var/log/php8.2-fpm.log 2>/dev/null || sudo grep -a php-fpm /var/log/syslog 2>/dev/null | tail -5000" | Set-Content -Path $PhpFpmLocal -Encoding UTF8
if (Test-Path $PhpFpmLocal) { Write-Host "  Saved: $PhpFpmLocal" -ForegroundColor Green }

Write-Host "`nDone. Log files are in: $((Resolve-Path $LocalLogDir).Path)" -ForegroundColor Cyan
Write-Host "Laravel:  $LocalLogDir\logs" -ForegroundColor Gray
Write-Host "Nginx:    $NginxErrorLocal" -ForegroundColor Gray
Write-Host "PHP-FPM:  $PhpFpmLocal" -ForegroundColor Gray
