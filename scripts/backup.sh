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
TEMP_DIR="$BACKUP_DIR/temp-$BACKUP_DATE"
BACKUP_DB="$TEMP_DIR/${BACKUP_DATE}-db.sql.gz"
BACKUP_UPLOADS="$TEMP_DIR/${BACKUP_DATE}-uploads.tar.gz"
BACKUP_PLUGINS="$TEMP_DIR/${BACKUP_DATE}-plugins.tar.gz"
BACKUP_THEMES="$TEMP_DIR/${BACKUP_DATE}-themes.tar.gz"
FINAL_BACKUP="$BACKUP_DIR/${BACKUP_DATE}-complete-backup.tar.gz"

echo "📦 Smart Gallery - Complete Backup"
echo "========================================"
echo "📅 Backup timestamp: $BACKUP_DATE"
echo "📁 Temporary directory: $TEMP_DIR"
echo "🎯 Final backup file: $FINAL_BACKUP"
echo ""

# Create temporary directory for individual backups
mkdir -p "$TEMP_DIR"

echo "🗄️  Exporting database to $BACKUP_DB ..."
ddev export-db --file="$BACKUP_DB"

echo "🗂️  Compressing uploads to $BACKUP_UPLOADS ..."
tar czf "$BACKUP_UPLOADS" wp-content/uploads

echo "🗂️  Compressing themes to $BACKUP_THEMES ..."
tar czf "$BACKUP_THEMES" wp-content/themes

echo "🗂️  Compressing plugins to $BACKUP_PLUGINS ..."
tar czf "$BACKUP_PLUGINS" wp-content/plugins

echo ""
echo "📦 Creating consolidated backup..."
echo "   Combining all backup files into: $FINAL_BACKUP"

# Create final consolidated backup from temporary directory
cd "$TEMP_DIR"
tar czf "../$(basename "$FINAL_BACKUP")" *.gz
cd - > /dev/null

# Clean up temporary directory
echo "🧹 Cleaning up temporary files..."
rm -rf "$TEMP_DIR"

# Show backup info
BACKUP_SIZE=$(du -h "$FINAL_BACKUP" | cut -f1)
echo ""
echo "✅ Backup completed successfully!"
echo "📊 Backup details:"
echo "   📁 File: $FINAL_BACKUP"
echo "   📏 Size: $BACKUP_SIZE"
echo "   📅 Date: $BACKUP_DATE"
echo ""
echo "💡 To restore this backup:"
echo "   ./scripts/restore.sh $BACKUP_DATE"