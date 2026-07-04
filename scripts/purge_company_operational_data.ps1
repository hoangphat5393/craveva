param(
    [Parameter(Mandatory = $true)]
    [ValidateRange(1, [int]::MaxValue)]
    [int] $CompanyId,

    [switch] $IncludeBoms,
    [switch] $Execute,
    [string] $ConfirmToken = ''
)

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

$projectRoot = Split-Path -Parent $PSScriptRoot
$artisan = Join-Path $projectRoot 'artisan'

if (-not (Test-Path -LiteralPath $artisan -PathType Leaf)) {
    throw "Laravel artisan file not found: $artisan"
}

if ($Execute -and [string]::IsNullOrWhiteSpace($ConfirmToken)) {
    throw 'Execute mode requires -ConfirmToken from a previous dry-run.'
}

$artisanArgs = @(
    $artisan,
    'company:purge-transactions',
    "--company-id=$CompanyId"
)

if ($IncludeBoms) {
    $artisanArgs += '--include-boms'
}

if ($Execute) {
    $artisanArgs += '--execute'
    $artisanArgs += "--confirm-token=$ConfirmToken"
} else {
    $artisanArgs += '--no-interaction'
}

Push-Location $projectRoot
try {
    & php @artisanArgs
    exit $LASTEXITCODE
} finally {
    Pop-Location
}
