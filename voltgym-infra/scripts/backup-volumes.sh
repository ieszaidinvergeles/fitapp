#!/usr/bin/env bash
# =============================================================================
# backup-volumes.sh
# Exports all Docker volumes to compressed tar archives.
# Run manually or add to cron for scheduled backups.
# Usage: bash backup-volumes.sh [output_dir]
# =============================================================================

set -euo pipefail

INFRA_DIR="/opt/voltgym/infra"
BACKUP_DIR="${1:-/opt/voltgym/backups}"
TIMESTAMP=$(date '+%Y%m%d_%H%M%S')
BACKUP_PATH="$BACKUP_DIR/$TIMESTAMP"

mkdir -p "$BACKUP_PATH"

echo "============================================"
echo " VOLT GYM — Volume Backup"
echo " $(date)"
echo " Output: $BACKUP_PATH"
echo "============================================"

# List of volumes to back up
VOLUMES=(
    "voltgym_infra_mysql_data"
    "voltgym_infra_laravel_storage"
    "voltgym_infra_wordpress_app"
    "voltgym_infra_wordpress_uploads"
    "voltgym_infra_redis_data"
)

for VOLUME in "${VOLUMES[@]}"; do
    echo "Backing up volume: $VOLUME ..."
    docker run --rm \
        -v "${VOLUME}:/data:ro" \
        -v "$BACKUP_PATH:/backup" \
        alpine \
        tar czf "/backup/${VOLUME}.tar.gz" -C /data . \
    && echo "  ✓ ${VOLUME}.tar.gz" \
    || echo "  ✗ FAILED: $VOLUME"
done

echo ""
echo "Backup complete. Files saved to: $BACKUP_PATH"
echo ""
ls -lh "$BACKUP_PATH"
