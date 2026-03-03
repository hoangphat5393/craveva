echo "--- CHECKING SESSION SETUP ---"
grep "SESSION_DRIVER" /var/www/craveva-staging/current/craveva/.env
ls -ld /var/www/craveva-staging/current/craveva/storage/framework/sessions
ls -l /var/www/craveva-staging/current/craveva/storage/framework/sessions | head -n 5
