#!/usr/bin/env bash
# =============================================================================
# auto-update.sh
# Runs every 2 hours via cron.
# Checks the remote repository for new commits on the configured branch.
# If changes are found, pulls the code and updates the app without touching
# database data, WordPress uploads, or Redis data.
# =============================================================================

set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
INFRA_DIR="$PROJECT_DIR/voltgym-infra"
LOG_PREFIX="[$(date '+%Y-%m-%d %H:%M:%S')] [auto-update]"

cd "$INFRA_DIR"
source .env

cd "$PROJECT_DIR"

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

# ─── Ensure dependencies are correctly updated ────────────────────────────────
cd "$INFRA_DIR"

echo "$LOG_PREFIX Running Laravel update steps..."
docker compose exec -T laravel bash -c "
    cd /var/www/laravel &&
    composer install --no-dev --optimize-autoloader --no-interaction --quiet &&
    php artisan migrate --force &&
    php artisan optimize:clear &&
    chmod -R 775 storage bootstrap/cache &&
    chown -R www-data:www-data storage bootstrap/cache
" && echo "$LOG_PREFIX Laravel update complete."

# ─── Restart queue worker to pick up new code ─────────────────────────────────
echo "$LOG_PREFIX Restarting queue worker..."
docker compose restart queue
echo "$LOG_PREFIX Queue worker restarted."

# Since WordPress code is now a local bind mount, git pull automatically syncs it!
echo "$LOG_PREFIX WordPress theme naturally synced via bind mount."

echo "$LOG_PREFIX ✓ Auto-update finished successfully. New commit: $REMOTE_HASH"
