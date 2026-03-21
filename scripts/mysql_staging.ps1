# Open MySQL interactive CLI on staging using the SAME credentials as the Laravel app
# (reads config from .env on the server — no password pasted in this script).
#
# Usage (PowerShell, repo root):
#   .\scripts\mysql_staging.ps1
#
# Prerequisites:
#   - SSH works:  ssh craveva-staging
#   - If not, use scripts/ssh_staging.ps1 once, or:  gcloud compute config-ssh
#
# Equivalent manual command on the server:
#   cd /var/www/craveva-staging/current/craveva && sudo -u www-data php artisan db

$StagingHost = "craveva-staging"
$StagingPath = "/var/www/craveva-staging/current/craveva"

Write-Host "Connecting to staging MySQL via Laravel (php artisan db)..."
Write-Host "Exit the mysql client with: exit  or  Ctrl+D"
Write-Host ""
ssh -t $StagingHost "cd $StagingPath && sudo -u www-data php artisan db"
