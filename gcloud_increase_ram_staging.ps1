# Increase RAM for craveva-staging VM only (no impact on craveva-ai, craveva-hub-server)
# Requires: gcloud CLI, already logged in (gcloud auth login)

$ErrorActionPreference = "Stop"
$Project = "craveva-org-55934-project"
$Zone = "asia-southeast1-a"
$Instance = "craveva-staging"

# Optional: set target machine type (more RAM). Leave empty to auto-upgrade one tier.
# Examples: e2-standard-2 (8GB), e2-standard-4 (16GB), e2-standard-8 (32GB)
$TargetMachineType = "e2-standard"   # 8GB RAM - change to e2-standard-4 for 16GB if needed

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

# 5. Start instance
Write-Host "[5/5] Starting $Instance..." -ForegroundColor Yellow
gcloud compute instances start $Instance --zone=$Zone --project=$Project
if ($LASTEXITCODE -ne 0) { throw "Failed to start instance" }
Write-Host "  Instance started." -ForegroundColor Gray

Write-Host ""
Write-Host "Done. craveva-staging now uses $TargetMachineType. Other servers unchanged." -ForegroundColor Green
