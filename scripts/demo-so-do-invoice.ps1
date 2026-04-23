param(
    [ValidateSet("prep", "verify", "cleanup-dry", "cleanup-apply")]
    [string]$Mode = "verify",
    [int]$CompanyId = 1,
    [string]$OrderNo = "ODR#004",
    [string]$DoNo = "SS-000008",
    [string]$InvoiceNo = "INV#028",
    [string]$BatchNo = "DEMO-ODR004-B1"
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

$repoRoot = Split-Path -Parent $PSScriptRoot
Set-Location $repoRoot

function Invoke-Artisan {
    param(
        [Parameter(Mandatory = $true)]
        [string[]]$Args
    )

    Write-Host ""
    Write-Host ">> php artisan $($Args -join ' ')" -ForegroundColor Cyan
    & php artisan @Args
    if ($LASTEXITCODE -ne 0) {
        throw "Command failed with exit code ${LASTEXITCODE}: php artisan $($Args -join ' ')"
    }
}

switch ($Mode) {
    "prep" {
        Write-Host "Demo PREP: duplicate batch audit" -ForegroundColor Yellow
        Invoke-Artisan -Args @("warehouse:batch-dedupe", "--company_id=$CompanyId")
        Write-Host ""
        Write-Host "Prep done. If duplicates are reported, run:" -ForegroundColor Green
        Write-Host "php artisan warehouse:batch-dedupe --apply --company_id=$CompanyId"
    }

    "verify" {
        Write-Host "Demo VERIFY: focused feature tests" -ForegroundColor Yellow
        Invoke-Artisan -Args @("test", "tests/Feature/SalesDoItemBatchDisplayTest.php", "tests/Feature/WarehouseBatchDedupeCommandTest.php", "tests/Feature/WarehouseDemoCleanupCommandTest.php")
    }

    "cleanup-dry" {
        Write-Host "Demo CLEANUP dry-run" -ForegroundColor Yellow
        Invoke-Artisan -Args @(
            "warehouse:demo-cleanup",
            "--company_id=$CompanyId",
            "--order_no=$OrderNo",
            "--do_no=$DoNo",
            "--invoice_no=$InvoiceNo",
            "--batch_no=$BatchNo"
        )
    }

    "cleanup-apply" {
        Write-Host "Demo CLEANUP apply" -ForegroundColor Yellow
        Invoke-Artisan -Args @(
            "warehouse:demo-cleanup",
            "--apply",
            "--company_id=$CompanyId",
            "--order_no=$OrderNo",
            "--do_no=$DoNo",
            "--invoice_no=$InvoiceNo",
            "--batch_no=$BatchNo"
        )
    }
}

Write-Host ""
Write-Host "Done: $Mode" -ForegroundColor Green
