# Shared helpers + file lists for zip deploy (hub + staging).
# Paths: deploy-zip.files.txt (files), deploy-zip.dirs.txt (recursive dirs). Repo root = parent of scripts/.

$ErrorActionPreference = "Stop"

function Get-DeployZipFileEntries {
    $f = Join-Path $PSScriptRoot "deploy-zip.files.txt"
    Get-Content $f | Where-Object { $_ -and -not $_.TrimStart().StartsWith("#") }
}

function Get-DeployZipDirEntries {
    $f = Join-Path $PSScriptRoot "deploy-zip.dirs.txt"
    Get-Content $f | Where-Object { $_ -and -not $_.TrimStart().StartsWith("#") }
}

function Initialize-DeployZipWorkspace {
    param(
        [Parameter(Mandatory = $true)][string]$LocalTempDir,
        [Parameter(Mandatory = $true)][string]$ZipFile
    )
    if (Test-Path $LocalTempDir) { Remove-Item -Recurse -Force $LocalTempDir }
    if (Test-Path $ZipFile) { Remove-Item -Force $ZipFile }
    New-Item -ItemType Directory -Force -Path $LocalTempDir | Out-Null
}

function Copy-DeployZipArtifacts {
    param(
        [Parameter(Mandatory = $true)][string]$RepoRoot,
        [Parameter(Mandatory = $true)][string]$LocalTempDir
    )
    Push-Location $RepoRoot
    try {
        foreach ($File in Get-DeployZipFileEntries) {
            if (Test-Path $File) {
                $Dest = Join-Path $LocalTempDir $File
                $Parent = Split-Path $Dest
                if (-not (Test-Path $Parent)) { New-Item -ItemType Directory -Force -Path $Parent | Out-Null }
                Copy-Item $File $Dest
            }
            else {
                Write-Warning "Missing file: $File"
            }
        }
        foreach ($Dir in Get-DeployZipDirEntries) {
            if (Test-Path $Dir) {
                $Dest = Join-Path $LocalTempDir $Dir
                $Parent = Split-Path $Dest
                if (-not (Test-Path $Parent)) { New-Item -ItemType Directory -Force -Path $Parent | Out-Null }
                Copy-Item -Recurse -Force $Dir $Parent -Exclude ".gitignore", ".git"
            }
            else {
                Write-Warning "Missing directory: $Dir"
            }
        }
    }
    finally {
        Pop-Location
    }
}

function Test-DeployZipCriticalFiles {
    param(
        [Parameter(Mandatory = $true)][string]$LocalTempDir,
        [Parameter(Mandatory = $true)][string[]]$RelativePaths
    )
    foreach ($rel in $RelativePaths) {
        $p = Join-Path $LocalTempDir $rel
        if (-not (Test-Path $p)) {
            throw "Critical path missing in zip temp: $rel"
        }
    }
}

function Build-DeployZipArchive {
    param(
        [Parameter(Mandatory = $true)][string]$RepoRoot,
        [Parameter(Mandatory = $true)][string]$LocalTempDir,
        [Parameter(Mandatory = $true)][string]$ZipFile
    )
    $zipper = Join-Path $RepoRoot "deploy_zipper.php"
    if (-not (Test-Path $zipper)) {
        throw "deploy_zipper.php not found at repo root."
    }
    & php $zipper $LocalTempDir $ZipFile
}

function Remove-DeployZipLocalArtifacts {
    param(
        [string]$LocalTempDir,
        [string]$ZipFile
    )
    if ($LocalTempDir -and (Test-Path $LocalTempDir)) { Remove-Item -Recurse -Force $LocalTempDir }
    if ($ZipFile -and (Test-Path $ZipFile)) { Remove-Item -Force $ZipFile }
}
