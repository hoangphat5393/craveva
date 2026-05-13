# Gắn public key id_rsa_gcp.pub vào metadata ssh-keys của VM craveva-staging với prefix hoangphat5393:
# (guest OS tạo user / authorized_keys). Chạy từ máy có gcloud + file pubkey.
#
# Sau đó trong ~/.ssh/config dùng User hoangphat5393 + IdentityFile id_rsa_gcp cho Host craveva-staging.

$ErrorActionPreference = "Stop"
$Project = "craveva-org-55934-project"
$Zone = "asia-southeast1-a"
$Instance = "craveva-staging"
$PubPath = Join-Path $env:USERPROFILE ".ssh\id_rsa_gcp.pub"

if (-not (Test-Path $PubPath)) {
    throw "Thieu $PubPath"
}

$pub = (Get-Content $PubPath -Raw).Trim() -replace "`r`n", ""
if ($pub -notmatch '^ssh-rsa ') {
    throw "Chi ho tro ssh-rsa trong script nay; pubkey: $PubPath"
}

$newLine = "hoangphat5393:$pub"

$json = gcloud compute instances describe $Instance --zone=$Zone --project=$Project --format="json(metadata.items)" | ConvertFrom-Json
$item = $json.metadata.items | Where-Object { $_.key -eq 'ssh-keys' }
if (-not $item) {
    throw "Instance khong co metadata ssh-keys"
}

$current = $item.value
if ($current.Contains($newLine.Trim())) {
    Write-Host "Key da ton tai trong ssh-keys, bo qua."
    exit 0
}

$keyBody = $pub.Substring(6)
$already = $false
foreach ($line in ($current -split "`n")) {
    if ($line.StartsWith("hoangphat5393:") -and $line.Contains($keyBody.Substring(0, [Math]::Min(40, $keyBody.Length)))) {
        $already = $true
        break
    }
}

if ($already) {
    Write-Host "hoangphat5393 da co key trung noi dung, bo qua."
    exit 0
}

$merged = $current.TrimEnd("`n") + "`n" + $newLine + "`n"
$tmp = [System.IO.Path]::GetTempFileName()
[System.IO.File]::WriteAllText($tmp, $merged, [System.Text.UTF8Encoding]::new($false))
try {
    gcloud compute instances add-metadata $Instance --zone=$Zone --project=$Project --metadata-from-file ssh-keys=$tmp
    Write-Host "Da them dong hoangphat5393: + id_rsa_gcp vao metadata ssh-keys."
} finally {
    Remove-Item $tmp -Force -ErrorAction SilentlyContinue
}
