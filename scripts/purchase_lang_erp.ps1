# Purchase module ERP wording — LanguagePack audit/apply + optional sync/publish.
#
# From project root:
#   powershell -ExecutionPolicy Bypass -File scripts/purchase_lang_erp.ps1
#   powershell -ExecutionPolicy Bypass -File scripts/purchase_lang_erp.ps1 -Apply -Publish
#   powershell -ExecutionPolicy Bypass -File scripts/purchase_lang_erp.ps1 -Apply -SyncKeys -Publish
#
# Glossary: FUNC_LOGIC/GLOSSARY_PURCHASE_ERP_VI.json
# Audit script: scripts/audit_purchase_lang.php
#
# Language Pack CLI (no artisan "translate" — auto-translate is UI in Language Settings):
#   languagepack:sync-keys     — scan code, add missing keys to LanguagePack
#   languagepack:publish-translation — copy LanguagePack → lang/ + Modules/*/Resources/lang

param(
    [switch] $Apply,
    [switch] $SyncKeys,
    [switch] $Publish,
    [switch] $PatternsOnly,
    [string] $Csv = '',
    [string] $Locale = 'all'
)

$ErrorActionPreference = 'Stop'
$repoRoot = Split-Path $PSScriptRoot -Parent
if (-not (Test-Path (Join-Path $repoRoot 'artisan'))) {
    Write-Error "Run from Laravel project root."
}

$auditArgs = @()
if ($Apply) { $auditArgs += '--apply' }
if ($PatternsOnly) { $auditArgs += '--patterns-only' }
if ($Locale -ne 'all') { $auditArgs += "--locale=$Locale" }
if ($Csv) { $auditArgs += "--csv=$Csv" }

Write-Host "=== Purchase LanguagePack ERP wording ===" -ForegroundColor Cyan
php (Join-Path $repoRoot 'scripts/audit_purchase_lang.php') @auditArgs
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

if ($SyncKeys) {
    Write-Host "`n=== languagepack:sync-keys (Modules/Purchase) ===" -ForegroundColor Cyan
    Push-Location $repoRoot
    php artisan languagepack:sync-keys --paths=Modules/Purchase --no-interaction
    Pop-Location
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
}

if ($Publish) {
    Write-Host "`n=== languagepack:publish-translation ===" -ForegroundColor Cyan
    Push-Location $repoRoot
    php artisan languagepack:publish-translation --no-interaction
    Pop-Location
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
}

if (-not $Apply -and -not $Publish) {
    Write-Host "`nTip: -Apply to write glossary; -Publish to push LanguagePack to runtime lang files." -ForegroundColor DarkGray
}
