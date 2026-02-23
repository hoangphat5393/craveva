# PowerShell Script: Upload custom.js to craveva-hub-server
$ErrorActionPreference = "Stop"

$HubHost = "craveva-hub-server"
$HubPath = "/var/www/hub.craveva.com"
$LocalTempDir = ".deploy_js_tmp"
$ZipFile = "deploy_js.zip"

Write-Host "Starting upload to $HubHost (Custom JS only)..."

# 1. Clean up previous runs
if (Test-Path $LocalTempDir) { Remove-Item -Recurse -Force $LocalTempDir }
if (Test-Path $ZipFile) { Remove-Item -Force $ZipFile }

New-Item -ItemType Directory -Force -Path $LocalTempDir | Out-Null

# 2. Define files to upload
$FilesToCopy = @(
    "public/js/custom.js",
    "deploy_zipper.php"
)

# 3. Copy files to temp dir
Write-Host "Preparing files..."
foreach ($File in $FilesToCopy) {
    if (Test-Path $File) {
        $Dest = Join-Path $LocalTempDir $File
        $Parent = Split-Path $Dest
        if (-not (Test-Path $Parent)) { New-Item -ItemType Directory -Force -Path $Parent | Out-Null }
        Copy-Item $File $Dest
    }
    else {
        Write-Warning "File not found: $File"
    }
}

# 4. Zip files
Write-Host "Compressing files using PHP..."
php deploy_zipper.php $LocalTempDir $ZipFile

# 5. Upload Zip
Write-Host "Uploading zip package to home directory (~/$ZipFile)..."
scp $ZipFile "${HubHost}:$ZipFile"

# 6. Extract on server
Write-Host "Extracting on server and deploying to $HubPath (sudo may be required)..."
$RemoteCommand = "sudo mv ~/$ZipFile $HubPath/$ZipFile && cd $HubPath"
$RemoteCommand += " && sudo unzip -o $ZipFile && sudo rm $ZipFile"
$RemoteCommand += " && sudo chown www-data:www-data $HubPath/public/js/custom.js"
$RemoteCommand += " && echo 'Deployed custom.js successfully'"

ssh -t "${HubHost}" $RemoteCommand

# 7. Local Cleanup
Remove-Item -Recurse -Force $LocalTempDir
Remove-Item -Force $ZipFile

Write-Host "----------------------------------------------------------------"
Write-Host "Upload of custom.js to $HubHost complete!"
Write-Host "----------------------------------------------------------------"
