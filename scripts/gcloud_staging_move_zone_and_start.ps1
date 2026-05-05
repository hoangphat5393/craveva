# LEGACY: Google removed "gcloud compute instances move". Use snapshot migration instead:
# https://cloud.google.com/compute/docs/instances/moving-instance-across-zones
#
# Staging hiện ở zone `asia-southeast1-a` (migrate snapshot 2026-05). Script cũ (a->b) chỉ giữ tham chiếu.

$ErrorActionPreference = "Stop"
Write-Host "SCRIPT DEPRECATED: instances move was removed from gcloud. See doc above." -ForegroundColor Red
Write-Host "Staging zone: asia-southeast1-a (scripts/ssh_staging.ps1)." -ForegroundColor Yellow
exit 1
