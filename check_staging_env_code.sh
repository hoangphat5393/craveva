echo "--- CHECKING User.php ---"
grep -n "public function userAuth" /var/www/craveva-staging/current/craveva/app/Models/User.php
grep -n "setPasswordAttribute" /var/www/craveva-staging/current/craveva/app/Models/User.php

echo "--- CHECKING .env ---"
grep "APP_URL" /var/www/craveva-staging/current/craveva/.env
grep "SESSION_" /var/www/craveva-staging/current/craveva/.env
grep "SANCTUM_STATEFUL_DOMAINS" /var/www/craveva-staging/current/craveva/.env
grep "REDIRECT_HTTPS" /var/www/craveva-staging/current/craveva/.env
