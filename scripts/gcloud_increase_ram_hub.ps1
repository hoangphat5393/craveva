# Increase RAM for craveva-hub-server VM only (no impact on craveva-ai, craveva-staging)
# Requires: gcloud CLI, already logged in (gcloud auth login)
#
# Hub hiện thường là e2-highcpu-4 (4 vCPU, ~4 GiB). Trên GCP không có E2 "4 vCPU + 8 GiB".
# - e2-standard-2 = 8 GiB nhưng chỉ 2 vCPU (giảm một nửa nhân so với highcpu-4).
# - e2-standard-4 = 4 vCPU, 16 GiB — giữ số nhân, RAM ≥ 8 GiB (mặc định dưới đây).
# Muốn đúng 8 GiB và chấp nhận 2 vCPU: đổi $TargetMachineType thành e2-standard-2.

$ErrorActionPreference = "Stop"
$Project = "craveva-org-55934-project"
$Zone = "asia-southeast1-a"
$Instance = "craveva-hub-server"
$TargetMachineType = "e2-standard-4"

Write-Host "Project: $Project | Zone: $Zone | Instance: $Instance" -ForegroundColor Cyan
Write-Host "Target machine type: $TargetMachineType (see script header for 8 GiB vs vCPU tradeoff)" -ForegroundColor Gray
Write-Host "Other VMs will NOT be modified." -ForegroundColor Green
Write-Host ""

Write-Host "[1/5] Setting project..." -ForegroundColor Yellow
gcloud config set project $Project
if ($LASTEXITCODE -ne 0) { throw "Failed to set project" }

Write-Host "[2/5] Getting current machine type for $Instance..." -ForegroundColor Yellow
$MachineLine = gcloud compute instances describe $Instance --zone=$Zone --format="value(machineType)" 2>&1
if ($LASTEXITCODE -ne 0) { throw "Failed to describe instance. Is gcloud logged in? Run: gcloud auth login" }
$CurrentType = $MachineLine -replace '.*/', ''
Write-Host "  Current machine type: $CurrentType" -ForegroundColor Gray

Write-Host "[3/5] Stopping $Instance..." -ForegroundColor Yellow
gcloud compute instances stop $Instance --zone=$Zone --project=$Project
if ($LASTEXITCODE -ne 0) { throw "Failed to stop instance" }
Write-Host "  Instance stopped." -ForegroundColor Gray

Write-Host "[4/5] Changing machine type to $TargetMachineType..." -ForegroundColor Yellow
gcloud compute instances set-machine-type $Instance --machine-type=$TargetMachineType --zone=$Zone --project=$Project
if ($LASTEXITCODE -ne 0) { throw "Failed to set machine type" }
Write-Host "  Machine type set to $TargetMachineType." -ForegroundColor Gray

Write-Host "[5/5] Starting $Instance..." -ForegroundColor Yellow
$startResult = gcloud compute instances start $Instance --zone=$Zone --project=$Project 2>&1
if ($LASTEXITCODE -ne 0) {
    if ($startResult -match "ZONE_RESOURCE_POOL_EXHAUSTED|does not have enough resources") {
        $ZoneAlt = "asia-southeast1-b"
        Write-Host "  Zone $Zone het tai nguyen. Dang chuyen VM sang $ZoneAlt..." -ForegroundColor Yellow
        gcloud compute instances move $Instance --zone=$Zone --destination-zone=$ZoneAlt --project=$Project
        if ($LASTEXITCODE -ne 0) { throw "Failed to move instance to $ZoneAlt" }
        $Zone = $ZoneAlt
        gcloud compute instances start $Instance --zone=$Zone --project=$Project
        if ($LASTEXITCODE -ne 0) { throw "Failed to start instance in $Zone" }
        Write-Host "  Instance da chuyen sang $Zone va da start." -ForegroundColor Gray
        Write-Host "  Luu y: IP co the thay doi; cap nhat firewall Cloud SQL / DNS neu can." -ForegroundColor Cyan
    } else {
        throw "Failed to start instance: $startResult"
    }
} else {
    Write-Host "  Instance started." -ForegroundColor Gray
}

Write-Host ""
Write-Host "Done. $Instance now uses $TargetMachineType." -ForegroundColor Green
