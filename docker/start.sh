#!/bin/sh
set -e

# Clear config first to ensure DB credentials are loaded
php /var/www/html/artisan config:clear

# Run Migrations (Creates 'cache' table if missing)
php /var/www/html/artisan migrate --force

# Now it's safe to clear other caches
php /var/www/html/artisan cache:clear
php /var/www/html/artisan route:clear
php /var/www/html/artisan storage:link

# Start Supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
