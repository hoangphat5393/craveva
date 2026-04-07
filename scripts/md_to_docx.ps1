<#
.SYNOPSIS
  Convert Markdown to Word (.docx) using Pandoc (free, no API cost).

.DESCRIPTION
  Requires Pandoc: https://pandoc.org/installing.html
  Windows:  winget install --id JohnMacFarlane.Pandoc
            or: choco install pandoc

.EXAMPLE
  .\scripts\md_to_docx.ps1 "ERP Product Usage & Policy Clarification.md"
  .\scripts\md_to_docx.ps1 -InputPath ".\docs\spec.md" -OutputPath ".\docs\spec.docx"
#>

param(
    [Parameter(Mandatory = $true, Position = 0)]
    [string] $InputPath,

    [Parameter(Mandatory = $false)]
    [string] $OutputPath = ""
)

$ErrorActionPreference = "Stop"

$pandoc = Get-Command pandoc -ErrorAction SilentlyContinue
if (-not $pandoc) {
    Write-Error @"
Pandoc not found. Install (pick one):
  winget install --id JohnMacFarlane.Pandoc
  choco install pandoc
Then reopen the terminal and run this script again.
"@
}

$in = Resolve-Path -LiteralPath $InputPath
if (-not $OutputPath) {
    $OutputPath = [System.IO.Path]::ChangeExtension($in.Path, ".docx")
}

# Use gfm if your file uses GitHub-style tables; plain markdown works for pipe tables too.
& pandoc $in.Path -o $OutputPath --from gfm --to docx
Write-Host "Wrote: $OutputPath"
