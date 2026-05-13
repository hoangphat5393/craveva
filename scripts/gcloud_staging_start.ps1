# Bật VM staging trên GCP + set project (không tương tác).
# `gcloud auth login hoangphat5393@gmail.com` phải chạy tay một lần trong terminal (mở trình duyệt).
# Sau đó: .\scripts\gcloud_staging_start.ps1

$ErrorActionPreference = "Stop"
$Project = "craveva-org-55934-project"
$Zone = "asia-southeast1-a"
$Instance = "craveva-staging"

gcloud config set project $Project
gcloud compute instances start $Instance --zone=$Zone --project=$Project
Write-Host "Done. SSH: dung .\scripts\ssh_staging.ps1 (user Admin + key GCP), khong dung User hoangphat5393 trong ssh config."
