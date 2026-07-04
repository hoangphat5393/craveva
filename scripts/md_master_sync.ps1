<#
.SYNOPSIS
  Read-only documentation sync checker for FUNC_* markdown structure.

.DESCRIPTION
  Scans markdown files under FUNC_BUG, FUNC_IMPROVE, FUNC_LOGIC, FUNC_TEST
  and reports what likely needs updates in:
  - FUNC_INDEX.md (root navigation)
  - group INDEX.md files
  - module master guides (*MASTER_GUIDE*.md)

  This script does NOT modify any files.

.EXAMPLE
  .\scripts\md_master_sync.ps1

.EXAMPLE
  .\scripts\md_master_sync.ps1 -ProjectRoot "E:\web\craveva-staging" -AsJson

.EXAMPLE
  .\scripts\md_master_sync.ps1 -Fix

.EXAMPLE
  .\scripts\md_master_sync.ps1 -FixMaster
#>
param(
    [string] $ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path,
    [switch] $AsJson,
    [switch] $Fix,
    [switch] $FixMaster,
    [string] $RulesPath = (Join-Path $PSScriptRoot "md_master_sync.rules.json"),
    [string] $Topic = "",
    [int] $Limit = 12
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function Get-FileText {
    param([string] $Path)
    if (Test-Path -LiteralPath $Path) {
        return [System.IO.File]::ReadAllText($Path)
    }
    return ""
}

function Get-RulesConfig {
    param([string] $Path)
    if (-not (Test-Path -LiteralPath $Path)) {
        return $null
    }
    $raw = [System.IO.File]::ReadAllText($Path)
    if ([string]::IsNullOrWhiteSpace($raw)) {
        return $null
    }
    return ($raw | ConvertFrom-Json)
}

function Test-MatchAnyPattern {
    param(
        [string] $InputValue,
        [object[]] $Patterns
    )
    foreach ($pattern in @($Patterns)) {
        if ($InputValue -like [string]$pattern) {
            return $true
        }
    }
    return $false
}

function Add-MissingLinksToGroupIndex {
    param(
        [string] $IndexPath,
        [string[]] $MissingRelPaths
    )

    if (-not (Test-Path -LiteralPath $IndexPath)) {
        return $false
    }

    $indexText = Get-FileText -Path $IndexPath
    $changed = $false
    $linesToAdd = @()
    foreach ($relPath in ($MissingRelPaths | Sort-Object -Unique)) {
        if (-not $indexText.Contains($relPath)) {
            $linesToAdd += "- ``$relPath``"
        }
    }

    if ($linesToAdd.Count -eq 0) {
        return $false
    }

    $sectionHeader = "## Auto-added by md_master_sync.ps1"
    if (-not $indexText.Contains($sectionHeader)) {
        if (-not $indexText.EndsWith("`n")) {
            $indexText += "`r`n"
        }
        $indexText += "`r`n$sectionHeader`r`n`r`n"
    }

    $indexText += ($linesToAdd -join "`r`n")
    $indexText += "`r`n"
    [System.IO.File]::WriteAllText($IndexPath, $indexText)
    $changed = $true
    return $changed
}

function Get-SuggestedMasterGuidePath {
    param(
        [string] $FileName,
        [string[]] $MasterGuidePaths,
        [object] $RulesConfig
    )

    if ($MasterGuidePaths.Count -eq 0) {
        return $null
    }

    if ($RulesConfig -and $RulesConfig.masterGuideRules) {
        foreach ($rule in @($RulesConfig.masterGuideRules)) {
            $masterGuideRel = [string]$rule.masterGuide
            $masterGuideAbs = Join-Path $ProjectRoot $masterGuideRel
            if (($MasterGuidePaths -contains $masterGuideAbs) -and (Test-MatchAnyPattern -InputValue $FileName -Patterns $rule.includePatterns)) {
                return $masterGuideAbs
            }
        }
    }

    $nameUpper = $FileName.ToUpperInvariant()
    $maolinGuide = $MasterGuidePaths | Where-Object { $_.ToUpperInvariant().Contains("MAOLIN_MASTER_GUIDE") } | Select-Object -First 1
    $warehouseGuide = $MasterGuidePaths | Where-Object { $_.ToUpperInvariant().Contains("WAREHOUSE_MASTER_GUIDE") } | Select-Object -First 1

    if ($nameUpper.Contains("MAOLIN") -or $nameUpper.Contains("IMPORT")) {
        if ($maolinGuide) { return $maolinGuide }
    }

    if ($nameUpper.Contains("WAREHOUSE") -or $nameUpper.Contains("PO_DO") -or $nameUpper.Contains("GRN")) {
        if ($warehouseGuide) { return $warehouseGuide }
    }

    return $null
}

function Add-MissingLinksToMasterGuide {
    param(
        [string] $GuidePath,
        [string[]] $MissingDocFileNames
    )

    if (-not (Test-Path -LiteralPath $GuidePath)) {
        return $false
    }

    $guideText = Get-FileText -Path $GuidePath
    $changed = $false
    $linesToAdd = @()
    foreach ($fileName in ($MissingDocFileNames | Sort-Object -Unique)) {
        if (-not $guideText.Contains($fileName)) {
            $linesToAdd += "- ``$fileName``"
        }
    }

    if ($linesToAdd.Count -eq 0) {
        return $false
    }

    $sectionHeader = "## Auto-added links by md_master_sync.ps1"
    if (-not $guideText.Contains($sectionHeader)) {
        if (-not $guideText.EndsWith("`n")) {
            $guideText += "`r`n"
        }
        $guideText += "`r`n$sectionHeader`r`n`r`n"
    }

    $guideText += ($linesToAdd -join "`r`n")
    $guideText += "`r`n"
    [System.IO.File]::WriteAllText($GuidePath, $guideText)
    $changed = $true
    return $changed
}

$groups = @("FUNC_BUG", "FUNC_IMPROVE", "FUNC_LOGIC", "FUNC_TEST")
$rulesConfig = Get-RulesConfig -Path $RulesPath
$rootFuncIndex = Join-Path $ProjectRoot "FUNC_INDEX.md"
$rootFuncIndexText = Get-FileText -Path $rootFuncIndex

$result = [ordered]@{
    project_root = $ProjectRoot
    generated_at = (Get-Date).ToString("s")
    checks = [ordered]@{
        root_func_index_exists = (Test-Path -LiteralPath $rootFuncIndex)
    }
    issues = @()
    stats = [ordered]@{
        total_groups = $groups.Count
        total_docs_scanned = 0
        missing_from_group_index = 0
        missing_from_master_guides = 0
        missing_from_both = 0
        missing_group_index_file = 0
        fixed_group_index_links = 0
        fixed_master_guide_links = 0
    }
}

$missingInGroupIndexByGroup = @{}
$missingForMasterByGuide = @{}
$docCatalog = @()

foreach ($group in $groups) {
    $groupDir = Join-Path $ProjectRoot $group
    if (-not (Test-Path -LiteralPath $groupDir)) {
        $result.issues += [ordered]@{
            type = "missing_group_directory"
            group = $group
            file = $groupDir
            message = "Group folder does not exist."
            suggest_update = @("Create folder or update script group list.")
        }
        continue
    }

    $groupIndexPath = Join-Path $groupDir "INDEX.md"
    $groupIndexExists = Test-Path -LiteralPath $groupIndexPath
    $groupIndexText = Get-FileText -Path $groupIndexPath
    $missingInGroupIndexByGroup[$group] = @()

    if (-not $groupIndexExists) {
        $result.stats.missing_group_index_file++
        $result.issues += [ordered]@{
            type = "missing_group_index"
            group = $group
            file = $groupIndexPath
            message = "Group INDEX.md is missing."
            suggest_update = @("Create $group/INDEX.md", "Add link in FUNC_INDEX.md")
        }
    }

    $masterGuides = @(Get-ChildItem -LiteralPath $groupDir -Filter "*MASTER_GUIDE*.md" -File -ErrorAction SilentlyContinue)
    $masterGuideTexts = @{}
    foreach ($guide in $masterGuides) {
        $masterGuideTexts[$guide.FullName] = Get-FileText -Path $guide.FullName
        if (-not $missingForMasterByGuide.ContainsKey($guide.FullName)) {
            $missingForMasterByGuide[$guide.FullName] = @()
        }
    }

    $docs = @(Get-ChildItem -LiteralPath $groupDir -Filter "*.md" -File -ErrorAction SilentlyContinue |
        Where-Object { $_.Name -ne "INDEX.md" })

    foreach ($doc in $docs) {
        $result.stats.total_docs_scanned++
        $relPath = ($doc.FullName.Substring($ProjectRoot.Length + 1)).Replace("\", "/")

        $inGroupIndex = $false
        if ($groupIndexText -and ($groupIndexText.Contains($relPath) -or $groupIndexText.Contains($doc.Name))) {
            $inGroupIndex = $true
        }

        $inAnyMasterGuide = $false
        $matchedGuides = @()
        foreach ($guidePath in $masterGuideTexts.Keys) {
            $text = $masterGuideTexts[$guidePath]
            if ($text -and ($text.Contains($relPath) -or $text.Contains($doc.Name))) {
                $inAnyMasterGuide = $true
                $matchedGuides += ($guidePath.Substring($ProjectRoot.Length + 1)).Replace("\", "/")
            }
        }

        $isNavigationDoc = (
            $doc.Name -ieq "README.md" -or
            $doc.Name -like "*INDEX*.md" -or
            $doc.Name -like "*MASTER_GUIDE*.md"
        )

        $suggestedGuidePath = $null
        if (-not $isNavigationDoc -and $masterGuides.Count -gt 0) {
            $suggestedGuidePath = Get-SuggestedMasterGuidePath -FileName $doc.Name -MasterGuidePaths @($masterGuideTexts.Keys) -RulesConfig $rulesConfig
        }

        $docCatalog += [ordered]@{
            group = $group
            file = $relPath
            file_name = $doc.Name
            is_navigation_doc = $isNavigationDoc
            suggested_master_guide = if ($suggestedGuidePath) { ($suggestedGuidePath.Substring($ProjectRoot.Length + 1)).Replace("\", "/") } else { "" }
        }

        if (-not $inGroupIndex) {
            $result.stats.missing_from_group_index++
            $missingInGroupIndexByGroup[$group] += $relPath
            $result.issues += [ordered]@{
                type = "doc_not_in_group_index"
                group = $group
                file = $relPath
                message = "Doc is not referenced in group INDEX.md."
                suggest_update = @("$group/INDEX.md")
            }
        }

        if (-not $isNavigationDoc -and $suggestedGuidePath -and -not $inAnyMasterGuide) {
            $result.stats.missing_from_master_guides++
            $guideNames = @($masterGuides | ForEach-Object { ($_.FullName.Substring($ProjectRoot.Length + 1)).Replace("\", "/") })
            if ($suggestedGuidePath) {
                $missingForMasterByGuide[$suggestedGuidePath] += $doc.Name
            }
            $result.issues += [ordered]@{
                type = "doc_not_in_master_guide"
                group = $group
                file = $relPath
                message = "Doc is not referenced by any module master guide in this group."
                suggest_update = $guideNames
            }
        }

        if (-not $isNavigationDoc -and -not $inGroupIndex -and -not $inAnyMasterGuide) {
            $result.stats.missing_from_both++
            $result.issues += [ordered]@{
                type = "doc_not_linked_anywhere"
                group = $group
                file = $relPath
                message = "Doc is missing from both group INDEX and master guide references."
                suggest_update = @("$group/INDEX.md", "Relevant *MASTER_GUIDE*.md")
            }
        }

    }

    if ($groupIndexExists) {
        $groupIndexRel = ($groupIndexPath.Substring($ProjectRoot.Length + 1)).Replace("\", "/")
        if (-not $rootFuncIndexText.Contains($groupIndexRel)) {
            $result.issues += [ordered]@{
                type = "group_index_not_in_root_func_index"
                group = $group
                file = $groupIndexRel
                message = "Group INDEX.md is not referenced in root FUNC_INDEX.md."
                suggest_update = @("FUNC_INDEX.md")
            }
        }
    }
}

if (-not [string]::IsNullOrWhiteSpace($Topic)) {
    $tokens = @(
        ($Topic.ToLowerInvariant() -split "[^a-z0-9_]+" | Where-Object { $_.Length -ge 2 })
    )
    $scored = @()
    foreach ($docMeta in $docCatalog) {
        $hay = ("{0} {1} {2}" -f $docMeta.file, $docMeta.file_name, $docMeta.suggested_master_guide).ToLowerInvariant()
        $score = 0
        foreach ($tk in $tokens) {
            if ($hay.Contains($tk)) { $score++ }
        }
        if ($score -gt 0) {
            $copy = [ordered]@{}
            foreach ($k in $docMeta.Keys) { $copy[$k] = $docMeta[$k] }
            $copy["topic_score"] = $score
            $scored += $copy
        }
    }

    $focusDocs = @($scored | Sort-Object -Property @{Expression="topic_score";Descending=$true}, @{Expression="file";Descending=$false} | Select-Object -First $Limit)
    $result["focus"] = [ordered]@{
        topic = $Topic
        limit = $Limit
        matched_docs = $focusDocs
    }
}

if ($Fix) {
    foreach ($group in $groups) {
        $groupDir = Join-Path $ProjectRoot $group
        $groupIndexPath = Join-Path $groupDir "INDEX.md"
        $missingList = @($missingInGroupIndexByGroup[$group])
        if ($missingList.Count -gt 0) {
            $fixed = Add-MissingLinksToGroupIndex -IndexPath $groupIndexPath -MissingRelPaths $missingList
            if ($fixed) {
                $result.stats.fixed_group_index_links += $missingList.Count
            }
        }
    }
}

if ($FixMaster) {
    foreach ($guidePath in $missingForMasterByGuide.Keys) {
        $missingList = @($missingForMasterByGuide[$guidePath])
        if ($missingList.Count -gt 0) {
            $fixed = Add-MissingLinksToMasterGuide -GuidePath $guidePath -MissingDocFileNames $missingList
            if ($fixed) {
                $result.stats.fixed_master_guide_links += $missingList.Count
            }
        }
    }
}

if ($AsJson) {
    $result | ConvertTo-Json -Depth 8
    exit 0
}

if ($Fix -and $FixMaster) {
    Write-Host "md_master_sync.ps1 (fix mode: group index + master guide links)"
} elseif ($Fix) {
    Write-Host "md_master_sync.ps1 (fix mode: group index links only)"
} elseif ($FixMaster) {
    Write-Host "md_master_sync.ps1 (fix mode: master guide links by heuristic)"
} else {
    Write-Host "md_master_sync.ps1 (read-only)"
}
Write-Host "Project root: $($result.project_root)"
Write-Host "Generated at : $($result.generated_at)"
Write-Host ""
Write-Host "Summary:"
Write-Host "  Total docs scanned            : $($result.stats.total_docs_scanned)"
Write-Host "  Missing from group INDEX      : $($result.stats.missing_from_group_index)"
Write-Host "  Missing from master guides    : $($result.stats.missing_from_master_guides)"
Write-Host "  Missing from both             : $($result.stats.missing_from_both)"
Write-Host "  Missing group INDEX files     : $($result.stats.missing_group_index_file)"
if ($Fix) {
    Write-Host "  Group index links auto-added  : $($result.stats.fixed_group_index_links)"
}
if ($FixMaster) {
    Write-Host "  Master guide links auto-added : $($result.stats.fixed_master_guide_links)"
}
Write-Host ""

if ($result.Contains("focus")) {
    Write-Host "Topic focus:"
    Write-Host "  Topic: $($result.focus.topic)"
    Write-Host "  Matched docs: $($result.focus.matched_docs.Count)"
    foreach ($m in @($result.focus.matched_docs)) {
        Write-Host ("  - [{0}] {1}" -f $m.topic_score, $m.file)
    }
    Write-Host ""
}

if ($result.issues.Count -eq 0) {
    Write-Host "No issues found. Navigation links look synchronized."
    exit 0
}

Write-Host "Action reminders:"
$result.issues | ForEach-Object {
    Write-Host "- [$($_.type)] $($_.file)"
    Write-Host "  -> $($_.message)"
    if ($_.suggest_update -and $_.suggest_update.Count -gt 0) {
        Write-Host "  -> Update: $([string]::Join(', ', $_.suggest_update))"
    }
}
