# SSH vao craveva-staging qua gcloud (VM o zone asia-southeast1-b)
# Chay script nay thay vi "ssh craveva-staging" de GCP tu them key va ket noi.

$Project = "craveva-org-55934-project"
$Zone = "asia-southeast1-b"
$Instance = "craveva-staging"

gcloud config set project $Project 2>$null
gcloud compute ssh $Instance --zone=$Zone --project=$Project
