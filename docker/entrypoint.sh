#!/bin/sh

set -e

echo "========================================"
echo "Starting Laravel Production Container"
echo "========================================"

mkdir -p storage bootstrap/cache

chmod -R ug+rwx storage bootstrap/cache

# Hanya jalankan cache-build & migration di container "app" (php-fpm).
# Container worker & scheduler pakai image yang sama tapi command berbeda,
# jadi tanpa guard ini ketiganya akan migrate bareng-bareng (race condition).
if [ "$1" = "php-fpm" ]; then

    echo "-> Running app bootstrap (cache + migrate)..."

    php artisan optimize:clear

    php artisan config:cache

    php artisan route:cache || true

    php artisan event:cache || true

    php artisan view:cache

    php artisan migrate --force

    echo "Laravel Ready."

else

    echo "-> Skipping bootstrap for command: $*"

fi

exec "$@"
