<#
.SYNOPSIS
  One-command helper to prepare low-token markdown context for AI.

.DESCRIPTION
  Wraps md_master_sync.ps1 topic filtering and outputs compact context:
  - Top matched docs (by topic)
  - Suggested master guide
  - Copy-paste prompt block for AI

.EXAMPLE
  .\scripts\md_ai_context.ps1 -Topic "warehouse inventory stock"

.EXAMPLE
  .\scripts\md_ai_context.ps1 -Topic "maolin import pricing" -Limit 6 -AsPromptOnly
#>
param(
    [Parameter(Mandatory = $true)]
    [string] $Topic,
    [int] $Limit = 8,
    [string] $ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path,
    [switch] $AsPromptOnly
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

$syncScript = Join-Path $PSScriptRoot "md_master_sync.ps1"
if (-not (Test-Path -LiteralPath $syncScript)) {
    Write-Error "Missing script: $syncScript"
    exit 1
}

$json = & $syncScript -ProjectRoot $ProjectRoot -AsJson -Topic $Topic -Limit $Limit
if ($LASTEXITCODE -ne 0) {
    Write-Error "Failed to run md_master_sync.ps1"
    exit $LASTEXITCODE
}

$result = $json | ConvertFrom-Json
$matched = @()
if ($result.focus -and $result.focus.matched_docs) {
    $matched = @($result.focus.matched_docs)
}

if ($matched.Count -eq 0) {
    Write-Host "No matching markdown docs found for topic: $Topic"
    Write-Host "Try broader keywords, e.g. 'warehouse stock', 'import pricing', 'client flow'."
    exit 0
}

$docLines = @()
$guideSet = New-Object System.Collections.Generic.HashSet[string]
foreach ($m in $matched) {
    $docLines += "- $($m.file)"
    if ($m.suggested_master_guide -and $m.suggested_master_guide.Trim().Length -gt 0) {
        [void]$guideSet.Add($m.suggested_master_guide)
    }
}

$guides = @($guideSet)
if ($guides.Count -eq 0) {
    $guides = @("FUNC_LOGIC/WAREHOUSE_MASTER_GUIDE.md", "FUNC_LOGIC/MAOLIN_MASTER_GUIDE.md")
}

$promptBlock = @"
[LOW-TOKEN MODE ON]
Topic: $Topic

Use ONLY these markdown files:
$(($docLines -join "`n"))

Preferred master guide(s):
$(($guides | ForEach-Object { "- $_" }) -join "`n")

Task:
- Review and update only the sections relevant to topic "$Topic".
- Do not read entire repo.
- Keep changes minimal and consistent with existing docs.
- Return:
  1) files updated
  2) short changelog (max 5 lines)
  3) remaining TODO/NEED_INPUT (if any)
"@

if ($AsPromptOnly) {
    Write-Output $promptBlock
    exit 0
}

Write-Host "AI context ready for topic: $Topic"
Write-Host ""
Write-Host "Matched docs:"
$docLines | ForEach-Object { Write-Host $_ }
Write-Host ""
Write-Host "Suggested master guide(s):"
$guides | ForEach-Object { Write-Host "- $_" }
Write-Host ""
Write-Host "Copy prompt below:"
Write-Host "----------------------------------------"
Write-Output $promptBlock
Write-Host "----------------------------------------"

