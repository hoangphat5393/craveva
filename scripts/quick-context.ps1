param(
    [switch]$BiomixingOnly
)

$ErrorActionPreference = "Stop"

function Show-FileHeader {
    param([string]$Path)
    Write-Host ""
    Write-Host "===== $Path =====" -ForegroundColor Cyan
}

function Show-FileQuick {
    param(
        [string]$Path,
        [int]$Head = 80
    )
    if (Test-Path $Path) {
        Show-FileHeader -Path $Path
        Get-Content -Path $Path -TotalCount $Head
    }
}

Write-Host "Quick context pack (token saver)..." -ForegroundColor Green

if ($BiomixingOnly) {
    Show-FileQuick -Path "FUNC_IMPROVE/BIOMIXING_PRODUCTION_IMPLEMENTATION_PLAYBOOK_PHASE0_1_VI.md" -Head 120
    Show-FileQuick -Path "FUNC_TEST/01_BIOMIXING_PROPOSAL_TEST_CASE_MATRIX_VI.md" -Head 160
    Show-FileQuick -Path "FUNC_IMPROVE/10_UX_UI_IMPROVEMENT_BACKLOG.md" -Head 120
    exit 0
}

Show-FileQuick -Path "FUNC_INDEX.md" -Head 120
Show-FileQuick -Path "FUNC_TEST/INDEX.md" -Head 120
Show-FileQuick -Path "FUNC_TEST/01_BIOMIXING_PROPOSAL_TEST_CASE_MATRIX_VI.md" -Head 160
Show-FileQuick -Path "FUNC_IMPROVE/10_UX_UI_IMPROVEMENT_BACKLOG.md" -Head 120
Show-FileQuick -Path "FUNC_IMPROVE/BIOMIXING_PRODUCTION_IMPLEMENTATION_PLAYBOOK_PHASE0_1_VI.md" -Head 80

Write-Host ""
Write-Host "Done. Tip: run with -BiomixingOnly for narrower output." -ForegroundColor Yellow
