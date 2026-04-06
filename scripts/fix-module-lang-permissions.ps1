# Fix Windows read-only + ACL on module lang folders so LanguagePack publish can copy/delete.
#
# Run from project root:
#   powershell -ExecutionPolicy Bypass -File scripts/fix-module-lang-permissions.ps1
#
# If Publish still fails in the browser but works in terminal, PHP is running as another user
# (e.g. IIS Application Pool). Grant that identity too, using the pool name from IIS Manager:
#   .\scripts\fix-module-lang-permissions.ps1 -IisAppPool "YourAppPoolName"
#
# Or pass extra Windows accounts / groups (repeat -ExtraGrant for each):
#   .\scripts\fix-module-lang-permissions.ps1 -ExtraGrant "NT AUTHORITY\IUSR","IIS_IUSRS"

param(
    [Parameter(Mandatory = $false)]
    [string] $IisAppPool = '',

    [Parameter(Mandatory = $false)]
    [string[]] $ExtraGrant = @()
)

$ErrorActionPreference = 'Stop'
# scripts/ -> project root
$repoRoot = Split-Path $PSScriptRoot -Parent
if (-not (Test-Path (Join-Path $repoRoot 'artisan'))) {
    Write-Error "Run this script from the Laravel project (artisan not found under $repoRoot)."
}
$modules = Join-Path $repoRoot 'Modules'
if (-not (Test-Path $modules)) {
    Write-Error "Modules folder not found under $repoRoot"
}

$user = if ($env:USERDOMAIN) { "$env:USERDOMAIN\$env:USERNAME" } else { $env:USERNAME }
$grantees = [System.Collections.Generic.List[string]]::new()
[void] $grantees.Add($user)
if ($IisAppPool) {
    [void] $grantees.Add("IIS APPPOOL\$IisAppPool")
}
foreach ($g in $ExtraGrant) {
    if ($g) {
        [void] $grantees.Add($g)
    }
}

function Grant-ModifyInherit {
    param([string] $Path, [string[]] $Accounts)
    foreach ($acct in $Accounts) {
        $rule = "${acct}:(OI)(CI)M"
        cmd /c "icacls `"$Path`" /grant `"$rule`" /T /Q" 2>&1 | Out-Null
    }
}

$paths = Get-ChildItem -Path $modules -Directory -ErrorAction SilentlyContinue | ForEach-Object {
    Join-Path $_.FullName 'Resources\lang'
} | Where-Object { Test-Path $_ }

foreach ($p in $paths) {
    cmd /c "attrib -R `"$p\*`" /S /D" 2>&1 | Out-Null
    Grant-ModifyInherit -Path $p -Accounts $grantees
    Write-Host "OK $p"
}

$lang = Join-Path $repoRoot 'lang'
if (Test-Path $lang) {
    cmd /c "attrib -R `"$lang\*`" /S /D" 2>&1 | Out-Null
    Grant-ModifyInherit -Path $lang -Accounts $grantees
    Write-Host "OK $lang"
}

Write-Host "Done. Granted Modify (inherit) to: $($grantees -join ', '). Retry Publish All in Language Settings."
