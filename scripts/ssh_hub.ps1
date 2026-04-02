# SSH vao craveva-hub-server qua gcloud (giong ssh_staging.ps1).
# VM: zone asia-southeast1-a — xem docs/FIREWALL_STATUS_HUB_AND_STAGING_DB.md
# Yeu cau: Google Cloud SDK, da chay gcloud auth login
#
# Chay:  .\scripts\ssh_hub.ps1
# User Linux khac mac dinh:  .\scripts\ssh_hub.ps1 -LinuxUser tenuser

param(
    [string]$LinuxUser = ""
)

$ErrorActionPreference = "Stop"

$Project = "craveva-org-55934-project"
$Zone = "asia-southeast1-a"
$Instance = "craveva-hub-server"

gcloud config set project $Project 2>$null

if ($LinuxUser) {
    gcloud compute ssh "${LinuxUser}@${Instance}" --zone=$Zone --project=$Project
}
else {
    gcloud compute ssh $Instance --zone=$Zone --project=$Project
}
