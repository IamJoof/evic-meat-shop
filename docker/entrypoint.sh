#!/bin/sh
set -e

# Ensure storage and bootstrap cache directories have correct permissions
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/database
mkdir -p /var/log/supervisor /var/log/nginx

touch /var/www/html/database/database.sqlite
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Ensure valid APP_KEY exists
if [ -z "$APP_KEY" ]; then
    echo "APP_KEY is missing. Generating application key..."
    php artisan key:generate --force
fi

# Run database migrations (and seed if empty)
php artisan migrate --force
php artisan db:seed --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start supervisor
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
