#!/bin/bash
# Script for complete WordPress site backup
# Saves database and uploads to synchronized directory (OneDrive)

set -e

if [ ! -d "./backups" ]; then
	echo "⚠️ Directory ./backups not found! Run first the script ./scripts/map_backup_dir.sh to configure backup destination."
	exit 1
fi

# Backup directory path (adjust as needed)
BACKUP_DIR="./backups/"

# Create directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

BACKUP_DATE="$(date +%Y%m%d-%H%M%S)"
BACKUP_DB="$BACKUP_DIR/${BACKUP_DATE}-db.sql.gz"
BACKUP_UPLOADS="$BACKUP_DIR/${BACKUP_DATE}-uploads.tar.gz"
BACKUP_PLUGINS="$BACKUP_DIR/${BACKUP_DATE}-plugins.tar.gz"
BACKUP_THEMES="$BACKUP_DIR/${BACKUP_DATE}-themes.tar.gz"

echo "🗄️  Exporting database to $BACKUP_DB ..."
ddev export-db --file="$BACKUP_DB"

echo "🗂️  Compressing uploads to $BACKUP_UPLOADS ..."
tar czf "$BACKUP_UPLOADS" wp-content/uploads

echo "🗂️  Compressing themes to $BACKUP_THEMES ..."
tar czf "$BACKUP_THEMES" wp-content/themes

echo "🗂️  Compressing plugins to $BACKUP_PLUGINS ..."
tar czf "$BACKUP_PLUGINS" wp-content/plugins

echo "✅ Backup completed."