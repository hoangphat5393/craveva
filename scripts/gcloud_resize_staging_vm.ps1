# Increase RAM for craveva-staging VM only (no impact on craveva-ai, craveva-hub-server)
# Requires: gcloud CLI, already logged in (gcloud auth login)

$ErrorActionPreference = "Stop"
$Project = "craveva-org-55934-project"
# Khớp scripts/ssh_staging.ps1 và docs/GCP_INVENTORY.md.
$Zone = "asia-southeast1-a"
$Instance = "craveva-staging"

# e2-medium ~4 GiB -> e2-standard-2 = 2 vCPU, 8 GiB RAM (GCP)
$TargetMachineType = "e2-standard-2"

Write-Host "Project: $Project | Zone: $Zone | Instance: $Instance" -ForegroundColor Cyan
Write-Host "Other VMs (craveva-ai, craveva-hub-server) will NOT be modified." -ForegroundColor Green
Write-Host ""

# 1. Set project
Write-Host "[1/5] Setting project..." -ForegroundColor Yellow
gcloud config set project $Project
if ($LASTEXITCODE -ne 0) { throw "Failed to set project" }

# 2. Get current machine type
Write-Host "[2/5] Getting current machine type for $Instance..." -ForegroundColor Yellow
$MachineLine = gcloud compute instances describe $Instance --zone=$Zone --format="value(machineType)" 2>&1
if ($LASTEXITCODE -ne 0) { throw "Failed to describe instance. Is gcloud logged in? Run: gcloud auth login" }
$CurrentType = $MachineLine -replace '.*/', ''   # e.g. e2-medium
Write-Host "  Current machine type: $CurrentType" -ForegroundColor Gray

# 3. Stop instance (required before changing machine type)
Write-Host "[3/5] Stopping $Instance (other VMs are not touched)..." -ForegroundColor Yellow
gcloud compute instances stop $Instance --zone=$Zone --project=$Project
if ($LASTEXITCODE -ne 0) { throw "Failed to stop instance" }
Write-Host "  Instance stopped." -ForegroundColor Gray

# 4. Change machine type
Write-Host "[4/5] Changing machine type to $TargetMachineType..." -ForegroundColor Yellow
gcloud compute instances set-machine-type $Instance --machine-type=$TargetMachineType --zone=$Zone --project=$Project
if ($LASTEXITCODE -ne 0) { throw "Failed to set machine type" }
Write-Host "  Machine type set to $TargetMachineType." -ForegroundColor Gray

# 5. Start instance (nếu zone hết tài nguyên thì chuyển sang zone khác trong region)
Write-Host "[5/5] Starting $Instance..." -ForegroundColor Yellow
$startResult = gcloud compute instances start $Instance --zone=$Zone --project=$Project 2>&1
if ($LASTEXITCODE -ne 0) {
    if ($startResult -match "ZONE_RESOURCE_POOL_EXHAUSTED|does not have enough resources") {
        throw @"
Zone $Zone het tai nguyen cho machine type hien tai. Google da go lenh 'gcloud compute instances move'.
Cach xu ly: doi machine type nho hon / thu lai sau, hoac migrate bang snapshot disk sang zone khac (xem Google doc moving-instance-across-zones).
Chi tiet loi: $startResult
"@
    }
    throw "Failed to start instance: $startResult"
} else {
    Write-Host "  Instance started." -ForegroundColor Gray
}

Write-Host ""
Write-Host "Done. craveva-staging now uses $TargetMachineType. Other servers unchanged." -ForegroundColor Green
