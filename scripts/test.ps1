# Chay test Laravel/Pest local — giam token khi lam viec voi AI.
# AI chi can doc output khi FAIL; ban tu chay script nay truoc khi hoi agent.
#
# Vi du:
#   .\scripts\test.ps1              # full suite (~3-7 phut)
#   .\scripts\test.ps1 phase1       # chi Estimate / Phase 1 OEM (~20s)
#   .\scripts\test.ps1 unit         # thu muc tests/Unit
#   .\scripts\test.ps1 file EstimateSubmitForReviewTest.php
#   .\scripts\test.ps1 filter "vp margin"
#
# Sau khi sua code Phase 1: uu tien `phase1` hoac `file <ten test>`.

param(
    [Parameter(Position = 0)]
    [string] $Mode = "full",

    [Parameter(Position = 1)]
    [string] $Extra = ""
)

$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent $PSScriptRoot
Set-Location $Root

if (-not (Test-Path "vendor\bin\pest")) {
    Write-Host "Chua co vendor. Chay: composer install" -ForegroundColor Red
    exit 1
}

$args = @("test", "--compact")

switch ($Mode.ToLowerInvariant()) {
    "phase1" {
        $args += "--filter=Estimate"
        Write-Host ">>> Phase 1 / Estimate tests" -ForegroundColor Cyan
    }
    "unit" {
        $args += "tests/Unit"
        Write-Host ">>> tests/Unit" -ForegroundColor Cyan
    }
    "feature" {
        $args += "tests/Feature"
        Write-Host ">>> tests/Feature" -ForegroundColor Cyan
    }
    "file" {
        if ($Extra -eq "") {
            Write-Host "Dung: .\scripts\test.ps1 file EstimateSubmitForReviewTest.php" -ForegroundColor Yellow
            exit 1
        }
        $path = $Extra
        if ($path -notmatch "^tests/") {
            $path = "tests/Feature/$path"
            if (-not (Test-Path $path)) {
                $path = "tests/Unit/$Extra"
            }
        }
        $args += $path
        Write-Host ">>> $path" -ForegroundColor Cyan
    }
    "filter" {
        if ($Extra -eq "") {
            Write-Host "Dung: .\scripts\test.ps1 filter `"vp margin`"" -ForegroundColor Yellow
            exit 1
        }
        $args += "--filter=$Extra"
        Write-Host ">>> filter: $Extra" -ForegroundColor Cyan
    }
    "full" {
        Write-Host ">>> Full suite (co the mat vai phut)" -ForegroundColor Cyan
    }
    default {
        Write-Host "Mode khong hop le: $Mode" -ForegroundColor Yellow
        Write-Host "Modes: full | phase1 | unit | feature | file <path> | filter <name>"
        exit 1
    }
}

$sw = [System.Diagnostics.Stopwatch]::StartNew()
& php artisan @args
$code = $LASTEXITCODE
$sw.Stop()

Write-Host ""
if ($code -eq 0) {
    Write-Host "PASS ($([math]::Round($sw.Elapsed.TotalSeconds, 1))s)" -ForegroundColor Green
} else {
    Write-Host "FAIL ($([math]::Round($sw.Elapsed.TotalSeconds, 1))s) — chi gui agent doan FAILED phia tren" -ForegroundColor Red
}

exit $code
