#!/usr/bin/env bash
# =============================================================================
# voltgym-deploy.sh
# First-time deployment script for the Volt Gym infrastructure on Ubuntu Server.
# Run as a user with sudo and Docker permissions.
# Usage: bash voltgym-deploy.sh
# =============================================================================

set -euo pipefail

# ─── Config ───────────────────────────────────────────────────────────────────
INSTALL_DIR="/opt/voltgym"
INFRA_DIR="$INSTALL_DIR/infra"
LARAVEL_DIR="$INSTALL_DIR/laravel"
WORDPRESS_DIR="$INSTALL_DIR/wordpress"
LOG_FILE="/var/log/voltgym-deploy.log"

source "$INFRA_DIR/.env"

echo "============================================"
echo " VOLT GYM — First Deploy"
echo " $(date)"
echo "============================================"

# ─── 1. Clone or update application code ─────────────────────────────────────
echo "[1/7] Cloning application repository..."
if [ ! -d "$LARAVEL_DIR/.git" ]; then
    git clone --branch "$REPO_BRANCH" "$REPO_URL" "$LARAVEL_DIR"
else
    echo "  Repository already exists, pulling latest..."
    git -C "$LARAVEL_DIR" fetch origin
    git -C "$LARAVEL_DIR" reset --hard "origin/$REPO_BRANCH"
fi

# ─── 2. Build and start Docker services ──────────────────────────────────────
echo "[2/7] Building Docker images and starting services..."
cd "$INFRA_DIR"
docker compose build --no-cache
docker compose up -d

# ─── 3. Wait for MySQL to be healthy ─────────────────────────────────────────
echo "[3/7] Waiting for MySQL to be ready..."
until docker compose exec -T mysql mysqladmin ping -h localhost -u root -p"$DB_ROOT_PASSWORD" --silent 2>/dev/null; do
    echo "  MySQL not ready yet, waiting 5s..."
    sleep 5
done
echo "  MySQL is ready."

# ─── 4. Install Laravel dependencies and bootstrap ───────────────────────────
echo "[4/7] Installing Laravel dependencies..."
docker compose exec -T laravel bash -c "
    cd /var/www/laravel &&
    composer install --no-dev --optimize-autoloader --no-interaction &&
    php artisan key:generate --force &&
    php artisan migrate --force &&
    php artisan db:seed --force &&
    php artisan config:cache &&
    php artisan route:cache &&
    php artisan view:cache &&
    php artisan storage:link &&
    chmod -R 775 storage bootstrap/cache &&
    chown -R www-data:www-data storage bootstrap/cache
"

# ─── 5. Set up WordPress volume with code from repo ──────────────────────────
echo "[5/7] Copying WordPress theme and plugin into volume..."
docker compose exec -T wordpress bash -c "
    cp -r /var/www/html/. /var/www/wordpress/ 2>/dev/null || true
"

# ─── 6. Install the auto-update cron ─────────────────────────────────────────
echo "[6/7] Installing auto-update cron (every 2 hours)..."
CRON_JOB="0 */2 * * * /bin/bash $INFRA_DIR/scripts/auto-update.sh >> /var/log/voltgym-update.log 2>&1"
( crontab -l 2>/dev/null | grep -v "auto-update.sh"; echo "$CRON_JOB" ) | crontab -
echo "  Cron installed: $CRON_JOB"

# ─── 7. Done ──────────────────────────────────────────────────────────────────
echo "[7/7] Deploy complete."
echo ""
echo "  Laravel API :  http://$APP_URL"
echo "  WordPress   :  http://$APP_URL:8000"
echo "  Adminer     :  http://$APP_URL:8080"
echo "  DB host     :  voltgym_mysql (internal) / port 3306"
echo ""
echo "  Auto-update cron is active — checks for repo changes every 2 hours."
echo "  Logs: /var/log/voltgym-update.log"
