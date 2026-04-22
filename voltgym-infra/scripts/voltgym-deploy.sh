#!/usr/bin/env bash
# =============================================================================
# voltgym-deploy.sh
# Simple deployment script for the Volt Gym infrastructure.
# Works out of the box after cloning the repository.
# Usage: bash voltgym-deploy.sh
# =============================================================================

set -euo pipefail

# ─── Config ───────────────────────────────────────────────────────────────────
# Detect the project directory dynamically from the script location
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
INFRA_DIR="$PROJECT_DIR/voltgym-infra"

echo "============================================"
echo " VOLT GYM — Deployment Initialization"
echo " Project Directory: $PROJECT_DIR"
echo " $(date)"
echo "============================================"

# ─── 1. Environment Setup ─────────────────────────────────────────────────────
echo "[1/4] Checking environment configuration..."
cd "$INFRA_DIR"
if [ ! -f ".env" ]; then
    echo "  .env not found in infra directory. Creating from example..."
    cp .env.example .env
    echo "  Please update .env with actual passwords if in production!"
else
    echo "  .env already exists."
fi

# Load variables
source .env

# ─── 2. Build and start Docker services ──────────────────────────────────────
echo "[2/4] Building Docker images and starting services..."
docker compose build --no-cache
docker compose up -d

# ─── 3. Wait for MySQL to be healthy ─────────────────────────────────────────
echo "[3/4] Waiting for MySQL to be ready..."
until docker compose exec -T mysql mysqladmin ping -h localhost -u root -p"$DB_ROOT_PASSWORD" --silent 2>/dev/null; do
    echo "  MySQL not ready yet, waiting 5s..."
    sleep 5
done
echo "  MySQL is ready."

# ─── 4. Install the auto-update cron (Optional) ──────────────────────────────
echo "[4/4] Installing auto-update cron (every 2 hours)..."
CRON_JOB="0 */2 * * * /bin/bash $INFRA_DIR/scripts/auto-update.sh >> /var/log/voltgym-update.log 2>&1"
( crontab -l 2>/dev/null | grep -v "auto-update.sh"; echo "$CRON_JOB" ) | crontab - 2>/dev/null || echo "  Warning: crontab not available on this host. Skipping cron installation."
if [ $? -eq 0 ]; then
    echo "  Cron installed: $CRON_JOB"
fi

# ─── Done ──────────────────────────────────────────────────────────────────
echo ""
echo "============================================"
echo " Deploy complete."
echo " Note: Laravel dependencies and migrations are handled automatically"
echo " by the container entrypoint. Wait a few moments before accessing the API."
echo ""
echo "  Laravel API :  http://${APP_URL:-localhost}"
echo "  WordPress   :  http://${APP_URL:-localhost}:8000"
echo "  Adminer     :  http://${APP_URL:-localhost}:8080"
echo "  DB host     :  voltgym_mysql (internal) / port 3306"
echo "============================================"
