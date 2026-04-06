<#
.SYNOPSIS
  In ra phần đầu file dạng text (hoặc peek nhanh .xlsx) để dán vào chat AI — giảm token so với gửi cả file.

.EXAMPLE
  .\scripts\preview_for_ai.ps1 -Path ".\storage\app\export.csv" -Lines 50
.EXAMPLE
  .\scripts\preview_for_ai.ps1 -Path "C:\data\book.xlsx"
#>
param(
    [Parameter(Mandatory = $true)]
    [string] $Path,
    [int] $Lines = 80
)

$resolved = Resolve-Path -LiteralPath $Path -ErrorAction SilentlyContinue
if (-not $resolved) {
    Write-Error "File not found: $Path"
    exit 1
}

$full = $resolved.Path
$ext = [System.IO.Path]::GetExtension($full).ToLowerInvariant()

$textLike = @('.txt', '.csv', '.log', '.md', '.sql', '.tsv', '.json', '.xml', '.yaml', '.yml')
if ($textLike -contains $ext) {
    Get-Content -LiteralPath $full -TotalCount $Lines -Encoding utf8
    exit 0
}

if ($ext -eq '.xlsx') {
    $peek = Join-Path $PSScriptRoot 'peek_maolin_sheet.php'
    if (-not (Test-Path -LiteralPath $peek)) {
        Write-Error "Missing $peek"
        exit 1
    }
    & php $peek $full
    exit $LASTEXITCODE
}

Write-Host "Unsupported extension: $ext"
Write-Host "Tip: For .docx, copy plain text or export PDF/txt; for large .xlsx use peek_maolin_sheet.php or export CSV."
