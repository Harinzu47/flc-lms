#!/bin/sh
set -e

# Cache configuration, routes, and views for production performance
echo "Warming up application cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Run migrations if this is the web server container (default or supervisord)
if [ "$1" = "/usr/bin/supervisord" ] || [ "$1" = "supervisord" ] || [ -z "$1" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
fi

# Set proper permissions for Laravel writeable directories
echo "Ensuring file permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Execute the requested command
if [ $# -eq 0 ]; then
    echo "Starting Supervisor..."
    exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
else
    echo "Executing command: $@"
    exec "$@"
fi
