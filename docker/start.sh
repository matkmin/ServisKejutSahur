#!/bin/sh
set -e

# Clear caches to ensure runtime env vars are picked up
php /var/www/html/artisan config:clear
php /var/www/html/artisan cache:clear
php /var/www/html/artisan route:clear

# Run Migrations
php /var/www/html/artisan migrate --force

# Start Supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
