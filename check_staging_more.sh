echo "--- AttemptToAuthenticate.php (Part 2) ---"
tail -n +100 /var/www/craveva-staging/current/craveva/app/Actions/Fortify/AttemptToAuthenticate.php
echo "--- STORAGE PERMS ---"
ls -ld /var/www/craveva-staging/current/craveva/storage/framework/views
ls -ld /var/www/craveva-staging/current/craveva/storage/logs
echo "--- LOG CONTENT (Yesterday) ---"
cat /var/www/craveva-staging/current/craveva/storage/logs/laravel-2026-03-02.log
