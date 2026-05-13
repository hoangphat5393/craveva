# SSH vào VM craveva-staging (GCP) — dùng script này thay cho `ssh craveva-staging` nếu bị Permission denied (publickey).
#
# Nguyên nhân: `~/.ssh/config` hay gán Host craveva-staging + User hoangphat5393 + IP.
# User hoangphat5393 trên VM là owner thư mục git (/var/www/...), KHÔNG phải tài khoản SSH mặc định của GCP cho máy này.
# `gcloud compute ssh --dry-run` dùng user Admin@<IP> + key google_compute_engine.
#
# Cách 1 (khuyến nghị): chạy script này.
# Cách 2: sửa ~/.ssh/config — Host craveva-staging: User Admin, IdentityFile ~/.ssh/google_compute_engine (OpenSSH).

$Project = "craveva-org-55934-project"
$Zone = "asia-southeast1-a"
$Instance = "craveva-staging"

gcloud config set project $Project 2>$null
gcloud compute ssh $Instance --zone=$Zone --project=$Project
