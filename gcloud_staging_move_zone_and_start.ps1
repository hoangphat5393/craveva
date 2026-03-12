# Chuyen craveva-staging sang zone khac va start (khi asia-southeast1-a het tai nguyen e2-medium)
# Chay tren may Windows (PowerShell), can: gcloud auth login

$ErrorActionPreference = "Stop"
$Project = "craveva-org-55934-project"
$ZoneFrom = "asia-southeast1-a"
$ZoneTo = "asia-southeast1-b"
$Instance = "craveva-staging"

Write-Host "Chuyen $Instance tu $ZoneFrom -> $ZoneTo roi start..." -ForegroundColor Cyan
gcloud config set project $Project
if ($LASTEXITCODE -ne 0) { throw "Failed to set project" }

Write-Host "[1/2] Moving instance to $ZoneTo..." -ForegroundColor Yellow
gcloud compute instances move $Instance --zone=$ZoneFrom --destination-zone=$ZoneTo --project=$Project
if ($LASTEXITCODE -ne 0) { throw "Move failed" }

Write-Host "[2/2] Starting instance..." -ForegroundColor Yellow
gcloud compute instances start $Instance --zone=$ZoneTo --project=$Project
if ($LASTEXITCODE -ne 0) { throw "Start failed" }

Write-Host "Done. craveva-staging dang chay o zone $ZoneTo. Kiem tra IP moi trong Console." -ForegroundColor Green
