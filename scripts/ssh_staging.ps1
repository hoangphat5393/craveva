# SSH vao craveva-staging qua gcloud (VM zone asia-southeast1-a sau migrate snapshot 2026-05)
# Chay script nay thay vi "ssh craveva-staging" de GCP tu them key va ket noi.

$Project = "craveva-org-55934-project"
$Zone = "asia-southeast1-a"
$Instance = "craveva-staging"

gcloud config set project $Project 2>$null
gcloud compute ssh $Instance --zone=$Zone --project=$Project
