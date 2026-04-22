#!/bin/bash
set -e

# We ensure we are in the correct directory
cd /var/www/laravel

echo "Checking environment setup..."

# Check if .env exists, if not, copy from .env.example
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        echo "Copying .env.example to .env..."
        cp .env.example .env
    else
        echo "Warning: .env and .env.example not found in Laravel root."
    fi
fi

# Check if vendor folder exists (i.e. composer dependencies are installed)
if [ ! -d "vendor" ]; then
    echo "Composer dependencies not found. Installing..."
    composer install --no-interaction --optimize-autoloader
else
    echo "Composer dependencies already installed."
fi

# Try running key:generate if key is not set
# artisan key:generate returns an error if run in production and no key exists without --force
if grep -q "^APP_KEY=$" .env 2>/dev/null; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force
fi

echo "Running migrations..."
# We try to run migrations. If DB is not fully ready, this might fail,
# but the deploy script or docker-compose wait depends_on condition (service_healthy)
# should prevent the laravel container from starting before MySQL is ready.
php artisan migrate --force

echo "Optimizing caches..."
php artisan optimize:clear
php artisan storage:link || true

echo "Fixing permissions on storage and bootstrap/cache..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

echo "Starting PHP-FPM..."
exec "$@"
