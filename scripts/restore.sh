#!/bin/bash
# Script to restore complete WordPress site backup
# Restores database, uploads, plugins and themes from synchronized directory (OneDrive)

set -e

if [ ! -d "./backups" ]; then
    echo "⚠️  Directory ./backups not found! Run first:"
    echo "$ ./scripts/map_backup_dir.sh /path/to/backup/directory"
    exit 1
fi

# Backup directory path (adjust as needed)
BACKUP_DIR="./backups"

# Check if date was passed as argument
if [ -z "$1" ]; then
    echo "Usage: $0 <DATE>"
    echo "Example: $0 20250827-153000"
    exit 1
fi
RESTORE_DATE="$1"

# Restore database
BACKUP_DB="$BACKUP_DIR/${RESTORE_DATE}-db.sql.gz"
if [ -f "$BACKUP_DB" ]; then
    echo "Restoring database from $BACKUP_DB..."
    ddev import-db --file="$BACKUP_DB"
else
    echo "❌ Database file not found for date $RESTORE_DATE."
fi

# Restore uploads
BACKUP_UPLOADS="$BACKUP_DIR/${RESTORE_DATE}-uploads.tar.gz"
if [ -f "$BACKUP_UPLOADS" ]; then
    echo "🗂️  Restoring uploads from $BACKUP_UPLOADS..."
    tar xzf "$BACKUP_UPLOADS"
else
    echo "❌ Uploads file not found for date $RESTORE_DATE."
fi

# Restore plugins
BACKUP_PLUGINS="$BACKUP_DIR/${RESTORE_DATE}-plugins.tar.gz"
if [ -f "$BACKUP_PLUGINS" ]; then
    echo "🗂️  Restoring plugins from $BACKUP_PLUGINS..."
    tar xzf "$BACKUP_PLUGINS"
else
    echo "❌ Plugins file not found for date $RESTORE_DATE."
fi

# Restore themes
BACKUP_THEMES="$BACKUP_DIR/${RESTORE_DATE}-themes.tar.gz"
if [ -f "$BACKUP_THEMES" ]; then
    echo "🗂️  Restoring themes from $BACKUP_THEMES..."
    tar xzf "$BACKUP_THEMES"
else
    echo " ❌ Themes file not found for date $RESTORE_DATE."
fi

echo "✅ Restore completed."