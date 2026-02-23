$ErrorActionPreference = "Stop"
$StagingHost = "craveva-staging"
$RemoteCommand = "cd /var/www/craveva-staging/current/craveva && sudo -u www-data php artisan migrate:refresh --path=database/migrations/2026_02_02_130000_fix_pricing_visibility_final.php --force"
ssh -t $StagingHost $RemoteCommand
