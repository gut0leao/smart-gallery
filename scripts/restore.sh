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

echo "📦 Smart Gallery - Restore Backup"
echo "========================================"

# Function to find the latest backup
find_latest_backup() {
    local latest=$(ls -t "$BACKUP_DIR"/*-complete-backup.tar.gz 2>/dev/null | head -n 1)
    if [ -n "$latest" ]; then
        basename "$latest" | sed 's/-complete-backup\.tar\.gz$//'
    fi
}

# Function to list available backups
list_backups() {
    echo "📋 Available backups:"
    ls -la "$BACKUP_DIR"/*-complete-backup.tar.gz 2>/dev/null | while read -r line; do
        file=$(echo "$line" | awk '{print $NF}')
        date_part=$(basename "$file" | sed 's/-complete-backup\.tar\.gz$//')
        size=$(echo "$line" | awk '{print $5}')
        echo "   📁 $date_part (Size: $size bytes)"
    done
    echo ""
}

# Determine restore date
RESTORE_DATE=""
if [ -z "$1" ]; then
    echo "🔍 No timestamp provided, searching for latest backup..."
    RESTORE_DATE=$(find_latest_backup)
    if [ -z "$RESTORE_DATE" ]; then
        echo "❌ No backups found in $BACKUP_DIR"
        list_backups
        echo "💡 Usage: $0 [DATE]"
        echo "   Example: $0 20250906-143000"
        echo "   Or run without arguments to use latest backup"
        exit 1
    fi
    echo "✅ Found latest backup: $RESTORE_DATE"
else
    RESTORE_DATE="$1"
    echo "🎯 Using specified backup: $RESTORE_DATE"
fi

echo "📅 Restore timestamp: $RESTORE_DATE"
echo ""

# Check if consolidated backup exists
CONSOLIDATED_BACKUP="$BACKUP_DIR/${RESTORE_DATE}-complete-backup.tar.gz"
if [ ! -f "$CONSOLIDATED_BACKUP" ]; then
    echo "❌ Consolidated backup file not found: $CONSOLIDATED_BACKUP"
    echo ""
    list_backups
    exit 1
fi

# Create temporary directory for extraction
TEMP_DIR="$BACKUP_DIR/restore-temp-$RESTORE_DATE"
mkdir -p "$TEMP_DIR"

echo "📦 Extracting consolidated backup..."
echo "   From: $CONSOLIDATED_BACKUP"
echo "   To: $TEMP_DIR"

# Extract consolidated backup
cd "$TEMP_DIR"
tar xzf "../$(basename "$CONSOLIDATED_BACKUP")"
cd - > /dev/null

echo "✅ Backup extracted successfully!"
echo ""

# Restore database
BACKUP_DB="$TEMP_DIR/${RESTORE_DATE}-db.sql.gz"
if [ -f "$BACKUP_DB" ]; then
    echo "🗄️  Restoring database from $BACKUP_DB..."
    ddev import-db --file="$BACKUP_DB"
    echo "   ✅ Database restored successfully"
else
    echo "   ❌ Database file not found for date $RESTORE_DATE."
fi

# Restore uploads
BACKUP_UPLOADS="$TEMP_DIR/${RESTORE_DATE}-uploads.tar.gz"
if [ -f "$BACKUP_UPLOADS" ]; then
    echo "🗂️  Restoring uploads from $BACKUP_UPLOADS..."
    tar xzf "$BACKUP_UPLOADS"
    echo "   ✅ Uploads restored successfully"
else
    echo "   ❌ Uploads file not found for date $RESTORE_DATE."
fi

# Restore plugins
BACKUP_PLUGINS="$TEMP_DIR/${RESTORE_DATE}-plugins.tar.gz"
if [ -f "$BACKUP_PLUGINS" ]; then
    echo "🗂️  Restoring plugins from $BACKUP_PLUGINS..."
    tar xzf "$BACKUP_PLUGINS"
    echo "   ✅ Plugins restored successfully"
else
    echo "   ❌ Plugins file not found for date $RESTORE_DATE."
fi

# Restore themes
BACKUP_THEMES="$TEMP_DIR/${RESTORE_DATE}-themes.tar.gz"
if [ -f "$BACKUP_THEMES" ]; then
    echo "🗂️  Restoring themes from $BACKUP_THEMES..."
    tar xzf "$BACKUP_THEMES"
    echo "   ✅ Themes restored successfully"
else
    echo "   ❌ Themes file not found for date $RESTORE_DATE."
fi

echo ""
echo "🧹 Cleaning up temporary files..."
rm -rf "$TEMP_DIR"

echo ""
echo "✅ Restore completed successfully!"
echo "📊 Restore details:"
echo "   📁 Source: $CONSOLIDATED_BACKUP"
echo "   📅 Backup date: $RESTORE_DATE"
echo ""
echo "🌐 Access your restored site:"
echo "   Site: https://smart-gallery.ddev.site"
echo "   Admin: https://smart-gallery.ddev.site/wp-admin"