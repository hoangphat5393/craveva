#!/bin/bash

# Fix permissions
echo "Fixing permissions for storage and bootstrap/cache..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Clear Laravel cache
echo "Clearing Laravel cache..."
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Run migrations (force for production)
echo "Running migrations..."
php artisan migrate --force

# Dump composer autoload
echo "Dumping autoload..."
composer dump-autoload

echo "Staging fix completed."
