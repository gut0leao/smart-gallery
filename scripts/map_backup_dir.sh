#!/bin/bash

# Script to map a local host directory to the backups directory at the project root
# Usage: ./scripts/map_backup_dir.sh /path/to/backup/directory
# It's recommended that the local host directory be a 
# directory synchronized with some cloud service, like OneDrive.

set -e

if [ -z "$1" ]; then
  echo "⚠️ Usage: ./scripts/map_backup_dir.sh /path/to/backup/directory"
  exit 1
fi

ORIG_DIR="$1"
DEST_DIR="$(pwd)/backups"

# Remove old symlink, if it exists
if [ -L "$DEST_DIR" ]; then
  rm "$DEST_DIR"
fi

# Create symlink
ln -sfn "$ORIG_DIR" "$DEST_DIR"

echo "✅ Backup directory mapped: $ORIG_DIR -> $DEST_DIR"
