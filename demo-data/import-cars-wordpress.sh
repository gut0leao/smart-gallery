#!/bin/bash

# Script to import cars using WP-CLI
# Usage: ./import-cars-wordpress.sh

echo "🚗 Car Demo Data Import for WordPress"
echo "====================================="
echo ""

# Check if we're in DDEV
if [ ! -f ".ddev/config.yaml" ]; then
    echo "❌ This script should be run from a DDEV project root"
    exit 1
fi

# Check if WP-CLI is available
echo "🔍 Checking WP-CLI availability..."
if ! ddev wp --info > /dev/null 2>&1; then
    echo "❌ WP-CLI not available in DDEV"
    exit 1
fi

echo "✅ WP-CLI is available"
echo ""

# Load the WP-CLI command and run dry-run
echo "📥 Running car import (dry-run mode)..."
echo ""
ddev exec "cd /var/www/html && wp eval '
require_once(\"demo-data/car-import-command.php\");
\$command = new Car_Import_Command();
\$command->import([], [\"dry-run\" => true]);
'"

echo ""
read -p "🤔 Do you want to proceed with the actual import? (y/N): " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo ""
    echo "🚀 Starting actual import..."
    echo ""
    
    # Run actual import
    ddev exec "cd /var/www/html && wp eval '
require_once(\"demo-data/car-import-command.php\");
\$command = new Car_Import_Command();
\$command->import([], []);
'"
    
    echo ""
    echo "🎉 Import completed!"
    echo ""
    echo "📊 You can now check your WordPress admin:"
    echo "   - Cars CPT: /wp-admin/edit.php?post_type=car"
    echo "   - Brands: /wp-admin/edit-tags.php?taxonomy=car_brand&post_type=car"
    echo "   - Categories: /wp-admin/edit-tags.php?taxonomy=car_category&post_type=car"
    echo ""
    echo "🧹 To cleanup later, run: ddev wp car-demo cleanup --confirm"
else
    echo ""
    echo "❌ Import cancelled"
fi
