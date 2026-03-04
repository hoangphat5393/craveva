#!/bin/bash
set -e

cd /var/www/craveva-staging/current/craveva

sudo -u www-data php artisan down || true

DB_HOST=$(grep -E '^DB_HOST=' .env | cut -d '=' -f2- | tr -d '"' | tr -d '\r')
DB_PORT=$(grep -E '^DB_PORT=' .env | cut -d '=' -f2- | tr -d '"' | tr -d '\r')
DB_DATABASE=$(grep -E '^DB_DATABASE=' .env | cut -d '=' -f2- | tr -d '"' | tr -d '\r')
DB_USERNAME=$(grep -E '^DB_USERNAME=' .env | cut -d '=' -f2- | tr -d '"' | tr -d '\r')
DB_PASSWORD=$(grep -E '^DB_PASSWORD=' .env | cut -d '=' -f2- | tr -d '"' | tr -d '\r')

MYSQL_PWD="$DB_PASSWORD" mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" "$DB_DATABASE" < ~/hub_db.sql

sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan up
