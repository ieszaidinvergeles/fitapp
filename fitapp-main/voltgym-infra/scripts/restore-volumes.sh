#!/usr/bin/env bash
# =============================================================================
# restore-volumes.sh
# Restores Docker volumes from a backup created by backup-volumes.sh.
# WARNING: This overwrites current volume data.
# Usage: bash restore-volumes.sh /opt/voltgym/backups/20260422_120000
# =============================================================================

set -euo pipefail

BACKUP_PATH="${1:-}"

if [ -z "$BACKUP_PATH" ] || [ ! -d "$BACKUP_PATH" ]; then
    echo "Usage: bash restore-volumes.sh /path/to/backup/timestamp_folder"
    exit 1
fi

echo "============================================"
echo " VOLT GYM — Volume Restore"
echo " Source: $BACKUP_PATH"
echo "============================================"
echo ""
read -p "WARNING: This will overwrite current volume data. Continue? (yes/no): " CONFIRM
if [ "$CONFIRM" != "yes" ]; then
    echo "Aborted."
    exit 0
fi

# Stop services before restoring (except mysql which needs its volume)
cd /opt/voltgym/infra
docker compose down

for ARCHIVE in "$BACKUP_PATH"/*.tar.gz; do
    VOLUME=$(basename "$ARCHIVE" .tar.gz)
    echo "Restoring volume: $VOLUME ..."
    docker run --rm \
        -v "${VOLUME}:/data" \
        -v "$BACKUP_PATH:/backup:ro" \
        alpine \
        sh -c "rm -rf /data/* /data/..?* /data/.[!.]* 2>/dev/null; tar xzf /backup/$(basename $ARCHIVE) -C /data" \
    && echo "  ✓ $VOLUME" \
    || echo "  ✗ FAILED: $VOLUME"
done

echo ""
echo "Restore complete. Starting services..."
docker compose up -d
echo "Done."
