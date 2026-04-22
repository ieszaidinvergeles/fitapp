#!/bin/sh
set -e

# Wait for MySQL
echo "Waiting for MySQL..."
until mysqladmin ping -h"$DB_HOST" --silent; do
  sleep 2
done
echo "MySQL is up."

# Run Laravel migrations (idempotent)
php artisan migrate --force

# Execute the main command (php-fpm)
exec "$@"