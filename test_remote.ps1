$ErrorActionPreference = "Stop"

$StagingHost = "craveva-staging"
$User = "trae"
$KeyFile = "$env:USERPROFILE\.ssh\google_compute_engine"
$StagingPath = "/var/www/craveva-staging/current/craveva"
$RemoteCommand = "cd $StagingPath && sudo -u www-data php -v"

Invoke-Expression "ssh -i '$KeyFile' -o StrictHostKeyChecking=no $User@$StagingHost '$RemoteCommand'"
