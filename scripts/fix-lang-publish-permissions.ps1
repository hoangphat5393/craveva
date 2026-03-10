# Mo quyen ghi cho lang va Modules de nut "Publish All" chay duoc (chi trong thu muc du an).
#
# CACH 1 (nen dung): Khong can script, chay trong terminal:
#   cd F:\web\craveva-staging
#   php artisan languagepack:publish-translation
#
# CACH 2: Neu muon dung nut Publish All tren web, chay script nay VOI QUYEN ADMINISTRATOR:
#   Chuot phai file nay -> Run with PowerShell
#   Hoac mo PowerShell as Administrator, cd den thu muc scripts, roi: .\fix-lang-publish-permissions.ps1

$ErrorActionPreference = "Stop"
$base = "F:\web\craveva-staging"

if (-not (Test-Path $base)) {
    Write-Host "Thu muc project khong ton tai: $base"
    exit 1
}

$paths = @(
    "$base\lang",
    "$base\Modules"
)

foreach ($path in $paths) {
    if (-not (Test-Path $path)) { continue }
    Write-Host "Xu ly: $path"
    icacls $path /grant "Everyone:(OI)(CI)M" /T
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  -> OK"
    } else {
        Write-Host "  -> Co loi. Hay chay PowerShell as Administrator roi chay lai script nay."
    }
}

Write-Host "Xong. Thu lai nut Publish All tren Language Settings."
