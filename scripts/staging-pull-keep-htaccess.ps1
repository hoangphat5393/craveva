# Trên staging: pull nhưng giữ .htaccess local, không kéo .htaccess từ repo
# Chạy trong thư mục gốc project: .\scripts\staging-pull-keep-htaccess.ps1

if (-not (Test-Path ".htaccess")) {
    Write-Host "Không có .htaccess local, chạy git pull bình thường."
    git pull
    exit
}

Write-Host "Backup .htaccess local..."
Rename-Item -Path ".htaccess" -NewName ".htaccess.staging"

git pull
if ($LASTEXITCODE -ne 0) {
    Rename-Item -Path ".htaccess.staging" -NewName ".htaccess"
    Write-Host "Pull lỗi, đã khôi phục .htaccess"
    exit 1
}

Write-Host "Khôi phục .htaccess của staging..."
if (Test-Path ".htaccess") { Remove-Item ".htaccess" -Force }
Rename-Item -Path ".htaccess.staging" -NewName ".htaccess"

# Tránh lần pull sau ghi đè .htaccess (file đang được track trên remote)
git update-index --assume-unchanged .htaccess 2>$null
Write-Host "Đã đánh dấu .htaccess assume-unchanged để pull sau không ghi đè."
