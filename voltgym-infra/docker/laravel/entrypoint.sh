#!/bin/bash
#
# Environment initialization script for the Laravel container.
#
# Responsibility: Prepares the runtime environment, ensures database consistency, 
#                 and configures filesystem access rights before starting PHP-FPM.
# Reliability: Uses idempotent commands to ensure safe execution on every container start.
#
set -e

# We ensure we are in the correct directory
cd /var/www/laravel

echo "Checking environment setup..."

# Ensures the presence of a configuration file.
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        echo "Copying .env.example to .env..."
        cp .env.example .env
    fi
fi

# Automates dependency availability.
if [ ! -d "vendor" ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction --optimize-autoloader
fi

# Secures the application with a unique cryptographic key.
if grep -q "^APP_KEY=$" .env 2>/dev/null || [ -z "$APP_KEY" ]; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force
fi

# Synchronizes the database schema with the application's latest migrations.
# This ensures the persistence layer is always compatible with the application code.
echo "Synchronizing database schema..."
php artisan migrate --force

# Optimizes the framework's internal caches and symbolic links.
echo "Optimizing caches and storage..."
php artisan optimize:clear
php artisan storage:link || true

# Enforces strict filesystem permissions to protect sensitive directories.
echo "Applying filesystem permissions..."
chmod -R 775 storage bootstrap/cache public/uploads 2>/dev/null || true
chown -R www-data:www-data storage bootstrap/cache public/uploads 2>/dev/null || true

# Pre-configures the storage structure for user-generated content.
echo "Bootstrapping image storage structure..."
PUBLIC_IMAGES_BASE="public/uploads/images"
for DIR in users exercises equipment recipes routines diet_plans rooms activities membership_plans gyms; do
    mkdir -p "${PUBLIC_IMAGES_BASE}/${DIR}"
done
chown -R www-data:www-data "${PUBLIC_IMAGES_BASE}" 2>/dev/null || true

echo "Starting PHP-FPM..."
exec "$@"
