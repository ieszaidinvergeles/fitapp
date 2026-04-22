#!/usr/bin/env bash
# =============================================================================
# auto-update.sh
# Runs every 2 hours via cron.
# Checks the remote repository for new commits on the configured branch.
# If changes are found, pulls the code and updates the app without touching
# database data, WordPress uploads, or Redis data.
# =============================================================================

set -euo pipefail

INFRA_DIR="/opt/voltgym/infra"
LARAVEL_DIR="/opt/voltgym/laravel"
LOG_PREFIX="[$(date '+%Y-%m-%d %H:%M:%S')] [auto-update]"

source "$INFRA_DIR/.env"

cd "$LARAVEL_DIR"

echo "$LOG_PREFIX Checking for remote changes on branch '$REPO_BRANCH'..."

# Fetch without merging
git fetch origin "$REPO_BRANCH" --quiet

LOCAL_HASH=$(git rev-parse HEAD)
REMOTE_HASH=$(git rev-parse "origin/$REPO_BRANCH")

if [ "$LOCAL_HASH" = "$REMOTE_HASH" ]; then
    echo "$LOG_PREFIX No changes detected. Nothing to do."
    exit 0
fi

echo "$LOG_PREFIX Changes detected. Local: $LOCAL_HASH → Remote: $REMOTE_HASH"
echo "$LOG_PREFIX Starting zero-downtime update..."

# ─── Pull latest code ─────────────────────────────────────────────────────────
git reset --hard "origin/$REPO_BRANCH"
echo "$LOG_PREFIX Code pulled successfully."

# ─── Update Laravel inside the running container ──────────────────────────────
cd "$INFRA_DIR"

echo "$LOG_PREFIX Running Laravel update steps..."
docker compose exec -T laravel bash -c "
    cd /var/www/laravel &&

    # Install/update composer deps (no-dev, optimized)
    composer install --no-dev --optimize-autoloader --no-interaction --quiet &&

    # Run any new migrations (never destructive — only additive)
    php artisan migrate --force &&

    # Clear and rebuild caches
    php artisan config:clear &&
    php artisan route:clear &&
    php artisan view:clear &&
    php artisan config:cache &&
    php artisan route:cache &&
    php artisan view:cache &&

    # Fix permissions on storage
    chmod -R 775 storage bootstrap/cache &&
    chown -R www-data:www-data storage bootstrap/cache
" && echo "$LOG_PREFIX Laravel update complete."

# ─── Restart queue worker to pick up new code ─────────────────────────────────
echo "$LOG_PREFIX Restarting queue worker..."
docker compose restart queue
echo "$LOG_PREFIX Queue worker restarted."

# ─── Update WordPress theme/plugin if changed ─────────────────────────────────
# Only copies theme and plugin — never touches wp-content/uploads or wp-config
WP_THEME_SRC="$LARAVEL_DIR/../wordpress/wordpress-theme"
if [ -d "$WP_THEME_SRC" ]; then
    echo "$LOG_PREFIX Syncing WordPress theme..."
    docker compose exec -T wordpress bash -c "
        rsync -a --delete /tmp/theme/ /var/www/wordpress/wp-content/themes/voltgym/ 2>/dev/null || true
    "
    # Copy theme into container tmp first
    docker cp "$WP_THEME_SRC/." "voltgym_wordpress:/tmp/theme/"
    docker compose exec -T wordpress bash -c "
        rsync -a --delete /tmp/theme/ /var/www/wordpress/wp-content/themes/voltgym/
        chown -R www-data:www-data /var/www/wordpress/wp-content/themes/voltgym/
    "
    echo "$LOG_PREFIX WordPress theme synced."
fi

echo "$LOG_PREFIX ✓ Auto-update finished successfully. New commit: $REMOTE_HASH"
