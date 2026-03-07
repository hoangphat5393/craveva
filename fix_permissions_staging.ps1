$StagingHost = "craveva-staging"
$StagingPath = "/var/www/craveva-staging/current/craveva"

Write-Host "Fixing permissions on $StagingHost..."

# 1. Fix ownership and permissions
$RemoteCommand = "cd $StagingPath"
$RemoteCommand += " && sudo chown -R www-data:www-data storage bootstrap/cache"
$RemoteCommand += " && sudo chmod -R 775 storage bootstrap/cache"

# 2. ACL: Cho phep ca www-data VA user SSH hien tai ghi vao storage (triet de)
#    Default ACL: file moi tao se ke thua quyen -> khong bi loi sau moi lan chay
$RemoteCommand += " && SSH_USER=`$(whoami)"
$RemoteCommand += " && sudo setfacl -R -m u:www-data:rwX -m u:`$SSH_USER:rwX storage bootstrap/cache 2>/dev/null || true"
$RemoteCommand += " && sudo setfacl -dR -m u:www-data:rwX -m u:`$SSH_USER:rwX storage bootstrap/cache 2>/dev/null || true"

# 3. Clear cache
$RemoteCommand += " && sudo -u www-data php artisan optimize:clear"

Write-Host "Executing remote commands..."
ssh $StagingHost $RemoteCommand

Write-Host "Permissions fixed and cache cleared!"
