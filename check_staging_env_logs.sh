echo "--- ENV CHECK ---"
grep "APP_NAME" /var/www/craveva-staging/current/craveva/.env
grep "APP_ENV" /var/www/craveva-staging/current/craveva/.env
grep "APP_DEBUG" /var/www/craveva-staging/current/craveva/.env
echo "--- LOG CHECK ---"
tail -n 50 /var/www/craveva-staging/current/craveva/storage/logs/laravel.log
