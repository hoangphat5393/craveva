# Export Cloud SQL allowlist status to markdown (short name: export_sql_allowlist.ps1; was export-cloudsql-allowlist-report.ps1).
param(
    [string]$ProjectId = "craveva-org-55934-project",
    [string[]]$Instances = @("craveva-staging-db", "craveva-hub-server"),
    [string]$OutputDirectory = "FUNC_REPORT"
)

$ErrorActionPreference = "Stop"

if (-not (Get-Command gcloud -ErrorAction SilentlyContinue)) {
    throw "gcloud CLI is not installed or not available in PATH."
}

if (-not (Test-Path -Path $OutputDirectory)) {
    New-Item -ItemType Directory -Path $OutputDirectory | Out-Null
}

$generatedAtUtc = (Get-Date).ToUniversalTime().ToString("yyyy-MM-ddTHH:mm:ssZ")
$timestamp = (Get-Date).ToUniversalTime().ToString("yyyyMMdd-HHmmss")
$outputFile = Join-Path $OutputDirectory "CLOUDSQL_ALLOWLIST_STATUS_$timestamp.md"

$lines = New-Object System.Collections.Generic.List[string]
$lines.Add("# Cloud SQL Allowlist Status Report")
$lines.Add("")
$lines.Add("- **Generated (UTC):** $generatedAtUtc")
$lines.Add("- **Project:** $ProjectId")
$lines.Add("- **Instances:** $($Instances -join ', ')")
$lines.Add("")

foreach ($instance in $Instances) {
    $jsonRaw = gcloud sql instances describe $instance --project=$ProjectId --format=json
    $obj = $jsonRaw | ConvertFrom-Json

    $lines.Add("## Instance: $instance")
    $lines.Add("")
    $lines.Add("- **Region:** $($obj.region)")
    $lines.Add("- **State:** $($obj.state)")
    $lines.Add("- **Database Version:** $($obj.databaseVersion)")

    $publicIps = @()
    foreach ($ip in $obj.ipAddresses) {
        if ($ip.type -eq "PRIMARY") {
            $publicIps += $ip.ipAddress
        }
    }

    if ($publicIps.Count -eq 0) {
        $publicIps = @("(none)")
    }

    $lines.Add("- **Public IP(s):** $($publicIps -join ', ')")
    $lines.Add("")
    $lines.Add("### Authorized Networks")
    $lines.Add("")

    $authorizedNetworks = @()
    if ($obj.settings -and $obj.settings.ipConfiguration -and $obj.settings.ipConfiguration.authorizedNetworks) {
        $authorizedNetworks = @($obj.settings.ipConfiguration.authorizedNetworks)
    }

    if ($authorizedNetworks.Count -eq 0) {
        $lines.Add("- (none)")
    } else {
        $sortedNetworks = $authorizedNetworks | Sort-Object -Property value
        foreach ($network in $sortedNetworks) {
            $label = if ($network.name) { $network.name } else { "(no-name)" }
            $lines.Add("- $($network.value) - $label")
        }
    }

    $lines.Add("")
}

Set-Content -Path $outputFile -Value $lines -Encoding UTF8

Write-Output "Report written to: $outputFile"
