#Requires -Version 5.1
<#
.SYNOPSIS
  Một worker PHP: tất cả queue import (ưu tiên) + default — giống run-queue-worker-all.sh.
.USAGE
  cd E:\web\craveva-staging
  .\scripts\run-queue-worker-all.ps1
#>

$ErrorActionPreference = "Stop"
if (-not $PSScriptRoot) {
    $PSScriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
}
$Root = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path
Set-Location $Root

if (-not (Test-Path -LiteralPath (Join-Path $Root "artisan"))) {
    Write-Error "Không thấy artisan — chạy từ thư mục gốc project."; exit 1
}

Write-Host "Preflight (chỉ đọc DB)…"
& php scripts/queue-worker-preflight.php
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
Write-Host ""

$Queues = @(
  "ClientImport", "ProductImport", "EmployeeImport", "ProjectImport", "DealImport", "LeadImport",
  "ExpenseImport", "AttendanceImport", "JobApplicationImport", "ClientProductPricingImport",
  "PricingTierItemsImport", "WarehouseImport", "InventoryImport", "default"
) -join ","

& php artisan queue:work database `
  --queue=$Queues `
  --tries=3 `
  --sleep=3 `
  @args
