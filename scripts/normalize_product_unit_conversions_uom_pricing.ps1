# Normalize product_unit_conversions (for_sale=0, selling_price -> cost_price).
# Usage from repo root on each machine (local / staging / hub):
#   .\scripts\normalize_product_unit_conversions_uom_pricing.ps1
#   .\scripts\normalize_product_unit_conversions_uom_pricing.ps1 -DryRun
#   .\scripts\normalize_product_unit_conversions_uom_pricing.ps1 -Apply

param(
    [switch]$DryRun,
    [switch]$Apply
)

$ErrorActionPreference = 'Stop'
Set-Location (Split-Path $PSScriptRoot -Parent)

if (-not $DryRun -and -not $Apply) {
    Write-Host 'Preview (dry-run). Use -Apply to write changes.' -ForegroundColor Yellow
    $DryRun = $true
}

Write-Host 'Step 1: ensure cost_price column (migrate)...' -ForegroundColor Cyan
php artisan migrate --no-interaction --force

$args = @('product-unit-conversions:normalize-uom-pricing', '--no-interaction')
if ($DryRun) {
    $args += '--dry-run'
} else {
    $args += '--force'
}

Write-Host "Step 2: php artisan $($args -join ' ')..." -ForegroundColor Cyan
php artisan @args

if (-not $DryRun) {
    Write-Host 'Done. Spot-check in HeidiSQL: selling_price NULL, for_sale=0, cost_price filled.' -ForegroundColor Green
}
